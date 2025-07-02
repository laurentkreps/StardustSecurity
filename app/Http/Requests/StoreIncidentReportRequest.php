<?php

// =============================================================================
// app/Http/Requests/StoreIncidentReportRequest.php
// =============================================================================

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncidentReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'playground_id'           => 'required|exists:playgrounds,id',
            'equipment_id'            => 'nullable|exists:equipment,id',
            'incident_date'           => 'required|date|before_or_equal:now',
            'incident_type'           => 'required|in:accident,serious_incident,damage,other',
            'severity'                => 'required|in:minor,moderate,serious,critical',
            'description'             => 'required|string|max:2000',
            'persons_involved'        => 'nullable|string|max:1000',
            'immediate_actions'       => 'required|string|max:2000',
            'preventive_measures'     => 'nullable|string|max:2000',
            'reported_to_authorities' => 'boolean',
            'reporter_name'           => 'required|string|max:255',
            'reporter_contact'        => 'nullable|string|max:255|email',
            'status'                  => 'required|in:reported,investigating,resolved',
        ];
    }

    public function messages(): array
    {
        return [
            'playground_id.required'        => 'L\'aire de jeux est obligatoire.',
            'incident_date.required'        => 'La date de l\'incident est obligatoire.',
            'incident_date.before_or_equal' => 'La date de l\'incident ne peut pas être dans le futur.',
            'incident_type.required'        => 'Le type d\'incident est obligatoire.',
            'severity.required'             => 'La gravité de l\'incident est obligatoire.',
            'description.required'          => 'La description de l\'incident est obligatoire.',
            'immediate_actions.required'    => 'Les actions immédiates sont obligatoires.',
            'reporter_name.required'        => 'Le nom du déclarant est obligatoire.',
            'reporter_contact.email'        => 'L\'adresse email du déclarant n\'est pas valide.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Automatiquement marquer comme signalé aux autorités si c'est critique ou grave
        if (in_array($this->severity, ['serious', 'critical']) || $this->incident_type === 'serious_incident') {
            $this->merge(['reported_to_authorities' => true]);
        }
    }
}
