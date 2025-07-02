<?php

// app/Console/Commands/CheckElectricalTestsCommand.php
namespace App\Console\Commands;

use App\Models\Equipment;
use App\Models\User;
use App\Notifications\ElectricalTestNotification;
use Illuminate\Console\Command;

class CheckElectricalTestsCommand extends Command
{
    protected $signature   = 'electrical:check-tests';
    protected $description = 'Vérifier les tests électriques EN 60335 dus et envoyer des notifications';

    public function handle()
    {
        $this->info('Vérification des tests électriques EN 60335 dus...');

        // Équipements électriques nécessitant un test
        $equipmentNeedingTests = Equipment::where('equipment_category', 'electrical_system')
            ->where(function ($query) {
                $query->whereNull('electrical_test_date')
                    ->orWhere('electrical_test_date', '<', now()->subYear());
            })
            ->with('playground')
            ->get();

        $this->info("Trouvé {$equipmentNeedingTests->count()} équipements nécessitant un test électrique.");

        // Équipements avec tests récents mais non conformes
        $unsafeEquipment = Equipment::where('equipment_category', 'electrical_system')
            ->whereHas('electricalTests', function ($query) {
                $query->where('safe_to_use', false)
                    ->where('test_date', '>=', now()->subMonth());
            })
            ->with('playground')
            ->get();

        $this->info("Trouvé {$unsafeEquipment->count()} équipements électriques non sûrs.");

        // Notifications pour tests dus
        foreach ($equipmentNeedingTests as $equipment) {
            $users = User::where('role', 'electrical_manager')
                ->orWhere('playground_id', $equipment->playground_id)
                ->get();

            foreach ($users as $user) {
                $user->notify(new ElectricalTestNotification($equipment, 'test_due'));
            }
        }

        // Notifications pour équipements non sûrs
        foreach ($unsafeEquipment as $equipment) {
            $users = User::where('role', 'safety_manager')
                ->orWhere('playground_id', $equipment->playground_id)
                ->get();

            foreach ($users as $user) {
                $user->notify(new ElectricalTestNotification($equipment, 'unsafe_equipment'));
            }
        }

        return 0;
    }
}
