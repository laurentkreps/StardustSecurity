<?php

// =============================================================================
// 3. app/Models/EquipmentCertification.php - FICHIER RENOMMÉ ET CORRIGÉ
// =============================================================================
// 🔧 RENOMMER: EquipementCertification.php -> EquipmentCertification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentCertification extends Model
{
    // 🔧 FILLABLE CORRIGÉ - 'technical_data' -> 'notes' selon migration
    protected $fillable = [
        'equipment_id',
        'certification_type',
        'norm_reference',
        'certificate_number',
        'issuing_body',
        'issue_date',
        'expiry_date',
        'status',
        'scope',
        'restrictions',
        'document_path',
        'notes', // 🔧 CORRIGÉ: était 'technical_data' dans l'original
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'expiry_date' => 'date',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function certificationTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn()                => match ($this->certification_type) {
                'ce_marking'             => 'Marquage CE',
                'declaration_conformity' => 'Déclaration de conformité',
                'type_examination'       => 'Examen de type',
                'electrical_safety'      => 'Sécurité électrique',
                'installation_approval'  => 'Agrément d\'installation',
                'operational_permit'     => 'Permis d\'exploitation',
                'other'                  => 'Autre',
                default                  => $this->certification_type
            }
        );
    }

    public function isExpiringSoon(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->expiry_date && $this->expiry_date->diffInDays(now()) <= 60
        );
    }

    public function isValid(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status === 'valid' && (! $this->expiry_date || $this->expiry_date->isFuture())
        );
    }

    // Scopes
    public function scopeValid($query)
    {
        return $query->where('status', 'valid')
            ->where(function ($q) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>', now());
            });
    }

    public function scopeExpiringSoon($query, $days = 60)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>', now());
    }

    public function scopeByNorm($query, $norm)
    {
        return $query->where('norm_reference', 'like', "%{$norm}%");
    }
}
