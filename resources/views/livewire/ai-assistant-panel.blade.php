<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between pb-2 border-b border-slate-200">
        <div>
            <h3 class="font-display text-sm font-semibold text-slate-900 flex items-center gap-1.5">
                <span class="inline-flex h-5 w-5 items-center justify-center rounded bg-indigo-50 text-indigo-600">
                    <x-ui.icon name="sparkles" class="h-3.5 w-3.5" />
                </span>
                AI Assistant
            </h3>
            <p class="text-[10px] text-slate-500 mt-0.5">
                Active: <span class="font-medium text-indigo-600 uppercase">{{ str_replace('_', ' ', $activeSection) }}</span>
            </p>
        </div>
        <button type="button" wire:click="clearAll" class="text-[10px] text-slate-400 hover:text-slate-600 font-medium">
            Reset
        </button>
    </div>

    <!-- Job Description Input (Always useful for contextualization) -->
    <div class="space-y-1.5">
        <label class="block text-[10px] font-semibold text-slate-700 uppercase tracking-wider">
            Target Job Description (Optional)
        </label>
        <textarea 
            wire:model.live.debounce.500ms="jobDescription" 
            placeholder="Paste target job description to tailor the AI output..." 
            class="w-full text-xs rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2 h-16 resize-none bg-slate-50/50"
        ></textarea>
    </div>

    <!-- Custom Prompt Input -->
    <div class="space-y-1.5">
        <label class="block text-[10px] font-semibold text-slate-700 uppercase tracking-wider">
            Custom Instructions (Optional)
        </label>
        <input 
            type="text" 
            wire:model.live="input" 
            placeholder="e.g. Focus on leadership; write in first person..." 
            class="w-full text-xs rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2 bg-slate-50/50"
        />
    </div>

    <!-- Tone & Parameters -->
    <div class="grid grid-cols-2 gap-2">
        <div class="space-y-1">
            <label class="block text-[9px] font-semibold text-slate-500 uppercase">Tone</label>
            <select wire:model.live="tone" class="w-full text-xs rounded-lg border border-slate-200 py-1 px-2 focus:border-indigo-500 bg-white">
                <option value="professional">Professional</option>
                <option value="confident">Confident</option>
                <option value="casual">Casual</option>
                <option value="academic">Academic</option>
                <option value="minimalist">Concise</option>
            </select>
        </div>
        <div class="space-y-1">
            <label class="block text-[9px] font-semibold text-slate-500 uppercase">Action Target</label>
            <select wire:model.live="activeSection" class="w-full text-xs rounded-lg border border-slate-200 py-1 px-2 focus:border-indigo-500 bg-white">
                <option value="personal">Headline / Job Title</option>
                <option value="summary">Professional Summary</option>
                <option value="experience">Work Experience</option>
                <option value="skills">Skills</option>
                <option value="projects">Projects</option>
                <option value="certifications">Certifications</option>
                <option value="awards">Awards & Honors</option>
                <option value="cover_letter">Cover Letter</option>
                <option value="ats">ATS Review</option>
                <option value="score">Calculate Score</option>
            </select>
        </div>
    </div>

    <!-- Contextual Suggestion Actions -->
    <div class="space-y-1.5 pt-2">
        <span class="block text-[10px] font-semibold text-slate-700 uppercase tracking-wider">Suggested Actions</span>
        
        <div class="grid grid-cols-1 gap-1.5">
            @if ($activeSection === 'personal')
                <button type="button" wire:click="runAction('experience')" class="studio-ai-action-btn">
                    <x-ui.icon name="sparkles" class="h-3.5 w-3.5 text-indigo-500" />
                    <span>Improve Job Title / Headline</span>
                </button>
            @elseif ($activeSection === 'summary')
                <button type="button" wire:click="runAction('summary')" class="studio-ai-action-btn">
                    <x-ui.icon name="sparkles" class="h-3.5 w-3.5 text-indigo-500" />
                    <span>Generate Professional Summary</span>
                </button>
                <button type="button" wire:click="runAction('grammar')" class="studio-ai-action-btn-secondary">
                    <x-ui.icon name="check" class="h-3.5 w-3.5 text-slate-500" />
                    <span>Improve Grammar & Clarity</span>
                </button>
            @elseif ($activeSection === 'experience')
                <button type="button" wire:click="runAction('experience')" class="studio-ai-action-btn">
                    <x-ui.icon name="sparkles" class="h-3.5 w-3.5 text-indigo-500" />
                    <span>Improve Experience Descriptions</span>
                </button>
                <button type="button" wire:click="runAction('bullet_points')" class="studio-ai-action-btn">
                    <x-ui.icon name="list-bullet" class="h-3.5 w-3.5 text-indigo-500" />
                    <span>Rewrite as STAR Bullet Points</span>
                </button>
                <button type="button" wire:click="runAction('grammar')" class="studio-ai-action-btn-secondary">
                    <x-ui.icon name="check" class="h-3.5 w-3.5 text-slate-500" />
                    <span>Correct Grammar & Structure</span>
                </button>
            @elseif ($activeSection === 'skills')
                <button type="button" wire:click="runAction('skills')" class="studio-ai-action-btn">
                    <x-ui.icon name="sparkles" class="h-3.5 w-3.5 text-indigo-500" />
                    <span>Generate Industry Skills</span>
                </button>
            @elseif ($activeSection === 'projects')
                <button type="button" wire:click="runAction('projects')" class="studio-ai-action-btn">
                    <x-ui.icon name="sparkles" class="h-3.5 w-3.5 text-indigo-500" />
                    <span>Improve Projects Description</span>
                </button>
                <button type="button" wire:click="runAction('grammar')" class="studio-ai-action-btn-secondary">
                    <x-ui.icon name="check" class="h-3.5 w-3.5 text-slate-500" />
                    <span>Improve Grammar</span>
                </button>
            @elseif ($activeSection === 'certifications')
                <button type="button" wire:click="runAction('certifications')" class="studio-ai-action-btn">
                    <x-ui.icon name="sparkles" class="h-3.5 w-3.5 text-indigo-500" />
                    <span>Suggest Relevant Certifications</span>
                </button>
            @elseif ($activeSection === 'awards')
                <button type="button" wire:click="runAction('achievements')" class="studio-ai-action-btn">
                    <x-ui.icon name="sparkles" class="h-3.5 w-3.5 text-indigo-500" />
                    <span>Optimize Awards & Honors Impact</span>
                </button>
                <button type="button" wire:click="runAction('grammar')" class="studio-ai-action-btn-secondary">
                    <x-ui.icon name="check" class="h-3.5 w-3.5 text-slate-500" />
                    <span>Improve Grammar</span>
                </button>
            @elseif ($activeSection === 'cover_letter')
                <button type="button" wire:click="runAction('cover_letter')" class="studio-ai-action-btn">
                    <x-ui.icon name="envelope" class="h-3.5 w-3.5 text-indigo-500" />
                    <span>Generate Target Cover Letter</span>
                </button>
            @elseif ($activeSection === 'ats')
                <button type="button" wire:click="runAction('ats')" class="studio-ai-action-btn">
                    <x-ui.icon name="shield-check" class="h-3.5 w-3.5 text-indigo-500" />
                    <span>Run ATS Compliance Review</span>
                </button>
                <button type="button" wire:click="runAction('keyword_optimizer')" class="studio-ai-action-btn">
                    <x-ui.icon name="command-line" class="h-3.5 w-3.5 text-indigo-500" />
                    <span>Analyze Keyword Gaps</span>
                </button>
            @elseif ($activeSection === 'score')
                <button type="button" wire:click="runAction('score')" class="studio-ai-action-btn">
                    <x-ui.icon name="chart-bar" class="h-3.5 w-3.5 text-indigo-500" />
                    <span>Calculate Resume Score & Tips</span>
                </button>
            @else
                <button type="button" wire:click="runAction('grammar')" class="studio-ai-action-btn">
                    <x-ui.icon name="sparkles" class="h-3.5 w-3.5 text-indigo-500" />
                    <span>Improve Grammar & Formatting</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Busy/Loading State -->
    <div wire:loading wire:target="runAction" class="w-full pt-4">
        <div class="flex flex-col items-center justify-center space-y-2 p-6 rounded-xl border border-dashed border-indigo-200 bg-indigo-50/30">
            <!-- Shimmer Animation Spinner -->
            <div class="relative h-8 w-8 animate-spin rounded-full border-2 border-indigo-100 border-t-indigo-600">
                <span class="absolute top-1 left-1 block h-1.5 w-1.5 rounded-full bg-indigo-600 animate-ping"></span>
            </div>
            <p class="text-xs font-medium text-indigo-700 animate-pulse">
                Gemini is composing suggestion...
            </p>
            <p class="text-[10px] text-slate-400">This may take a few seconds.</p>
        </div>
    </div>

    <!-- Response Container (Only show when not loading) -->
    <div wire:loading.remove>
        <!-- Alerts/Toasts -->
        @if ($errorMessage)
            <div class="p-3 mb-3 text-xs text-red-700 bg-red-50 rounded-lg border border-red-200 flex items-start gap-2 relative">
                <span class="mt-0.5 text-red-500 font-bold">⚠️</span>
                <div class="flex-1 pr-4">
                    <p class="font-semibold">AI Error</p>
                    <p class="text-[11px] leading-relaxed mt-0.5">{{ $errorMessage }}</p>
                </div>
                <button type="button" wire:click="$set('errorMessage', '')" class="absolute top-2 right-2 text-red-400 hover:text-red-700">
                    ✕
                </button>
            </div>
        @endif

        @if ($successMessage)
            <div class="p-3 mb-3 text-xs text-emerald-700 bg-emerald-50 rounded-lg border border-emerald-200 flex items-start gap-2 relative">
                <span class="mt-0.5 text-emerald-500 font-bold">✓</span>
                <div class="flex-1 pr-4">
                    <p class="font-semibold">{{ $successMessage }}</p>
                </div>
                <button type="button" wire:click="$set('successMessage', '')" class="absolute top-2 right-2 text-emerald-400 hover:text-emerald-700">
                    ✕
                </button>
            </div>
        @endif

        <!-- AI Output Box -->
        @if ($suggestion)
            <div class="space-y-2 pt-2 animate-fadeIn">
                <div class="flex items-center justify-between">
                    <span class="block text-[10px] font-semibold text-slate-700 uppercase tracking-wider">AI Suggestion</span>
                    <div class="flex items-center gap-1.5">
                        @if (!empty($history))
                            <button type="button" wire:click="undoSuggestion" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-0.5" title="Undo applying this suggestion">
                                ↺ Undo
                            </button>
                        @endif
                        <button 
                            type="button" 
                            x-on:click="navigator.clipboard.writeText($refs.aiOutputText.innerText); $wire.set('successMessage', 'Copied to clipboard!')" 
                            class="text-[10px] text-indigo-600 hover:text-indigo-800 font-medium"
                        >
                            Copy
                        </button>
                    </div>
                </div>

                <div class="rounded-xl border border-indigo-100 bg-gradient-to-b from-indigo-50/20 to-white p-3 shadow-sm">
                    <div 
                        x-ref="aiOutputText" 
                        class="text-xs text-slate-800 leading-relaxed font-sans whitespace-pre-wrap select-all max-h-60 overflow-y-auto"
                    >{{ $suggestion }}</div>
                </div>

                <!-- Apply suggestion button (only make sense for forms, not generic reviews) -->
                @if (in_array($activeSection, ['personal', 'summary', 'experience', 'skills', 'projects', 'certifications', 'awards'], true))
                    <div class="flex gap-2">
                        <button 
                            type="button" 
                            wire:click="applySuggestion" 
                            class="flex-1 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-medium text-xs py-2 px-3 rounded-lg shadow-sm transition-all duration-200 transform hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-1.5"
                        >
                            <x-ui.icon name="check" class="h-3.5 w-3.5" />
                            Apply to Resume
                        </button>
                        <button 
                            type="button" 
                            wire:click="runAction(activeSection === 'skills' ? 'skills' : (activeSection === 'summary' ? 'summary' : 'experience'))" 
                            class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 rounded-lg text-xs font-medium"
                        >
                            Regenerate
                        </button>
                    </div>
                @else
                    <!-- For cover letters, ATS reviews, scores -->
                    <button 
                        type="button" 
                        x-on:click="navigator.clipboard.writeText($refs.aiOutputText.innerText); $wire.set('successMessage', 'Copied to clipboard!')" 
                        class="w-full bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs py-2 px-3 rounded-lg shadow-sm transition flex items-center justify-center gap-1.5"
                    >
                        <x-ui.icon name="document-duplicate" class="h-3.5 w-3.5" />
                        Copy Result Content
                    </button>
                @endif
            </div>
        @endif
    </div>

    <!-- Extra Design Styles -->
    <style>
        .studio-ai-action-btn {
            display: flex;
            width: 100%;
            align-items: center;
            gap: 8px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            padding: 8px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 500;
            color: #334155;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.15s ease;
        }
        .studio-ai-action-btn:hover {
            border-color: #cbd5e1;
            background-color: #f8fafc;
            color: #0f172a;
        }
        .studio-ai-action-btn-secondary {
            display: flex;
            width: 100%;
            align-items: center;
            gap: 8px;
            border-radius: 8px;
            border: 1px dashed #cbd5e1;
            background-color: transparent;
            padding: 8px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 500;
            color: #475569;
            transition: all 0.15s ease;
        }
        .studio-ai-action-btn-secondary:hover {
            border-color: #94a3b8;
            background-color: #f1f5f9;
            color: #1e293b;
        }
    </style>
</div>
