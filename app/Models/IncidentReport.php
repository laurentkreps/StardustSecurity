<?php

// =============================================================================
// app/Models/IncidentReport.php
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentReport extends Model
{
    protected $fillable = [
        'playground_id',
        'equipment_id',
        'incident_date',
        'incident_type',
        'severity',
        'description',
        'persons_involved',
        'immediate_actions',
        'preventive_measures',
        'reported_to_authorities',
        'reporter_name',
        'reporter_contact',
        'status',
    ];

    protected $casts = [
        'incident_date'           => 'datetime',
        'reported_to_authorities' => 'boolean',
    ];

    // Relations
    public function playground(): BelongsTo
    {
        return $this->belongsTo(Playground::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    // Scopes
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('incident_type', $type);
    }

    public function scopeBySeverity(Builder $query, string $severity): Builder
    {
        return $query->where('severity', $severity);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeReported(Builder $query): Builder
    {
        return $query->where('status', 'reported');
    }

    public function scopeInvestigating(Builder $query): Builder
    {
        return $query->where('status', 'investigating');
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', 'resolved');
    }

    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('severity', 'critical');
    }

    public function scopeSerious(Builder $query): Builder
    {
        return $query->where('severity', 'serious');
    }

    public function scopeRecentIncidents(Builder $query, int $days = 30): Builder
    {
        return $query->where('incident_date', '>=', now()->subDays($days));
    }

    public function scopeReportedToAuthorities(Builder $query): Builder
    {
        return $query->where('reported_to_authorities', true);
    }

    // Accesseurs
    public function getIncidentTypeLabelAttribute(): string
    {
        return match ($this->incident_type) {
            'accident' => 'Accident',
            'serious_incident' => 'Incident grave',
            'damage' => 'Dommage matériel',
            'other' => 'Autre',
            default => 'Type inconnu'
        };
    }

    public function getSeverityLabelAttribute(): string
    {
        return match ($this->severity) {
            'minor' => 'Mineur',
            'moderate' => 'Modéré',
            'serious' => 'Grave',
            'critical' => 'Critique',
            default => 'Gravité inconnue'
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

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'reported' => 'Signalé',
            'investigating' => 'En cours d\'enquête',
            'resolved' => 'Résolu',
            default => 'Statut inconnu'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'reported' => 'red',
            'investigating' => 'yellow',
            'resolved' => 'green',
            default => 'gray'
        };
    }

    public function getTimeSinceIncidentAttribute(): string
    {
        return $this->incident_date->diffForHumans();
    }

    public function getIncidentNumberAttribute(): string
    {
        return 'INC-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    // Méthodes utiles
    public function isAccident(): bool
    {
        return $this->incident_type === 'accident';
    }

    public function isSeriousIncident(): bool
    {
        return $this->incident_type === 'serious_incident';
    }

    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }

    public function isSerious(): bool
    {
        return $this->severity === 'serious';
    }

    public function isReported(): bool
    {
        return $this->status === 'reported';
    }

    public function isInvestigating(): bool
    {
        return $this->status === 'investigating';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function requiresAuthorityReport(): bool
    {
        return in_array($this->severity, ['serious', 'critical']) ||
        $this->incident_type === 'serious_incident';
    }

    public function markAsReportedToAuthorities(): self
    {
        $this->update(['reported_to_authorities' => true]);
        return $this;
    }

    public function startInvestigation(): self
    {
        $this->update(['status' => 'investigating']);
        return $this;
    }

    public function resolve(string $preventiveMeasures = null): self
    {
        $updateData = ['status' => 'resolved'];

        if ($preventiveMeasures) {
            $updateData['preventive_measures'] = $preventiveMeasures;
        }

        $this->update($updateData);
        return $this;
    }

    public function getDaysToResolve(): ?int
    {
        if (! $this->isResolved()) {
            return null;
        }

        return $this->incident_date->diffInDays($this->updated_at);
    }

    // Méthodes statiques utiles
    public static function getIncidentStats(int $days = 30): array
    {
        $incidents = static::where('incident_date', '>=', now()->subDays($days));

        return [
            'total'                   => $incidents->count(),
            'by_severity'             => $incidents->get()->groupBy('severity')->map->count(),
            'by_type'                 => $incidents->get()->groupBy('incident_type')->map->count(),
            'by_status'               => $incidents->get()->groupBy('status')->map->count(),
            'reported_to_authorities' => $incidents->where('reported_to_authorities', true)->count(),
        ];
    }

    // Validation rules
    public static function validationRules(): array
    {
        return [
            'playground_id'           => 'required|exists:playgrounds,id',
            'equipment_id'            => 'nullable|exists:equipment,id',
            'incident_date'           => 'required|date|before_or_equal:now',
            'incident_type'           => 'required|in:accident,serious_incident,damage,other',
            'severity'                => 'required|in:minor,moderate,serious,critical',
            'description'             => 'required|string',
            'persons_involved'        => 'nullable|string',
            'immediate_actions'       => 'nullable|string',
            'preventive_measures'     => 'nullable|string',
            'reported_to_authorities' => 'boolean',
            'reporter_name'           => 'required|string|max:255',
            'reporter_contact'        => 'nullable|string|max:255',
            'status'                  => 'required|in:reported,investigating,resolved',
        ];
    }
}
