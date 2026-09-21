<?php

namespace Tests\Unit;

use App\Services\AI\LocalAIService;
use App\Services\AIService;
use App\Services\EmbeddingService;
use App\Services\NoteSearchService;
use PHPUnit\Framework\TestCase;

class NoteSearchServiceTest extends TestCase
{
    protected NoteSearchService $searchService;

    protected function setUp(): void
    {
        parent::setUp();
        $aiService = (new AIService())->setDriver(new LocalAIService());
        $embeddingService = new EmbeddingService($aiService);
        $this->searchService = new NoteSearchService($embeddingService);
    }

    /**
     * Test cosine similarity of identical unit vectors equals 1.0.
     */
    public function test_identical_vectors_have_similarity_of_one(): void
    {
        $vecA = [0.6, 0.8];
        $vecB = [0.6, 0.8];

        $similarity = $this->searchService->calculateCosineSimilarity($vecA, $vecB);

        $this->assertEqualsWithDelta(1.0, $similarity, 0.0001);
    }

    /**
     * Test cosine similarity of orthogonal (perpendicular) vectors equals 0.0.
     */
    public function test_orthogonal_vectors_have_similarity_of_zero(): void
    {
        $vecA = [1.0, 0.0];
        $vecB = [0.0, 1.0];

        $similarity = $this->searchService->calculateCosineSimilarity($vecA, $vecB);

        $this->assertEqualsWithDelta(0.0, $similarity, 0.0001);
    }

    /**
     * Test empty vectors gracefully return 0.0 without division by zero errors.
     */
    public function test_empty_vectors_return_zero_similarity(): void
    {
        $similarity = $this->searchService->calculateCosineSimilarity([], []);
        $this->assertEquals(0.0, $similarity);
    }

    /**
     * Test vectors with partial alignment return expected intermediate angle.
     */
    public function test_partially_aligned_vectors_return_accurate_cosine_score(): void
    {
        $vecA = [1.0, 1.0]; // 45 degrees
        $vecB = [1.0, 0.0]; // 0 degrees

        // Cosine of 45 deg = 1 / sqrt(2) ~= 0.7071
        $similarity = $this->searchService->calculateCosineSimilarity($vecA, $vecB);

        $this->assertEqualsWithDelta(0.7071, $similarity, 0.001);
    }
}
