<?php
// app/Http/Controllers/PlaygroundController.php
namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Playground;
use Illuminate\Http\Request;

class PlaygroundController extends Controller
{
    public function index(Request $request)
    {
        $query = Playground::with(['equipment', 'riskEvaluations']);

        // Filtres
        if ($request->filled('facility_type')) {
            $query->where('facility_type', $request->facility_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('city', 'like', "%{$request->search}%");
            });
        }

        $playgrounds = $query->paginate(20);

        return view('playgrounds.index', compact('playgrounds'));
    }

    public function show(Playground $playground)
    {
        $playground->load([
            'equipment.certifications',
            'equipment.amusementRideInspections' => function ($query) {
                $query->latest()->limit(5);
            },
            'equipment.electricalTests'          => function ($query) {
                $query->latest()->limit(5);
            },
            'riskEvaluations.dangerCategory',
            'maintenanceChecks'                  => function ($query) {
                $query->latest()->limit(10);
            },
            'incidentReports'                    => function ($query) {
                $query->latest()->limit(5);
            },
            'qualifiedOperators',
        ]);

        // Statistiques pour le tableau de bord de l'installation
        $stats = [
            'equipment_count'     => $playground->equipment->count(),
            'active_equipment'    => $playground->equipment->where('status', 'active')->count(),
            'high_risk_count'     => $playground->riskEvaluations->where('risk_category', '>=', 4)->count(),
            'overdue_maintenance' => $playground->maintenanceChecks->where('status', 'overdue')->count(),
            'qualified_operators' => $playground->qualifiedOperators->where('status', 'active')->count(),
        ];

        // Prochaines échéances
        $upcomingTasks = $this->getUpcomingTasks($playground);

        return view('playgrounds.show', compact('playground', 'stats', 'upcomingTasks'));
    }

    public function create()
    {
        return view('playgrounds.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'facility_type'     => 'required|in:playground,amusement_park,fairground,mixed_facility',
            'address'           => 'nullable|string',
            'city'              => 'nullable|string|max:100',
            'postal_code'       => 'nullable|string|max:10',
            'manager_name'      => 'nullable|string|max:255',
            'manager_contact'   => 'nullable|string|max:255',
            'installation_date' => 'nullable|date',
            'capacity'          => 'nullable|integer|min:1',
            'age_range'         => 'nullable|string|max:50',
            'is_fenced'         => 'boolean',
            'has_lighting'      => 'boolean',
            'is_permanent'      => 'boolean',
            'operating_license' => 'nullable|string',
            'license_expiry'    => 'nullable|date|after:today',
        ]);

        $playground = Playground::create($validated);

        return redirect()->route('playgrounds.show', $playground)
            ->with('success', 'Installation créée avec succès !');
    }

    public function edit(Playground $playground)
    {
        return view('playgrounds.edit', compact('playground'));
    }

    public function update(Request $request, Playground $playground)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'facility_type'     => 'required|in:playground,amusement_park,fairground,mixed_facility',
            'address'           => 'nullable|string',
            'city'              => 'nullable|string|max:100',
            'postal_code'       => 'nullable|string|max:10',
            'manager_name'      => 'nullable|string|max:255',
            'manager_contact'   => 'nullable|string|max:255',
            'installation_date' => 'nullable|date',
            'capacity'          => 'nullable|integer|min:1',
            'age_range'         => 'nullable|string|max:50',
            'is_fenced'         => 'boolean',
            'has_lighting'      => 'boolean',
            'is_permanent'      => 'boolean',
            'operating_license' => 'nullable|string',
            'license_expiry'    => 'nullable|date|after:today',
        ]);

        $playground->update($validated);

        return redirect()->route('playgrounds.show', $playground)
            ->with('success', 'Installation mise à jour avec succès !');
    }

    public function destroy(Playground $playground)
    {
        $playground->delete();

        return redirect()->route('playgrounds.index')
            ->with('success', 'Installation supprimée avec succès !');
    }

    private function getUpcomingTasks(Playground $playground)
    {
        $tasks = [];

        // Analyses de risques à renouveler
        if (! $playground->last_analysis_date || $playground->last_analysis_date->addYear()->isPast()) {
            $tasks[] = [
                'type'     => 'risk_analysis',
                'title'    => 'Analyse de risques à renouveler',
                'due_date' => $playground->last_analysis_date?->addYear() ?? now(),
                'priority' => 'medium',
                'url'      => route('risk-analysis.create', $playground),
            ];
        }

        // Maintenances en retard
        $overdueMaintenance = $playground->maintenanceChecks()
            ->where('status', 'overdue')
            ->with('equipment')
            ->get();

        foreach ($overdueMaintenance as $maintenance) {
            $tasks[] = [
                'type'        => 'maintenance',
                'title'       => "Maintenance {$maintenance->check_type}",
                'description' => $maintenance->equipment?->reference_code ?? 'Installation générale',
                'due_date'    => $maintenance->scheduled_date,
                'priority'    => 'high',
                'url'         => route('maintenance.show', $maintenance),
            ];
        }

        // Inspections manèges dues
        $ridesNeedingInspection = $playground->equipment()
            ->where('equipment_category', 'amusement_ride')
            ->whereDoesntHave('amusementRideInspections', function ($q) {
                $q->where('inspection_date', '>=', now()->subDays(30));
            })
            ->get();

        foreach ($ridesNeedingInspection as $ride) {
            $tasks[] = [
                'type'        => 'ride_inspection',
                'title'       => 'Inspection EN 13814 requise',
                'description' => "{$ride->reference_code} - {$ride->equipment_type}",
                'due_date'    => now(),
                'priority'    => 'high',
                'url'         => route('inspections.create', ['equipment' => $ride]),
            ];
        }

        // Tests électriques dus
        $electricalTestsDue = $playground->equipment()
            ->electricalTestDue()
            ->get();

        foreach ($electricalTestsDue as $equipment) {
            $tasks[] = [
                'type'        => 'electrical_test',
                'title'       => 'Test électrique EN 60335 requis',
                'description' => "{$equipment->reference_code} - {$equipment->equipment_type}",
                'due_date'    => $equipment->electrical_test_date?->addYear() ?? now(),
                'priority'    => 'medium',
                'url'         => route('electrical-tests.create', ['equipment' => $equipment]),
            ];
        }

        // Trier par priorité et date
        usort($tasks, function ($a, $b) {
            $priorities   = ['high' => 2, 'medium' => 1, 'low' => 0];
            $priorityDiff = $priorities[$b['priority']] - $priorities[$a['priority']];

            if ($priorityDiff === 0) {
                return $a['due_date']->timestamp - $b['due_date']->timestamp;
            }

            return $priorityDiff;
        });

        return array_slice($tasks, 0, 10);
    }
}
