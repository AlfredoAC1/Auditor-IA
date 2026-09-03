<?php

declare(strict_types=1);

namespace App\Http\Requests\BugTickets;

use Illuminate\Foundation\Http\FormRequest;

final class CreateBugTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Describe brevemente el problema en el título.',
            'title.max' => 'El título no puede superar los 255 caracteres.',
            'description.required' => 'Detalla la descripción del error o fallo encontrado.',
            'description.max' => 'La descripción no puede superar los 5000 caracteres.',
        ];
    }
}
