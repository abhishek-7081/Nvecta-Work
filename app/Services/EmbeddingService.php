<?php

namespace App\Services;

use App\Models\Note;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmbeddingService
{
    public function __construct(
        protected AIService $aiService
    ) {}

    /**
     * Generate and update the embedding for a given note.
     *
     * @param Note $note
     * @return bool
     */
    public function syncNoteEmbedding(Note $note): bool
    {
        try {
            $textToEmbed = $note->title . "\n\n" . $note->content;
            $embedding = $this->aiService->generateEmbedding($textToEmbed);

            if (!empty($embedding)) {
                $note->embedding = $embedding;
                $note->saveQuietly();
                return true;
            }
        } catch (Throwable $e) {
            Log::error('Failed to sync embedding for note ID: ' . $note->id, [
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Generate an embedding vector for a search query.
     *
     * @param string $query
     * @return array<float>
     */
    public function getQueryEmbedding(string $query): array
    {
        return $this->aiService->generateEmbedding($query);
    }
}
