<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for attaching a supporting document to an existing analysis.
 *
 * Validates that the target document exists within the authenticated user's
 * organization, enforcing tenant isolation at the validation layer.
 */
class AttachSupportingDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = $this->user();

        return $user !== null && $user->hasOrganization();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var \App\Models\User $user */
        $user = $this->user();

        return [
            'document_id' => [
                'required',
                'uuid',
                // Tenant-scoped: document must belong to the same organization
                \Illuminate\Validation\Rule::exists('documents', 'id')
                    ->where('organization_id', $user->organization_id)
                    ->whereNull('deleted_at'),
            ],
            'relationship_type' => [
                'nullable',
                'string',
                \Illuminate\Validation\Rule::in(['referenced', 'related', 'clarification', 'complementary']),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'document_id.required' => 'Debe especificar el documento de apoyo.',
            'document_id.uuid'     => 'El identificador del documento no es válido.',
            'document_id.exists'   => 'El documento no existe o no pertenece a su organización.',
            'relationship_type.in' => 'El tipo de relación no es válido. Use: referenced, related, clarification o complementary.',
            'notes.max'            => 'Las notas no pueden superar los 1000 caracteres.',
        ];
    }

    /**
     * Get custom attribute labels.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'document_id'       => 'documento de apoyo',
            'relationship_type' => 'tipo de relación',
            'notes'             => 'notas',
        ];
    }
}
