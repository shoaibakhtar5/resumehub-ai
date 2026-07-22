<?php

namespace App\Services\Ai;

class PromptBuilder
{
    /**
     * Build the system prompt based on the action.
     *
     * @param string $action
     * @return string
     */
    public function getSystemPrompt(string $action): string
    {
        $baseSystemPrompt = "You are an expert career consultant and professional resume writer. Your goal is to help candidates optimize their resumes to stand out, pass Applicant Tracking Systems (ATS), and impress hiring managers.\n";

        $specificPrompt = match ($action) {
            'summary' => "Write a highly professional, compelling resume summary. It must be concise (2-4 sentences), impact-oriented, and highlight the candidate's core strengths, years of experience, and target alignment. Return ONLY the summary text. No introductory or concluding remarks, no labels, no quotes, no markdown formatting.",
            
            'experience', 'bullet_points' => "Rewrite the given work experience achievements/descriptions into highly professional, result-oriented bullet points. Use strong action verbs at the start of each bullet point, quantify achievements where possible (e.g., increased sales by 15%, reduced latency by 200ms), and apply the STAR (Situation, Task, Action, Result) methodology. Return ONLY the bullet points, each on a new line starting with a dash (-). No introductory or concluding remarks.",
            
            'skills' => "Analyze the candidate context and suggest a comma-separated list of relevant hard and soft skills. Do not include categories, formatting, or bullet points. Just return the skills separated by commas, e.g., 'React, TypeScript, Agile, Project Management'. Limit the response to 10-15 key skills.",
            
            'projects' => "Analyze the project details and improve the description. Focus on what was built, the technologies used, the candidate's contribution, and the outcome/impact. Return ONLY the enhanced description, formatted as a paragraph or clean bullet points. No introductory text.",
            
            'certifications' => "Suggest relevant industry certifications for the candidate's target role that would strengthen their profile. List 3-5 certifications, each on a new line with the certification name and issuer, e.g., 'AWS Certified Solutions Architect - Amazon Web Services'. Return ONLY the list.",
            
            'achievements' => "Rewrite the given achievements to maximize professional impact. Focus on metrics, scale, complexity, and concrete business outcomes. Return ONLY the bullet points starting with a dash (-). No explanations.",
            
            'cover_letter' => "Draft a professional, compelling, and tailored cover letter based on the candidate's resume, the target job title, and the job description (if provided). Use a standard business letter format. The tone should match the requested tone (e.g. professional, confident, or warm). Use placeholder text like [Hiring Manager Name] where necessary. Return ONLY the cover letter content.",
            
            'grammar' => "Proofread the input text and correct all spelling, grammar, punctuation, and phrasing errors. Improve readability while preserving the original meaning and professional context. Return ONLY the corrected text, maintaining the original structure. No remarks.",
            
            'rewrite_resume' => "Review and rewrite the provided resume content to optimize it for a professional target role. Ensure consistent tone, active voice, and professional syntax. Return the rewritten content in clean, structured plain text.",
            
            'keyword_optimizer' => "Analyze the resume context against the job description. Identify critical keywords, technical terms, and industry phrases that are missing from the resume. Return a structured list of: 1) Keywords to add to Skills, 2) Keywords to weave into Experience descriptions, 3) Suggestions for placement. Be extremely concise.",
            
            'ats' => "Perform a comprehensive ATS (Applicant Tracking System) compatibility review. Analyze formatting risks, keyword gaps, section hierarchy, and overall readability. Provide clear, actionable recommendations to improve the ATS parse rate.",
            
            'score' => "Evaluate the resume score out of 100 based on completeness, keyword match (if job description is provided), impact metrics, and formatting. Provide a breakdown including: 1) Overall Score, 2) Keyword Match Score, 3) Formatting Score, 4) Impact Score, and 5) Top 3 actions to increase the score. Return the response in clean, human-readable markdown.",
            
            'interview_questions' => "Generate 5 highly relevant, role-specific behavioral and technical interview questions based on the candidate's resume and target role. For each question, provide a brief guidance note on how to structure a winning response using the STAR method.",
            
            'review' => "Provide a candid, expert review of the resume. Highlight what is working well, what needs immediate improvement, and what is missing to make it stand out for the target role.",
            
            'keywords' => "Extract the top 15-20 critical keywords and technical skills from the provided job description that the candidate should include in their resume. Return only a comma-separated list of these keywords.",
            
            default => "Provide professional career optimization suggestions based on the provided resume content."
        };

        return $baseSystemPrompt . $specificPrompt;
    }

    /**
     * Build the user prompt combining inputs, context, and requirements.
     *
     * @param string $action
     * @param string $input
     * @param string $resumeContext
     * @param string $jobDescription
     * @param string $tone
     * @return string
     */
    public function getUserPrompt(
        string $action,
        string $input,
        string $resumeContext,
        string $jobDescription = '',
        string $tone = 'professional'
    ): string {
        $prompt = "";

        if (!empty($resumeContext)) {
            $prompt .= "### CANDIDATE RESUME CONTEXT:\n{$resumeContext}\n\n";
        }

        if (!empty($jobDescription)) {
            $prompt .= "### TARGET JOB DESCRIPTION:\n{$jobDescription}\n\n";
        }

        if (!empty($input) && $input !== $resumeContext) {
            $prompt .= "### SPECIFIC INPUT/INSTRUCTION TO PROCESS:\n{$input}\n\n";
        }

        $prompt .= "### ADDITIONAL PARAMETERS:\n";
        $prompt .= "- Tone: " . (empty($tone) ? 'professional' : $tone) . "\n";
        
        $prompt .= "\n### INSTRUCTION:\n";
        $prompt .= match ($action) {
            'summary' => "Generate a professional summary for my resume. Align it with the target job description if provided.",
            'experience' => "Improve the following experience description to make it highly professional and impact-driven with action verbs.",
            'bullet_points' => "Rewrite the input into strong, quantified bullet points starting with active verbs.",
            'skills' => "List the most relevant skills based on my experience and target job.",
            'projects' => "Improve the description of my project to emphasize technical complexity and outcomes.",
            'certifications' => "What certifications should I acquire or list for this target role?",
            'achievements' => "Optimize these achievements to emphasize metrics, outcomes, and business scale.",
            'cover_letter' => "Draft a cover letter tailoring my experience to the target job description. Tone: {$tone}.",
            'grammar' => "Correct spelling, grammar, and sentence structure for the input text.",
            'rewrite_resume' => "Rewrite my resume content to align with the target job. Make it professional and clean.",
            'keyword_optimizer' => "What keywords should I add to my resume to match this job description, and where?",
            'ats' => "Review my resume against the target job description and list ATS issues and fixes.",
            'score' => "Score my resume out of 100 and provide a breakdown with optimization steps.",
            'interview_questions' => "Generate practice interview questions based on my resume and target job.",
            'review' => "Review my resume and provide professional critique on what can be improved.",
            'keywords' => "Extract keywords from the job description.",
            default => "Process the input content and optimize it for my resume."
        };

        return $prompt;
    }
}
