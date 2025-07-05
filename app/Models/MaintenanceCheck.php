<?php

// =============================================================================
// 2. app/Models/MaintenanceCheck.php - CORRIGÉ
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceCheck extends Model
{
    // 🔧 FILLABLE CORRIGÉ - TOUS les champs de la migration
    protected $fillable = [
        'playground_id',
        'equipment_id',
        'check_type',
        'scheduled_date',
        'completed_date',
        'inspector_name',
        // 🆕 AJOUTÉS - manquaient dans l'original
        'inspector_qualification',
        'observations',
        'issues_found',
        'actions_taken',
        // 🆕 AJOUTÉS - manquaient dans l'original
        'recommendations',
        'next_check_date',
        // 🆕 AJOUTÉS - manquaient dans l'original
        'overall_condition',
        'status',
        // 🆕 AJOUTÉS - manquaient dans l'original
        'duration_hours',
        'cost',
        'weather_conditions',
        'checklist_items',
        'photos',
        'requires_follow_up',
        'follow_up_date',
        'compliance_notes',
    ];

    // 🔧 CASTS CORRIGÉS - TOUS les types correspondants
    protected $casts = [
        'scheduled_date'     => 'date',
        'completed_date'     => 'date',
        'next_check_date'    => 'date',
        'follow_up_date'     => 'date',      // 🆕
        'duration_hours'     => 'decimal:2', // 🆕
        'cost'               => 'decimal:2', // 🆕
        'checklist_items'    => 'array',     // 🆕
        'photos'             => 'array',     // 🆕
        'requires_follow_up' => 'boolean',   // 🆕
    ];

    // Relations (inchangées)
    public function playground(): BelongsTo
    {
        return $this->belongsTo(Playground::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    // Scopes existants + nouveaux
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'overdue');
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('check_type', $type);
    }

    public function scopeByInspector(Builder $query, string $inspector): Builder
    {
        return $query->where('inspector_name', $inspector);
    }

    public function scopeDueThisWeek(Builder $query): Builder
    {
        return $query->where('scheduled_date', '>=', now()->startOfWeek())
            ->where('scheduled_date', '<=', now()->endOfWeek());
    }

    public function scopeDueThisMonth(Builder $query): Builder
    {
        return $query->where('scheduled_date', '>=', now()->startOfMonth())
            ->where('scheduled_date', '<=', now()->endOfMonth());
    }

    public function scopeWithIssues(Builder $query): Builder
    {
        return $query->whereNotNull('issues_found')
            ->where('issues_found', '!=', '');
    }

    // 🆕 SCOPES AJOUTÉS
    public function scopeRequiringFollowUp(Builder $query): Builder
    {
        return $query->where('requires_follow_up', true);
    }

    public function scopeByCondition(Builder $query, string $condition): Builder
    {
        return $query->where('overall_condition', $condition);
    }

    public function scopeCriticalCondition(Builder $query): Builder
    {
        return $query->whereIn('overall_condition', ['poor', 'critical']);
    }

    // Accesseurs existants + nouveaux
    public function getCheckTypeLabelAttribute(): string
    {
        return match ($this->check_type) {
            'regular_verification' => 'Vérification régulière',
            'maintenance' => 'Maintenance',
            'periodic_control' => 'Contrôle périodique',
            default => 'Type inconnu'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'Planifié',
            'in_progress' => 'En cours', // 🆕
            'completed' => 'Terminé',
            'overdue' => 'En retard',
            'cancelled' => 'Annulé', // 🆕
            default => 'Statut inconnu'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'blue',
            'in_progress' => 'orange', // 🆕
            'completed' => 'green',
            'overdue' => 'red',
            'cancelled' => 'gray', // 🆕
            default => 'gray'
        ];
    }

    // 🆕 ACCESSEURS AJOUTÉS
    public function getOverallConditionLabelAttribute(): string
        {
        return match ($this->overall_condition) {
            'excellent' => 'Excellent',
            'good' => 'Bon',
            'acceptable' => 'Acceptable',
            'poor' => 'Mauvais',
            'critical' => 'Critique',
            default => 'Non évalué'
        };
    }

    public function getOverallConditionColorAttribute(): string
        {
        return match ($this->overall_condition) {
            'excellent' => 'green',
            'good' => 'blue',
            'acceptable' => 'yellow',
            'poor' => 'orange',
            'critical' => 'red',
            default => 'gray'
        };
    }

    public function getDaysUntilDueAttribute(): ?int
        {
        return $this->scheduled_date ?
        now()->diffInDays($this->scheduled_date, false) : null;
    }

    public function getDurationAttribute(): ?string
        {
        if (! $this->completed_date || ! $this->scheduled_date) {
            return null;
        }

        $days = $this->scheduled_date->diffInDays($this->completed_date);

        if ($days === 0) {
            return 'Même jour';
        } elseif ($days === 1) {
            return '1 jour';
        } else {
            return "{$days} jours";
        }
    }

    // 🆕 ACCESSEURS AJOUTÉS
    public function getFormattedCostAttribute(): ?string
        {
        return $this->cost ? number_format($this->cost, 2) . ' €' : null;
    }

    public function getFormattedDurationAttribute(): ?string
        {
        if (! $this->duration_hours) {
            return null;
        }

        $hours   = floor($this->duration_hours);
        $minutes = ($this->duration_hours - $hours) * 60;

        if ($minutes > 0) {
            return "{$hours}h" . sprintf('%02d', $minutes);
        }

        return "{$hours}h";
    }

    public function getChecklistCompletionAttribute(): ?float
        {
        if (! $this->checklist_items) {
            return null;
        }

        $items     = $this->checklist_items;
        $total     = count($items);
        $completed = count(array_filter($items, fn($item) => $item['completed'] ?? false));

        return $total > 0 ? ($completed / $total) * 100 : 0;
    }

    // Méthodes utiles existantes + nouvelles
    public function isScheduled(): bool
        {
        return $this->status === 'scheduled';
    }

    public function isCompleted(): bool
        {
        return $this->status === 'completed';
    }

    public function isOverdue(): bool
        {
        return $this->status === 'overdue' ||
            ($this->status === 'scheduled' && $this->scheduled_date < now());
    }

    public function hasIssues(): bool
        {
        return ! empty($this->issues_found);
    }

    public function isDueWithinDays(int $days): bool
        {
        return $this->scheduled_date &&
        $this->scheduled_date <= now()->addDays($days);
    }

    // 🆕 MÉTHODES AJOUTÉES
    public function isCriticalCondition(): bool
        {
        return in_array($this->overall_condition, ['poor', 'critical']);
    }

    public function needsFollowUp(): bool
        {
        return $this->requires_follow_up && $this->follow_up_date && $this->follow_up_date <= now();
    }

    public function hasPhotos(): bool
        {
        return ! empty($this->photos);
    }

    public function addPhoto(string $path, ?string $description = null): void
        {
        $photos   = $this->photos ?? [];
        $photos[] = [
            'path'        => $path,
            'description' => $description,
            'uploaded_at' => now()->toISOString(),
        ];
        $this->photos = $photos;
        $this->save();
    }

    public function addChecklistItem(string $item, bool $completed = false, ?string $notes = null): void
        {
        $items   = $this->checklist_items ?? [];
        $items[] = [
            'item'       => $item,
            'completed'  => $completed,
            'notes'      => $notes,
            'checked_at' => $completed ? now()->toISOString() : null,
        ];
        $this->checklist_items = $items;
        $this->save();
    }

    public function markAsCompleted(array $data = []): self
        {
        $this->update(array_merge([
            'status'         => 'completed',
            'completed_date' => now(),
        ], $data));

        // Programmer le prochain contrôle si c'est une vérification régulière
        if ($this->check_type === 'regular_verification' && $this->equipment) {
            $this->scheduleNextCheck();
        }

        return $this;
    }

    public function markAsOverdue(): self
        {
        $this->update(['status' => 'overdue']);
        return $this;
    }

    protected function scheduleNextCheck(): void
        {
        if (! $this->equipment || ! $this->next_check_date) {
            return;
        }

        $this->equipment->maintenanceChecks()->create([
            'playground_id'  => $this->playground_id,
            'check_type'     => $this->check_type,
            'scheduled_date' => $this->next_check_date,
            'status'         => 'scheduled',
        ]);
    }

    // Boot method pour les événements automatiques
    protected static function boot()
        {
        parent::boot();

        // Marquer automatiquement comme en retard si la date est dépassée
        static::retrieved(function ($check) {
            if ($check->status === 'scheduled' && $check->scheduled_date < now()) {
                $check->markAsOverdue();
            }
        });
    }

    // Validation rules (mises à jour)
    public static function validationRules(): array
        {
        return [
            'playground_id'           => 'required|exists:playgrounds,id',
            'equipment_id'            => 'nullable|exists:equipment,id',
            'check_type'              => 'required|in:regular_verification,maintenance,periodic_control',
            'scheduled_date'          => 'required|date',
            'completed_date'          => 'nullable|date|after_or_equal:scheduled_date',
            'inspector_name'          => 'nullable|string|max:255',
            'inspector_qualification' => 'nullable|string|max:255', // 🆕
            'observations'            => 'nullable|string',
            'issues_found'            => 'nullable|string',
            'actions_taken'           => 'nullable|string',
            'recommendations'         => 'nullable|string', // 🆕
            'next_check_date'         => 'nullable|date|after:scheduled_date',
            'overall_condition'       => 'nullable|in:excellent,good,acceptable,poor,critical',           // 🆕
            'status'                  => 'required|in:scheduled,in_progress,completed,overdue,cancelled', // 🆕
            'duration_hours'          => 'nullable|numeric|min:0',                                        // 🆕
            'cost'                    => 'nullable|numeric|min:0',                                        // 🆕
            'weather_conditions'      => 'nullable|string|max:100',                                       // 🆕
            'checklist_items'         => 'nullable|array',                                                // 🆕
            'photos'                  => 'nullable|array',                                                // 🆕
            'requires_follow_up'      => 'boolean',                                                       // 🆕
            'follow_up_date'          => 'nullable|date|after_or_equal:scheduled_date',                   // 🆕
            'compliance_notes'        => 'nullable|string',                                               // 🆕
        ];
    }
}
