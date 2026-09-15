<?php

declare(strict_types=1);

namespace App\Services\LLM;

use App\Contracts\LLM\LLMAnalysisServiceInterface;
use App\Models\Document;
use App\Models\Organization;
use App\Models\User;

/**
 * Resolves the appropriate LLM Analysis Service based on tenant tier.
 *
 * Routing logic:
 *   - Pro / Lifetime organizations -> ProLLMAnalysisService (Qwen Cloud exclusively)
 *   - Free organizations -> LlmAnalysisService (Cerebras GPT-OSS 20B exclusively)
 */
final class LlmServiceResolver
{
    public function __construct(
        private readonly LlmAnalysisService $freeService,
        private readonly ProLLMAnalysisService $proService,
    ) {}

    /**
     * Resolve the service for a given organization.
     */
    public function resolveForOrganization(?Organization $organization): LLMAnalysisServiceInterface
    {
        if ($organization !== null && $organization->hasProAccess()) {
            return $this->proService;
        }

        return $this->freeService;
    }

    /**
     * Resolve the service for a specific document based on its tenant ownership.
     */
    public function resolveForDocument(Document $document): LLMAnalysisServiceInterface
    {
        $organization = $document->relationLoaded('organization')
            ? $document->organization
            : Organization::query()->find($document->organization_id);

        return $this->resolveForOrganization($organization);
    }

    /**
     * Resolve the service for an authenticated user.
     */
    public function resolveForUser(?User $user): LLMAnalysisServiceInterface
    {
        if ($user === null) {
            return $this->freeService;
        }

        if ($user->hasProAccess()) {
            return $this->proService;
        }

        $organization = $user->relationLoaded('organization')
            ? $user->organization
            : $user->organization;

        return $this->resolveForOrganization($organization);
    }

    /**
     * Direct access to the Free tier service.
     */
    public function freeService(): LlmAnalysisService
    {
        return $this->freeService;
    }

    /**
     * Direct access to the Pro tier service.
     */
    public function proService(): ProLLMAnalysisService
    {
        return $this->proService;
    }
}
