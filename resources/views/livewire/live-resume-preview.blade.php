<div id="resume-live-preview" class="resume-template-{{ $templateVariant }} min-h-full w-full overflow-hidden bg-white" data-template="{{ $templateSlug }}">
    <div class="resume-rendered-document h-full w-full [&>html]:h-full [&>html]:w-full [&_body]:h-full [&_body]:w-full">
        {!! $renderedHtml !!}
    </div>
</div>
