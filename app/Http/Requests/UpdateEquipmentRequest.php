<?php

// =============================================================================
// app/Http/Requests/UpdateEquipmentRequest.php
// =============================================================================

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'playground_id'             => 'required|exists:playgrounds,id',
            'reference_code'            => [
                'required',
                'string',
                'max:50',
                Rule::unique('equipment', 'reference_code')->ignore($this->equipment),
            ],
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
}
