<?php
// app/Models/EquipmentCertification.php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentCertification extends Model
{
    protected $fillable = [
        'equipment_id', 'certification_type', 'norm_reference', 'certificate_number',
        'issuing_body', 'issue_date', 'expiry_date', 'status', 'scope',
        'restrictions', 'document_path', 'technical_data',
    ];

    protected $casts = [
        'issue_date'     => 'date',
        'expiry_date'    => 'date',
        'technical_data' => 'array',
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
                'production_quality'     => 'Assurance qualité production',
                'electrical_safety'      => 'Sécurité électrique',
                'structural_calculation' => 'Calculs de structure',
                'installation_approval'  => 'Agrément d\'installation',
                'operational_permit'     => 'Permis d\'exploitation',
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
