<?php
// =============================================================================
// app/Http/Requests/StoreMaintenanceCheckRequest.php
// =============================================================================

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'playground_id'   => 'required|exists:playgrounds,id',
            'equipment_id'    => 'nullable|exists:equipment,id',
            'check_type'      => 'required|in:regular_verification,maintenance,periodic_control',
            'scheduled_date'  => 'required|date|after_or_equal:today',
            'completed_date'  => 'nullable|date|after_or_equal:scheduled_date',
            'inspector_name'  => 'required_with:completed_date|string|max:255',
            'observations'    => 'nullable|string|max:2000',
            'issues_found'    => 'nullable|string|max:2000',
            'actions_taken'   => 'required_with:issues_found|string|max:2000',
            'next_check_date' => 'nullable|date|after:scheduled_date',
            'status'          => 'required|in:scheduled,completed,overdue',
        ];
    }

    public function messages(): array
    {
        return [
            'playground_id.required'        => 'L\'aire de jeux est obligatoire.',
            'check_type.required'           => 'Le type de contrôle est obligatoire.',
            'scheduled_date.required'       => 'La date de planification est obligatoire.',
            'scheduled_date.after_or_equal' => 'La date de planification ne peut pas être dans le passé.',
            'completed_date.after_or_equal' => 'La date de réalisation doit être postérieure à la date planifiée.',
            'inspector_name.required_with'  => 'Le nom de l\'inspecteur est obligatoire si le contrôle est terminé.',
            'actions_taken.required_with'   => 'Les actions entreprises sont obligatoires si des problèmes ont été trouvés.',
            'next_check_date.after'         => 'La date du prochain contrôle doit être postérieure à la date planifiée.',
        ];
    }
}
