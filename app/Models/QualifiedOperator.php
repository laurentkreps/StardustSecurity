<?php

// app/Models/QualifiedOperator.php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualifiedOperator extends Model
{
    protected $fillable = [
        'playground_id', 'first_name', 'last_name', 'birth_date', 'employee_id',
        'phone', 'email', 'equipment_qualifications', 'certifications',
        'training_completion_date', 'certification_expiry', 'medical_fitness_valid',
        'medical_check_date', 'medical_check_expiry', 'years_experience',
        'previous_experience', 'languages_spoken', 'status', 'employment_start',
        'employment_end', 'notes',
    ];

    protected $casts = [
        'birth_date'               => 'date',
        'training_completion_date' => 'date',
        'certification_expiry'     => 'date',
        'medical_check_date'       => 'date',
        'medical_check_expiry'     => 'date',
        'employment_start'         => 'date',
        'employment_end'           => 'date',
        'equipment_qualifications' => 'array',
        'certifications'           => 'array',
        'languages_spoken'         => 'array',
        'medical_fitness_valid'    => 'boolean',
    ];

    public function playground(): BelongsTo
    {
        return $this->belongsTo(Playground::class);
    }

    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => "{$this->first_name} {$this->last_name}"
        );
    }

    public function age(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->birth_date ? $this->birth_date->age : null
        );
    }

    public function isCertificationExpiring(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->certification_expiry && $this->certification_expiry->diffInDays(now()) <= 60
        );
    }

    public function isMedicalCheckExpiring(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->medical_check_expiry && $this->medical_check_expiry->diffInDays(now()) <= 30
        );
    }

    public function isQualifiedFor(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status === 'active' &&
            $this->medical_fitness_valid &&
            (! $this->certification_expiry || $this->certification_expiry->isFuture())
        );
    }

    public function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn()   => match ($this->status) {
                'active'    => 'Actif',
                'inactive'  => 'Inactif',
                'suspended' => 'Suspendu',
                'training'  => 'En formation',
                default     => $this->status
            }
        );
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeQualified($query)
    {
        return $query->where('status', 'active')
            ->where('medical_fitness_valid', true)
            ->where(function ($q) {
                $q->whereNull('certification_expiry')
                    ->orWhere('certification_expiry', '>', now());
            });
    }

    public function scopeQualifiedForEquipment($query, $equipmentType)
    {
        return $query->qualified()
            ->whereJsonContains('equipment_qualifications', $equipmentType);
    }

    public function scopeExpiringCertifications($query, $days = 60)
    {
        return $query->where('certification_expiry', '<=', now()->addDays($days))
            ->where('certification_expiry', '>', now());
    }
}
