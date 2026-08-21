<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Enums\CreditRefreshInterval;
use App\Enums\CreditTransactionType;
use App\Enums\UsageType;
use App\Models\AiCreditLedger;
use App\Models\AiModel;
use App\Models\CreditTransaction;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Models\UsageRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Core Service for AI Credits Quota management, model access control,
 * credit deduction, and periodic refresh logic.
 *
 * Rules:
 * - Free users: Weekly credit refresh (counter starts from last use).
 *   Model selection is locked to Lightweight (1x), but can change analysis type.
 *   Must always allow at least one analysis if credits > 0.
 *   If credits are depleted mid/post analysis failure, analysis must stop and delete itself.
 * - Pro / Lifetime users: Monthly credit refresh (or unlimited for Lifetime).
 *   Allowed to select between various AI models of different tiers (1x up to 4x).
 */
final class AiCreditService
{
    /**
     * Check and perform quota refresh if the current period has expired.
     */
    public function checkAndRefreshQuota(Organization $organization): AiCreditLedger
    {
        $ledger = $organization->getOrCreateCreditLedger();
        $plan = $organization->currentPlan();

        if ($plan->hasUnlimitedCredits()) {
            return $ledger;
        }

        if (! $ledger->isPeriodExpired() && $ledger->period_started_at !== null) {
            return $ledger;
        }

        return DB::transaction(function () use ($organization, $ledger, $plan): AiCreditLedger {
            $quota = $plan->ai_credits_quota ?? 0;
            $now = now();

            $nextExpiry = match ($plan->credit_refresh_interval) {
                CreditRefreshInterval::Weekly => $now->copy()->addDays(7),
                CreditRefreshInterval::Monthly => $now->copy()->addMonth(),
                default => $now->copy()->addMonth(),
            };

            $ledger->update([
                'credits_remaining' => $quota,
                'credits_quota' => $quota,
                'period_started_at' => $now,
                'period_expires_at' => $nextExpiry,
            ]);

            CreditTransaction::create([
                'organization_id' => $organization->id,
                'user_id' => null,
                'type' => CreditTransactionType::QuotaRefresh,
                'credits_amount' => $quota,
                'credits_balance_after' => $quota,
                'metadata' => [
                    'plan' => $plan->slug,
                    'refresh_interval' => $plan->credit_refresh_interval?->value,
                ],
                'created_at' => $now,
            ]);

            Log::info('AI Credit quota refreshed.', [
                'organization_id' => $organization->id,
                'new_balance' => $quota,
                'expires_at' => $nextExpiry->toIso8601String(),
            ]);

            return $ledger;
        });
    }

    /**
     * Determine if an organization can execute an analysis using the given model.
     */
    public function canPerformAnalysis(Organization $organization, AiModel $model): bool
    {
        $plan = $organization->currentPlan();

        // 1. Verify model tier allowed for plan
        if (! $plan->allowsTier($model->tier)) {
            Log::warning('AI model tier not allowed for plan.', [
                'organization_id' => $organization->id,
                'plan' => $plan->slug,
                'model_tier' => $model->tier->value,
            ]);

            return false;
        }

        // 2. Free tier model lock check: Free users cannot select non-lightweight models
        if ($plan->is_free && $model->tier->value !== 'lightweight') {
            return false;
        }

        // 3. Check credits
        $ledger = $this->checkAndRefreshQuota($organization);

        if ($ledger->isUnlimited()) {
            return true;
        }

        // Rule: Must always allow at least 1 analysis if user has any positive credit balance (> 0)
        return $ledger->credits_remaining > 0;
    }

    /**
     * Deduct credits for a completed analysis and record audit trail.
     */
    public function deductCredits(
        Organization $organization,
        User $user,
        AiModel $model,
        DocumentAnalysis $analysis,
    ): void {
        DB::transaction(function () use ($organization, $user, $model, $analysis): void {
            $ledger = $this->checkAndRefreshQuota($organization);

            if ($ledger->isUnlimited()) {
                return;
            }

            $cost = $model->creditCostPerAnalysis();
            $currentBalance = $ledger->credits_remaining;

            // Deduct cost, floor at 0
            $newBalance = max(0, $currentBalance - $cost);
            $actualDeducted = $currentBalance - $newBalance;
            $now = now();

            // Update ledger
            $ledger->update([
                'credits_remaining' => $newBalance,
                'last_usage_at' => $now,
            ]);

            // Create immutable credit transaction record
            CreditTransaction::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'type' => CreditTransactionType::AnalysisDebit,
                'credits_amount' => -$actualDeducted,
                'credits_balance_after' => $newBalance,
                'model_tier' => $model->tier,
                'analysis_id' => $analysis->id,
                'metadata' => [
                    'model_slug' => $model->slug,
                    'input_tokens' => $analysis->input_tokens,
                    'output_tokens' => $analysis->output_tokens,
                    'cost_charged' => $cost,
                ],
                'created_at' => $now,
            ]);

            // Track usage record for general metrics
            UsageRecord::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'usage_type' => UsageType::AiCreditsConsumed,
                'quantity' => $actualDeducted,
                'metadata' => [
                    'analysis_id' => $analysis->id,
                    'model_slug' => $model->slug,
                ],
                'period_start' => $ledger->period_started_at?->toDateString() ?? $now->toDateString(),
                'period_end' => $ledger->period_expires_at?->toDateString() ?? $now->toDateString(),
                'created_at' => $now,
            ]);

            Log::info('AI credits deducted for analysis.', [
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'analysis_id' => $analysis->id,
                'credits_deducted' => $actualDeducted,
                'remaining_balance' => $newBalance,
            ]);
        });
    }

    /**
     * Stop and purge an analysis if credits are depleted or analysis failed due to quota limit.
     */
    public function purgeDepletedAnalysis(DocumentAnalysis $analysis, string $reason): void
    {
        Log::warning('Purging analysis due to credit depletion.', [
            'analysis_id' => $analysis->id,
            'organization_id' => $analysis->organization_id,
            'reason' => $reason,
        ]);

        $analysis->delete();
    }
}
