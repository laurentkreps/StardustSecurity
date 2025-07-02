<?php

// =============================================================================
// app/Http/Requests/UpdatePlaygroundRequest.php
// =============================================================================

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlaygroundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => [
                'required',
                'string',
                'max:255',
                Rule::unique('playgrounds', 'name')->ignore($this->playground),
            ],
            'address'            => 'nullable|string',
            'city'               => 'nullable|string|max:100',
            'postal_code'        => 'nullable|string|max:10|regex:/^[0-9]{4}$/',
            'manager_name'       => 'nullable|string|max:255',
            'manager_contact'    => 'nullable|string|max:255|email',
            'installation_date'  => 'nullable|date|before_or_equal:today',
            'last_analysis_date' => 'nullable|date|before_or_equal:today',
            'status'             => 'required|in:active,inactive,maintenance',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                      => 'Le nom de l\'aire de jeux est obligatoire.',
            'name.unique'                        => 'Une aire de jeux avec ce nom existe déjà.',
            'postal_code.regex'                  => 'Le code postal doit contenir 4 chiffres.',
            'manager_contact.email'              => 'L\'adresse email du gestionnaire n\'est pas valide.',
            'installation_date.before_or_equal'  => 'La date d\'installation ne peut pas être dans le futur.',
            'last_analysis_date.before_or_equal' => 'La date d\'analyse ne peut pas être dans le futur.',
        ];
    }
}
