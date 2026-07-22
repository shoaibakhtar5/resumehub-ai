<?php

namespace App\Services\Ai;

interface AiProviderInterface
{
    /**
     * Generate content from the provider using a system prompt and a user prompt.
     *
     * @param string $systemPrompt
     * @param string $userPrompt
     * @param array $options
     * @return string
     * @throws \Exception
     */
    public function generateResponse(string $systemPrompt, string $userPrompt, array $options = []): string;
}
