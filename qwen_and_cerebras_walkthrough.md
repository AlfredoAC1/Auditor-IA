# Cerebras Integration Walkthrough

## What Was Done

### 1. `config/llm.php` — Token ceiling & Qwen env fix
- Raised `CEREBRAS_MAX_TOKENS` default: **16,384 → 32,768** (conservative ceiling below the 40,960 hard cap)
- Raised `CEREBRAS_TIMEOUT` default: **120s → 180s** to handle richer responses
- Raised Qwen `max_tokens` default: **8,192 → 16,384**
- Fixed Qwen env var: now reads `env('QWEN_API_KEY', env('QWEN_CLOUD_API_KEY'))` — supports both naming conventions

### 2. `.env` — Explicit Cerebras configuration
Added:
```env
CEREBRAS_MODEL=gemma-4-31b
CEREBRAS_MAX_TOKENS=32768
CEREBRAS_TIMEOUT=180
QWEN_API_KEY=        # placeholder — set when key is available
```

### 3. [`CerebrasAdapter.php`](file:///d:/Xampp/htdocs/laravel/auditor-ia/app/Services/LLM/Providers/CerebrasAdapter.php) — **New file**
The critical fix: Cerebras uses `max_completion_tokens` in the payload, not `max_tokens`. Sending `max_tokens` to Cerebras is silently ignored, causing the model to run uncontrolled to its own max. The new adapter overrides `buildPayload()` to correct this.

```php
// OpenAI (and most providers)
'max_tokens' => 32768,       ← was being sent

// Cerebras (correct)  
'max_completion_tokens' => 32768,  ← now correctly sent
```

### 4. [`LlmAnalysisService.php`](file:///d:/Xampp/htdocs/laravel/auditor-ia/app/Services/LLM/LlmAnalysisService.php) — Adapter routing
`resolveAdapter()` now routes `cerebras` provider to `CerebrasAdapter`. All other providers continue using `OpenAiCompatibleAdapter`.

### 5. [`LlmPromptBuilder.php`](file:///d:/Xampp/htdocs/laravel/auditor-ia/app/Services/LLM/LlmPromptBuilder.php) — Richer prompts
Removed the overly conservative instruction:
> *"Mantén las explicaciones breves (1-2 oraciones por campo) para asegurar que el JSON se genere completo sin alcanzar el límite de tokens."*

Replaced with:
> *"Proporciona análisis completos y detallados; no trunces información relevante."*

This allows Cerebras to use its full 32k output budget for genuinely richer legal analyses.

### 6. [`AiModelSeeder.php`](file:///d:/Xampp/htdocs/laravel/auditor-ia/database/seeders/AiModelSeeder.php) — DB record update
- `cerebras-gemma4` `max_tokens`: **8,192 → 32,768**
- Display name: `"Gemma 4 31B"` → `"Gemma 4 31B (Preview)"`

### 7. [`analysis-detail.blade.php`](file:///d:/Xampp/htdocs/laravel/auditor-ia/resources/views/analysis-detail.blade.php) — Provider badge
Replaced hardcoded binary (NVIDIA vs Groq) with a full provider map:

| Provider | Label | Badge Color |
|----------|-------|-------------|
| `cerebras` | Cerebras AI | 🟠 Amber/brown |
| `nvidia_nim` | NVIDIA NIM | 🟢 Green |
| `qwen_cloud` | Qwen Cloud | 🟣 Purple |
| `deepseek` | DeepSeek | 🔵 Blue |
| `azure` | Azure OpenAI | 🔵 Sky blue |
| `groq` (default) | Groq Cloud | ⚪ Icy (theme default) |

---

## How to Test

Send a POST request to analyze a parsed document using the Cerebras model:

```bash
POST /api/v1/documents/{document_id}/analyze
Authorization: Bearer <token>
Content-Type: application/json

{
  "analysis_type": "full_summary",
  "model_slug": "cerebras-gemma4"
}
```

**Expected results:**
- `llm_provider` = `"cerebras"`
- `llm_model` = `"cerebras-gemma4"`
- `output_tokens` should be in the thousands (much higher than Groq's typical 400-800)
- The `content` JSON should be **significantly richer** — more detailed clause explanations, more risks identified, fuller recommendations

---

## Qwen Cloud (Pending)
- Env var mismatch is fixed (both `QWEN_API_KEY` and `QWEN_CLOUD_API_KEY` are now recognized)
- All Qwen models remain `is_active = false` in the DB until you obtain a key
- Once you have a key: set `QWEN_API_KEY=<your_key>` in `.env` and run `php artisan db:seed --class=AiModelSeeder` after flipping the desired Qwen model to `is_active = true`
