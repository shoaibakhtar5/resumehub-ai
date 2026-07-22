<x-app-layout title="AI Resume Studio" mode="user">
    <div class="space-y-8 pb-12">
        <!-- Premium Header Block -->
        <x-ui.page-header eyebrow="AI Studio Active" title="AI Resume Studio" description="Turn role descriptions into tailored bullets, summaries, skills, and recruiter-ready positioning.">
            <x-ui.button href="{{ route('resume.builder') }}" icon="plus-circle" variant="dark">Create Resume</x-ui.button>
            <x-ui.button href="{{ route('ats.checker') }}" variant="secondary" icon="shield-check">Run ATS Scan</x-ui.button>
        </x-ui.page-header>

        @if (session('status'))
            <div class="p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 border border-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 mb-4 text-sm text-red-800 rounded-xl bg-red-50 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        @if (session('ai_output'))
            <div class="p-6 rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50/40 via-white to-indigo-50/10 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-5">
                    <x-ui.icon name="sparkles" class="h-32 w-32 text-indigo-600" />
                </div>
                <h3 class="font-display text-sm font-semibold text-slate-900 flex items-center gap-1.5">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded bg-indigo-50 text-indigo-600">
                        <x-ui.icon name="sparkles" class="h-3.5 w-3.5" />
                    </span>
                    Latest AI Generation
                </h3>
                <p class="mt-4 whitespace-pre-line text-xs leading-relaxed text-slate-700 bg-slate-50/50 p-4 rounded-xl border border-slate-200/60 select-all font-sans">{{ session('ai_output') }}</p>
                <div class="mt-4 flex gap-2">
                    <button type="button" onclick="navigator.clipboard.writeText(`{{ addslashes(session('ai_output')) }}`)" class="text-xs bg-slate-900 hover:bg-slate-800 text-white font-medium py-1.5 px-3 rounded-lg flex items-center gap-1 transition">
                        <x-ui.icon name="document-duplicate" class="h-3.5 w-3.5" /> Copy output
                    </button>
                </div>
            </div>
        @endif

        <!-- Studio Landing Grid -->
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Usage & Balance Card -->
            <div class="lg:col-span-1 rounded-2xl border border-indigo-50 bg-gradient-to-b from-indigo-50/40 to-white p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-semibold text-indigo-600 uppercase tracking-wider">Usage & Plan Balance</span>
                        <x-ui.badge variant="ai" icon="sparkles" class="bg-indigo-600 text-white font-semibold">Pro Active</x-ui.badge>
                    </div>
                    <div class="mt-6 flex items-baseline gap-1">
                        <span class="text-4xl font-extrabold text-slate-900 tracking-tight">68</span>
                        <span class="text-xs text-slate-500 font-medium">/ 100 monthly credits</span>
                    </div>
                    
                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100/80">
                        <div class="h-full rounded-full bg-gradient-to-r from-indigo-600 to-violet-600" style="width: 68%"></div>
                    </div>
                    <p class="mt-2.5 text-[10px] text-slate-400 font-medium flex items-center gap-1">
                        <span>⏳ Resets in 12 days</span>
                        <span class="text-slate-300">•</span>
                        <span>Daily limit: 1,500 requests</span>
                    </p>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100 space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">AI Match Lift</span>
                        <span class="font-bold text-emerald-600 font-display">+31% higher match</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Upgrade quality</span>
                        <span class="font-bold text-slate-700">24 impact statements</span>
                    </div>
                </div>
            </div>

            <!-- Quick Action Hub Form -->
            <div class="lg:col-span-2 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h3 class="font-display text-sm font-semibold text-slate-900 flex items-center gap-1.5">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded bg-indigo-50 text-indigo-600">
                        <x-ui.icon name="command-line" class="h-3.5 w-3.5" />
                    </span>
                    AI Resume Command Center
                </h3>
                
                <form method="POST" action="{{ route('ai.generate') }}" class="mt-6 space-y-4">
                    @csrf
                    <input type="hidden" name="feature" value="ai-resume-studio">
                    
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Select Resume Context</label>
                            <select name="resume_id" class="w-full text-xs rounded-xl border border-slate-200 p-2 focus:border-indigo-500 focus:ring-indigo-500 bg-white text-slate-800">
                                <option value="">No Resume Context (Generic)</option>
                                @foreach ($resumes as $r)
                                    <option value="{{ $r->id }}">{{ $r->title }} (Score: {{ $r->completion_score }}%)</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">AI Action Type</label>
                            <select name="action" class="w-full text-xs rounded-xl border border-slate-200 p-2 focus:border-indigo-500 focus:ring-indigo-500 bg-white text-slate-800">
                                <option value="summary">Professional Summary Generator</option>
                                <option value="experience">STAR Bullet Points Rewriter</option>
                                <option value="skills">Suggest Industry Skills</option>
                                <option value="cover_letter">Draft Custom Cover Letter</option>
                                <option value="interview_questions">Generate Interview Practice Questions</option>
                                <option value="review">Candid Expert Review</option>
                                <option value="score">ATS Readiness Score & Tips</option>
                                <option value="keywords">Extract Job Keywords</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tone Profile</label>
                            <input type="text" name="tone" value="confident" placeholder="e.g. professional, confident, concise" class="w-full text-xs rounded-xl border border-slate-200 p-2 focus:border-indigo-500 focus:ring-indigo-500 text-slate-800">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Target Job Description (Highly Recommended)</label>
                            <input type="text" name="job_description" placeholder="Paste job description here to align the suggestions..." class="w-full text-xs rounded-xl border border-slate-200 p-2 focus:border-indigo-500 focus:ring-indigo-500 text-slate-800">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Custom Prompt or Profile Notes</label>
                        <textarea name="input" rows="3" placeholder="Provide extra instructions e.g. Focus on cloud architecture; limit word count..." class="w-full text-xs rounded-xl border border-slate-200 p-2 focus:border-indigo-500 focus:ring-indigo-500 text-slate-800 resize-none"></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <span class="text-[10px] text-slate-400 font-medium">✨ Powered by Google Gemini 2.0 Flash</span>
                        <x-ui.button type="submit" variant="dark" icon="sparkles">Execute AI Command</x-ui.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- AI Feature Modules Grid -->
        <div class="space-y-4">
            <h3 class="font-display text-sm font-semibold text-slate-900">Dedicated AI Modules</h3>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('ats.checker') }}" class="group block rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm hover:shadow-md hover:border-indigo-100 transition duration-200">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition duration-200">
                        <x-ui.icon name="shield-check" class="h-4 w-4" />
                    </span>
                    <h4 class="mt-4 font-display text-xs font-semibold text-slate-900 group-hover:text-indigo-600 transition">ATS Compliance Scan</h4>
                    <p class="mt-1.5 text-[11px] leading-relaxed text-slate-500">Scan structure, keyword coverage, and format errors that block Applicant Tracking Systems.</p>
                </a>

                <a href="{{ route('cover-letter') }}" class="group block rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm hover:shadow-md hover:border-indigo-100 transition duration-200">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition duration-200">
                        <x-ui.icon name="envelope" class="h-4 w-4" />
                    </span>
                    <h4 class="mt-4 font-display text-xs font-semibold text-slate-900 group-hover:text-indigo-600 transition">Cover Letter Draft</h4>
                    <p class="mt-1.5 text-[11px] leading-relaxed text-slate-500">Produce tailor-made cover letters that link your experience cleanly to job requirements.</p>
                </a>

                <a href="{{ route('keyword.optimizer') }}" class="group block rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm hover:shadow-md hover:border-indigo-100 transition duration-200">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition duration-200">
                        <x-ui.icon name="command-line" class="h-4 w-4" />
                    </span>
                    <h4 class="mt-4 font-display text-xs font-semibold text-slate-900 group-hover:text-indigo-600 transition">Keyword Optimization</h4>
                    <p class="mt-1.5 text-[11px] leading-relaxed text-slate-500">Identify keyword gaps against job descriptions and organically inject critical industry phrases.</p>
                </a>

                <a href="{{ route('interview.questions') }}" class="group block rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm hover:shadow-md hover:border-indigo-100 transition duration-200">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition duration-200">
                        <x-ui.icon name="chat-bubble-left-right" class="h-4 w-4" />
                    </span>
                    <h4 class="mt-4 font-display text-xs font-semibold text-slate-900 group-hover:text-indigo-600 transition">Interview Practice Questions</h4>
                    <p class="mt-1.5 text-[11px] leading-relaxed text-slate-500">Simulate practice questions and STAR-method guidelines mapped to your exact resume.</p>
                </a>

                <a href="{{ route('resume.review') }}" class="group block rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm hover:shadow-md hover:border-indigo-100 transition duration-200">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition duration-200">
                        <x-ui.icon name="document-magnifying-glass" class="h-4 w-4" />
                    </span>
                    <h4 class="mt-4 font-display text-xs font-semibold text-slate-900 group-hover:text-indigo-600 transition">Expert Resume Review</h4>
                    <p class="mt-1.5 text-[11px] leading-relaxed text-slate-500">Receive actionable styling, clarity, hierarchy, and credential optimization reports.</p>
                </a>

                <a href="{{ route('resume.score') }}" class="group block rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm hover:shadow-md hover:border-indigo-100 transition duration-200">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition duration-200">
                        <x-ui.icon name="chart-bar" class="h-4 w-4" />
                    </span>
                    <h4 class="mt-4 font-display text-xs font-semibold text-slate-900 group-hover:text-indigo-600 transition">ATS Readiness Score</h4>
                    <p class="mt-1.5 text-[11px] leading-relaxed text-slate-500">Review real-time numerical parsing scores, layout issues, and clear action recommendations.</p>
                </a>
            </div>
        </div>

        <!-- Recent AI history timeline -->
        <div class="space-y-4">
            <h3 class="font-display text-sm font-semibold text-slate-900">Recent AI Execution Logs</h3>
            <div class="rounded-2xl border border-slate-200/70 bg-white overflow-hidden shadow-sm">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider text-[9px]">
                            <th class="p-4">AI Action</th>
                            <th class="p-4">Target Resume</th>
                            <th class="p-4">Triggered At</th>
                            <th class="p-4">Latency</th>
                            <th class="p-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($aiHistories as $history)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="p-4 font-medium flex items-center gap-1.5">
                                    <span class="h-1.5 w-1.5 rounded-full bg-indigo-600"></span>
                                    {{ $history->title }}
                                </td>
                                <td class="p-4 text-slate-500">
                                    {{ $history->resume?->title ?? 'Generic Context' }}
                                </td>
                                <td class="p-4 text-slate-400">
                                    {{ $history->created_at?->diffForHumans() }}
                                </td>
                                <td class="p-4 text-slate-500 font-medium">
                                    {{ number_format(($history->metadata['latency_ms'] ?? 0) / 1000, 2) }}s
                                </td>
                                <td class="p-4 text-right">
                                    <button type="button" onclick="navigator.clipboard.writeText(`{{ addslashes($history->output) }}`); alert('Output copied to clipboard!')" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold transition">
                                        Copy Output
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400 font-medium">
                                    No recent AI execution history logs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
