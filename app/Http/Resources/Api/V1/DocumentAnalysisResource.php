<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\DocumentAnalysis;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for DocumentAnalysis model.
 *
 * @mixin DocumentAnalysis
 */
class DocumentAnalysisResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_id' => $this->document_id,
            'organization_id' => $this->organization_id,
            'analysis_type' => $this->analysis_type->value,
            'analysis_type_label' => $this->analysis_type->label(),
            'llm_provider' => $this->llm_provider,
            'llm_model' => $this->llm_model,
            'content' => $this->content,
            'confidence_score' => $this->confidence_score,
            'input_tokens' => $this->input_tokens,
            'output_tokens' => $this->output_tokens,
            'total_tokens' => $this->totalTokens(),
            'processing_time_ms' => $this->processing_time_ms,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'version' => $this->version,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Relationships (only when loaded)
            'requester' => $this->whenLoaded('requester', fn () => [
                'id' => $this->requester->id,
                'name' => $this->requester->name,
            ]),
        ];
    }
}
