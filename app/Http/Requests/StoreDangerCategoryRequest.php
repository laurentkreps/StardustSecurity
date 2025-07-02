<?php

// =============================================================================
// app/Http/Requests/StoreDangerCategoryRequest.php
// =============================================================================

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDangerCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'        => 'required|string|max:10|unique:danger_categories,code|regex:/^[A-Z0-9.]+$/',
            'title'       => 'required|string|max:500',
            'description' => 'nullable|string|max:2000',
            'applies_to'  => 'required|in:playground,equipment',
            'is_active'   => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'       => 'Le code de la catégorie est obligatoire.',
            'code.unique'         => 'Ce code de catégorie existe déjà.',
            'code.regex'          => 'Le code doit contenir uniquement des lettres majuscules, chiffres et points.',
            'title.required'      => 'Le titre de la catégorie est obligatoire.',
            'applies_to.required' => 'Le domaine d\'application est obligatoire.',
        ];
    }
}
