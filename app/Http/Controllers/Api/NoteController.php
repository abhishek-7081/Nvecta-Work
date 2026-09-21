<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginationRequest;
use App\Http\Requests\SearchNotesRequest;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use App\Services\AIService;
use App\Services\EmbeddingService;
use App\Services\NoteSearchService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class NoteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected EmbeddingService $embeddingService,
        protected AIService $aiService,
        protected NoteSearchService $searchService
    ) {}

    /**
     * Display a paginated listing of notes.
     *
     * @param PaginationRequest $request
     * @return JsonResponse
     */
    public function index(PaginationRequest $request): JsonResponse
    {
        $limit = (int) ($request->query('limit') ?? $request->query('per_page', 10));
        $sortBy = $request->query('sort_by', 'id');
        $order = strtolower($request->query('order', 'desc'));

        $notes = Note::orderBy($sortBy, $order)->paginate($limit);

        return $this->successResponse(
            data: [
                'notes' => NoteResource::collection($notes->items()),
                'pagination' => [
                    'total' => $notes->total(),
                    'count' => $notes->count(),
                    'per_page' => $notes->perPage(),
                    'current_page' => $notes->currentPage(),
                    'total_pages' => $notes->lastPage(),
                    'has_more_pages' => $notes->hasMorePages(),
                ],
            ],
            message: 'Notes retrieved successfully',
            statusCode: Response::HTTP_OK
        );
    }

    /**
     * Store a newly created note in storage.
     *
     * @param StoreNoteRequest $request
     * @return JsonResponse
     */
    public function store(StoreNoteRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $note = Note::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        // Automatically generate vector embedding for semantic search
        $this->embeddingService->syncNoteEmbedding($note);

        return $this->successResponse(
            data: new NoteResource($note->fresh()),
            message: 'Note created successfully',
            statusCode: Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified note.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $note = Note::find($id);

        if (!$note) {
            return $this->errorResponse(
                message: 'Note not found',
                statusCode: Response::HTTP_NOT_FOUND
            );
        }

        return $this->successResponse(
            data: new NoteResource($note),
            message: 'Note retrieved successfully',
            statusCode: Response::HTTP_OK
        );
    }

    /**
     * Update the specified note in storage.
     *
     * @param UpdateNoteRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateNoteRequest $request, int $id): JsonResponse
    {
        $note = Note::find($id);

        if (!$note) {
            return $this->errorResponse(
                message: 'Note not found',
                statusCode: Response::HTTP_NOT_FOUND
            );
        }

        $validated = $request->validated();

        // If content is modified, invalidate cached summary and embedding
        if (isset($validated['content']) && $validated['content'] !== $note->content) {
            $validated['summary'] = null;
            $validated['embedding'] = null;
        }

        $note->update($validated);

        // If content was updated, regenerate the embedding
        if (isset($validated['content'])) {
            $this->embeddingService->syncNoteEmbedding($note);
        }

        return $this->successResponse(
            data: new NoteResource($note->fresh()),
            message: 'Note updated successfully',
            statusCode: Response::HTTP_OK
        );
    }

    /**
     * Remove the specified note from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $note = Note::find($id);

        if (!$note) {
            return $this->errorResponse(
                message: 'Note not found',
                statusCode: Response::HTTP_NOT_FOUND
            );
        }

        $note->delete();

        return $this->successResponse(
            data: null,
            message: 'Note deleted successfully',
            statusCode: Response::HTTP_OK
        );
    }

    /**
     * Generate or retrieve an AI-based summary for the selected note.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function summary(Request $request, int $id): JsonResponse
    {
        $note = Note::find($id);

        if (!$note) {
            return $this->errorResponse(
                message: 'Note not found',
                statusCode: Response::HTTP_NOT_FOUND
            );
        }

        $forceRegenerate = $request->boolean('force', false);

        // If summary is already cached and force is false, return cached summary
        if (!empty($note->summary) && !$forceRegenerate) {
            return $this->successResponse(
                data: [
                    'note_id' => $note->id,
                    'title' => $note->title,
                    'summary' => $note->summary,
                    'cached' => true,
                ],
                message: 'AI summary retrieved successfully (from cache)',
                statusCode: Response::HTTP_OK
            );
        }

        try {
            $summary = $this->aiService->generateSummary($note->content);

            $note->summary = $summary;
            $note->saveQuietly();

            return $this->successResponse(
                data: [
                    'note_id' => $note->id,
                    'title' => $note->title,
                    'summary' => $summary,
                    'cached' => false,
                ],
                message: 'AI summary generated successfully',
                statusCode: Response::HTTP_OK
            );
        } catch (Throwable $e) {
            return $this->errorResponse(
                message: 'Failed to generate AI summary at this time. Please try again later.',
                statusCode: Response::HTTP_SERVICE_UNAVAILABLE
            );
        }
    }

    /**
     * Perform AI-powered semantic vector search across notes.
     *
     * @param SearchNotesRequest $request
     * @return JsonResponse
     */
    public function search(SearchNotesRequest $request): JsonResponse
    {
        $query = $request->query('q');
        $limit = (int) $request->query('limit', 10);

        $results = $this->searchService->search($query, $limit);

        return $this->successResponse(
            data: [
                'query' => $query,
                'total_matches' => $results->count(),
                'notes' => NoteResource::collection($results),
            ],
            message: 'Semantic search completed successfully',
            statusCode: Response::HTTP_OK
        );
    }
}
