<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\AIServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiService implements AIServiceInterface
{
    protected ?string $apiKey;
    protected string $embeddingModel;
    protected string $chatModel;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->embeddingModel = config('services.gemini.embedding_model', 'text-embedding-004');
        $this->chatModel = config('services.gemini.chat_model', 'gemini-1.5-flash');
        $this->baseUrl = config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
    }

    /**
     * {@inheritDoc}
     */
    public function generateEmbedding(string $text): array
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Gemini API key is missing. Please configure GEMINI_API_KEY in your .env file.');
        }

        $url = "{$this->baseUrl}/models/{$this->embeddingModel}:embedContent?key={$this->apiKey}";

        $response = Http::withoutVerifying()
            ->timeout(15)
            ->retry(2, 500)
            ->post($url, [
                'model' => "models/{$this->embeddingModel}",
                'content' => [
                    'parts' => [
                        ['text' => mb_substr(trim($text), 0, 8000)],
                    ],
                ],
            ]);

        if ($response->failed()) {
            Log::error('Gemini Embedding API Error', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new RuntimeException('Failed to generate embedding from Gemini: ' . ($response->json('error.message') ?? 'Unknown error'));
        }

        $embedding = $response->json('embedding.values');

        if (!is_array($embedding)) {
            throw new RuntimeException('Invalid embedding format received from Gemini.');
        }

        return $embedding;
    }

    /**
     * {@inheritDoc}
     */
    public function generateSummary(string $content): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Gemini API key is missing. Please configure GEMINI_API_KEY in your .env file.');
        }

        $url = "{$this->baseUrl}/models/{$this->chatModel}:generateContent?key={$this->apiKey}";

        $prompt = "You are an expert executive assistant. Summarize the following note concisely in 2-3 clear, informative sentences highlighting the main points:\n\n" . $content;

        $response = Http::withoutVerifying()
            ->timeout(20)
            ->retry(2, 500)
            ->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'maxOutputTokens' => 300,
                ],
            ]);

        if ($response->failed()) {
            Log::error('Gemini GenerateContent API Error', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new RuntimeException('Failed to generate summary from Gemini: ' . ($response->json('error.message') ?? 'Unknown error'));
        }

        $summary = trim($response->json('candidates.0.content.parts.0.text') ?? '');

        if (empty($summary)) {
            throw new RuntimeException('Empty summary received from Gemini.');
        }

        return $summary;
    }
}
