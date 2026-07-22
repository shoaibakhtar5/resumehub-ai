@php
    $editing = (bool)$template;
    $mappingCandidates = $editing ? data_get($template->config, 'mapping_candidates', []) : [];
@endphp

<x-admin-layout :title="$editing ? 'Edit Template — ' . $template->name : 'Upload New Template'">
    <div class="mx-auto max-w-7xl pb-16" 
         x-data="{
             saving: false,
             fileName: '',
             fileSize: '',
             sourceType: '{{ $editing ? strtoupper($template->source_type === 'latex' ? 'TEX' : $template->source_type) : '' }}',
             dragover: false,
             primaryColor: '{{ old('primary_color', data_get($template?->config, 'primary_color', '#3155e7')) }}',
             fontFamily: '{{ old('font_family', data_get($template?->config, 'font_family', 'Inter, Arial, sans-serif')) }}',
             placeholderSearch: '',
             copied: '',
             thumbnailPreview: '{{ $editing && $template->preview_url ? $template->preview_url : (old('preview_images.0') ?: '') }}',
             handleThumbnail(file) {
                 if (!file) return;
                 this.thumbnailPreview = URL.createObjectURL(file);
             },
             copyPlaceholder(text) {
                 navigator.clipboard.writeText('{{ ' + text + ' }}');
                 this.copied = text;
                 setTimeout(() => this.copied = '', 2000);
             },
             handleFile(file) {
                 if (!file) return;
                 this.fileName = file.name;
                 this.fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                 const ext = file.name.split('.').pop().toUpperCase();
                 this.sourceType = (ext === 'TEX' ? 'TEX' : ext) + ' Source';
             }
         }">

        <!-- Toast Notification for Placeholder Copy -->
        <div x-show="copied" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-6 right-6 z-50 flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-3 text-xs font-semibold text-white shadow-2xl border border-slate-800"
             x-cloak>
            <svg class="h-4 w-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span>Copied <code class="font-mono text-amber-300" x-text="`\{\{ ${copied} \}\}`"></code> to clipboard!</span>
        </div>

        <!-- Top Header & Breadcrumbs -->
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200/80 pb-5">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                    <a href="{{ route('admin.templates') }}" class="hover:text-indigo-600 transition">Templates</a>
                    <span>/</span>
                    <span class="text-slate-600">{{ $editing ? 'Edit' : 'Upload' }}</span>
                </div>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-3">
                    <span>{{ $editing ? $template->name : 'Upload Resume Template' }}</span>
                    @if($editing)
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold 
                            {{ $template->status === 'published' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($template->status === 'draft' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600 border border-slate-200') }}">
                            {{ ucfirst($template->status) }}
                        </span>
                    @endif
                </h1>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.templates') }}" 
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50 transition">
                    ← Back to Templates
                </a>

                @if($editing)
                    <a href="{{ route('admin.templates.preview', $template) }}" 
                       target="_blank"
                       class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50/50 px-4 py-2.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100/50 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Preview Live
                    </a>
                @endif
            </div>
        </div>

        <form method="POST" 
              enctype="multipart/form-data" 
              action="{{ $editing ? route('admin.templates.update', $template) : route('admin.templates.store') }}" 
              x-on:submit="saving=true" 
              class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_340px]">
            @csrf 
            @if($editing) @method('PATCH') @endif

            <!-- LEFT MAIN CONTENT AREA -->
            <div class="space-y-8 min-w-0">

                <!-- 1. FILE UPLOAD CARD (Drag & Drop) -->
                <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
                    <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Template Source File {{ $editing ? '(Optional)' : '*' }}</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Upload HTML, TXT, or LaTeX (.tex) resume template files.</p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="rounded-md bg-blue-50 px-2 py-1 text-[10px] font-bold text-blue-700 border border-blue-200">HTML</span>
                            <span class="rounded-md bg-purple-50 px-2 py-1 text-[10px] font-bold text-purple-700 border border-purple-200">TXT</span>
                            <span class="rounded-md bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-700 border border-emerald-200">TEX</span>
                        </div>
                    </div>

                    <div class="p-6">
                        <label class="relative flex min-h-48 cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed transition-all duration-150 p-6 text-center"
                               :class="dragover ? 'border-indigo-500 bg-indigo-50/40 scale-[0.99]' : 'border-slate-200 bg-slate-50/50 hover:border-indigo-300 hover:bg-slate-50'"
                               x-on:dragover.prevent="dragover = true"
                               x-on:dragleave.prevent="dragover = false"
                               x-on:drop.prevent="dragover = false; const file = $event.dataTransfer.files[0]; if(file) { $refs.fileInput.files = $event.dataTransfer.files; handleFile(file); }">
                            
                            <input type="file" 
                                   name="template_file" 
                                   x-ref="fileInput"
                                   accept=".html,.htm,.txt,.tex,text/html,text/plain,application/x-tex" 
                                   {{ $editing ? '' : 'required' }} 
                                   class="sr-only" 
                                   x-on:change="handleFile($event.target.files[0])">

                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100/70 text-indigo-600 shadow-xs mb-3">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            </div>

                            <div class="space-y-1">
                                <p class="text-sm font-bold text-slate-800" x-text="fileName || 'Click or drag & drop template file here'"></p>
                                <p class="text-xs text-slate-500" x-text="sourceType || 'Supports HTML5, TXT, or LaTeX (.tex) — Max 5MB'"></p>
                                <p x-show="fileSize" class="text-xs font-mono font-semibold text-indigo-600" x-text="`File Size: ${fileSize}`" x-cloak></p>
                            </div>
                        </label>

                        @if($editing)
                            <div class="mt-3 flex items-center justify-between text-xs text-slate-500 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                <span>Current source: <strong class="font-mono text-slate-700">{{ $template->package_path ?: 'Default HTML' }}</strong> (v{{ $template->version }})</span>
                                <span class="text-slate-400">Leave empty to keep existing file</span>
                            </div>
                        @endif
                    </div>
                </section>

                <!-- 2. BASIC INFORMATION CARD -->
                <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-bold text-slate-900">Basic Information</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Template title, URL slug, and public description.</p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Template Name *</label>
                            <input name="name" 
                                   value="{{ old('name', $template?->name) }}" 
                                   required 
                                   placeholder="e.g. Modern Executive Resume"
                                   class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 shadow-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">URL Slug *</label>
                            <input name="slug" 
                                   value="{{ old('slug', $template?->slug) }}" 
                                   required 
                                   pattern="[A-Za-z0-9_-]+" 
                                   placeholder="e.g. modern-executive-resume"
                                   class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-mono text-slate-900 shadow-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Description</label>
                            <textarea name="description" 
                                      rows="3" 
                                      placeholder="Brief summary highlighting template features, ideal career roles, and design aesthetic..."
                                      class="w-full rounded-xl border border-slate-200 p-4 text-sm text-slate-900 shadow-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">{{ old('description', $template?->description) }}</textarea>
                        </div>
                    </div>
                </section>

                <!-- 3. TEMPLATE SETTINGS CARD -->
                <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-bold text-slate-900">Template Settings & Visibility</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Categorization, publishing status, and display options.</p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Category</label>
                            <select name="template_category_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 shadow-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                                <option value="">Uncategorized</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected((int)old('template_category_id', $template?->template_category_id) === $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Publish Status *</label>
                            <select name="status" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 shadow-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                                @foreach(['draft' => 'Draft (Hidden)', 'published' => 'Published (Live)', 'disabled' => 'Disabled'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $template?->status ?? 'draft') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Display Order *</label>
                            <input name="sort_order" 
                                   type="number" 
                                   min="0" 
                                   value="{{ old('sort_order', $template?->sort_order ?? 0) }}" 
                                   required 
                                   class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 shadow-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                        </div>
                    </div>

                    <!-- Toggles for Featured and Premium -->
                    <div class="grid gap-4 sm:grid-cols-2 pt-2 border-t border-slate-100">
                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 cursor-pointer hover:bg-slate-50 transition">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $template?->is_featured)) class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="block text-xs font-bold text-slate-900">Featured Template</span>
                                <span class="text-[11px] text-slate-500">Highlight this template at top of template picker</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 cursor-pointer hover:bg-slate-50 transition">
                            <input type="hidden" name="is_premium" value="0">
                            <input type="checkbox" name="is_premium" value="1" @checked(old('is_premium', $template?->is_premium)) class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="block text-xs font-bold text-slate-900">PRO / Premium Template</span>
                                <span class="text-[11px] text-slate-500">Restrict access to paid subscribers only</span>
                            </div>
                        </label>
                    </div>
                </section>

                <!-- 4. APPEARANCE & STYLING CARD -->
                <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-bold text-slate-900">Default Styling & Appearance</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Primary brand color accent and base typography font family.</p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Primary Color Picker + Swatches -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Primary Color *</label>
                            <div class="flex items-center gap-3">
                                <input name="primary_color" 
                                       type="color" 
                                       x-model="primaryColor"
                                       required 
                                       class="h-10 w-16 cursor-pointer rounded-xl border border-slate-200 p-1">
                                <input type="text" 
                                       x-model="primaryColor" 
                                       class="w-32 rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono font-semibold uppercase text-slate-800">
                            </div>
                            <!-- Color Swatches -->
                            <div class="mt-3 flex items-center gap-2">
                                <span class="text-[10px] text-slate-400 font-semibold uppercase">Presets:</span>
                                @foreach(['#3155e7', '#0f172a', '#059669', '#d97706', '#dc2626', '#7c3aed'] as $color)
                                    <button type="button" 
                                            x-on:click="primaryColor = '{{ $color }}'" 
                                            class="h-5 w-5 rounded-full border border-white shadow-xs transition hover:scale-110" 
                                            style="background-color: {{ $color }}"></button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Font Family Picker + Suggestions -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Font Family *</label>
                            <input name="font_family" 
                                   x-model="fontFamily"
                                   required 
                                   class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-900 shadow-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                            <!-- Font Chips -->
                            <div class="mt-3 flex flex-wrap items-center gap-1.5">
                                <span class="text-[10px] text-slate-400 font-semibold uppercase">Presets:</span>
                                @foreach(['Inter', 'Poppins', 'Roboto', 'Lato', 'Merriweather'] as $font)
                                    <button type="button" 
                                            x-on:click="fontFamily = '{{ $font }}, sans-serif'"
                                            class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                        {{ $font }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 5. SUPPORTED SECTIONS & PREVIEW MEDIA -->
                <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-bold text-slate-900">Supported Sections & Preview Images</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Toggle compatible resume section blocks and preview screenshots.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-3">Supported Sections *</label>
                        <div class="grid gap-2.5 sm:grid-cols-3">
                            @foreach($sections as $key => $label)
                                <label class="flex items-center gap-2.5 rounded-xl border border-slate-200 p-3 text-xs font-semibold text-slate-800 cursor-pointer hover:bg-slate-50 transition">
                                    <input type="checkbox" 
                                           name="supported_sections[]" 
                                           value="{{ $key }}" 
                                           @checked(in_array($key, old('supported_sections', data_get($template?->config, 'supported_sections', array_keys($sections->all()))), true)) 
                                           class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Preview Image URLs <span class="normal-case font-normal text-slate-400">(Up to 5 screenshots)</span></label>
                        <div class="space-y-2">
                            @for($index = 0; $index < 5; $index++)
                                <input name="preview_images[]" 
                                       type="url" 
                                       value="{{ old('preview_images.'.$index, data_get($template?->config, 'preview_images.'.$index)) }}" 
                                       @if($index === 0) x-on:input="if(!thumbnailPreview || thumbnailPreview.startsWith('http')) thumbnailPreview = $event.target.value" @endif
                                       class="w-full rounded-xl border border-slate-200 px-4 py-2 text-xs font-mono text-slate-800 shadow-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" 
                                       placeholder="https://example.com/template-preview-{{ $index + 1 }}.png">
                            @endfor
                        </div>
                    </div>
                </section>

                <!-- UNRESOLVED FIELD MAPPING PANEL (If Present) -->
                @if($mappingCandidates)
                    <section class="overflow-hidden rounded-2xl border border-amber-300/80 bg-amber-50/50 shadow-xs">
                        <div class="border-b border-amber-200/80 bg-amber-100/40 px-6 py-4">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <h3 class="text-sm font-bold text-amber-950">Complete automatic field mapping</h3>
                            </div>
                            <p class="mt-1 text-xs leading-relaxed text-amber-800">
                                The original template layout was preserved, but these elements were not detected automatically with 100% confidence. Map each detected field to a standard ResumeHub placeholder.
                            </p>
                        </div>

                        <div class="divide-y divide-amber-200/60 p-4">
                            @foreach($mappingCandidates as $candidate)
                                <div class="grid gap-3 p-3 sm:grid-cols-[minmax(0,1fr)_240px] sm:items-center">
                                    <div>
                                        <span class="block text-xs font-bold text-slate-900">{{ $candidate['label'] ?? 'Unmapped Field' }}</span>
                                        <span class="mt-0.5 block truncate font-mono text-[11px] text-slate-500" title="{{ $candidate['preview'] ?? '' }}">{{ $candidate['preview'] ?? 'No text preview' }}</span>
                                    </div>
                                    <select name="template_mappings[{{ $candidate['id'] }}]" class="w-full rounded-xl border border-amber-300 bg-white px-3 py-2 text-xs font-semibold text-slate-800 shadow-xs">
                                        <option value="">Leave Unresolved</option>
                                        @foreach($placeholders as $key => $label)
                                            <option value="{{ $key }}" @selected(old('template_mappings.'.$candidate['id'], $candidate['suggested_placeholder'] ?? '') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                <!-- Validation Errors Alert -->
                @if($errors->any())
                    <div class="rounded-2xl border border-rose-200 bg-rose-50/80 p-5 text-xs text-rose-800 shadow-xs">
                        <p class="font-bold text-sm text-rose-900">Please correct the following errors:</p>
                        <ul class="mt-2 list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </div>

            <!-- RIGHT STICKY SIDEBAR -->
            <aside class="space-y-6 lg:sticky lg:top-8 lg:self-start">

                <!-- LIVE PREVIEW / THUMBNAIL CARD -->
                <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Thumbnail & Preview</h3>
                    
                    <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-slate-100 aspect-[210/297] flex items-center justify-center group shadow-xs">
                        <template x-if="thumbnailPreview">
                            <img :src="thumbnailPreview" alt="Thumbnail Preview" class="h-full w-full object-cover">
                        </template>
                        <template x-if="!thumbnailPreview">
                            <div class="text-center p-4">
                                <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                <span class="mt-2 block text-xs font-semibold text-slate-400">No Image Thumbnail</span>
                            </div>
                        </template>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Upload Thumbnail Image</label>
                        <input type="file" 
                               name="thumbnail" 
                               accept="image/png,image/jpeg,image/webp" 
                               x-on:change="handleThumbnail($event.target.files[0])"
                               class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200 transition">
                        <p class="mt-1 text-[11px] text-slate-400">PNG, JPG or WEBP (Max 2MB)</p>
                    </div>
                </section>

                <!-- SEARCHABLE & GROUPED PLACEHOLDERS CARD (One-Click Copy) -->
                <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Placeholder Variables</h3>
                        <span class="text-[10px] text-slate-400">Click to copy</span>
                    </div>

                    <!-- Search Filter -->
                    <div class="relative">
                        <input type="text" 
                               x-model="placeholderSearch"
                               placeholder="Search variables..." 
                               class="w-full rounded-xl border border-slate-200 pl-8 pr-3 py-1.5 text-xs text-slate-800 shadow-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        <svg class="absolute left-2.5 top-2 h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    </div>

                    <!-- Grouped Placeholders -->
                    <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                        @php
                            $groups = [
                                'Personal' => ['full_name', 'job_title', 'photo'],
                                'Contact' => ['email', 'phone', 'location', 'website'],
                                'Content Slots' => ['summary', 'content_sections', 'sidebar_sections'],
                                'Layout Hints' => ['layout_class', 'profile_class'],
                                'Modular' => ['experiences', 'education', 'skills', 'projects', 'languages', 'certifications', 'awards', 'references', 'social_links']
                            ];
                        @endphp

                        @foreach($groups as $groupName => $keys)
                            <div x-show="!placeholderSearch || '{{ strtolower(implode(' ', $keys)) }}'.includes(placeholderSearch.toLowerCase())">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">{{ $groupName }}</span>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($keys as $key)
                                        <button type="button"
                                                x-show="!placeholderSearch || '{{ $key }}'.includes(placeholderSearch.toLowerCase())"
                                                x-on:click="copyPlaceholder('{{ $key }}')"
                                                title="Click to copy \{\{ {{ $key }} \}\}"
                                                class="group inline-flex items-center gap-1 rounded-lg bg-indigo-50/70 border border-indigo-100 px-2 py-1 text-[11px] font-mono font-semibold text-indigo-700 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition">
                                            <span>\{\{ {{ $key }} \}\}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <!-- SAVE ACTIONS CARD -->
                <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs space-y-3">
                    <button type="submit" 
                            :disabled="saving"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-xs font-bold text-white shadow-md shadow-indigo-500/20 hover:bg-indigo-700 active:scale-[0.98] disabled:opacity-60 transition">
                        <svg x-show="saving" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24" x-cloak><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="saving ? 'Saving Template...' : '{{ $editing ? 'Save Changes' : 'Upload Template' }}'"></span>
                    </button>

                    <a href="{{ route('admin.templates') }}" 
                       class="w-full inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Cancel
                    </a>
                </section>

            </aside>
        </form>
    </div>
</x-admin-layout>
