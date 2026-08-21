<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Introduces the AI Credits system:
 *
 * 1. Modifies `plans` table to replace analysis/token limits with credit quota
 * 2. Creates `ai_models` registry table for available AI models
 * 3. Creates `ai_credit_ledgers` table for per-org credit balance tracking
 * 4. Creates `credit_transactions` table as immutable audit log
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ---------------------------------------------------------------
        // 1. Modify plans table: remove old limits, add credit fields
        // ---------------------------------------------------------------
        Schema::table('plans', function (Blueprint $table) {
            // New credit-based columns
            $table->unsignedInteger('ai_credits_quota')
                ->nullable()
                ->after('max_storage_bytes')
                ->comment('AI credits per refresh period. NULL = unlimited');

            $table->string('credit_refresh_interval', 20)
                ->default('monthly')
                ->after('ai_credits_quota')
                ->comment('weekly or monthly');

            $table->json('allowed_model_tiers')
                ->nullable()
                ->after('credit_refresh_interval')
                ->comment('JSON array of AiModelTier values this plan can access');

            // Remove superseded columns
            $table->dropColumn([
                'max_analyses_per_month',
                'max_tokens_per_month',
            ]);
        });

        // ---------------------------------------------------------------
        // 2. AI Models registry
        // ---------------------------------------------------------------
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();

            $table->string('slug', 80)->unique();
            $table->string('display_name', 100);

            // Provider mapping (hidden from free users, visible to pro/lifetime)
            $table->string('provider', 50)
                ->comment('Internal provider key: groq, nvidia_nim, deepseek, azure');
            $table->string('provider_model_id', 150)
                ->comment('Actual model ID sent to provider API');

            // Credit economy
            $table->string('tier', 30)
                ->comment('AiModelTier enum: lightweight, standard, advanced, premium');
            $table->decimal('credit_multiplier', 3, 1)
                ->default(1.0)
                ->comment('Credits consumed = base_cost * multiplier');

            // Configuration
            $table->unsignedInteger('max_tokens')
                ->default(4096);
            $table->boolean('is_active')
                ->default(true);
            $table->unsignedSmallInteger('sort_order')
                ->default(0);

            $table->timestamps();

            $table->index('tier');
            $table->index('is_active');
            $table->index('provider');
        });

        // ---------------------------------------------------------------
        // 3. AI Credit Ledgers — current balance per organization
        // ---------------------------------------------------------------
        Schema::create('ai_credit_ledgers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
                ->unique()
                ->constrained('organizations')
                ->cascadeOnDelete();

            // Current period balance
            $table->integer('credits_remaining')
                ->default(0)
                ->comment('Current available credits');
            $table->unsignedInteger('credits_quota')
                ->default(0)
                ->comment('Total credits allocated for this period');

            // Period tracking
            $table->timestamp('period_started_at')
                ->nullable()
                ->comment('When the current credit period began');
            $table->timestamp('period_expires_at')
                ->nullable()
                ->comment('When credits expire and must be refreshed');

            // Free-tier weekly decay: timer starts from last usage
            $table->timestamp('last_usage_at')
                ->nullable()
                ->comment('Last time credits were consumed. Used for free-tier weekly refresh calculation');

            $table->timestamps();
        });

        // ---------------------------------------------------------------
        // 4. Credit Transactions — immutable audit log
        // ---------------------------------------------------------------
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Transaction classification
            $table->string('type', 50)
                ->comment('CreditTransactionType enum value');

            // Credits: positive = credit (add), negative = debit (subtract)
            $table->integer('credits_amount')
                ->comment('Positive for credits, negative for debits');
            $table->integer('credits_balance_after')
                ->comment('Snapshot of ledger balance after this transaction');

            // Analysis context (when type = analysis_debit)
            $table->string('model_tier', 30)
                ->nullable()
                ->comment('AiModelTier used for this transaction');

            $table->uuid('analysis_id')
                ->nullable()
                ->comment('FK to document_analyses if this was an analysis debit');
            $table->foreign('analysis_id')
                ->references('id')
                ->on('document_analyses')
                ->nullOnDelete();

            // Extended context
            $table->json('metadata')
                ->nullable()
                ->comment('tokens_used, model_slug, provider, etc.');

            // Audit timestamp only (immutable records)
            $table->timestamp('created_at')
                ->useCurrent();

            // Performance indexes
            $table->index(['organization_id', 'type', 'created_at'], 'ct_org_type_created');
            $table->index(['organization_id', 'created_at'], 'ct_org_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
        Schema::dropIfExists('ai_credit_ledgers');
        Schema::dropIfExists('ai_models');

        // Restore removed columns on plans
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedInteger('max_analyses_per_month')
                ->nullable()
                ->after('max_documents_per_month');
            $table->unsignedInteger('max_tokens_per_month')
                ->nullable()
                ->after('max_storage_bytes');

            $table->dropColumn([
                'ai_credits_quota',
                'credit_refresh_interval',
                'allowed_model_tiers',
            ]);
        });
    }
};
