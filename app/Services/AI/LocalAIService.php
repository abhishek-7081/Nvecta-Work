<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\AIServiceInterface;

class LocalAIService implements AIServiceInterface
{
    protected int $vectorDimension = 384;

    /**
     * {@inheritDoc}
     */
    public function generateEmbedding(string $text): array
    {
        $vector = array_fill(0, $this->vectorDimension, 0.0);
        $clean = strtolower(trim(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text)));
        $words = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (empty($words)) {
            return $vector;
        }

        // Feature hashing over word tokens and character 3-grams
        foreach ($words as $word) {
            $h = crc32($word);
            $idx = abs($h) % $this->vectorDimension;
            $vector[$idx] += 1.0;

            // Add subword tri-grams for semantic fuzzy overlap
            $len = mb_strlen($word);
            if ($len >= 3) {
                for ($i = 0; $i <= $len - 3; $i++) {
                    $tri = mb_substr($word, $i, 3);
                    $triIdx = abs(crc32($tri)) % $this->vectorDimension;
                    $vector[$triIdx] += 0.5;
                }
            }
        }

        // L2 Normalize the vector so cosine similarity is simply the dot product
        $sumSquares = 0.0;
        foreach ($vector as $val) {
            $sumSquares += $val * $val;
        }

        $norm = sqrt($sumSquares);
        if ($norm > 0.0) {
            for ($i = 0; $i < $this->vectorDimension; $i++) {
                $vector[$i] = round($vector[$i] / $norm, 6);
            }
        }

        return $vector;
    }

    /**
     * {@inheritDoc}
     */
    public function generateSummary(string $content): string
    {
        $sentences = preg_split('/(?<=[.?!])\s+/', trim($content), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($sentences) <= 2) {
            return trim($content);
        }

        // Extract key sentences
        $summary = implode(' ', array_slice($sentences, 0, 2));
        if (mb_strlen($summary) > 200) {
            $summary = mb_substr($summary, 0, 197) . '...';
        }

        return $summary;
    }
}
