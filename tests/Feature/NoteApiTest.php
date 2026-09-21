<?php

namespace Tests\Feature;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a note with valid payload.
     */
    public function test_can_create_note_successfully(): void
    {
        $response = $this->postJson('/api/notes', [
            'title' => 'Software Engineering Note',
            'content' => 'Deep architectural exploration of Laravel and AI microservices.',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Note created successfully',
            ])
            ->assertJsonPath('data.title', 'Software Engineering Note')
            ->assertJsonPath('data.has_embedding', true);

        $this->assertDatabaseHas('notes', [
            'title' => 'Software Engineering Note',
        ]);
    }

    /**
     * Test note creation validation failure.
     */
    public function test_create_note_fails_without_required_fields(): void
    {
        $response = $this->postJson('/api/notes', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['title', 'content'],
            ]);
    }

    /**
     * Test listing notes with pagination.
     */
    public function test_can_list_paginated_notes(): void
    {
        Note::create(['title' => 'Note 1', 'content' => 'Content 1']);
        Note::create(['title' => 'Note 2', 'content' => 'Content 2']);
        Note::create(['title' => 'Note 3', 'content' => 'Content 3']);

        $response = $this->getJson('/api/notes?page=1&limit=2');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Notes retrieved successfully',
            ])
            ->assertJsonCount(2, 'data.notes')
            ->assertJsonPath('data.pagination.total', 3)
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.total_pages', 2);
    }

    /**
     * Test pagination upper limit restriction.
     */
    public function test_pagination_rejects_limit_exceeding_100(): void
    {
        $response = $this->getJson('/api/notes?limit=500');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ])
            ->assertJsonStructure(['errors' => ['limit']]);
    }

    /**
     * Test retrieving a single note.
     */
    public function test_can_retrieve_single_note(): void
    {
        $note = Note::create(['title' => 'Single Note', 'content' => 'Sample content']);

        $response = $this->getJson("/api/notes/{$note->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Note retrieved successfully',
                'data' => [
                    'id' => $note->id,
                    'title' => 'Single Note',
                ],
            ]);
    }

    /**
     * Test single note not found returns 404.
     */
    public function test_get_non_existent_note_returns_404(): void
    {
        $response = $this->getJson('/api/notes/9999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Note not found',
            ]);
    }

    /**
     * Test updating a note.
     */
    public function test_can_update_note(): void
    {
        $note = Note::create(['title' => 'Original Title', 'content' => 'Original Content']);

        $response = $this->putJson("/api/notes/{$note->id}", [
            'title' => 'Updated Title',
            'content' => 'Updated Content',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Note updated successfully',
            ])
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'title' => 'Updated Title',
        ]);
    }

    /**
     * Test soft-deleting a note.
     */
    public function test_can_delete_note(): void
    {
        $note = Note::create(['title' => 'To Delete', 'content' => 'Delete content']);

        $response = $this->deleteJson("/api/notes/{$note->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Note deleted successfully',
            ]);

        $this->assertSoftDeleted('notes', ['id' => $note->id]);
    }

    /**
     * Test generating and caching AI summary.
     */
    public function test_can_generate_and_cache_ai_summary(): void
    {
        $note = Note::create([
            'title' => 'AI Note',
            'content' => 'Laravel provides expressive syntax and powerful database migrations. It makes building full stack applications effortless.',
        ]);

        // 1st Call -> Fresh Summary
        $response1 = $this->postJson("/api/notes/{$note->id}/summary");

        $response1->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'AI summary generated successfully',
                'data' => [
                    'note_id' => $note->id,
                    'cached' => false,
                ],
            ]);

        $this->assertNotEmpty($note->fresh()->summary);

        // 2nd Call -> Cached Summary
        $response2 = $this->postJson("/api/notes/{$note->id}/summary");

        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'AI summary retrieved successfully (from cache)',
                'data' => [
                    'note_id' => $note->id,
                    'cached' => true,
                ],
            ]);
    }

    /**
     * Test AI summary returns 404 for missing note.
     */
    public function test_ai_summary_returns_404_for_missing_note(): void
    {
        $response = $this->postJson('/api/notes/9999/summary');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Note not found',
            ]);
    }

    /**
     * Test semantic search finds conceptually related notes.
     */
    public function test_semantic_search_finds_related_notes(): void
    {
        $this->postJson('/api/notes', [
            'title' => 'How to prepare for a software engineering interview',
            'content' => 'Focus on data structures, algorithms, mock interviews, and system design.',
        ]);

        $this->postJson('/api/notes', [
            'title' => 'Pasta Carbonara Recipe',
            'content' => 'Egg yolks, pecorino cheese, guanciale, and pepper.',
        ]);

        $response = $this->getJson('/api/notes/search?q=' . urlencode('How should I prepare for a coding interview?'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Semantic search completed successfully',
            ])
            ->assertJsonPath('data.notes.0.title', 'How to prepare for a software engineering interview');
    }

    /**
     * Test semantic search validation.
     */
    public function test_semantic_search_requires_query(): void
    {
        $response = $this->getJson('/api/notes/search');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ])
            ->assertJsonStructure(['errors' => ['q']]);
    }
}
