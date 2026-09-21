<?php

namespace App\Services;

use App\Models\Note;
use Illuminate\Support\Collection;

class NoteSearchService
{
    public function __construct(
        protected EmbeddingService $embeddingService
    ) {}

    /**
     * Perform semantic vector search across notes.
     *
     * @param string $query
     * @param int $limit
     * @param float $minSimilarity
     * @return Collection<Note>
     */
    public function search(string $query, int $limit = 10, float $minSimilarity = 0.15): Collection
    {
        $cleanQuery = trim($query);
        if (empty($cleanQuery)) {
            return collect([]);
        }

        // 1. Generate vector embedding for the search query
        $queryVector = $this->embeddingService->getQueryEmbedding($cleanQuery);
        if (empty($queryVector)) {
            return $this->keywordFallbackSearch($cleanQuery, $limit);
        }

        // 2. Fetch all non-deleted notes with embeddings
        $notes = Note::whereNotNull('embedding')->get();

        // 3. Calculate cosine similarity score for each note
        $scoredNotes = $notes->map(function (Note $note) use ($queryVector) {
            $noteVector = $note->embedding;
            if (!is_array($noteVector) || empty($noteVector)) {
                $score = 0.0;
            } else {
                $score = $this->calculateCosineSimilarity($queryVector, $noteVector);
            }

            $note->similarity_score = round($score, 4);
            $note->similarity_percentage = round($score * 100, 1);
            return $note;
        });

        // 4. Filter by minimum similarity threshold and sort descending by score
        $results = $scoredNotes
            ->filter(fn(Note $note) => $note->similarity_score >= $minSimilarity)
            ->sortByDesc('similarity_score')
            ->values()
            ->take($limit);

        // If vector results are empty, provide keyword fallback
        if ($results->isEmpty()) {
            return $this->keywordFallbackSearch($cleanQuery, $limit);
        }

        return $results;
    }

    /**
     * Compute cosine similarity between two float vectors.
     *
     * @param array<float> $vecA
     * @param array<float> $vecB
     * @return float
     */
    public function calculateCosineSimilarity(array $vecA, array $vecB): float
    {
        $countA = count($vecA);
        $countB = count($vecB);

        if ($countA === 0 || $countB === 0) {
            return 0.0;
        }

        $minLen = min($countA, $countB);
        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $minLen; $i++) {
            $valA = (float) $vecA[$i];
            $valB = (float) $vecB[$i];

            $dotProduct += $valA * $valB;
            $normA += $valA * $valA;
            $normB += $valB * $valB;
        }

        // If dimensions differed, account for remaining norm
        if ($countA > $minLen) {
            for ($i = $minLen; $i < $countA; $i++) {
                $normA += ((float) $vecA[$i]) ** 2;
            }
        } elseif ($countB > $minLen) {
            for ($i = $minLen; $i < $countB; $i++) {
                $normB += ((float) $vecB[$i]) ** 2;
            }
        }

        $denominator = sqrt($normA) * sqrt($normB);

        if ($denominator <= 0.0) {
            return 0.0;
        }

        return max(0.0, min(1.0, $dotProduct / $denominator));
    }

    /**
     * Fallback search when embeddings are unavailable or query produces 0 vector matches.
     *
     * @param string $query
     * @param int $limit
     * @return Collection<Note>
     */
    protected function keywordFallbackSearch(string $query, int $limit): Collection
    {
        return Note::where('title', 'LIKE', "%{$query}%")
            ->orWhere('content', 'LIKE', "%{$query}%")
            ->latest('id')
            ->take($limit)
            ->get()
            ->map(function (Note $note) {
                $note->similarity_score = 0.5;
                $note->similarity_percentage = 50.0;
                return $note;
            });
    }
}
