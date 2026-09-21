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
        $cleanContent = trim(strip_tags($content));
        if (empty($cleanContent)) {
            return 'No content available to summarize.';
        }

        // Split into sentences
        $sentences = preg_split('/(?<=[.?!])\s+/u', $cleanContent, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($sentences) <= 2) {
            return $cleanContent;
        }

        // Frequency-based extractive summarization
        $wordFreq = [];
        $words = preg_split('/[^\p{L}\p{N}]+/u', strtolower($cleanContent), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $stopWords = array_flip(['the', 'is', 'at', 'which', 'on', 'a', 'an', 'and', 'or', 'in', 'to', 'for', 'with', 'it', 'this', 'that', 'as', 'by', 'from', 'of', 'be', 'are', 'was', 'were']);

        foreach ($words as $w) {
            if (!isset($stopWords[$w]) && mb_strlen($w) > 2) {
                $wordFreq[$w] = ($wordFreq[$w] ?? 0) + 1;
            }
        }

        // Score sentences
        $scored = [];
        foreach ($sentences as $idx => $s) {
            $sWords = preg_split('/[^\p{L}\p{N}]+/u', strtolower($s), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $score = 0;
            foreach ($sWords as $sw) {
                $score += $wordFreq[$sw] ?? 0;
            }
            // Normalize by sentence length
            $len = max(1, count($sWords));
            $scored[$idx] = $score / sqrt($len);
        }

        // Pick top 2-3 most informative sentences while keeping original order
        arsort($scored);
        $topIndices = array_slice(array_keys($scored), 0, min(3, count($sentences)));
        sort($topIndices);

        $summaryParts = [];
        foreach ($topIndices as $i) {
            $summaryParts[] = trim($sentences[$i]);
        }

        return implode(' ', $summaryParts);
    }
}
