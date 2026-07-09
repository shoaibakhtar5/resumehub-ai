<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $title = 'Welcome back',
        public string $eyebrow = 'AI-powered excellence',
        public string $heading = 'Build with precision AI.',
        public string $subheading = 'ResumeHub AI helps professionals create polished, ATS-ready resumes with intelligent guidance at every step.',
    ) {
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
