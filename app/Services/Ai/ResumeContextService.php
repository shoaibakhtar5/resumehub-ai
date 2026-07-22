<?php

namespace App\Services\Ai;

use App\Models\Resume;

class ResumeContextService
{
    /**
     * Build plain markdown text representing the entire resume context.
     *
     * @param Resume $resume
     * @return string
     */
    public function getFullResumeContext(Resume $resume): string
    {
        $resume->loadMissing([
            'profile', 'summary', 'socialLinks', 'experiences', 'educations', 
            'projects', 'skills', 'languages', 'certifications', 'awards', 
            'references', 'customSections.items'
        ]);

        $markdown = "# " . ($resume->profile?->full_name ?? 'Candidate Resume') . "\n";
        
        if ($resume->profile?->headline || $resume->target_role) {
            $markdown .= "Target Role / Headline: " . ($resume->profile?->headline ?? $resume->target_role) . "\n";
        }
        
        if ($resume->profile?->email || $resume->profile?->phone || $resume->profile?->location) {
            $markdown .= "Contact: " . implode(' | ', array_filter([
                $resume->profile?->email,
                $resume->profile?->phone,
                $resume->profile?->location,
                $resume->profile?->website
            ])) . "\n";
        }
        
        $markdown .= "\n";

        if ($resume->summary?->content) {
            $markdown .= "## Professional Summary\n";
            $markdown .= $resume->summary->content . "\n\n";
        }

        if ($resume->experiences->isNotEmpty()) {
            $markdown .= "## Work Experience\n";
            foreach ($resume->experiences->sortBy('sort_order') as $exp) {
                $markdown .= "### " . $exp->position . " at " . $exp->company;
                if ($exp->location) $markdown .= " (" . $exp->location . ")";
                $markdown .= "\n";
                
                $startDate = $exp->start_date ? $exp->start_date->format('M Y') : 'N/A';
                $endDate = $exp->is_current ? 'Present' : ($exp->end_date ? $exp->end_date->format('M Y') : 'N/A');
                $markdown .= "Duration: {$startDate} - {$endDate}\n";
                
                if ($exp->description) {
                    $markdown .= "Description:\n" . $exp->description . "\n";
                }
                
                if (!empty($exp->technologies)) {
                    $markdown .= "Technologies: " . implode(', ', (array) $exp->technologies) . "\n";
                }
                $markdown .= "\n";
            }
        }

        if ($resume->educations->isNotEmpty()) {
            $markdown .= "## Education\n";
            foreach ($resume->educations->sortBy('sort_order') as $edu) {
                $markdown .= "### " . $edu->degree . " in " . $edu->field_of_study . "\n";
                $markdown .= $edu->institution;
                if ($edu->location) $markdown .= ", " . $edu->location;
                $markdown .= "\n";
                
                $startDate = $edu->start_date ? $edu->start_date->format('Y') : 'N/A';
                $endDate = $edu->is_current ? 'Present' : ($edu->end_date ? $edu->end_date->format('Y') : 'N/A');
                $markdown .= "Timeline: {$startDate} - {$endDate}\n";
                
                if ($edu->grade) {
                    $markdown .= "Grade / GPA: " . $edu->grade . "\n";
                }
                
                if ($edu->description) {
                    $markdown .= "Description: " . $edu->description . "\n";
                }
                $markdown .= "\n";
            }
        }

        if ($resume->projects->isNotEmpty()) {
            $markdown .= "## Projects\n";
            foreach ($resume->projects->sortBy('sort_order') as $project) {
                $markdown .= "### " . $project->name;
                if ($project->role) $markdown .= " (" . $project->role . ")";
                $markdown .= "\n";
                
                if ($project->url) $markdown .= "URL: " . $project->url . "\n";
                if ($project->description) $markdown .= $project->description . "\n";
                if (!empty($project->technologies)) {
                    $markdown .= "Technologies: " . implode(', ', (array) $project->technologies) . "\n";
                }
                $markdown .= "\n";
            }
        }

        if ($resume->skills->isNotEmpty()) {
            $markdown .= "## Skills\n";
            $skillsGrouped = $resume->skills->groupBy('pivot.category');
            foreach ($skillsGrouped as $category => $skills) {
                $categoryName = $category ?: 'General Skills';
                $skillList = $skills->map(function ($s) {
                    $proficiency = $s->pivot?->proficiency;
                    return $s->name . ($proficiency ? " ({$proficiency})" : '');
                })->implode(', ');
                $markdown .= "- **{$categoryName}**: {$skillList}\n";
            }
            $markdown .= "\n";
        }

        if ($resume->certifications->isNotEmpty()) {
            $markdown .= "## Certifications\n";
            foreach ($resume->certifications->sortBy('sort_order') as $cert) {
                $markdown .= "- **" . $cert->name . "** - Issued by " . $cert->issuer;
                $date = $cert->issued_at ? $cert->issued_at->format('M Y') : null;
                if ($date) $markdown .= " ({$date})";
                $markdown .= "\n";
            }
            $markdown .= "\n";
        }

        if ($resume->awards->isNotEmpty()) {
            $markdown .= "## Awards & Honors\n";
            foreach ($resume->awards->sortBy('sort_order') as $award) {
                $markdown .= "- **" . $award->title . "** from " . $award->issuer;
                $date = $award->awarded_at ? $award->awarded_at->format('M Y') : null;
                if ($date) $markdown .= " ({$date})";
                $markdown .= "\n";
            }
            $markdown .= "\n";
        }

        return trim($markdown);
    }

    /**
     * Get context of a specific experience item.
     *
     * @param array $experience
     * @return string
     */
    public function getExperienceContext(array $experience): string
    {
        return sprintf(
            "Company: %s\nRole: %s\nLocation: %s\nDescription: %s\nTechnologies: %s",
            $experience['company'] ?? 'N/A',
            $experience['position'] ?? 'N/A',
            $experience['location'] ?? 'N/A',
            $experience['description'] ?? '',
            $experience['technologies'] ?? 'N/A'
        );
    }
}
