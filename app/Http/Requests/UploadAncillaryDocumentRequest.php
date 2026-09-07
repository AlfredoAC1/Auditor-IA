<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\DocumentAnalysis;
use Illuminate\Foundation\Http\FormRequest;

class UploadAncillaryDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var DocumentAnalysis|string|null $analysis */
        $analysis = $this->route('analysis');

        if (! $analysis instanceof DocumentAnalysis) {
            $analysis = DocumentAnalysis::find($analysis);
        }

        if ($analysis === null) {
            return false;
        }

        return (bool) $this->user()?->can('addAncillary', $analysis);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:51200',
                'mimetypes:application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
            'relationship_type' => ['nullable', 'string', 'max:50'],
            'notes'             => ['nullable', 'string', 'max:1000'],
            'model_slug'        => ['nullable', 'string', 'exists:ai_models,slug'],
        ];
    }

    /**
     * Custom validation messages in Spanish.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required'  => 'Debe adjuntar un archivo anexo.',
            'file.file'      => 'El archivo adjunto no es válido.',
            'file.max'       => 'El archivo anexo no debe superar los 50 MB.',
            'file.mimetypes' => 'Solo se permiten archivos en formato PDF o Word (.docx).',
            'notes.max'      => 'Las notas no pueden superar los 1000 caracteres.',
        ];
    }
}
