<?php

// =============================================================================
// MODÈLE ELOQUENT CORRIGÉ: IncidentReport.php
// =============================================================================

// Avec génération automatique du numéro d'incident

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

    // Accesseurs pour JSON fields (stockés comme text)
    public function getPersonsInvolvedAttribute($value)
    {
        return $value ? json_decode($value, true) : null;
    }

    public function setPersonsInvolvedAttribute($value)
    {
        $this->attributes['persons_involved'] = $value ? json_encode($value) : null;
    }

    public function getWitnessesAttribute($value)
    {
        return $value ? json_decode($value, true) : null;
    }

    public function setWitnessesAttribute($value)
    {
        $this->attributes['witnesses'] = $value ? json_encode($value) : null;
    }

    public function getCorrectiveActionsAttribute($value)
    {
        return $value ? json_decode($value, true) : null;
    }

    public function setCorrectiveActionsAttribute($value)
    {
        $this->attributes['corrective_actions'] = $value ? json_encode($value) : null;
    }

    public function getAttachmentsAttribute($value)
    {
        return $value ? json_decode($value, true) : null;
    }

    public function setAttachmentsAttribute($value)
    {
        $this->attributes['attachments'] = $value ? json_encode($value) : null;
    }
}
