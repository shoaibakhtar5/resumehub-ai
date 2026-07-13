<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $resume->title }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #152238; font-family: DejaVu Sans, sans-serif; font-size: 9px; line-height: 1.55; }
        .page { width: 100%; min-height: 100vh; }
        .sidebar { width: 32%; min-height: 100vh; padding: 30px 22px; color: #fff; vertical-align: top; background: #172b45; }
        .content { width: 68%; padding: 34px 30px; vertical-align: top; }
        .photo { display: block; width: 88px; height: 88px; margin: 0 auto 18px; border: 4px solid rgba(255,255,255,.8); border-radius: 50%; object-fit: cover; }
        h1 { margin: 0; font-size: 21px; line-height: 1.15; }
        .role { margin-top: 5px; color: rgba(255,255,255,.85); }
        h2 { margin: 0 0 12px; padding-bottom: 6px; border-bottom: 1px solid #cbd5e1; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; }
        .sidebar h2 { margin-top: 24px; border-color: rgba(255,255,255,.3); color: #fff; }
        .muted { color: #526174; }
        .sidebar .muted { color: rgba(255,255,255,.82); }
        .section { margin-bottom: 24px; }
        .entry { margin-bottom: 16px; }
        .entry-title { font-weight: bold; }
        .date { float: right; color: #64748b; font-size: 8px; }
        ul { margin: 7px 0 0; padding-left: 14px; }
        p { margin: 3px 0; }
    </style>
</head>
<body>
@php
    $profile = $resume->profile;
    $theme = $settings['theme'] ?? [];
    $photo = $profile?->photo_path;
    $photoPath = $photo && str_starts_with($photo, '/storage/') ? public_path(ltrim($photo, '/')) : $photo;
@endphp
<table class="page" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sidebar" style="background: {{ $theme['accent_color'] ?? '#172b45' }};">
            @if ($photoPath)<img class="photo" src="{{ $photoPath }}" alt="">@endif
            <h1>{{ $profile?->full_name ?: $resume->title }}</h1>
            @if ($profile?->headline || $resume->target_role)<p class="role">{{ $profile?->headline ?: $resume->target_role }}</p>@endif

            @if (collect([$profile?->phone, $profile?->email, $profile?->location, $profile?->website])->filter()->isNotEmpty())
                <h2>Contact</h2>
                <div class="muted">
                    @if ($profile?->phone)<p>{{ $profile->phone }}</p>@endif
                    @if ($profile?->email)<p>{{ $profile->email }}</p>@endif
                    @if ($profile?->location)<p>{{ $profile->location }}</p>@endif
                    @if ($profile?->website)<p>{{ $profile->website }}</p>@endif
                </div>
            @endif

            @if ($resume->skills->isNotEmpty())
                <h2>Skills</h2>
                <ul>@foreach ($resume->skills as $skill)<li>{{ $skill->name }}</li>@endforeach</ul>
            @endif

            @if ($resume->educations->isNotEmpty())
                <h2>Education</h2>
                @foreach ($resume->educations as $education)
                    <div class="entry">
                        <p class="entry-title">{{ trim($education->degree.' '.$education->field_of_study) }}</p>
                        <p class="muted">{{ $education->institution }}</p>
                        <p class="muted">{{ $education->start_date?->format('Y') }}{{ $education->end_date ? ' – '.$education->end_date->format('Y') : '' }}</p>
                    </div>
                @endforeach
            @endif

            @if ($resume->languages->isNotEmpty())
                <h2>Languages</h2>
                @foreach ($resume->languages as $language)<p>{{ $language->name }} <span style="float:right">{{ $language->pivot?->proficiency }}</span></p>@endforeach
            @endif
        </td>
        <td class="content">
            @if ($resume->summary?->content)
                <div class="section"><h2>Professional Summary</h2><p class="muted">{!! nl2br(e($resume->summary->content)) !!}</p></div>
            @endif

            @if ($resume->experiences->isNotEmpty())
                <div class="section">
                    <h2>Experience</h2>
                    @foreach ($resume->experiences as $experience)
                        <div class="entry">
                            <span class="date">{{ $experience->start_date?->format('M Y') }}{{ $experience->is_current ? ' – Present' : ($experience->end_date ? ' – '.$experience->end_date->format('M Y') : '') }}</span>
                            <p class="entry-title">{{ $experience->position }}</p>
                            <p class="muted">{{ $experience->company }}{{ $experience->location ? ' · '.$experience->location : '' }}</p>
                            @if ($experience->description)<p class="muted">{!! nl2br(e($experience->description)) !!}</p>@endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($resume->projects->isNotEmpty())
                <div class="section">
                    <h2>Projects</h2>
                    @foreach ($resume->projects as $project)
                        <div class="entry">
                            <p class="entry-title">{{ $project->name }}{{ $project->role ? ' · '.$project->role : '' }}</p>
                            @if ($project->description)<p class="muted">{!! nl2br(e($project->description)) !!}</p>@endif
                            @if ($project->technologies)<p class="muted">{{ implode(' · ', $project->technologies) }}</p>@endif
                        </div>
                    @endforeach
                </div>
            @endif
        </td>
    </tr>
</table>
</body>
</html>
