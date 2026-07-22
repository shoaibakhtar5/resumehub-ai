<div id="resume-live-preview" class="resume-template-{{ $templateVariant }} w-full overflow-visible" data-template="{{ $templateSlug }}" x-on:resume-photo-selected.window="$el.querySelector('.rh-photo')?.setAttribute('src', $event.detail)">
    <div wire:key="rendered-doc-{{ $payload['template_id'] ?? 'default' }}-{{ md5($renderedHtml) }}" class="resume-rendered-document w-full">
        {!! $renderedHtml !!}
    </div>
</div>
