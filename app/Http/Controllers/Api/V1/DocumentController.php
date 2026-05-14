<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Documents\StoreDocumentAction;
use App\DTOs\Documents\StoreDocumentDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDocumentRequest;
use App\Http\Resources\Api\V1\DocumentResource;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * REST controller for document operations.
 *
 * This controller only coordinates — all business logic lives in
 * Actions and Services. Authorization is handled by DocumentPolicy.
 */
class DocumentController extends Controller
{
    public function __construct(
        private readonly StoreDocumentAction $storeAction,
    ) {}

    /**
     * List documents for the authenticated user's organization.
     *
     * GET /api/v1/documents
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Document::class);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $perPage = min(
            (int) $request->input('per_page', config('documents.pagination.default_per_page')),
            (int) config('documents.pagination.max_per_page'),
        );

        $documents = Document::query()
            ->forOrganization($user->organization_id)
            ->with('uploader:id,name')
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->input('status')),
            )
            ->when(
                $request->filled('document_type'),
                fn ($query) => $query->where('document_type', $request->input('document_type')),
            )
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return DocumentResource::collection($documents);
    }

    /**
     * Upload and store a new document.
     *
     * POST /api/v1/documents
     */
    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $this->authorize('create', Document::class);

        $dto = StoreDocumentDTO::fromRequest($request);

        $document = $this->storeAction->execute(
            dto: $dto,
            ipAddress: $request->ip(),
        );

        $document->load('uploader:id,name');

        return (new DocumentResource($document))
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }

    /**
     * Show a specific document.
     *
     * GET /api/v1/documents/{document}
     */
    public function show(Document $document): DocumentResource
    {
        $this->authorize('view', $document);

        $document->load('uploader:id,name');

        return new DocumentResource($document);
    }
}
