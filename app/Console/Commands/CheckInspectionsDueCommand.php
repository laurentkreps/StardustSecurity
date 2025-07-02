<?php

// app/Console/Commands/CheckInspectionsDueCommand.php
namespace App\Console\Commands;

use App\Models\Equipment;
use App\Models\User;
use App\Notifications\InspectionNotification;
use Illuminate\Console\Command;

class CheckInspectionsDueCommand extends Command
{
    protected $signature   = 'inspections:check-due';
    protected $description = 'Vérifier les inspections EN 13814 dues et envoyer des notifications';

    public function handle()
    {
        $this->info('Vérification des inspections EN 13814 dues...');

        // Manèges nécessitant une inspection périodique (mensuelle)
        $ridesNeedingPeriodicInspection = Equipment::where('equipment_category', 'amusement_ride')
            ->whereDoesntHave('amusementRideInspections', function ($query) {
                $query->where('inspection_type', 'periodic_inspection')
                    ->where('inspection_date', '>=', now()->subMonth());
            })
            ->with('playground')
            ->get();

        $this->info("Trouvé {$ridesNeedingPeriodicInspection->count()} manèges nécessitant une inspection périodique.");

        // Manèges nécessitant un contrôle quotidien
        $ridesNeedingDailyCheck = Equipment::where('equipment_category', 'amusement_ride')
            ->whereDoesntHave('amusementRideInspections', function ($query) {
                $query->where('inspection_type', 'daily_check')
                    ->where('inspection_date', '>=', now()->subDay());
            })
            ->with('playground')
            ->get();

        $this->info("Trouvé {$ridesNeedingDailyCheck->count()} manèges nécessitant un contrôle quotidien.");

        // Envoyer notifications pour inspections périodiques
        foreach ($ridesNeedingPeriodicInspection as $equipment) {
            $users = User::where('role', 'inspection_manager')
                ->orWhere('playground_id', $equipment->playground_id)
                ->get();

            foreach ($users as $user) {
                $user->notify(new InspectionNotification($equipment, 'inspection_due'));
            }
        }

        // Envoyer notifications pour contrôles quotidiens manqués
        foreach ($ridesNeedingDailyCheck as $equipment) {
            $users = User::where('playground_id', $equipment->playground_id)->get();

            foreach ($users as $user) {
                $user->notify(new InspectionNotification($equipment, 'daily_check_missing'));
            }
        }

        return 0;
    }
}
