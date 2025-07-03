<?php
// =============================================================================
// MIGRATION SIMPLIFIÉE 3: Specialized norm tables (OPTIONNELLE)
// =============================================================================

// OPTION 1: Garder seulement la table equipment_certifications (simplifiée)
// Remplacer le contenu de : 2025_07_01_153743_create_specialized_norm_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table simplifiée pour les certifications uniquement
        Schema::create('equipment_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->onDelete('cascade');
            $table->enum('certification_type', [
                'ce_marking',             // Marquage CE
                'declaration_conformity', // Déclaration de conformité
                'type_examination',       // Examen de type
                'electrical_safety',      // Sécurité électrique
                'installation_approval',  // Agrément d'installation
                'operational_permit',     // Permis d'exploitation
                'other',                  // Autre
            ])->comment('Type de certification');
            $table->string('norm_reference')->comment('Référence norme (EN 1176, EN 13814, etc.)');
            $table->string('certificate_number')->nullable()->comment('Numéro de certificat');
            $table->string('issuing_body')->comment('Organisme émetteur');
            $table->date('issue_date')->comment('Date d\'émission');
            $table->date('expiry_date')->nullable()->comment('Date d\'expiration');
            $table->enum('status', ['valid', 'expired', 'suspended', 'revoked'])->default('valid');
            $table->text('scope')->nullable()->comment('Domaine d\'application');
            $table->text('restrictions')->nullable()->comment('Restrictions d\'usage');
            $table->string('document_path')->nullable()->comment('Chemin vers le document');
            $table->text('notes')->nullable()->comment('Notes complémentaires');
            $table->timestamps();

            $table->index(['equipment_id', 'certification_type']);
            $table->index(['norm_reference', 'status']);
            $table->index('expiry_date');
        });

        // Table simplifiée pour les opérateurs qualifiés
        Schema::create('qualified_operators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playground_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->string('employee_id')->nullable()->comment('Numéro employé');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('equipment_qualifications')->nullable()->comment('Équipements qualifiés (JSON as text)');
            $table->text('certifications')->nullable()->comment('Certifications détenues (JSON as text)');
            $table->date('training_completion_date')->nullable();
            $table->date('certification_expiry')->nullable();
            $table->boolean('medical_fitness_valid')->default(true)->comment('Aptitude médicale valide');
            $table->date('medical_check_date')->nullable();
            $table->date('medical_check_expiry')->nullable();
            $table->integer('years_experience')->nullable()->comment('Années d\'expérience');
            $table->text('previous_experience')->nullable();
            $table->enum('status', [
                'active',    // Actif
                'inactive',  // Inactif
                'suspended', // Suspendu
                'training',  // En formation
            ])->default('active');
            $table->date('employment_start')->nullable();
            $table->date('employment_end')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['playground_id', 'status']);
            $table->index('certification_expiry');
            $table->index('medical_check_expiry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qualified_operators');
        Schema::dropIfExists('equipment_certifications');
    }
};
