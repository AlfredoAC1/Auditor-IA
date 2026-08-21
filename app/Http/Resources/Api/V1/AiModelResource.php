<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\AiModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AiModel
 */
class AiModelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Note: Internal provider details are hidden from users.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'display_name' => $this->display_name,
            'tier' => [
                'value' => $this->tier->value,
                'label' => $this->tier->label(),
                'description' => $this->tier->description(),
            ],
            'credit_multiplier' => $this->credit_multiplier,
            'credit_cost' => $this->creditCostPerAnalysis(),
            'max_tokens' => $this->max_tokens,
        ];
    }
}
