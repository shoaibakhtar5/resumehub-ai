<?php

namespace App\Services\Ai;

use App\Models\AiHistory;
use App\Models\User;

class ResumeAiService
{
    public function __construct(private readonly AIService $aiService)
    {
    }

    /**
     * Generate AI suggestion by delegating to the unified AI Service Layer.
     *
     * @param User $user
     * @param array $data
     * @return AiHistory
     */
    public function generate(User $user, array $data): AiHistory
    {
        return $this->aiService->generate($user, $data);
    }
}
