<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AiModelResource;
use App\Models\AiModel;
use App\Models\User;
use App\Services\Billing\AiCreditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiModelController extends Controller
{
    public function __construct(
        private readonly AiCreditService $creditService,
    ) {}

    /**
     * List AI models available for the user's organization.
     *
     * GET /api/v1/ai-models
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $organization = $user->organization;

        if ($organization === null) {
            return response()->json([
                'error' => 'no_organization',
                'message' => 'El usuario no pertenece a ninguna organización.',
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        $plan = $organization->currentPlan();
        $ledger = $this->creditService->checkAndRefreshQuota($organization);

        // Fetch models based on plan access
        $query = AiModel::query()->active()->ordered();

        if ($plan->is_free) {
            // Free users are locked to lightweight models only
            $query->where('tier', 'lightweight');
        } else {
            // Pro / Lifetime users can access allowed tiers
            $allowedTiers = array_map(fn ($tier) => $tier->value, $plan->allowedTiers());
            if ($allowedTiers !== []) {
                $query->whereIn('tier', $allowedTiers);
            }
        }

        $models = $query->get();

        return response()->json([
            'data' => AiModelResource::collection($models),
            'meta' => [
                'allows_model_selection' => $plan->allowsModelSelection(),
                'credits_remaining' => $ledger->credits_remaining,
                'credits_quota' => $ledger->credits_quota,
                'is_unlimited' => $ledger->isUnlimited(),
                'refresh_interval' => $plan->credit_refresh_interval?->value,
                'period_expires_at' => $ledger->period_expires_at?->toIso8601String(),
            ],
        ]);
    }
}
