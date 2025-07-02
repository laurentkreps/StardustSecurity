<?php

// =============================================================================
// app/Http/Requests/StoreEquipmentRequest.php
// =============================================================================

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'playground_id'             => 'required|exists:playgrounds,id',
            'reference_code'            => 'required|string|max:50|unique:equipment,reference_code',
            'equipment_type'            => 'required|string|max:255',
            'brand'                     => 'nullable|string|max:255',
            'manufacturer_details'      => 'nullable|string|max:1000',
            'supplier_details'          => 'nullable|string|max:1000',
            'applicable_norm'           => 'nullable|string|max:255',
            'purchase_date'             => 'nullable|date|before_or_equal:today',
            'installation_date'         => 'nullable|date|before_or_equal:today|after_or_equal:purchase_date',
            'verification_frequency'    => 'nullable|string|max:100',
            'risk_analysis_certificate' => 'nullable|string|max:1000',
            'status'                    => 'required|in:active,maintenance,out_of_service',
        ];
    }

    public function messages(): array
    {
        return [
            'playground_id.required'           => 'L\'aire de jeux est obligatoire.',
            'playground_id.exists'             => 'L\'aire de jeux sélectionnée n\'existe pas.',
            'reference_code.required'          => 'Le code de référence est obligatoire.',
            'reference_code.unique'            => 'Ce code de référence existe déjà.',
            'equipment_type.required'          => 'Le type d\'équipement est obligatoire.',
            'purchase_date.before_or_equal'    => 'La date d\'achat ne peut pas être dans le futur.',
            'installation_date.after_or_equal' => 'La date d\'installation doit être postérieure à la date d\'achat.',
        ];
    }
}
