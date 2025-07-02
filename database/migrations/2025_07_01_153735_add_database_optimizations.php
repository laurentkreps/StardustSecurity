<?php
// database/migrations/2024_01_01_000007_add_database_optimizations.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Créer une vue pour les statistiques des aires de jeux
        DB::statement("
            CREATE VIEW playground_statistics AS
            SELECT
                p.id,
                p.name,
                p.status,
                COUNT(DISTINCT e.id) as equipment_count,
                COUNT(DISTINCT re.id) as risk_evaluations_count,
                COUNT(DISTINCT CASE WHEN re.risk_category >= 4 THEN re.id END) as high_risk_count,
                COUNT(DISTINCT CASE WHEN mc.status = 'overdue' THEN mc.id END) as overdue_checks_count,
                MAX(re.evaluation_date) as last_risk_evaluation_date,
                MAX(mc.completed_date) as last_maintenance_date
            FROM playgrounds p
            LEFT JOIN equipment e ON p.id = e.playground_id AND e.status = 'active'
            LEFT JOIN risk_evaluations re ON p.id = re.playground_id AND re.is_present = TRUE
            LEFT JOIN maintenance_checks mc ON p.id = mc.playground_id
            GROUP BY p.id, p.name, p.status
        ");

        // Créer une vue pour le tableau de bord des risques
        DB::statement("
            CREATE VIEW risk_dashboard AS
            SELECT
                re.risk_category,
                COUNT(*) as count,
                COUNT(CASE WHEN re.measure_status = 'completed' THEN 1 END) as measures_completed,
                COUNT(CASE WHEN re.target_date < CURDATE() AND re.measure_status != 'completed' THEN 1 END) as overdue_measures,
                AVG(re.risk_value) as avg_risk_value
            FROM risk_evaluations re
            WHERE re.is_present = TRUE
            GROUP BY re.risk_category
            ORDER BY re.risk_category DESC
        ");

        // Créer une fonction pour générer automatiquement les numéros d'incident
        DB::statement("
            CREATE FUNCTION generate_incident_number() RETURNS VARCHAR(20)
            READS SQL DATA
            DETERMINISTIC
            BEGIN
                DECLARE next_number INT;
                DECLARE year_part VARCHAR(4);

                SET year_part = YEAR(CURDATE());

                SELECT COALESCE(MAX(CAST(SUBSTRING(incident_number, 6) AS UNSIGNED)), 0) + 1
                INTO next_number
                FROM incident_reports
                WHERE SUBSTRING(incident_number, 1, 4) = year_part;

                RETURN CONCAT(year_part, '-', LPAD(next_number, 4, '0'));
            END
        ");

        // Créer un trigger pour auto-générer les numéros d'incident
        DB::statement("
            CREATE TRIGGER generate_incident_number_trigger
            BEFORE INSERT ON incident_reports
            FOR EACH ROW
            BEGIN
                IF NEW.incident_number IS NULL OR NEW.incident_number = '' THEN
                    SET NEW.incident_number = generate_incident_number();
                END IF;
            END
        ");

        // Créer un trigger pour mettre à jour automatiquement next_check_date
        DB::statement("
            CREATE TRIGGER update_next_check_date
            AFTER INSERT ON maintenance_checks
            FOR EACH ROW
            BEGIN
                IF NEW.check_type = 'regular_verification' AND NEW.completed_date IS NOT NULL THEN
                    UPDATE maintenance_checks
                    SET next_check_date = DATE_ADD(NEW.completed_date, INTERVAL 1 WEEK)
                    WHERE id = NEW.id;
                ELSEIF NEW.check_type = 'maintenance' AND NEW.completed_date IS NOT NULL THEN
                    UPDATE maintenance_checks
                    SET next_check_date = DATE_ADD(NEW.completed_date, INTERVAL 1 MONTH)
                    WHERE id = NEW.id;
                ELSEIF NEW.check_type = 'periodic_control' AND NEW.completed_date IS NOT NULL THEN
                    UPDATE maintenance_checks
                    SET next_check_date = DATE_ADD(NEW.completed_date, INTERVAL 1 YEAR)
                    WHERE id = NEW.id;
                END IF;
            END
        ");

        // Ajouter des index composites supplémentaires pour optimiser les requêtes fréquentes
        DB::statement("CREATE INDEX idx_risk_category_date ON risk_evaluations(risk_category, evaluation_date)");
        DB::statement("CREATE INDEX idx_playground_status_date ON playgrounds(status, last_analysis_date)");
        DB::statement("CREATE INDEX idx_maintenance_type_status ON maintenance_checks(check_type, status, scheduled_date)");
        DB::statement("CREATE INDEX idx_incident_severity_date ON incident_reports(severity, incident_date)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les triggers
        DB::statement("DROP TRIGGER IF EXISTS generate_incident_number_trigger");
        DB::statement("DROP TRIGGER IF EXISTS update_next_check_date");

        // Supprimer la fonction
        DB::statement("DROP FUNCTION IF EXISTS generate_incident_number");

        // Supprimer les vues
        DB::statement("DROP VIEW IF EXISTS risk_dashboard");
        DB::statement("DROP VIEW IF EXISTS playground_statistics");

        // Supprimer les index
        DB::statement("DROP INDEX IF EXISTS idx_risk_category_date ON risk_evaluations");
        DB::statement("DROP INDEX IF EXISTS idx_playground_status_date ON playgrounds");
        DB::statement("DROP INDEX IF EXISTS idx_maintenance_type_status ON maintenance_checks");
        DB::statement("DROP INDEX IF EXISTS idx_incident_severity_date ON incident_reports");
    }
};
