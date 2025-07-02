<?php

// =============================================================================
// app/Http/Requests/StoreRiskEvaluationRequest.php
// =============================================================================

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRiskEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'playground_id'       => 'required|exists:playgrounds,id',
            'equipment_id'        => 'nullable|exists:equipment,id',
            'danger_category_id'  => 'required|exists:danger_categories,id',
            'evaluation_type'     => 'required|in:initial,post_measures',
            'is_present'          => 'required|boolean',
            'risk_description'    => 'required_if:is_present,true|string|max:1000',
            'probability_value'   => 'required_if:is_present,true|numeric|min:0.1|max:10',
            'exposure_value'      => 'required_if:is_present,true|numeric|min:0.5|max:10',
            'gravity_value'       => 'required_if:is_present,true|numeric|min:1|max:40',
            'preventive_measures' => 'nullable|string|max:2000',
            'evaluator_name'      => 'required|string|max:255',
            'evaluation_date'     => 'required|date|before_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'playground_id.required'          => 'L\'aire de jeux est obligatoire.',
            'danger_category_id.required'     => 'La catégorie de danger est obligatoire.',
            'risk_description.required_if'    => 'La description du risque est obligatoire si le danger est présent.',
            'probability_value.required_if'   => 'La valeur de probabilité est obligatoire si le danger est présent.',
            'probability_value.min'           => 'La probabilité doit être au minimum 0.1.',
            'probability_value.max'           => 'La probabilité doit être au maximum 10.',
            'exposure_value.required_if'      => 'La valeur d\'exposition est obligatoire si le danger est présent.',
            'exposure_value.min'              => 'L\'exposition doit être au minimum 0.5.',
            'exposure_value.max'              => 'L\'exposition doit être au maximum 10.',
            'gravity_value.required_if'       => 'La valeur de gravité est obligatoire si le danger est présent.',
            'gravity_value.min'               => 'La gravité doit être au minimum 1.',
            'gravity_value.max'               => 'La gravité doit être au maximum 40.',
            'evaluator_name.required'         => 'Le nom de l\'évaluateur est obligatoire.',
            'evaluation_date.before_or_equal' => 'La date d\'évaluation ne peut pas être dans le futur.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Si le danger n'est pas présent, on supprime les valeurs numériques
        if (! $this->boolean('is_present')) {
            $this->merge([
                'risk_description'  => null,
                'probability_value' => null,
                'exposure_value'    => null,
                'gravity_value'     => null,
            ]);
        }
    }
}
