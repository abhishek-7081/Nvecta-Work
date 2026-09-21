<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\AIServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OpenAIService implements AIServiceInterface
{
    protected ?string $apiKey;
    protected string $embeddingModel;
    protected string $chatModel;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->embeddingModel = config('services.openai.embedding_model', 'text-embedding-3-small');
        $this->chatModel = config('services.openai.chat_model', 'gpt-4o-mini');
        $this->baseUrl = config('services.openai.base_url', 'https://api.openai.com/v1');
    }

    /**
     * {@inheritDoc}
     */
    public function generateEmbedding(string $text): array
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('OpenAI API key is missing. Please configure OPENAI_API_KEY in your .env file.');
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(15)
            ->retry(2, 500)
            ->post("{$this->baseUrl}/embeddings", [
                'model' => $this->embeddingModel,
                'input' => mb_substr(trim($text), 0, 8000),
            ]);

        if ($response->failed()) {
            Log::error('OpenAI Embedding API Error', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new RuntimeException('Failed to generate embedding from OpenAI: ' . ($response->json('error.message') ?? 'Unknown error'));
        }

        $embedding = $response->json('data.0.embedding');

        if (!is_array($embedding)) {
            throw new RuntimeException('Invalid embedding response format received from OpenAI.');
        }

        return $embedding;
    }

    /**
     * {@inheritDoc}
     */
    public function generateSummary(string $content): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('OpenAI API key is missing. Please configure OPENAI_API_KEY in your .env file.');
        }

        $prompt = "You are an expert executive assistant. Summarize the following note concisely in 2-3 clear, informative sentences highlighting the main points:\n\n" . $content;

        $response = Http::withToken($this->apiKey)
            ->timeout(20)
            ->retry(2, 500)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->chatModel,
                'messages' => [
                    ['role' => 'system', 'content' => 'You generate clear, concise, high-value summaries.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.3,
                'max_tokens' => 200,
            ]);

        if ($response->failed()) {
            Log::error('OpenAI Chat Completion API Error', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new RuntimeException('Failed to generate summary from OpenAI: ' . ($response->json('error.message') ?? 'Unknown error'));
        }

        $summary = trim($response->json('choices.0.message.content') ?? '');

        if (empty($summary)) {
            throw new RuntimeException('Empty summary received from OpenAI.');
        }

        return $summary;
    }
}
