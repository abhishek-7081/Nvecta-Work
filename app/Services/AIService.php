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

    public function __construct(?AIServiceInterface $driver = null)
    {
        if ($driver !== null) {
            $this->driver = $driver;
            return;
        }

        $provider = 'openai';
        $openaiKey = null;
        $geminiKey = null;

        try {
            if (function_exists('config')) {
                $provider = strtolower(config('services.ai.provider', 'openai'));
                $openaiKey = config('services.openai.api_key');
                $geminiKey = config('services.gemini.api_key');
            }
        } catch (Throwable) {
            // Environment outside of Laravel Application container (e.g. Unit tests)
        }

        // Auto-select driver based on provider preference and available credentials
        if ($provider === 'gemini' && !empty($geminiKey)) {
            $this->driver = new GeminiService();
        } elseif ($provider === 'openai' && !empty($openaiKey)) {
            $this->driver = new OpenAIService();
        } elseif (!empty($geminiKey)) {
            $this->driver = new GeminiService();
        } elseif (!empty($openaiKey)) {
            $this->driver = new OpenAIService();
        } else {
            // Graceful fallback to LocalAIService
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
            Log::warning('Primary AI driver (' . get_class($this->driver) . ') failed for embedding, using LocalAIService fallback: ' . $e->getMessage());
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
            Log::warning('Primary AI driver (' . get_class($this->driver) . ') failed for summary, using LocalAIService fallback: ' . $e->getMessage());
            $fallback = new LocalAIService();
            return $fallback->generateSummary($content);
        }
    }
}
