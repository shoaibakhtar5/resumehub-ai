<?php

return [
    'marketing_nav' => [
        ['label' => 'Home', 'route' => 'home'],
        ['label' => 'Features', 'route' => 'features'],
        ['label' => 'Pricing', 'route' => 'pricing'],
        ['label' => 'Blog', 'route' => 'blog.index'],
        ['label' => 'Contact', 'route' => 'contact'],
    ],

    'feature_cards' => [
        ['icon' => 'sparkles', 'title' => 'AI Resume Studio', 'body' => 'Rewrite bullets, tune tone, and tailor every section to the role you want.'],
        ['icon' => 'shield-check', 'title' => 'ATS Checker', 'body' => 'Scan structure, keywords, section clarity, and parsing risk before you apply.'],
        ['icon' => 'squares-2x2', 'title' => 'Premium Templates', 'body' => 'Choose polished layouts designed for designers, operators, engineers, and executives.'],
        ['icon' => 'chat-bubble-left-right', 'title' => 'Interview Prep', 'body' => 'Generate role-specific questions and model answers from the resume you already built.'],
    ],

    'pricing_plans' => [
        ['name' => 'Starter', 'price' => '$0', 'body' => 'For quick resumes and template exploration.', 'features' => ['1 active resume', '3 template exports', 'Basic ATS checks']],
        ['name' => 'Pro', 'price' => '$14', 'body' => 'For active job seekers who need AI acceleration.', 'features' => ['Unlimited resumes', 'AI tailoring studio', 'Cover letters and interview prep'], 'featured' => true],
        ['name' => 'Team', 'price' => '$49', 'body' => 'For universities, bootcamps, and career teams.', 'features' => ['Shared template library', 'Admin analytics', 'Seat and role management']],
    ],

    'faqs' => [
        ['question' => 'Can ResumeHub AI improve an existing resume?', 'answer' => 'Yes. Import your content, run an ATS scan, and use AI suggestions to strengthen impact, keywords, and structure.'],
        ['question' => 'Are templates ATS friendly?', 'answer' => 'Templates are designed with clean hierarchy, readable text, and parser-safe sections while preserving a premium visual style.'],
        ['question' => 'Can I create cover letters too?', 'answer' => 'The cover letter generator uses the selected resume, role description, and company context to draft focused letters.'],
        ['question' => 'Does the admin area support teams?', 'answer' => 'The frontend includes team, roles, permissions, media, blog, SEO, AI, and analytics management surfaces.'],
    ],

    'blog_posts' => [
        [
            'slug' => 'resume-ai-launch',
            'title' => 'How AI resume workflows are changing job search quality',
            'excerpt' => 'A practical look at targeted resumes, ATS scanning, and faster iteration for modern candidates.',
            'date' => 'Jul 8, 2026',
            'read' => '6 min read',
            'category' => 'Product',
        ],
        [
            'slug' => 'ats-keyword-strategy',
            'title' => 'A sharper keyword strategy for ATS-friendly resumes',
            'excerpt' => 'How to balance human readability with role-specific keyword coverage.',
            'date' => 'Jul 3, 2026',
            'read' => '8 min read',
            'category' => 'Guides',
        ],
        [
            'slug' => 'portfolio-resume-templates',
            'title' => 'Choosing a resume template for creative and technical roles',
            'excerpt' => 'Template selection principles for clarity, personality, and scan performance.',
            'date' => 'Jun 27, 2026',
            'read' => '5 min read',
            'category' => 'Templates',
        ],
    ],

    'user_nav' => [
        [
            'label' => 'Studio',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home'],
                ['label' => 'AI Resume Studio', 'route' => 'ai.studio', 'icon' => 'sparkles'],
                ['label' => 'Resume Builder', 'route' => 'resume.builder', 'icon' => 'pencil-square'],
                ['label' => 'Resume Preview', 'route' => 'resume.preview', 'icon' => 'eye'],
            ],
        ],
        [
            'label' => 'Library',
            'items' => [
                ['label' => 'My Resumes', 'route' => 'resumes.index', 'icon' => 'document-text'],
                ['label' => 'Templates', 'route' => 'resume.templates', 'icon' => 'squares-2x2'],
                ['label' => 'Downloads', 'route' => 'downloads', 'icon' => 'arrow-down-tray'],
                ['label' => 'Shared Resumes', 'route' => 'shared-resumes', 'icon' => 'share'],
                ['label' => 'Favorites', 'route' => 'favorite-resumes', 'icon' => 'heart'],
                ['label' => 'Archived', 'route' => 'archived-resumes', 'icon' => 'archive-box'],
                ['label' => 'Version History', 'route' => 'version-history', 'icon' => 'clock'],
            ],
        ],
        [
            'label' => 'Optimization',
            'items' => [
                ['label' => 'ATS Checker', 'route' => 'ats.checker', 'icon' => 'shield-check'],
                ['label' => 'Resume Review', 'route' => 'resume.review', 'icon' => 'document-magnifying-glass'],
                ['label' => 'Resume Score', 'route' => 'resume.score', 'icon' => 'chart-bar'],
                ['label' => 'Cover Letter', 'route' => 'cover-letter', 'icon' => 'envelope'],
                ['label' => 'Interview Questions', 'route' => 'interview.questions', 'icon' => 'chat-bubble-left-right'],
                ['label' => 'Keyword Optimizer', 'route' => 'keyword.optimizer', 'icon' => 'command-line'],
            ],
        ],
        [
            'label' => 'Account',
            'items' => [
                ['label' => 'Profile', 'route' => 'profile.edit', 'icon' => 'user'],
                ['label' => 'Settings', 'route' => 'settings', 'icon' => 'cog-6-tooth'],
                ['label' => 'Notifications', 'route' => 'notifications', 'icon' => 'bell'],
            ],
        ],
    ],

    'admin_nav' => [
        [
            'label' => 'Control',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'chart-pie'],
                ['label' => 'Analytics', 'route' => 'admin.analytics', 'icon' => 'presentation-chart-line'],
                ['label' => 'Logs', 'route' => 'admin.logs', 'icon' => 'server-stack'],
            ],
        ],
        [
            'label' => 'Content',
            'items' => [
                ['label' => 'Users', 'route' => 'admin.users', 'icon' => 'users'],
                ['label' => 'Resumes', 'route' => 'admin.resumes', 'icon' => 'document-text'],
                ['label' => 'Templates', 'route' => 'admin.templates', 'icon' => 'squares-2x2'],
                ['label' => 'Upload Template', 'route' => 'admin.template-upload', 'icon' => 'cloud-arrow-up'],
                ['label' => 'Blog CMS', 'route' => 'admin.blog', 'icon' => 'newspaper'],
                ['label' => 'Media Library', 'route' => 'admin.media-library', 'icon' => 'photo'],
            ],
        ],
        [
            'label' => 'Taxonomy',
            'items' => [
                ['label' => 'Categories', 'route' => 'admin.categories', 'icon' => 'folder'],
                ['label' => 'Tags', 'route' => 'admin.tags', 'icon' => 'tag'],
                ['label' => 'Team Members', 'route' => 'admin.team', 'icon' => 'building-office'],
            ],
        ],
        [
            'label' => 'Settings',
            'items' => [
                ['label' => 'Website Settings', 'route' => 'admin.website-settings', 'icon' => 'globe-alt'],
                ['label' => 'SEO Settings', 'route' => 'admin.seo-settings', 'icon' => 'magnifying-glass'],
                ['label' => 'AI Settings', 'route' => 'admin.ai-settings', 'icon' => 'sparkles'],
                ['label' => 'Messages', 'route' => 'admin.contact-messages', 'icon' => 'inbox-stack'],
                ['label' => 'Roles', 'route' => 'admin.roles', 'icon' => 'key'],
                ['label' => 'Permissions', 'route' => 'admin.permissions', 'icon' => 'shield-check'],
            ],
        ],
    ],

    'user_pages' => [
        'my-resumes' => [
            'title' => 'My Resumes',
            'eyebrow' => 'Resume library',
            'description' => 'Manage active, tailored, shared, and draft resumes from one clean workspace.',
            'stats' => [
                ['label' => 'Active resumes', 'value' => '8', 'icon' => 'document-text', 'trend' => '+2 this week'],
                ['label' => 'Average score', 'value' => '92%', 'icon' => 'chart-bar', 'trend' => '+8 points'],
                ['label' => 'Tailored roles', 'value' => '14', 'icon' => 'briefcase', 'tone' => 'ai'],
            ],
            'cards' => [
                ['icon' => 'pencil-square', 'title' => 'Product Designer - Meta', 'body' => 'Updated with leadership metrics and interaction design keywords.'],
                ['icon' => 'shield-check', 'title' => 'Senior UX Resume', 'body' => 'ATS parser clarity is strong with improved section labeling.'],
                ['icon' => 'share', 'title' => 'Shared recruiter copy', 'body' => 'Public link is active and tracked for recruiter opens.'],
            ],
            'table' => [
                'headers' => ['Resume', 'Target Role', 'Score', 'Status'],
                'rows' => [
                    ['Alex Rivers - Product', 'Senior Product Designer', '96%', 'Ready'],
                    ['Alex Rivers - UX Lead', 'Design Manager', '91%', 'Needs keywords'],
                    ['Alex Rivers - Startup', 'Founding Designer', '88%', 'Draft'],
                ],
            ],
        ],
        'downloads' => [
            'title' => 'Downloads',
            'eyebrow' => 'Export center',
            'description' => 'Track PDF, DOCX, and share-link exports for every version you send.',
            'stats' => [
                ['label' => 'Exports', 'value' => '31', 'icon' => 'arrow-down-tray'],
                ['label' => 'PDF downloads', 'value' => '24', 'icon' => 'document-text'],
                ['label' => 'Share opens', 'value' => '118', 'icon' => 'eye', 'tone' => 'ai'],
            ],
            'cards' => [
                ['icon' => 'document-duplicate', 'title' => 'PDF export', 'body' => 'High fidelity layout with embedded typography and clean page breaks.'],
                ['icon' => 'arrow-down-tray', 'title' => 'DOCX package', 'body' => 'Editable version for recruiters and internal company forms.'],
                ['icon' => 'link', 'title' => 'Trackable link', 'body' => 'Live resume URL with analytics and controlled visibility.'],
            ],
            'table' => [
                'headers' => ['File', 'Format', 'Created', 'Activity'],
                'rows' => [
                    ['Senior Product Designer', 'PDF', 'Today', '14 opens'],
                    ['UX Leadership Resume', 'DOCX', 'Yesterday', 'Downloaded'],
                    ['Portfolio Resume', 'Share link', 'Jul 7', '6 opens'],
                ],
            ],
        ],
        'shared-resumes' => [
            'title' => 'Shared Resumes',
            'eyebrow' => 'Link management',
            'description' => 'Review public resume links, recruiter access, and open activity.',
            'stats' => [
                ['label' => 'Live links', 'value' => '5', 'icon' => 'share'],
                ['label' => 'Recruiter opens', 'value' => '42', 'icon' => 'eye'],
                ['label' => 'Protected links', 'value' => '3', 'icon' => 'lock-closed', 'tone' => 'success'],
            ],
            'cards' => [
                ['icon' => 'shield-check', 'title' => 'Password protected', 'body' => 'Sensitive links can require a passcode and expire automatically.'],
                ['icon' => 'presentation-chart-line', 'title' => 'Engagement timeline', 'body' => 'See when recruiters open and revisit each resume.'],
                ['icon' => 'arrow-path', 'title' => 'Version-aware links', 'body' => 'Shared URLs stay stable while published content updates.'],
            ],
            'table' => [
                'headers' => ['Resume', 'Visibility', 'Last Opened', 'Status'],
                'rows' => [
                    ['Meta Product Design', 'Public link', '12 minutes ago', 'Active'],
                    ['Stripe Product Designer', 'Passcode', 'Yesterday', 'Active'],
                    ['Archive 2025', 'Private', 'Never', 'Paused'],
                ],
            ],
        ],
        'favorite-resumes' => [
            'title' => 'Favorite Resumes',
            'eyebrow' => 'Pinned work',
            'description' => 'Keep high-performing resumes and trusted template combinations within quick reach.',
            'stats' => [
                ['label' => 'Favorites', 'value' => '6', 'icon' => 'heart'],
                ['label' => 'Top score', 'value' => '98%', 'icon' => 'chart-bar', 'tone' => 'success'],
                ['label' => 'Template mixes', 'value' => '4', 'icon' => 'squares-2x2'],
            ],
            'cards' => [
                ['icon' => 'heart', 'title' => 'Executive Flow', 'body' => 'Best performer for leadership and strategy applications.'],
                ['icon' => 'sparkles', 'title' => 'AI-optimized creative', 'body' => 'Strong storytelling resume for portfolio-forward roles.'],
                ['icon' => 'briefcase', 'title' => 'Operator profile', 'body' => 'Balanced business impact and operational metrics.'],
            ],
            'table' => [
                'headers' => ['Favorite', 'Use Case', 'Score', 'Last Edited'],
                'rows' => [
                    ['Product Leadership', 'FAANG design roles', '98%', 'Today'],
                    ['Founding Designer', 'Startup roles', '94%', 'Yesterday'],
                    ['Creative Director', 'Agency roles', '90%', 'Jul 6'],
                ],
            ],
        ],
        'archived-resumes' => [
            'title' => 'Archived Resumes',
            'eyebrow' => 'Quiet storage',
            'description' => 'Review older versions without cluttering active job-search work.',
            'stats' => [
                ['label' => 'Archived', 'value' => '12', 'icon' => 'archive-box'],
                ['label' => 'Recoverable', 'value' => '12', 'icon' => 'arrow-path'],
                ['label' => 'Storage used', 'value' => '41 MB', 'icon' => 'server-stack'],
            ],
            'cards' => [
                ['icon' => 'archive-box', 'title' => 'Version-safe archive', 'body' => 'Archived resumes keep exports, notes, scores, and template choices intact.'],
                ['icon' => 'arrow-path', 'title' => 'One-click restore', 'body' => 'Move any archive back into the active workspace with its history preserved.'],
                ['icon' => 'trash', 'title' => 'Clean deletion flow', 'body' => 'Permanent deletion is separated from archive actions for safer cleanup.'],
            ],
            'table' => [
                'headers' => ['Resume', 'Archived', 'Score', 'Restore'],
                'rows' => [
                    ['2025 Product Resume', 'Jun 12', '84%', 'Available'],
                    ['UX Writer Variant', 'May 22', '79%', 'Available'],
                    ['Legacy Portfolio', 'Apr 4', '81%', 'Available'],
                ],
            ],
        ],
        'version-history' => [
            'title' => 'Version History',
            'eyebrow' => 'Resume timeline',
            'description' => 'Compare edits, AI changes, exports, and score movement across every saved version.',
            'stats' => [
                ['label' => 'Saved versions', 'value' => '38', 'icon' => 'clock'],
                ['label' => 'AI revisions', 'value' => '21', 'icon' => 'sparkles', 'tone' => 'ai'],
                ['label' => 'Restores', 'value' => '3', 'icon' => 'arrow-path'],
            ],
            'cards' => [
                ['icon' => 'document-duplicate', 'title' => 'Compare content', 'body' => 'See what changed in bullets, skills, summary, and section order.'],
                ['icon' => 'chart-bar', 'title' => 'Score history', 'body' => 'Understand which edits increased ATS and recruiter clarity.'],
                ['icon' => 'arrow-path', 'title' => 'Restore safely', 'body' => 'Return to a previous version while preserving the current one.'],
            ],
            'table' => [
                'headers' => ['Version', 'Change', 'Score', 'Saved'],
                'rows' => [
                    ['v18', 'Added AI impact rewrite', '96%', '12 min ago'],
                    ['v17', 'Updated product metrics', '93%', 'Yesterday'],
                    ['v16', 'New template applied', '90%', 'Jul 7'],
                ],
            ],
        ],
        'settings' => [
            'title' => 'Settings',
            'eyebrow' => 'Workspace controls',
            'description' => 'Manage export preferences, privacy, theme behavior, AI defaults, and notification rules.',
            'stats' => [
                ['label' => 'AI defaults', 'value' => '4', 'icon' => 'sparkles', 'tone' => 'ai'],
                ['label' => 'Security rules', 'value' => '7', 'icon' => 'shield-check'],
                ['label' => 'Export presets', 'value' => '3', 'icon' => 'arrow-down-tray'],
            ],
            'cards' => [
                ['icon' => 'cog-6-tooth', 'title' => 'Resume preferences', 'body' => 'Default template, language tone, page size, and export format.'],
                ['icon' => 'lock-closed', 'title' => 'Privacy controls', 'body' => 'Manage public links, passcodes, analytics retention, and account sessions.'],
                ['icon' => 'bell', 'title' => 'Notification rules', 'body' => 'Control alerts for recruiter opens, AI reviews, and expiring links.'],
            ],
            'table' => [
                'headers' => ['Setting', 'Current Value', 'Scope', 'Status'],
                'rows' => [
                    ['Default tone', 'Confident and concise', 'AI Studio', 'Active'],
                    ['Export format', 'PDF first', 'Downloads', 'Active'],
                    ['Link expiry', '30 days', 'Shared resumes', 'Active'],
                ],
            ],
        ],
        'notifications' => [
            'title' => 'Notifications',
            'eyebrow' => 'Career signals',
            'description' => 'Follow resume opens, AI review completions, exports, and workspace reminders.',
            'stats' => [
                ['label' => 'Unread', 'value' => '7', 'icon' => 'bell', 'tone' => 'warning'],
                ['label' => 'Opens today', 'value' => '12', 'icon' => 'eye'],
                ['label' => 'AI alerts', 'value' => '4', 'icon' => 'sparkles', 'tone' => 'ai'],
            ],
            'cards' => [
                ['icon' => 'eye', 'title' => 'Recruiter opened Meta resume', 'body' => 'Shared link viewed twice in the last hour.'],
                ['icon' => 'shield-check', 'title' => 'ATS check complete', 'body' => 'Keyword coverage improved to 94% after the latest edit.'],
                ['icon' => 'arrow-down-tray', 'title' => 'Export package ready', 'body' => 'PDF and DOCX exports for Product Design Lead are ready.'],
            ],
            'table' => [
                'headers' => ['Signal', 'Source', 'Time', 'State'],
                'rows' => [
                    ['Resume opened', 'Shared link', '8 min ago', 'Unread'],
                    ['AI review complete', 'Resume Review', '1 hour ago', 'Unread'],
                    ['Download created', 'Exports', 'Yesterday', 'Read'],
                ],
            ],
        ],
        'ai-resume-studio' => [
            'title' => 'AI Resume Studio',
            'eyebrow' => 'AI studio active',
            'description' => 'Turn role descriptions into tailored bullets, summaries, skills, and recruiter-ready positioning.',
            'stats' => [
                ['label' => 'Rewrite credits', 'value' => '68', 'icon' => 'sparkles', 'tone' => 'ai'],
                ['label' => 'Impact upgrades', 'value' => '24', 'icon' => 'rocket-launch'],
                ['label' => 'Match lift', 'value' => '+31%', 'icon' => 'chart-bar', 'tone' => 'success'],
            ],
            'cards' => [
                ['icon' => 'sparkles', 'title' => 'Bullet rewrite', 'body' => 'Transform task-based bullets into measurable accomplishment statements.'],
                ['icon' => 'briefcase', 'title' => 'Role targeting', 'body' => 'Map resume language to the job description without keyword stuffing.'],
                ['icon' => 'clipboard-document-check', 'title' => 'Final polish', 'body' => 'Tighten grammar, tense, impact, and section consistency.'],
            ],
            'table' => [
                'headers' => ['AI Action', 'Section', 'Impact', 'Status'],
                'rows' => [
                    ['Rewrote summary', 'Profile', '+9% match', 'Applied'],
                    ['Added metrics', 'Experience', '+14% clarity', 'Reviewing'],
                    ['Optimized skills', 'Skills', '+8 keywords', 'Applied'],
                ],
            ],
        ],
        'ats-checker' => [
            'title' => 'ATS Checker',
            'eyebrow' => 'Parser quality',
            'description' => 'Audit structure, role keywords, measurable outcomes, and readability before sending applications.',
            'stats' => [
                ['label' => 'ATS score', 'value' => '94%', 'icon' => 'shield-check', 'tone' => 'success'],
                ['label' => 'Keywords found', 'value' => '37', 'icon' => 'command-line'],
                ['label' => 'Risk items', 'value' => '3', 'icon' => 'exclamation-triangle', 'tone' => 'warning'],
            ],
            'cards' => [
                ['icon' => 'check', 'title' => 'Strong section labels', 'body' => 'Experience, skills, education, and summary are parser-friendly.'],
                ['icon' => 'command-line', 'title' => 'Keyword alignment', 'body' => 'Design systems, research synthesis, and leadership terms are covered.'],
                ['icon' => 'exclamation-triangle', 'title' => 'Improve dates', 'body' => 'Normalize date ranges for two roles to reduce parsing ambiguity.'],
            ],
            'table' => [
                'headers' => ['Check', 'Result', 'Priority', 'Recommendation'],
                'rows' => [
                    ['Section parsing', 'Pass', 'Low', 'No action'],
                    ['Keyword coverage', 'Good', 'Medium', 'Add SaaS metrics'],
                    ['Date formatting', 'Warning', 'High', 'Normalize ranges'],
                ],
            ],
        ],
        'resume-review' => [
            'title' => 'Resume Review',
            'eyebrow' => 'Expert-grade critique',
            'description' => 'Review clarity, credibility, role fit, hierarchy, language quality, and visual polish.',
            'stats' => [
                ['label' => 'Review score', 'value' => '91%', 'icon' => 'document-magnifying-glass'],
                ['label' => 'Suggestions', 'value' => '11', 'icon' => 'sparkles', 'tone' => 'ai'],
                ['label' => 'Accepted edits', 'value' => '8', 'icon' => 'check', 'tone' => 'success'],
            ],
            'cards' => [
                ['icon' => 'document-magnifying-glass', 'title' => 'Hierarchy review', 'body' => 'Headline, summary, and recent experience are clear and easy to scan.'],
                ['icon' => 'chart-bar', 'title' => 'Impact review', 'body' => 'Three bullets need stronger outcomes tied to business metrics.'],
                ['icon' => 'paint-brush', 'title' => 'Visual review', 'body' => 'Spacing and typography are consistent across the selected template.'],
            ],
            'table' => [
                'headers' => ['Area', 'Score', 'Feedback', 'Action'],
                'rows' => [
                    ['Summary', '94%', 'Clear positioning', 'Keep'],
                    ['Experience', '88%', 'Add revenue impact', 'Improve'],
                    ['Skills', '92%', 'Strong grouping', 'Keep'],
                ],
            ],
        ],
        'resume-score' => [
            'title' => 'Resume Score',
            'eyebrow' => 'Performance metrics',
            'description' => 'Monitor ATS fit, recruiter clarity, keyword coverage, impact density, and export readiness.',
            'stats' => [
                ['label' => 'Overall', 'value' => '94%', 'icon' => 'chart-bar', 'tone' => 'success'],
                ['label' => 'Impact density', 'value' => '87%', 'icon' => 'rocket-launch'],
                ['label' => 'Readability', 'value' => '96%', 'icon' => 'eye'],
            ],
            'cards' => [
                ['icon' => 'shield-check', 'title' => 'ATS compatibility', 'body' => 'Strong parser structure and keyword coverage for product design roles.'],
                ['icon' => 'rocket-launch', 'title' => 'Impact density', 'body' => 'More quantified outcomes can push the score above 97%.'],
                ['icon' => 'eye', 'title' => 'Recruiter scan', 'body' => 'The top third communicates role fit within the first six seconds.'],
            ],
            'table' => [
                'headers' => ['Metric', 'Score', 'Movement', 'Priority'],
                'rows' => [
                    ['ATS fit', '94%', '+7', 'High'],
                    ['Keyword coverage', '91%', '+12', 'Medium'],
                    ['Readability', '96%', '+2', 'Low'],
                ],
            ],
        ],
        'cover-letter-generator' => [
            'title' => 'Cover Letter Generator',
            'eyebrow' => 'Application companion',
            'description' => 'Create targeted cover letters from your resume, role description, and company context.',
            'stats' => [
                ['label' => 'Letters drafted', 'value' => '9', 'icon' => 'envelope'],
                ['label' => 'Tailoring score', 'value' => '93%', 'icon' => 'sparkles', 'tone' => 'ai'],
                ['label' => 'Tone presets', 'value' => '6', 'icon' => 'pencil-square'],
            ],
            'cards' => [
                ['icon' => 'building-office', 'title' => 'Company context', 'body' => 'Reference mission, product surface, and role expectations with restraint.'],
                ['icon' => 'document-text', 'title' => 'Resume-aligned proof', 'body' => 'Pull achievements directly from the selected resume.'],
                ['icon' => 'sparkles', 'title' => 'Tone control', 'body' => 'Choose concise, warm, executive, technical, or bold variants.'],
            ],
            'table' => [
                'headers' => ['Letter', 'Company', 'Tone', 'Status'],
                'rows' => [
                    ['Product Design Lead', 'Meta', 'Confident', 'Ready'],
                    ['Senior UX Designer', 'Stripe', 'Warm', 'Draft'],
                    ['Founding Designer', 'SeedCo', 'Bold', 'Reviewing'],
                ],
            ],
        ],
        'interview-questions' => [
            'title' => 'Interview Questions',
            'eyebrow' => 'Practice studio',
            'description' => 'Generate behavioral, portfolio, systems, and role-specific prompts from your target resume.',
            'stats' => [
                ['label' => 'Question sets', 'value' => '12', 'icon' => 'chat-bubble-left-right'],
                ['label' => 'Model answers', 'value' => '42', 'icon' => 'document-text'],
                ['label' => 'Practice score', 'value' => '89%', 'icon' => 'academic-cap', 'tone' => 'success'],
            ],
            'cards' => [
                ['icon' => 'briefcase', 'title' => 'Role-specific prompts', 'body' => 'Design leadership, research influence, prioritization, and business impact.'],
                ['icon' => 'document-text', 'title' => 'STAR answer builder', 'body' => 'Structure answers around situation, task, action, and result.'],
                ['icon' => 'sparkles', 'title' => 'Follow-up simulation', 'body' => 'Practice deeper interviewer probes based on your answers.'],
            ],
            'table' => [
                'headers' => ['Set', 'Focus', 'Questions', 'Status'],
                'rows' => [
                    ['Portfolio review', 'Design decisions', '14', 'Ready'],
                    ['Leadership loop', 'Team influence', '12', 'In progress'],
                    ['Behavioral', 'Conflict and ambiguity', '16', 'Ready'],
                ],
            ],
        ],
        'keyword-optimizer' => [
            'title' => 'Keyword Optimizer',
            'eyebrow' => 'Role language',
            'description' => 'Balance role keywords, natural phrasing, and recruiter readability without stuffing.',
            'stats' => [
                ['label' => 'Matched keywords', 'value' => '37', 'icon' => 'command-line'],
                ['label' => 'Missing terms', 'value' => '6', 'icon' => 'exclamation-triangle', 'tone' => 'warning'],
                ['label' => 'Naturalness', 'value' => '95%', 'icon' => 'check', 'tone' => 'success'],
            ],
            'cards' => [
                ['icon' => 'command-line', 'title' => 'Keyword gap analysis', 'body' => 'Compare job descriptions against summary, skills, and bullet language.'],
                ['icon' => 'sparkles', 'title' => 'Natural insertion', 'body' => 'Rewrite sentences so keywords feel earned and human.'],
                ['icon' => 'chart-bar', 'title' => 'Coverage tracking', 'body' => 'Watch match quality improve with each accepted suggestion.'],
            ],
            'table' => [
                'headers' => ['Keyword', 'Coverage', 'Section', 'Action'],
                'rows' => [
                    ['Design systems', 'Strong', 'Experience', 'Keep'],
                    ['Experimentation', 'Partial', 'Summary', 'Add'],
                    ['Roadmapping', 'Missing', 'Experience', 'Add proof'],
                ],
            ],
        ],
    ],

    'admin_pages' => [
        'dashboard' => [
            'title' => 'Admin Dashboard',
            'eyebrow' => 'Operating overview',
            'description' => 'Monitor users, resumes, templates, AI usage, exports, messages, and system health.',
            'stats' => [
                ['label' => 'Active users', 'value' => '18.4k', 'icon' => 'users', 'trend' => '+12.8%'],
                ['label' => 'Resumes created', 'value' => '74k', 'icon' => 'document-text', 'trend' => '+3.1k'],
                ['label' => 'AI credits used', 'value' => '1.2M', 'icon' => 'sparkles', 'tone' => 'ai'],
                ['label' => 'Conversion', 'value' => '8.6%', 'icon' => 'chart-bar', 'tone' => 'success'],
            ],
            'cards' => [
                ['icon' => 'presentation-chart-line', 'title' => 'Revenue and usage', 'body' => 'Track signups, active workspaces, AI usage, exports, and template adoption.'],
                ['icon' => 'shield-check', 'title' => 'Moderation queue', 'body' => 'Review reported media, public resume links, and suspicious account activity.'],
                ['icon' => 'sparkles', 'title' => 'AI quality monitor', 'body' => 'Watch prompt success, model latency, and credit burn by feature.'],
            ],
            'table' => [
                'headers' => ['Signal', 'Metric', 'Trend', 'Owner'],
                'rows' => [
                    ['New subscriptions', '412', '+18%', 'Growth'],
                    ['Template uploads', '19', '+6', 'Design'],
                    ['Open messages', '34', '-12', 'Support'],
                ],
            ],
        ],
        'users' => [
            'title' => 'User Management',
            'eyebrow' => 'Accounts',
            'description' => 'Review account health, subscription tier, verification state, and workspace activity.',
            'stats' => [
                ['label' => 'Users', 'value' => '18,430', 'icon' => 'users'],
                ['label' => 'Verified', 'value' => '94%', 'icon' => 'shield-check', 'tone' => 'success'],
                ['label' => 'Pro plans', 'value' => '2,384', 'icon' => 'credit-card'],
            ],
            'cards' => [
                ['icon' => 'user', 'title' => 'Lifecycle view', 'body' => 'Segment new, activated, paid, churn-risk, and enterprise users.'],
                ['icon' => 'bell', 'title' => 'Account alerts', 'body' => 'See verification, billing, security, and support signals in context.'],
                ['icon' => 'key', 'title' => 'Access controls', 'body' => 'Manage role assignment and workspace-level privileges.'],
            ],
            'table' => [
                'headers' => ['User', 'Plan', 'Resumes', 'State'],
                'rows' => [
                    ['Alex Rivers', 'Pro', '8', 'Active'],
                    ['Mina Shah', 'Starter', '2', 'Trial'],
                    ['Design Lab', 'Team', '214', 'Active'],
                ],
            ],
        ],
        'resumes' => [
            'title' => 'Resume Management',
            'eyebrow' => 'Content operations',
            'description' => 'Audit resume volume, public links, export quality, and template usage across the platform.',
            'stats' => [
                ['label' => 'Total resumes', 'value' => '74,118', 'icon' => 'document-text'],
                ['label' => 'Shared links', 'value' => '9,204', 'icon' => 'share'],
                ['label' => 'Flagged', 'value' => '18', 'icon' => 'exclamation-triangle', 'tone' => 'warning'],
            ],
            'cards' => [
                ['icon' => 'document-magnifying-glass', 'title' => 'Quality sampling', 'body' => 'Review parse quality, export issues, and template rendering exceptions.'],
                ['icon' => 'archive-box', 'title' => 'Storage lifecycle', 'body' => 'Monitor archived resumes and document storage utilization.'],
                ['icon' => 'shield-check', 'title' => 'Public link controls', 'body' => 'Pause or review links that trigger trust and safety rules.'],
            ],
            'table' => [
                'headers' => ['Resume', 'Owner', 'Template', 'Status'],
                'rows' => [
                    ['Senior Designer', 'Alex Rivers', 'Neo-Minimalist', 'Published'],
                    ['Data Scientist', 'Ravi Khan', 'Algorithm Pro', 'Draft'],
                    ['VP Product', 'Leah Wong', 'Executive Flow', 'Shared'],
                ],
            ],
        ],
        'templates' => [
            'title' => 'Templates',
            'eyebrow' => 'Design library',
            'description' => 'Manage template categories, previews, tags, popularity, and AI optimization metadata.',
            'stats' => [
                ['label' => 'Templates', 'value' => '48', 'icon' => 'squares-2x2'],
                ['label' => 'AI optimized', 'value' => '31', 'icon' => 'sparkles', 'tone' => 'ai'],
                ['label' => 'Top adoption', 'value' => '26%', 'icon' => 'chart-bar'],
            ],
            'cards' => [
                ['icon' => 'paint-brush', 'title' => 'Template families', 'body' => 'Creative, corporate, tech, healthcare, academic, and executive sets.'],
                ['icon' => 'eye', 'title' => 'Preview QA', 'body' => 'Validate screenshot crops, page breaks, and mobile gallery rendering.'],
                ['icon' => 'tag', 'title' => 'Metadata', 'body' => 'Maintain best-for labels, industries, style tags, and ATS compatibility notes.'],
            ],
            'table' => [
                'headers' => ['Template', 'Category', 'Uses', 'State'],
                'rows' => [
                    ['Neo-Minimalist', 'Creative', '8,420', 'Live'],
                    ['Executive Flow', 'Corporate', '7,884', 'Live'],
                    ['Syntax Master', 'Tech', '6,112', 'Live'],
                ],
            ],
        ],
        'template-upload' => [
            'title' => 'Template Upload',
            'eyebrow' => 'Asset intake',
            'description' => 'Upload template previews, source files, metadata, and ATS compatibility checks.',
            'stats' => [
                ['label' => 'Pending uploads', 'value' => '5', 'icon' => 'cloud-arrow-up'],
                ['label' => 'QA pass rate', 'value' => '96%', 'icon' => 'shield-check', 'tone' => 'success'],
                ['label' => 'Preview assets', 'value' => '126', 'icon' => 'photo'],
            ],
            'cards' => [
                ['icon' => 'cloud-arrow-up', 'title' => 'Upload workflow', 'body' => 'Collect thumbnail, PDF proof, Blade partial, category, and search tags.'],
                ['icon' => 'clipboard-document-check', 'title' => 'Automated checks', 'body' => 'Validate dimensions, contrast, metadata, and parser safety.'],
                ['icon' => 'sparkles', 'title' => 'AI labels', 'body' => 'Generate best-fit industries and optimization notes from the design file.'],
            ],
            'table' => [
                'headers' => ['Upload', 'Owner', 'Check', 'Status'],
                'rows' => [
                    ['Consultant Grid', 'Design', 'Contrast', 'Passed'],
                    ['Clinical Pro', 'Design', 'Parser', 'Reviewing'],
                    ['Data Story', 'Design', 'Preview', 'Queued'],
                ],
            ],
        ],
        'blog' => [
            'title' => 'Blog CMS',
            'eyebrow' => 'Editorial',
            'description' => 'Plan, edit, optimize, and publish ResumeHub AI articles and product guides.',
            'stats' => [
                ['label' => 'Published', 'value' => '38', 'icon' => 'newspaper'],
                ['label' => 'Drafts', 'value' => '9', 'icon' => 'pencil-square'],
                ['label' => 'SEO score', 'value' => '91%', 'icon' => 'magnifying-glass'],
            ],
            'cards' => [
                ['icon' => 'newspaper', 'title' => 'Editorial pipeline', 'body' => 'Move ideas through outline, draft, review, SEO, and publish states.'],
                ['icon' => 'tag', 'title' => 'Taxonomy', 'body' => 'Assign categories and tags for guides, templates, product updates, and hiring advice.'],
                ['icon' => 'sparkles', 'title' => 'AI summaries', 'body' => 'Generate excerpts, meta descriptions, and social blurbs from article drafts.'],
            ],
            'table' => [
                'headers' => ['Post', 'Category', 'SEO', 'Status'],
                'rows' => [
                    ['AI resume workflows', 'Product', '94%', 'Published'],
                    ['ATS keyword strategy', 'Guides', '89%', 'Draft'],
                    ['Template selection', 'Templates', '92%', 'Review'],
                ],
            ],
        ],
        'categories' => [
            'title' => 'Categories',
            'eyebrow' => 'Taxonomy',
            'description' => 'Organize templates, blog posts, and help content by domain and role family.',
            'stats' => [
                ['label' => 'Categories', 'value' => '18', 'icon' => 'folder'],
                ['label' => 'Mapped content', 'value' => '214', 'icon' => 'link'],
                ['label' => 'Empty categories', 'value' => '0', 'icon' => 'check', 'tone' => 'success'],
            ],
            'cards' => [
                ['icon' => 'folder', 'title' => 'Role categories', 'body' => 'Creative, corporate, engineering, healthcare, education, and executive.'],
                ['icon' => 'globe-alt', 'title' => 'SEO hierarchy', 'body' => 'Categories power landing pages, blog archives, and template filters.'],
                ['icon' => 'squares-2x2', 'title' => 'Template filtering', 'body' => 'Each template can appear in multiple user-facing categories.'],
            ],
            'table' => [
                'headers' => ['Category', 'Templates', 'Posts', 'Status'],
                'rows' => [
                    ['Creative', '12', '8', 'Live'],
                    ['Corporate', '14', '6', 'Live'],
                    ['Technology', '11', '9', 'Live'],
                ],
            ],
        ],
        'tags' => [
            'title' => 'Tags',
            'eyebrow' => 'Search metadata',
            'description' => 'Maintain granular labels that improve search, filters, content linking, and recommendations.',
            'stats' => [
                ['label' => 'Tags', 'value' => '126', 'icon' => 'tag'],
                ['label' => 'High usage', 'value' => '34', 'icon' => 'chart-bar'],
                ['label' => 'Needs merge', 'value' => '7', 'icon' => 'arrow-path', 'tone' => 'warning'],
            ],
            'cards' => [
                ['icon' => 'tag', 'title' => 'Smart labels', 'body' => 'Track tags for roles, industries, seniority, tone, and template aesthetics.'],
                ['icon' => 'magnifying-glass', 'title' => 'Search boost', 'body' => 'Tags improve discoverability for templates and editorial content.'],
                ['icon' => 'arrow-path', 'title' => 'Merge workflow', 'body' => 'Consolidate duplicate tags while preserving relationships.'],
            ],
            'table' => [
                'headers' => ['Tag', 'Type', 'Uses', 'State'],
                'rows' => [
                    ['ATS friendly', 'Template', '48', 'Active'],
                    ['Leadership', 'Role', '31', 'Active'],
                    ['Portfolio', 'Style', '22', 'Active'],
                ],
            ],
        ],
        'team' => [
            'title' => 'Team Members',
            'eyebrow' => 'Internal team',
            'description' => 'Manage operators, designers, writers, support agents, and administrators.',
            'stats' => [
                ['label' => 'Team', 'value' => '16', 'icon' => 'building-office'],
                ['label' => 'Admins', 'value' => '4', 'icon' => 'shield-check'],
                ['label' => 'Support queue', 'value' => '34', 'icon' => 'inbox-stack'],
            ],
            'cards' => [
                ['icon' => 'users', 'title' => 'Team directory', 'body' => 'See ownership, role, permissions, and recent administrative activity.'],
                ['icon' => 'key', 'title' => 'Role assignment', 'body' => 'Grant admin, editor, support, analyst, and content permissions.'],
                ['icon' => 'bell', 'title' => 'Operational alerts', 'body' => 'Route contact, billing, AI, and template alerts to the right team.'],
            ],
            'table' => [
                'headers' => ['Member', 'Role', 'Focus', 'Status'],
                'rows' => [
                    ['Sara Malik', 'Admin', 'Operations', 'Active'],
                    ['Jon Lee', 'Editor', 'Blog CMS', 'Active'],
                    ['Ava Chen', 'Designer', 'Templates', 'Active'],
                ],
            ],
        ],
        'website-settings' => [
            'title' => 'Website Settings',
            'eyebrow' => 'Public site',
            'description' => 'Control brand copy, navigation, footer links, hero messaging, and contact settings.',
            'stats' => [
                ['label' => 'Active pages', 'value' => '12', 'icon' => 'globe-alt'],
                ['label' => 'Nav links', 'value' => '8', 'icon' => 'link'],
                ['label' => 'Forms', 'value' => '3', 'icon' => 'inbox-stack'],
            ],
            'cards' => [
                ['icon' => 'home', 'title' => 'Landing content', 'body' => 'Update headline, proof points, hero media, and conversion CTAs.'],
                ['icon' => 'link', 'title' => 'Navigation', 'body' => 'Manage marketing links, footer groups, social URLs, and legal pages.'],
                ['icon' => 'inbox-stack', 'title' => 'Contact flow', 'body' => 'Route sales, support, and partnership messages to the right inbox.'],
            ],
            'table' => [
                'headers' => ['Setting', 'Value', 'Owner', 'Status'],
                'rows' => [
                    ['Hero CTA', 'Build Your Resume', 'Growth', 'Live'],
                    ['Footer legal', 'Terms + Privacy', 'Ops', 'Live'],
                    ['Contact inbox', 'support@resumehub.ai', 'Support', 'Live'],
                ],
            ],
        ],
        'seo-settings' => [
            'title' => 'SEO Settings',
            'eyebrow' => 'Search visibility',
            'description' => 'Manage metadata, sitemap priorities, canonical strategy, and structured content quality.',
            'stats' => [
                ['label' => 'Indexed pages', 'value' => '86', 'icon' => 'magnifying-glass'],
                ['label' => 'SEO average', 'value' => '92%', 'icon' => 'chart-bar', 'tone' => 'success'],
                ['label' => 'Fixes needed', 'value' => '5', 'icon' => 'exclamation-triangle', 'tone' => 'warning'],
            ],
            'cards' => [
                ['icon' => 'document-text', 'title' => 'Metadata templates', 'body' => 'Control title, description, Open Graph, and canonical formats.'],
                ['icon' => 'globe-alt', 'title' => 'Sitemap health', 'body' => 'Prioritize landing pages, blogs, templates, and legal documents.'],
                ['icon' => 'sparkles', 'title' => 'AI meta assistant', 'body' => 'Generate concise meta descriptions from page content and search intent.'],
            ],
            'table' => [
                'headers' => ['Page', 'SEO Score', 'Issue', 'Action'],
                'rows' => [
                    ['Home', '96%', 'None', 'Monitor'],
                    ['Pricing', '91%', 'Meta length', 'Trim'],
                    ['Template gallery', '89%', 'Alt text', 'Improve'],
                ],
            ],
        ],
        'ai-settings' => [
            'title' => 'AI Settings',
            'eyebrow' => 'Model operations',
            'description' => 'Configure prompt behavior, credit limits, feature access, model routing, and quality guardrails.',
            'stats' => [
                ['label' => 'Prompts today', 'value' => '42k', 'icon' => 'sparkles', 'tone' => 'ai'],
                ['label' => 'Avg latency', 'value' => '1.8s', 'icon' => 'server-stack'],
                ['label' => 'Quality pass', 'value' => '97%', 'icon' => 'shield-check', 'tone' => 'success'],
            ],
            'cards' => [
                ['icon' => 'sparkles', 'title' => 'Feature prompts', 'body' => 'Separate instructions for resume rewrite, ATS, cover letter, and interviews.'],
                ['icon' => 'credit-card', 'title' => 'Credit budgets', 'body' => 'Set plan limits, burst rules, admin overrides, and team allowances.'],
                ['icon' => 'shield-check', 'title' => 'Safety controls', 'body' => 'Enforce content quality, privacy, and unsupported-claim guardrails.'],
            ],
            'table' => [
                'headers' => ['Feature', 'Model', 'Latency', 'Status'],
                'rows' => [
                    ['Resume rewrite', 'Fast creative', '1.4s', 'Healthy'],
                    ['ATS analysis', 'Structured', '2.1s', 'Healthy'],
                    ['Interview prep', 'Reasoning', '2.8s', 'Healthy'],
                ],
            ],
        ],
        'analytics' => [
            'title' => 'Analytics',
            'eyebrow' => 'Business intelligence',
            'description' => 'Track acquisition, activation, exports, template usage, AI usage, and conversion quality.',
            'stats' => [
                ['label' => 'Visitors', 'value' => '142k', 'icon' => 'eye'],
                ['label' => 'Activation', 'value' => '38%', 'icon' => 'rocket-launch', 'tone' => 'success'],
                ['label' => 'Exports/user', 'value' => '3.4', 'icon' => 'arrow-down-tray'],
            ],
            'cards' => [
                ['icon' => 'chart-pie', 'title' => 'Acquisition mix', 'body' => 'Compare organic, paid, referral, campus, and partner channels.'],
                ['icon' => 'presentation-chart-line', 'title' => 'Product funnels', 'body' => 'Measure resume creation, ATS checks, exports, and upgrade events.'],
                ['icon' => 'squares-2x2', 'title' => 'Template adoption', 'body' => 'See conversion and retention by template category.'],
            ],
            'table' => [
                'headers' => ['Funnel', 'Conversion', 'Change', 'Note'],
                'rows' => [
                    ['Visitor to signup', '11.8%', '+1.2%', 'Healthy'],
                    ['Signup to first resume', '62%', '+4.8%', 'Improving'],
                    ['Export to upgrade', '8.6%', '+0.9%', 'Watch'],
                ],
            ],
        ],
        'contact-messages' => [
            'title' => 'Contact Messages',
            'eyebrow' => 'Inbox',
            'description' => 'Triage sales, support, partnership, and feedback messages from public forms.',
            'stats' => [
                ['label' => 'Open', 'value' => '34', 'icon' => 'inbox-stack', 'tone' => 'warning'],
                ['label' => 'Sales leads', 'value' => '11', 'icon' => 'briefcase'],
                ['label' => 'Avg response', 'value' => '2h', 'icon' => 'clock'],
            ],
            'cards' => [
                ['icon' => 'inbox-stack', 'title' => 'Message triage', 'body' => 'Separate product support, billing, partnerships, and institutional leads.'],
                ['icon' => 'bell', 'title' => 'Priority routing', 'body' => 'Escalate account access, payment, and enterprise interest quickly.'],
                ['icon' => 'chat-bubble-left-right', 'title' => 'Reply context', 'body' => 'Keep page source, plan, user state, and message history visible.'],
            ],
            'table' => [
                'headers' => ['Sender', 'Topic', 'Priority', 'Status'],
                'rows' => [
                    ['Nora Patel', 'Team pricing', 'High', 'Open'],
                    ['Marcus Lee', 'Export issue', 'Medium', 'Assigned'],
                    ['Career Lab', 'Partnership', 'High', 'Open'],
                ],
            ],
        ],
        'media-library' => [
            'title' => 'Media Library',
            'eyebrow' => 'Assets',
            'description' => 'Manage template thumbnails, blog images, avatars, exports, and brand visuals.',
            'stats' => [
                ['label' => 'Assets', 'value' => '1,284', 'icon' => 'photo'],
                ['label' => 'Optimized', 'value' => '96%', 'icon' => 'check', 'tone' => 'success'],
                ['label' => 'Storage', 'value' => '7.4 GB', 'icon' => 'server-stack'],
            ],
            'cards' => [
                ['icon' => 'photo', 'title' => 'Image library', 'body' => 'Preview thumbnails, dimensions, alt text, file size, and usage.'],
                ['icon' => 'cloud-arrow-up', 'title' => 'Bulk upload', 'body' => 'Upload template screenshots and blog media with metadata capture.'],
                ['icon' => 'shield-check', 'title' => 'Asset hygiene', 'body' => 'Flag missing alt text, oversized files, and unused assets.'],
            ],
            'table' => [
                'headers' => ['Asset', 'Type', 'Size', 'Used In'],
                'rows' => [
                    ['templates-gallery.png', 'Screenshot', '330 KB', 'Templates'],
                    ['landing-mobile.png', 'Screenshot', '105 KB', 'Home'],
                    ['builder-editor.png', 'Screenshot', '127 KB', 'Builder'],
                ],
            ],
        ],
        'roles' => [
            'title' => 'Roles',
            'eyebrow' => 'Access model',
            'description' => 'Define admin capabilities for operations, content, design, support, finance, and analytics.',
            'stats' => [
                ['label' => 'Roles', 'value' => '7', 'icon' => 'key'],
                ['label' => 'Assigned users', 'value' => '16', 'icon' => 'users'],
                ['label' => 'Protected roles', 'value' => '2', 'icon' => 'shield-check'],
            ],
            'cards' => [
                ['icon' => 'key', 'title' => 'Role design', 'body' => 'Group permissions into practical job responsibilities.'],
                ['icon' => 'shield-check', 'title' => 'Protected admin', 'body' => 'Restrict billing, AI, logs, and role changes to trusted admins.'],
                ['icon' => 'users', 'title' => 'Team assignment', 'body' => 'Assign roles by person and review access changes over time.'],
            ],
            'table' => [
                'headers' => ['Role', 'Members', 'Scope', 'Status'],
                'rows' => [
                    ['Super Admin', '2', 'All areas', 'Protected'],
                    ['Content Editor', '4', 'Blog + media', 'Active'],
                    ['Support Agent', '6', 'Users + messages', 'Active'],
                ],
            ],
        ],
        'permissions' => [
            'title' => 'Permissions',
            'eyebrow' => 'Capability matrix',
            'description' => 'Fine-tune access for users, resumes, templates, CMS, settings, analytics, and logs.',
            'stats' => [
                ['label' => 'Permissions', 'value' => '42', 'icon' => 'shield-check'],
                ['label' => 'Sensitive', 'value' => '9', 'icon' => 'lock-closed'],
                ['label' => 'Unused', 'value' => '0', 'icon' => 'check', 'tone' => 'success'],
            ],
            'cards' => [
                ['icon' => 'shield-check', 'title' => 'Granular capabilities', 'body' => 'Control view, create, edit, delete, export, publish, and configure actions.'],
                ['icon' => 'lock-closed', 'title' => 'Sensitive zones', 'body' => 'Separate billing, AI settings, logs, roles, and permissions from normal access.'],
                ['icon' => 'document-magnifying-glass', 'title' => 'Audit friendly', 'body' => 'Keep every permission readable and traceable for reviews.'],
            ],
            'table' => [
                'headers' => ['Permission', 'Area', 'Assigned Roles', 'State'],
                'rows' => [
                    ['templates.publish', 'Templates', 'Admin, Designer', 'Active'],
                    ['ai.configure', 'AI Settings', 'Super Admin', 'Sensitive'],
                    ['blog.edit', 'Blog CMS', 'Admin, Editor', 'Active'],
                ],
            ],
        ],
        'logs' => [
            'title' => 'Logs',
            'eyebrow' => 'System audit',
            'description' => 'Inspect application events, admin actions, AI usage, exports, and authentication activity.',
            'stats' => [
                ['label' => 'Events today', 'value' => '18k', 'icon' => 'server-stack'],
                ['label' => 'Warnings', 'value' => '12', 'icon' => 'exclamation-triangle', 'tone' => 'warning'],
                ['label' => 'Errors', 'value' => '0', 'icon' => 'check', 'tone' => 'success'],
            ],
            'cards' => [
                ['icon' => 'server-stack', 'title' => 'Operational stream', 'body' => 'Review auth, export, AI, payment, and admin events in sequence.'],
                ['icon' => 'magnifying-glass', 'title' => 'Filtered search', 'body' => 'Filter by user, route, feature, severity, source, and time window.'],
                ['icon' => 'shield-check', 'title' => 'Audit trail', 'body' => 'Keep sensitive actions visible for compliance and operational debugging.'],
            ],
            'table' => [
                'headers' => ['Event', 'Source', 'Severity', 'Time'],
                'rows' => [
                    ['AI rewrite completed', 'AI Studio', 'Info', '2 min ago'],
                    ['Template published', 'Admin', 'Info', '14 min ago'],
                    ['Login challenge passed', 'Auth', 'Info', '1 hour ago'],
                ],
            ],
        ],
    ],
];
