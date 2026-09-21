<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoteController extends Controller
{
    use ApiResponse;

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

        return $this->successResponse(
            data: new NoteResource($note),
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
}
