<?php

namespace App\Services\AI\Contracts;

interface AIServiceInterface
{
    /**
     * Generate a vector embedding for a given text.
     *
     * @param string $text
     * @return array<float>
     */
    public function generateEmbedding(string $text): array;

    /**
     * Generate a concise summary for a note's content.
     *
     * @param string $content
     * @return string
     */
    public function generateSummary(string $content): string;
}
