<?php

namespace App\Services;

use App\Services\AI\Contracts\AIServiceInterface;
use App\Services\AI\GeminiService;
use App\Services\AI\LocalAIService;
use App\Services\AI\OpenAIService;
use Illuminate\Support\Facades\Log;
use Throwable;

class AIService implements AIServiceInterface
{
    protected AIServiceInterface $driver;

    public function __construct()
    {
        $provider = strtolower(config('services.ai.provider', 'openai'));

        if ($provider === 'openai' && !empty(config('services.openai.api_key'))) {
            $this->driver = new OpenAIService();
        } elseif ($provider === 'gemini' && !empty(config('services.gemini.api_key'))) {
            $this->driver = new GeminiService();
        } else {
            // Graceful fallback to LocalAIService if API key is not configured
            $this->driver = new LocalAIService();
        }
    }

    /**
     * Set a custom driver (useful for testing and mocking).
     *
     * @param AIServiceInterface $driver
     * @return self
     */
    public function setDriver(AIServiceInterface $driver): self
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * Get the active driver instance.
     *
     * @return AIServiceInterface
     */
    public function getDriver(): AIServiceInterface
    {
        return $this->driver;
    }

    /**
     * {@inheritDoc}
     */
    public function generateEmbedding(string $text): array
    {
        try {
            return $this->driver->generateEmbedding($text);
        } catch (Throwable $e) {
            Log::warning('Primary AI driver failed for embedding, using LocalAIService fallback: ' . $e->getMessage());
            $fallback = new LocalAIService();
            return $fallback->generateEmbedding($text);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function generateSummary(string $content): string
    {
        try {
            return $this->driver->generateSummary($content);
        } catch (Throwable $e) {
            Log::warning('Primary AI driver failed for summary, using LocalAIService fallback: ' . $e->getMessage());
            $fallback = new LocalAIService();
            return $fallback->generateSummary($content);
        }
    }
}
