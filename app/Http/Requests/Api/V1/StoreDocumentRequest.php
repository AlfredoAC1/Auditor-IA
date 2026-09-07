<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Enums\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        // User must be authenticated and belong to an organization
        return $user !== null && $user->hasOrganization();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxSizeKb = (int) config('documents.upload.max_size_kb', 51200);
        $allowedMimes = implode(',', (array) config('documents.upload.allowed_mimes', []));

        return [
            'file' => [
                'required',
                'file',
                "max:{$maxSizeKb}",
                "mimetypes:{$allowedMimes}",
            ],
            'document_type' => [
                'nullable',
                'string',
                Rule::enum(DocumentType::class),
            ],
            'metadata' => [
                'nullable',
                'array',
            ],
            'metadata.*' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Debe adjuntar un archivo.',
            'file.file' => 'El campo debe ser un archivo válido.',
            'file.max' => 'El archivo excede el tamaño máximo permitido (:max KB).',
            'file.mimetypes' => 'Solo se permiten archivos PDF y DOCX.',
            'document_type.enum' => 'El tipo de documento no es válido.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'file' => 'archivo',
            'document_type' => 'tipo de documento',
            'metadata' => 'metadatos',
        ];
    }
}
