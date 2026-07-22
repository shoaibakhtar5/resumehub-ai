<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Exception;

class GeminiService implements AiProviderInterface
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.ai.gemini.api_key') ?? '';
        $this->model = config('services.ai.model') ?? 'gemini-2.0-flash';
    }

    /**
     * Generate content using Google Gemini API.
     *
     * @param string $systemPrompt
     * @param string $userPrompt
     * @param array $options
     * @return string
     * @throws Exception
     */
    public function generateResponse(string $systemPrompt, string $userPrompt, array $options = []): string
    {
        if (app()->runningUnitTests()) {
            return 'Mocked AI suggestion for test.';
        }

        if (empty($this->apiKey)) {
            throw new Exception('Google Gemini API Key is not configured. Please add GEMINI_API_KEY to your .env file.');
        }

        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        // Combine system instruction into the prompt or structure if supported.
        // For Gemini, we can pass systemInstruction in the request body.
        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $userPrompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => (float) ($options['temperature'] ?? 0.7),
                'maxOutputTokens' => (int) ($options['max_tokens'] ?? 2048),
            ]
        ];

        if (!empty($systemPrompt)) {
            $payload['systemInstruction'] = [
                'parts' => [
                    ['text' => $systemPrompt]
                ]
            ];
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->timeout($options['timeout'] ?? 30)
            ->post($endpoint, $payload);

            if ($response->failed()) {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? 'Gemini API returned an error (' . $response->status() . ')';
                
                // Handle specific HTTP error status codes
                if ($response->status() === 429) {
                    throw new Exception('Gemini rate limit exceeded. Please try again in a few moments.');
                }
                if ($response->status() === 401) {
                    throw new Exception('Gemini API key is unauthorized (401). Check your GEMINI_API_KEY in .env.');
                }
                if ($response->status() === 400 && str_contains($errorMessage, 'API key')) {
                    throw new Exception('Invalid Gemini API key. Please check your credentials in the .env file.');
                }
                
                throw new Exception($errorMessage);
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            if (empty($text)) {
                throw new Exception('Google Gemini API returned an empty response. It might have blocked the prompt due to safety settings.');
            }

            return $text;
        } catch (Exception $e) {
            // Only wrap if it's an unexpected/connection-level error, not our own thrown exceptions
            if (str_starts_with($e->getMessage(), 'Gemini') || str_starts_with($e->getMessage(), 'Google') || str_starts_with($e->getMessage(), 'Invalid')) {
                throw $e;
            }
            throw new Exception('AI Generation failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
