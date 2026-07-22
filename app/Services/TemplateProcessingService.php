<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class TemplateProcessingService
{
    private const MAX_BYTES = 5 * 1024 * 1024;

    private const ALIASES = [
        'name' => 'full_name',
        'fullname' => 'full_name',
        'full_name' => 'full_name',
        'title' => 'job_title',
        'role' => 'job_title',
        'jobtitle' => 'job_title',
        'job_title' => 'job_title',
        'professional_summary' => 'summary',
        'profile_summary' => 'summary',
        'work_experience' => 'experiences',
        'experience' => 'experiences',
        'experiences' => 'experiences',
        'educations' => 'education',
        'education' => 'education',
        'skill' => 'skills',
        'skills' => 'skills',
        'project' => 'projects',
        'projects' => 'projects',
        'certification' => 'certifications',
        'certifications' => 'certifications',
        'language' => 'languages',
        'languages' => 'languages',
        'award' => 'awards',
        'awards' => 'awards',
        'reference' => 'references',
        'references' => 'references',
        'socials' => 'social_links',
        'social_links' => 'social_links',
    ];

    private const SECTION_LABELS = [
        'summary' => ['summary', 'professional summary', 'profile', 'career profile', 'objective', 'about me'],
        'experiences' => ['experience', 'work experience', 'professional experience', 'employment', 'employment history', 'career history'],
        'education' => ['education', 'academic background', 'academic history', 'qualifications'],
        'skills' => ['skills', 'technical skills', 'core skills', 'competencies', 'expertise'],
        'projects' => ['projects', 'key projects', 'personal projects', 'selected projects'],
        'languages' => ['languages', 'language proficiency'],
        'certifications' => ['certifications', 'certificates', 'licenses', 'licenses and certifications'],
        'awards' => ['awards', 'honors', 'awards and honors', 'achievements'],
        'references' => ['references', 'professional references'],
    ];

    public function __construct(private readonly TemplateRenderingService $renderer) {}

    /**
     * @return array{html:string,source_type:string,extension:string,mime_type:string,original_name:string,size_bytes:int,checksum:string,placeholders:array<int,string>,mapping_candidates:array<int,array<string,string>>,requires_mapping:bool,detected_fields:array<int,string>}
     */
    public function process(UploadedFile $file): array
    {
        $sourceType = $this->detectType($file);
        $source = file_get_contents($file->getRealPath());

        if (! is_string($source) || trim($source) === '') {
            $this->fail('The uploaded template is empty or cannot be read.');
        }
        if (strlen($source) > self::MAX_BYTES) {
            $this->fail('The template source must not exceed 5MB.');
        }
        if (str_contains($source, "\0")) {
            $this->fail('Binary files are not valid template sources.');
        }

        $normalized = $this->normalizePlaceholders($source);
        $html = $sourceType === 'latex' ? $this->latexToHtml($normalized) : $normalized;
        $html = $this->normalizeDocument($html, $sourceType);
        $this->validateSafety($html);

        $analysis = $this->analyzeDocument($html);
        $html = $analysis['html'];

        $placeholders = $this->extractPlaceholders($html);
        $unknown = array_values(array_diff($placeholders, $this->renderer->allowedPlaceholders()));
        if ($unknown !== []) {
            $this->fail('Unknown placeholder(s): '.implode(', ', $unknown).'.');
        }

        $hasIdentity = in_array('full_name', $placeholders, true);
        $hasContent = array_intersect($placeholders, array_keys(self::SECTION_LABELS)) !== [];
        $requiresMapping = $analysis['mapping_candidates'] !== [] || ! $hasIdentity || ! $hasContent;

        return [
            'html' => $html,
            'source_type' => $sourceType,
            'extension' => strtolower($file->getClientOriginalExtension()),
            'mime_type' => $file->getMimeType() ?: 'text/plain',
            'original_name' => $file->getClientOriginalName(),
            'size_bytes' => strlen($source),
            'checksum' => hash('sha256', $source),
            'placeholders' => $placeholders,
            'mapping_candidates' => $analysis['mapping_candidates'],
            'requires_mapping' => $requiresMapping,
            'detected_fields' => $analysis['detected_fields'],
        ];
    }

    /**
     * @param  array<string,string|null>  $mappings
     * @param  array<int,array<string,string>>  $candidates
     * @return array{html:string,placeholders:array<int,string>,mapping_candidates:array<int,array<string,string>>,requires_mapping:bool}
     */
    public function applyMappings(string $html, array $mappings, array $candidates): array
    {
        [$document, $xpath] = $this->loadDom($html);
        $remaining = [];

        foreach ($candidates as $candidate) {
            $id = (string) ($candidate['id'] ?? '');
            $placeholder = $this->canonical((string) ($mappings[$id] ?? ''));
            $nodes = $id !== '' ? $xpath->query('//*[@data-rh-map-id="'.$id.'"]') : false;
            $node = $nodes && $nodes->length ? $nodes->item(0) : null;

            if (! $node instanceof DOMElement || ! in_array($placeholder, $this->renderer->allowedPlaceholders(), true)) {
                $remaining[] = $candidate;
                continue;
            }

            if (($candidate['kind'] ?? '') === 'image') {
                $node->setAttribute('src', '{{ '.$placeholder.' }}');
                $node->removeAttribute('srcset');
            } else {
                $this->replaceElementContent($node, '{{ '.$placeholder.' }}');
            }
            $node->removeAttribute('data-rh-map-id');
        }

        $processed = $this->saveDom($document);
        $placeholders = $this->extractPlaceholders($processed);

        return [
            'html' => $processed,
            'placeholders' => $placeholders,
            'mapping_candidates' => $remaining,
            'requires_mapping' => $remaining !== []
                || ! in_array('full_name', $placeholders, true)
                || array_intersect($placeholders, array_keys(self::SECTION_LABELS)) === [],
        ];
    }

    /** @return array{html:string,mapping_candidates:array<int,array<string,string>>,detected_fields:array<int,string>} */
    private function analyzeDocument(string $html): array
    {
        [$document, $xpath] = $this->loadDom($html);
        $detected = $this->extractPlaceholders($html);
        $candidates = [];

        $headings = [];
        foreach ($xpath->query('//body//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6 or @role="heading"]') ?: [] as $heading) {
            if (! $heading instanceof DOMElement || str_contains($heading->textContent, '{{')) {
                continue;
            }
            $section = $this->detectSection($heading);
            if ($section !== null) {
                $headings[] = [$heading, $section];
            }
        }
        foreach ($headings as [$heading, $section]) {
            if (! in_array($section, $detected, true)) {
                $this->replaceSection($heading, $section);
                $detected[] = $section;
            }
        }

        foreach ($xpath->query('//body//img') ?: [] as $image) {
            if (! $image instanceof DOMElement || $image->getAttribute('src') === '{{ photo }}') {
                continue;
            }
            $hint = $this->elementHint($image);
            if (preg_match('/\b(photo|avatar|portrait|headshot|profile[-_ ]?image)\b/i', $hint)) {
                $image->setAttribute('src', '{{ photo }}');
                $image->removeAttribute('srcset');
                $detected[] = 'photo';
            }
        }
        if (! in_array('photo', $detected, true)) {
            $image = $xpath->query('//body//img[@src]')->item(0);
            if ($image instanceof DOMElement) {
                $image->setAttribute('src', '{{ photo }}');
                $image->removeAttribute('srcset');
                $detected[] = 'photo';
            }
        }

        $elements = iterator_to_array($xpath->query('//body//*[not(self::script or self::style or self::svg)]') ?: []);
        foreach ($elements as $element) {
            if (! $element instanceof DOMElement || $this->hasElementChildren($element) || str_contains($element->textContent, '{{')) {
                continue;
            }

            $text = trim(preg_replace('/\s+/u', ' ', $element->textContent) ?? '');
            if ($text === '' || mb_strlen($text) > 180) {
                continue;
            }
            $hint = $this->elementHint($element);
            $placeholder = $this->detectPersonalField($element, $text, $hint);
            if ($placeholder !== null && ! in_array($placeholder, $detected, true)) {
                $this->replaceElementContent($element, '{{ '.$placeholder.' }}');
                $detected[] = $placeholder;
                continue;
            }

            if ($this->looksMappable($element, $text, $hint)) {
                $id = 'field_'.substr(hash('sha256', $this->domPath($element).'|'.$text), 0, 12);
                $element->setAttribute('data-rh-map-id', $id);
                $candidates[] = [
                    'id' => $id,
                    'label' => $this->candidateLabel($hint, $text),
                    'preview' => mb_substr($text, 0, 100),
                    'kind' => 'text',
                    'suggested_placeholder' => $this->suggestPlaceholder($hint),
                ];
            }
        }

        if (! in_array('full_name', $detected, true)) {
            $nameNode = $xpath->query('//body//h1[normalize-space()] | //body//*[contains(translate(@class,"NAME","name"),"name")][normalize-space()] | //body//strong[normalize-space()]')->item(0);
            if ($nameNode instanceof DOMElement && ! str_contains($nameNode->textContent, '{{')) {
                $id = $this->markCandidate($nameNode, 'Full name', 'full_name', 'text');
                $candidates[] = $id;
            } else {
                $body = $document->getElementsByTagName('body')->item(0);
                if ($body instanceof DOMElement) {
                    $nameNode = $document->createElement('div');
                    $nameNode->setAttribute('class', 'rh-auto-identity');
                    $body->insertBefore($nameNode, $body->firstChild);
                    $candidates[] = $this->markCandidate($nameNode, 'Name field was not detected', 'full_name', 'text');
                }
            }
        }

        if (array_intersect($detected, array_keys(self::SECTION_LABELS)) === []) {
            $container = $xpath->query('//body//section[.//*[self::h2 or self::h3]] | //body//article[.//*[self::h2 or self::h3]]')->item(0);
            if ($container instanceof DOMElement && ! str_contains($container->textContent, '{{')) {
                $candidate = $this->markCandidate($container, 'Resume section: '.mb_substr(trim($container->textContent), 0, 35), '', 'section');
                $candidates[] = $candidate;
            } else {
                $body = $document->getElementsByTagName('body')->item(0);
                if ($body instanceof DOMElement) {
                    $container = $document->createElement('section');
                    $container->setAttribute('class', 'rh-auto-section');
                    $body->appendChild($container);
                    $candidates[] = $this->markCandidate($container, 'Resume content section was not detected', 'summary', 'section');
                }
            }
        }

        $detected = array_values(array_unique($detected));

        return [
            'html' => $this->saveDom($document),
            'mapping_candidates' => $this->uniqueCandidates($candidates),
            'detected_fields' => $detected,
        ];
    }

    /** @return array{0:DOMDocument,1:DOMXPath} */
    private function loadDom(string $html): array
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded || ! $document->getElementsByTagName('body')->length) {
            $this->fail('The template could not be parsed as a valid HTML document. Correct the markup and upload it again.');
        }
        $fatal = collect($errors)->first(fn ($error) => $error->level === LIBXML_ERR_FATAL);
        if ($fatal) {
            $this->fail('The template contains invalid HTML near line '.$fatal->line.': '.trim($fatal->message));
        }

        foreach (iterator_to_array($document->childNodes) as $node) {
            if ($node->nodeType === XML_PI_NODE) {
                $document->removeChild($node);
            }
        }

        return [$document, new DOMXPath($document)];
    }

    private function saveDom(DOMDocument $document): string
    {
        $html = $document->saveHTML();
        if (! is_string($html) || trim($html) === '') {
            $this->fail('The processed template could not be serialized.');
        }

        $html = preg_replace_callback('/%7B%7B(?:%20|\+)*([a-z_]+)(?:%20|\+)*%7D%7D/i', fn (array $match): string => '{{ '.strtolower($match[1]).' }}', $html) ?? $html;

        return $html;
    }

    private function detectSection(DOMElement $element): ?string
    {
        $text = $this->normalizeLabel($element->textContent);
        $hint = $this->normalizeLabel($this->elementHint($element));
        foreach (self::SECTION_LABELS as $placeholder => $labels) {
            foreach ($labels as $label) {
                if ($text === $label || preg_match('/\b'.preg_quote($label, '/').'\b/', $hint)) {
                    return $placeholder;
                }
            }
        }

        return null;
    }

    private function replaceSection(DOMElement $heading, string $placeholder): void
    {
        $parent = $heading->parentNode;
        if (! $parent instanceof DOMElement) {
            return;
        }

        $cursor = $heading->nextSibling;
        $inserted = false;
        while ($cursor) {
            $next = $cursor->nextSibling;
            if ($cursor instanceof DOMElement && $this->detectSection($cursor) !== null) {
                break;
            }
            if (! $inserted) {
                $parent->insertBefore($heading->ownerDocument->createTextNode('{{ '.$placeholder.' }}'), $cursor);
                $inserted = true;
            }
            $parent->removeChild($cursor);
            $cursor = $next;
        }
        if (! $inserted) {
            $parent->appendChild($heading->ownerDocument->createTextNode('{{ '.$placeholder.' }}'));
        }
    }

    private function detectPersonalField(DOMElement $element, string $text, string $hint): ?string
    {
        if (filter_var($text, FILTER_VALIDATE_EMAIL) || preg_match('/\b[\w.+-]+@[\w.-]+\.[a-z]{2,}\b/i', $text)) {
            return 'email';
        }
        if (preg_match('/(?:\+?\d[\d\s().-]{7,}\d)/', $text)) {
            return 'phone';
        }
        if (preg_match('/linkedin(?:\.com)?/i', $text.' '.$hint)) {
            return 'social_links';
        }
        if (preg_match('/github(?:\.com)?/i', $text.' '.$hint)) {
            return 'social_links';
        }
        if (preg_match('/\b(full[-_ ]?name|candidate[-_ ]?name|profile[-_ ]?name)\b/i', $hint)) {
            return 'full_name';
        }
        if ($element->tagName === 'h1' && mb_strlen($text) <= 80 && $this->detectSection($element) === null) {
            return 'full_name';
        }
        if (preg_match('/\b(job[-_ ]?title|headline|profession|occupation|designation)\b/i', $hint)) {
            return 'job_title';
        }
        $previous = $this->previousElementSibling($element);
        if (in_array($element->tagName, ['p', 'h2', 'h3', 'span'], true)
            && $previous instanceof DOMElement
            && ($previous->tagName === 'h1' || str_contains($previous->textContent, '{{ full_name }}'))
            && mb_strlen($text) <= 80) {
            return 'job_title';
        }
        if (preg_match('/\b(location|address|city|residence)\b/i', $hint)) {
            return 'location';
        }
        if (preg_match('/\b(website|portfolio|personal[-_ ]?url)\b/i', $hint) || filter_var($text, FILTER_VALIDATE_URL)) {
            return 'website';
        }

        return null;
    }

    private function looksMappable(DOMElement $element, string $text, string $hint): bool
    {
        if (preg_match('/\b(name|title|headline|email|phone|mobile|contact|address|location|city|linkedin|github|website|portfolio)\b/i', $hint)) {
            return true;
        }

        return false;
    }

    private function previousElementSibling(DOMElement $element): ?DOMElement
    {
        for ($node = $element->previousSibling; $node; $node = $node->previousSibling) {
            if ($node instanceof DOMElement) {
                return $node;
            }
        }

        return null;
    }

    private function markCandidate(DOMElement $element, string $label, string $suggested, string $kind): array
    {
        $text = trim(preg_replace('/\s+/u', ' ', $element->textContent) ?? '');
        $id = 'field_'.substr(hash('sha256', $this->domPath($element).'|'.$text), 0, 12);
        $element->setAttribute('data-rh-map-id', $id);

        return ['id' => $id, 'label' => $label, 'preview' => mb_substr($text, 0, 100), 'kind' => $kind, 'suggested_placeholder' => $suggested];
    }

    private function replaceElementContent(DOMElement $element, string $replacement): void
    {
        while ($element->firstChild) {
            $element->removeChild($element->firstChild);
        }
        $element->appendChild($element->ownerDocument->createTextNode($replacement));
    }

    private function hasElementChildren(DOMElement $element): bool
    {
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                return true;
            }
        }

        return false;
    }

    private function elementHint(DOMElement $element): string
    {
        return implode(' ', [$element->tagName, $element->getAttribute('id'), $element->getAttribute('class'), $element->getAttribute('aria-label'), $element->getAttribute('itemprop'), $element->getAttribute('href'), $element->getAttribute('alt')]);
    }

    private function normalizeLabel(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', strtolower(preg_replace('/[^\pL\pN]+/u', ' ', $value) ?? $value)) ?? '');
    }

    private function domPath(DOMNode $node): string
    {
        $parts = [];
        while ($node instanceof DOMElement) {
            $index = 1;
            for ($sibling = $node->previousSibling; $sibling; $sibling = $sibling->previousSibling) {
                if ($sibling instanceof DOMElement && $sibling->tagName === $node->tagName) {
                    $index++;
                }
            }
            array_unshift($parts, $node->tagName.'['.$index.']');
            $node = $node->parentNode;
        }

        return '/'.implode('/', $parts);
    }

    private function candidateLabel(string $hint, string $text): string
    {
        $clean = trim(preg_replace('/[^a-z0-9]+/i', ' ', $hint) ?? '');

        return $clean !== '' ? ucfirst(mb_substr($clean, 0, 60)) : 'Unmapped field: '.mb_substr($text, 0, 30);
    }

    private function suggestPlaceholder(string $hint): string
    {
        foreach (['full_name' => 'name', 'job_title' => 'title|headline', 'email' => 'email', 'phone' => 'phone|mobile', 'location' => 'location|address|city', 'website' => 'website|portfolio'] as $placeholder => $pattern) {
            if (preg_match('/'.$pattern.'/i', $hint)) {
                return $placeholder;
            }
        }

        return '';
    }

    /** @param array<int,array<string,string>> $candidates */
    private function uniqueCandidates(array $candidates): array
    {
        $seen = [];

        return array_values(array_filter($candidates, function (array $candidate) use (&$seen): bool {
            if (isset($seen[$candidate['id']])) {
                return false;
            }
            $seen[$candidate['id']] = true;
            return true;
        }));
    }

    private function detectType(UploadedFile $file): string
    {
        return match (strtolower($file->getClientOriginalExtension())) {
            'html', 'htm' => 'html',
            'txt' => 'txt',
            'tex' => 'latex',
            default => $this->fail('Unsupported template type. Upload an HTML (.html), TXT (.txt), or LaTeX (.tex) file.'),
        };
    }

    private function normalizePlaceholders(string $source): string
    {
        $patterns = [
            '/{{\s*([a-z][a-z0-9_-]*)\s*}}/i',
            '/\[\[\s*([a-z][a-z0-9_-]*)\s*\]\]/i',
            '/\$\{\s*([a-z][a-z0-9_-]*)\s*}/i',
            '/%%\s*([a-z][a-z0-9_-]*)\s*%%/i',
            '/\\\\(?:resumehub|placeholder)\s*\{\s*([a-z][a-z0-9_-]*)\s*}/i',
        ];

        foreach ($patterns as $pattern) {
            $source = preg_replace_callback($pattern, fn (array $match): string => '{{ '.$this->canonical($match[1]).' }}', $source) ?? $source;
        }

        $latexCommands = [
            'FullName' => 'full_name', 'JobTitle' => 'job_title', 'Email' => 'email', 'Phone' => 'phone',
            'Website' => 'website', 'Location' => 'location', 'Summary' => 'summary',
            'Experiences' => 'experiences', 'Education' => 'education', 'Skills' => 'skills',
            'Projects' => 'projects', 'Certifications' => 'certifications', 'Languages' => 'languages',
            'Awards' => 'awards', 'References' => 'references',
        ];
        foreach ($latexCommands as $command => $placeholder) {
            $source = preg_replace('/\\\\'.preg_quote($command, '/').'\b/i', '{{ '.$placeholder.' }}', $source) ?? $source;
        }

        return $source;
    }

    private function canonical(string $name): string
    {
        $key = strtolower(str_replace('-', '_', trim($name)));

        return self::ALIASES[$key] ?? $key;
    }

    private function latexToHtml(string $latex): string
    {
        $latex = str_replace(["\r\n", "\r"], "\n", $latex);
        $latex = preg_replace('/(?<!\\\\)%.*$/m', '', $latex) ?? $latex;
        if (preg_match('/\\\\begin\s*\{document}/i', $latex, $start, PREG_OFFSET_CAPTURE)
            && preg_match('/\\\\end\s*\{document}/i', $latex, $end, PREG_OFFSET_CAPTURE)) {
            $offset = $start[0][1] + strlen($start[0][0]);
            $latex = substr($latex, $offset, max(0, $end[0][1] - $offset));
        }

        $tokens = [];
        $latex = preg_replace_callback('/{{\s*([a-z_]+)\s*}}/i', function (array $match) use (&$tokens): string {
            $token = '@@RH_PLACEHOLDER_'.count($tokens).'@@';
            $tokens[$token] = '{{ '.strtolower($match[1]).' }}';
            return $token;
        }, $latex) ?? $latex;

        $body = htmlspecialchars($latex, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $body = preg_replace('/\\\\(?:documentclass|usepackage|geometry|pagestyle|thispagestyle)(?:\[[^\]]*])?\{[^{}]*}/i', '', $body) ?? $body;
        $body = preg_replace('/\\\\(?:begin|end)\{(?:center|flushleft|flushright|minipage|document)}/i', '', $body) ?? $body;
        $body = preg_replace('/\\\\section\*?\{([^{}]*)}/i', '<h2>$1</h2>', $body) ?? $body;
        $body = preg_replace('/\\\\subsection\*?\{([^{}]*)}/i', '<h3>$1</h3>', $body) ?? $body;
        $body = preg_replace('/\\\\(?:textbf|textsc)\{([^{}]*)}/i', '<strong>$1</strong>', $body) ?? $body;
        $body = preg_replace('/\\\\(?:emph|textit)\{([^{}]*)}/i', '<em>$1</em>', $body) ?? $body;
        $body = preg_replace('/\\\\underline\{([^{}]*)}/i', '<u>$1</u>', $body) ?? $body;
        $body = preg_replace('/\\\\begin\{(?:itemize|enumerate)}/i', '<ul>', $body) ?? $body;
        $body = preg_replace('/\\\\end\{(?:itemize|enumerate)}/i', '</ul>', $body) ?? $body;
        $body = preg_replace('/^[ \t]*\\\\item\s+(.+)$/m', '<li>$1</li>', $body) ?? $body;
        $body = preg_replace('/\\\\href\{(https?:\/\/[^{}\s]+)}\{([^{}]*)}/i', '<a href="$1">$2</a>', $body) ?? $body;
        $body = preg_replace('/\\\\(?:vspace|hspace)\*?\{[^{}]*}/i', '', $body) ?? $body;
        $body = preg_replace('/\\\\(?:smallskip|medskip|bigskip|noindent)\b/i', '', $body) ?? $body;
        $body = preg_replace('/\\\\[a-zA-Z@]+\*?(?:\[[^\]]*])?\{([^{}]*)}/', '$1', $body) ?? $body;
        $body = preg_replace('/\\\\[a-zA-Z@]+\*?(?:\[[^\]]*])?/', '', $body) ?? $body;
        $body = str_replace(['\\&amp;', '\\%', '\\#', '\\_', '\\{', '\\}'], ['&amp;', '%', '#', '_', '{', '}'], $body);
        $body = preg_replace('/\\\\\\\\\s*/', '<br>', $body) ?? $body;
        $body = preg_replace('/(?:\n\s*){2,}/', "</p>\n<p>", trim($body)) ?? $body;
        $body = '<p>'.$body.'</p>';
        $body = str_replace(['<p><h2>', '</h2></p>', '<p><h3>', '</h3></p>', '<p><ul>', '</ul></p>'], ['<h2>', '</h2>', '<h3>', '</h3>', '<ul>', '</ul>'], $body);
        $body = strtr($body, $tokens);

        return '<!doctype html><html><head><meta charset="utf-8"><style>'.$this->latexStyles().'</style></head><body><main class="latex-resume">'.$body.'</main></body></html>';
    }

    private function latexStyles(): string
    {
        return '*{box-sizing:border-box}html,body{margin:0;min-height:100%;font-family:var(--rh-body-font,Inter),Arial,sans-serif;color:#172033;background:var(--rh-page-bg,#fff)}body{padding:42px;font-size:calc(11px * var(--rh-font-scale,1));line-height:1.55}.latex-resume{max-width:100%;overflow-wrap:anywhere}.latex-resume h1{margin:0 0 8px;font-family:var(--rh-heading-font,Inter),sans-serif;font-size:28px}.latex-resume h2{margin:calc(var(--rh-section-gap,20px) * 1.1) 0 10px;padding-bottom:6px;font-size:13px;text-transform:uppercase;letter-spacing:.06em;position:relative}.latex-resume h2::after{content:"";display:var(--rh-divider-display,block);position:absolute;bottom:0;left:0;right:0;height:1px;background-color:var(--rh-accent,#4f46e5)}.latex-resume h3{margin:14px 0 5px;font-size:11px}.latex-resume p{margin:0 0 10px}.latex-resume ul{margin:7px 0 12px;padding-left:18px}.rh-section{margin:0 0 var(--rh-section-gap,18px)}.rh-section h2{margin:0 0 8px}.rh-item{margin-bottom:calc(var(--rh-section-gap,18px) * 0.67)}.rh-item-head{display:flex;justify-content:space-between;gap:14px}.rh-item time{white-space:nowrap;color:#64748b}.rh-meta{color:var(--rh-accent,#4f46e5)}.rh-tag{display:inline-block;margin:0 5px 5px 0;padding:4px 7px;border-radius:4px;background:#eef2ff}';
    }

    private function txtStyles(): string
    {
        return '*{box-sizing:border-box}html,body{margin:0;min-height:100%;font-family:var(--rh-body-font,Inter),Arial,sans-serif;color:#172033;background:var(--rh-page-bg,#fff)}body{padding:42px;font-size:calc(11px * var(--rh-font-scale,1));line-height:1.55}.rh-section{margin-bottom:var(--rh-section-gap,18px)}.rh-section h2{margin:0 0 8px;font-family:var(--rh-heading-font,var(--rh-body-font,Inter)),sans-serif;color:var(--rh-accent,#4f46e5);position:relative;padding-bottom:5px}.rh-section h2::after{content:"";display:var(--rh-divider-display,block);position:absolute;bottom:0;left:0;right:0;height:1px;background-color:var(--rh-accent,#4f46e5)}.rh-item{margin-bottom:calc(var(--rh-section-gap,18px) * 0.67)}.rh-item-head{display:flex;justify-content:space-between;gap:14px}.rh-item time{white-space:nowrap;color:#64748b}.rh-meta{color:var(--rh-accent,#4f46e5)}.rh-tag{display:inline-block;margin:0 5px 5px 0;padding:4px 7px;border-radius:4px;background:#eef2ff}';
    }

    private function normalizeDocument(string $html, string $sourceType = 'html'): string
    {
        $html = str_replace(["\r\n", "\r"], "\n", trim($html));
        if (! preg_match('/<\s*[a-z][^>]*>/i', $html)) {
            $this->fail('The processed template does not contain valid HTML markup.');
        }

        $defaultStyles = '';
        if ($sourceType === 'txt' || !str_contains($html, '<style')) {
            $defaultStyles = '<style>' . $this->txtStyles() . '</style>';
        }

        if (! preg_match('/<html\b/i', $html)) {
            $html = '<!doctype html><html><head><meta charset="utf-8">' . $defaultStyles . '</head><body>'.$html.'</body></html>';
        }
        if (! preg_match('/<head\b/i', $html)) {
            $html = preg_replace('/<html\b[^>]*>/i', '$0<head><meta charset="utf-8">' . $defaultStyles . '</head>', $html, 1) ?? $html;
        } else if ($defaultStyles !== '') {
            $html = preg_replace('/<head\b[^>]*>/i', '$0' . $defaultStyles, $html, 1) ?? $html;
        }
        if (! preg_match('/<body\b/i', $html)) {
            $html = preg_replace('/<\/head>/i', '</head><body>', $html, 1) ?? $html;
            $html = preg_replace('/<\/html>/i', '</body></html>', $html, 1) ?? $html;
        }

        return $html;
    }

    private function validateSafety(string $html): void
    {
        if (preg_match('/<(script|iframe|object|embed|form|base|link|foreignObject)\b/i', $html)
            || preg_match('/\son[a-z]+\s*=/i', $html)
            || preg_match('/(?:javascript:|vbscript:|data:text\/html|@import|expression\s*\(|<\?)/i', $html)
            || preg_match('/<meta\b[^>]*http-equiv\s*=/i', $html)) {
            $this->fail('The template contains unsafe executable, remote, or embedded content.');
        }
    }

    /** @return array<int,string> */
    private function extractPlaceholders(string $html): array
    {
        preg_match_all('/{{\s*([a-z_]+)\s*}}/i', $html, $matches);

        return collect($matches[1] ?? [])->map(fn (string $name): string => strtolower($name))->unique()->sort()->values()->all();
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['template_file' => $message]);
    }
}
