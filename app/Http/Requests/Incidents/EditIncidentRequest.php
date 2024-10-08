<?php

namespace App\Http\Requests\Incidents;

use Illuminate\Foundation\Http\FormRequest;

class EditIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required'],
            'description' => ['required'],
            'date' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'O campo tipo é obrigatório.',
            'date.required' => 'O campo data é obrigatório.',
            'description.required' => 'O campo descrição é obrigatório.',
        ];
    }
}
