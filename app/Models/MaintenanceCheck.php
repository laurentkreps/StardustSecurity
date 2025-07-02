<?php

// =============================================================================
// app/Models/MaintenanceCheck.php
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceCheck extends Model
{
    protected $fillable = [
        'playground_id',
        'equipment_id',
        'check_type',
        'scheduled_date',
        'completed_date',
        'inspector_name',
        'observations',
        'issues_found',
        'actions_taken',
        'next_check_date',
        'status',
    ];

    protected $casts = [
        'scheduled_date'  => 'date',
        'completed_date'  => 'date',
        'next_check_date' => 'date',
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

    // Accesseurs
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
            'completed' => 'Terminé',
            'overdue' => 'En retard',
            default => 'Statut inconnu'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'blue',
            'completed' => 'green',
            'overdue' => 'red',
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

    // Méthodes utiles
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

    // Validation rules
    public static function validationRules(): array
    {
        return [
            'playground_id'   => 'required|exists:playgrounds,id',
            'equipment_id'    => 'nullable|exists:equipment,id',
            'check_type'      => 'required|in:regular_verification,maintenance,periodic_control',
            'scheduled_date'  => 'required|date',
            'completed_date'  => 'nullable|date|after_or_equal:scheduled_date',
            'inspector_name'  => 'nullable|string|max:255',
            'observations'    => 'nullable|string',
            'issues_found'    => 'nullable|string',
            'actions_taken'   => 'nullable|string',
            'next_check_date' => 'nullable|date|after:scheduled_date',
            'status'          => 'required|in:scheduled,completed,overdue',
        ];
    }
}
