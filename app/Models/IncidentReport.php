<?php

// =============================================================================
// 1. app/Models/IncidentReport.php - CORRIGÉ avec JSON natif
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class IncidentReport extends Model
{
    protected $fillable = [
        'incident_number', 'playground_id', 'equipment_id', 'incident_date',
        'incident_type', 'severity', 'description', 'circumstances',
        'persons_involved', 'witnesses', 'injuries_description',
        'medical_assistance_required', 'immediate_actions', 'preventive_measures',
        'reported_to_authorities', 'authority_report_date', 'authority_reference',
        'reporter_name', 'reporter_contact', 'reporter_function', 'status',
        'investigation_notes', 'corrective_actions', 'closure_date', 'closed_by',
        'lessons_learned', 'attachments', 'weather_conditions',
        'visitor_count_estimate', 'incident_time', 'temperature',
        'requires_equipment_shutdown', 'equipment_restart_date',
    ];

    // 🔧 CASTS CORRIGÉS - JSON natif au lieu d'accesseurs manuels
    protected $casts = [
        'incident_date'               => 'datetime',
        'authority_report_date'       => 'date',
        'closure_date'                => 'date',
        'equipment_restart_date'      => 'date',
        'incident_time'               => 'datetime:H:i',
        'reported_to_authorities'     => 'boolean',
        'medical_assistance_required' => 'boolean',
        'requires_equipment_shutdown' => 'boolean',
        'temperature'                 => 'decimal:1',
        // 🆕 JSON NATIF - Remplace les accesseurs manuels
        'persons_involved'            => 'array',
        'witnesses'                   => 'array',
        'corrective_actions'          => 'array',
        'attachments'                 => 'array',
    ];

    // Boot method pour générer automatiquement le numéro d'incident
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($incident) {
            if (empty($incident->incident_number)) {
                $incident->incident_number = static::generateIncidentNumber($incident->incident_date);
            }
        });
    }

    public static function generateIncidentNumber($incidentDate = null): string
    {
        $year = $incidentDate ? date('Y', strtotime($incidentDate)) : date('Y');

        return DB::transaction(function () use ($year) {
            $maxNumber = static::where('incident_number', 'like', $year . '-%')
                ->lockForUpdate()
                ->selectRaw('MAX(CAST(SUBSTR(incident_number, 6) AS INTEGER)) as max_num')
                ->value('max_num') ?? 0;

            $nextNumber = $maxNumber + 1;
            return $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }

    // Relations
    public function playground(): BelongsTo
    {
        return $this->belongsTo(Playground::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    // 🆕 ACCESSEURS AJOUTÉS pour améliorer l'API
    public function getSeverityLabelAttribute(): string
    {
        return match ($this->severity) {
            'minor' => 'Mineur',
            'moderate' => 'Modéré',
            'serious' => 'Grave',
            'critical' => 'Critique',
            default => 'Non défini'
        };
    }

    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'minor' => 'green',
            'moderate' => 'yellow',
            'serious' => 'orange',
            'critical' => 'red',
            default => 'gray'
        };
    }

    public function getIncidentTypeLabelAttribute(): string
    {
        return match ($this->incident_type) {
            'accident' => 'Accident',
            'serious_incident' => 'Incident grave',
            'damage' => 'Dégâts matériels',
            'near_miss' => 'Presque accident',
            'vandalism' => 'Vandalisme',
            'other' => 'Autre',
            default => 'Non défini'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'reported' => 'Signalé',
            'investigating' => 'En cours d\'investigation',
            'resolved' => 'Résolu',
            'closed' => 'Clôturé',
            default => 'Statut inconnu'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'reported' => 'blue',
            'investigating' => 'orange',
            'resolved' => 'green',
            'closed' => 'gray',
            default => 'gray'
        };
    }

    // 🆕 MÉTHODES UTILITAIRES AJOUTÉES
    public function isOpen(): bool
    {
        return in_array($this->status, ['reported', 'investigating']);
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function requiresAuthorityReporting(): bool
    {
        // Les incidents graves et critiques nécessitent un signalement
        return in_array($this->severity, ['serious', 'critical']) ||
        $this->medical_assistance_required ||
        $this->requires_equipment_shutdown;
    }

    public function hasPersonsInvolved(): bool
    {
        return ! empty($this->persons_involved);
    }

    public function hasWitnesses(): bool
    {
        return ! empty($this->witnesses);
    }

    public function hasAttachments(): bool
    {
        return ! empty($this->attachments);
    }

    public function getPersonsInvolvedCount(): int
    {
        return is_array($this->persons_involved) ? count($this->persons_involved) : 0;
    }

    public function getWitnessesCount(): int
    {
        return is_array($this->witnesses) ? count($this->witnesses) : 0;
    }

    public function getDaysOpen(): int
    {
        if ($this->isClosed() && $this->closure_date) {
            return $this->incident_date->diffInDays($this->closure_date);
        }

        return $this->incident_date->diffInDays(now());
    }

    // 🆕 MÉTHODES POUR GÉRER LES TABLEAUX JSON
    public function addPersonInvolved(array $person): void
    {
        $persons   = $this->persons_involved ?? [];
        $persons[] = array_merge([
            'name'               => null,
            'age'                => null,
            'contact'            => null,
            'role'               => null,
            'injury_description' => null,
        ], $person);

        $this->persons_involved = $persons;
        $this->save();
    }

    public function addWitness(array $witness): void
    {
        $witnesses   = $this->witnesses ?? [];
        $witnesses[] = array_merge([
            'name'      => null,
            'contact'   => null,
            'statement' => null,
        ], $witness);

        $this->witnesses = $witnesses;
        $this->save();
    }

    public function addCorrectiveAction(array $action): void
    {
        $actions   = $this->corrective_actions ?? [];
        $actions[] = array_merge([
            'action'          => null,
            'responsible'     => null,
            'deadline'        => null,
            'status'          => 'planned',
            'completion_date' => null,
            'notes'           => null,
        ], $action);

        $this->corrective_actions = $actions;
        $this->save();
    }

    public function addAttachment(string $path, string $type, ?string $description = null): void
    {
        $attachments   = $this->attachments ?? [];
        $attachments[] = [
            'path'        => $path,
            'type'        => $type, // 'photo', 'document', 'video', etc.
            'description' => $description,
            'uploaded_at' => now()->toISOString(),
        ];

        $this->attachments = $attachments;
        $this->save();
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['reported', 'investigating']);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeRequiringAuthorityReport($query)
    {
        return $query->where(function ($q) {
            $q->whereIn('severity', ['serious', 'critical'])
                ->orWhere('medical_assistance_required', true)
                ->orWhere('requires_equipment_shutdown', true);
        });
    }

    public function scopeNotReportedToAuthorities($query)
    {
        return $query->where('reported_to_authorities', false);
    }

    public function scopeWithEquipmentShutdown($query)
    {
        return $query->where('requires_equipment_shutdown', true);
    }

    // Validation rules
    public static function validationRules(): array
    {
        return [
            'playground_id'               => 'required|exists:playgrounds,id',
            'equipment_id'                => 'nullable|exists:equipment,id',
            'incident_date'               => 'required|date|before_or_equal:now',
            'incident_type'               => 'required|in:accident,serious_incident,damage,near_miss,vandalism,other',
            'severity'                    => 'required|in:minor,moderate,serious,critical',
            'description'                 => 'required|string',
            'circumstances'               => 'nullable|string',
            'persons_involved'            => 'nullable|array',
            'witnesses'                   => 'nullable|array',
            'injuries_description'        => 'nullable|string',
            'medical_assistance_required' => 'boolean',
            'immediate_actions'           => 'nullable|string',
            'preventive_measures'         => 'nullable|string',
            'reported_to_authorities'     => 'boolean',
            'authority_report_date'       => 'nullable|date|after_or_equal:incident_date',
            'authority_reference'         => 'nullable|string',
            'reporter_name'               => 'required|string|max:255',
            'reporter_contact'            => 'nullable|string|max:255',
            'reporter_function'           => 'nullable|string|max:255',
            'status'                      => 'required|in:reported,investigating,resolved,closed',
            'investigation_notes'         => 'nullable|string',
            'corrective_actions'          => 'nullable|array',
            'closure_date'                => 'nullable|date|after_or_equal:incident_date',
            'closed_by'                   => 'nullable|string|max:255',
            'lessons_learned'             => 'nullable|string',
            'attachments'                 => 'nullable|array',
            'weather_conditions'          => 'nullable|string|max:100',
            'visitor_count_estimate'      => 'nullable|integer|min:0',
            'incident_time'               => 'nullable|date_format:H:i',
            'temperature'                 => 'nullable|numeric',
            'requires_equipment_shutdown' => 'boolean',
            'equipment_restart_date'      => 'nullable|date|after_or_equal:incident_date',
        ];
    }
}

// =============================================================================
// 2. INSTRUCTIONS POUR DÉPLACER ElectricalTestManager
// =============================================================================

/*
🔧 ACTIONS MANUELLES REQUISES :

1. DÉPLACER LE FICHIER :
   mv app/Models/ElectricalTestManager.php app/Livewire/ElectricalTestManager.php

2. METTRE À JOUR LE NAMESPACE dans le fichier déplacé :
   Changer : namespace App\Models;
   Vers :    namespace App\Livewire;

3. VÉRIFIER LES IMPORTS dans les autres fichiers :
   - Si des contrôleurs ou vues importent App\Models\ElectricalTestManager
   - Les changer vers App\Livewire\ElectricalTestManager

4. SUPPRIMER TOUTE RÉFÉRENCE à ElectricalTestManager comme modèle :
   - Dans les relations Eloquent
   - Dans les seeders
   - Dans les tests

5. VÉRIFIER que c'est bien un composant Livewire :
   - Doit extends Component (pas Model)
   - Doit avoir une méthode render()
   - Doit être utilisé dans des vues Blade avec <livewire:electrical-test-manager>

Le fichier ElectricalTestManager que nous avons analysé est effectivement un composant
Livewire (extends Component, méthode render(), etc.) qui était mal placé dans app/Models/.
*/
