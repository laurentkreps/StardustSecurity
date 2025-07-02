<?php
// app/Http/Controllers/EquipmentController.php
namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Playground;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Equipment::with(['playground', 'certifications', 'riskEvaluations']);

        // Filtres
        if ($request->filled('playground_id')) {
            $query->where('playground_id', $request->playground_id);
        }

        if ($request->filled('equipment_category')) {
            $query->where('equipment_category', $request->equipment_category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reference_code', 'like', "%{$request->search}%")
                    ->orWhere('equipment_type', 'like', "%{$request->search}%")
                    ->orWhere('brand', 'like', "%{$request->search}%");
            });
        }

        $equipment   = $query->paginate(20);
        $playgrounds = Playground::all();

        return view('equipment.index', compact('equipment', 'playgrounds'));
    }

    public function show(Equipment $equipment)
    {
        $equipment->load([
            'playground',
            'certifications',
            'riskEvaluations.dangerCategory',
            'maintenanceChecks'        => function ($query) {
                $query->latest();
            },
            'amusementRideInspections' => function ($query) {
                $query->latest();
            },
            'electricalTests'          => function ($query) {
                $query->latest();
            },
            'technicalData',
        ]);

        // Statut de conformité
        $complianceStatus = $this->calculateEquipmentCompliance($equipment);

        // Prochaines échéances
        $upcomingActions = $this->getUpcomingEquipmentActions($equipment);

        return view('equipment.show', compact('equipment', 'complianceStatus', 'upcomingActions'));
    }

    public function create(Request $request)
    {
        $playground = null;
        if ($request->filled('playground_id')) {
            $playground = Playground::findOrFail($request->playground_id);
        }

        $playgrounds = Playground::all();

        return view('equipment.create', compact('playground', 'playgrounds'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'playground_id'        => 'required|exists:playgrounds,id',
            'reference_code'       => 'required|string|max:50',
            'equipment_category'   => 'required|in:playground_equipment,amusement_ride,electrical_system,infrastructure,safety_equipment',
            'equipment_type'       => 'required|string|max:255',
            'brand'                => 'nullable|string|max:255',
            'manufacturer_details' => 'nullable|string',
            'supplier_details'     => 'nullable|string',
            'applicable_norms'     => 'nullable|array',
            'purchase_date'        => 'nullable|date',
            'installation_date'    => 'nullable|date',
            'height'               => 'nullable|numeric|min:0',
            'max_speed'            => 'nullable|numeric|min:0',
            'max_acceleration'     => 'nullable|numeric|min:0',
            'max_passengers'       => 'nullable|integer|min:0',
            'voltage'              => 'nullable|numeric|min:0',
            'current'              => 'nullable|numeric|min:0',
            'protection_class'     => 'nullable|string',
            'ip_rating'            => 'nullable|string',
        ]);

        // Vérifier l'unicité du code de référence par installation
        $existingEquipment = Equipment::where('playground_id', $validated['playground_id'])
            ->where('reference_code', $validated['reference_code'])
            ->exists();

        if ($existingEquipment) {
            return back()->withErrors(['reference_code' => 'Ce code de référence existe déjà pour cette installation.']);
        }

        $equipment = Equipment::create($validated);

        return redirect()->route('equipment.show', $equipment)
            ->with('success', 'Équipement créé avec succès !');
    }

    public function edit(Equipment $equipment)
    {
        $playgrounds = Playground::all();

        return view('equipment.edit', compact('equipment', 'playgrounds'));
    }

    public function update(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'playground_id'        => 'required|exists:playgrounds,id',
            'reference_code'       => 'required|string|max:50',
            'equipment_category'   => 'required|in:playground_equipment,amusement_ride,electrical_system,infrastructure,safety_equipment',
            'equipment_type'       => 'required|string|max:255',
            'brand'                => 'nullable|string|max:255',
            'manufacturer_details' => 'nullable|string',
            'supplier_details'     => 'nullable|string',
            'applicable_norms'     => 'nullable|array',
            'purchase_date'        => 'nullable|date',
            'installation_date'    => 'nullable|date',
            'height'               => 'nullable|numeric|min:0',
            'max_speed'            => 'nullable|numeric|min:0',
            'max_acceleration'     => 'nullable|numeric|min:0',
            'max_passengers'       => 'nullable|integer|min:0',
            'voltage'              => 'nullable|numeric|min:0',
            'current'              => 'nullable|numeric|min:0',
            'protection_class'     => 'nullable|string',
            'ip_rating'            => 'nullable|string',
        ]);

        // Vérifier l'unicité du code de référence (excluant l'équipement actuel)
        $existingEquipment = Equipment::where('playground_id', $validated['playground_id'])
            ->where('reference_code', $validated['reference_code'])
            ->where('id', '!=', $equipment->id)
            ->exists();

        if ($existingEquipment) {
            return back()->withErrors(['reference_code' => 'Ce code de référence existe déjà pour cette installation.']);
        }

        $equipment->update($validated);

        return redirect()->route('equipment.show', $equipment)
            ->with('success', 'Équipement mis à jour avec succès !');
    }

    public function destroy(Equipment $equipment)
    {
        $equipment->delete();

        return redirect()->route('equipment.index')
            ->with('success', 'Équipement supprimé avec succès !');
    }

    private function calculateEquipmentCompliance(Equipment $equipment)
    {
        $compliance = [
            'overall'          => true,
            'certifications'   => false,
            'inspections'      => false,
            'electrical_tests' => false,
            'risk_assessment'  => false,
            'issues'           => [],
        ];

        // Vérifier les certifications
        $validCertifications          = $equipment->certifications()->valid()->count();
        $compliance['certifications'] = $validCertifications > 0;

        if (! $compliance['certifications']) {
            $compliance['issues'][] = 'Aucune certification valide';
            $compliance['overall']  = false;
        }

        // Vérifier les inspections (pour manèges)
        if ($equipment->equipment_category === 'amusement_ride') {
            $recentInspection = $equipment->amusementRideInspections()
                ->where('inspection_date', '>=', now()->subMonth())
                ->where('operation_authorized', true)
                ->exists();

            $compliance['inspections'] = $recentInspection;

            if (! $compliance['inspections']) {
                $compliance['issues'][] = 'Inspection EN 13814 requise';
                $compliance['overall']  = false;
            }
        }

        // Vérifier les tests électriques
        if ($equipment->equipment_category === 'electrical_system') {
            $recentElectricalTest = $equipment->electricalTests()
                ->where('test_date', '>=', now()->subYear())
                ->where('safe_to_use', true)
                ->exists();

            $compliance['electrical_tests'] = $recentElectricalTest;

            if (! $compliance['electrical_tests']) {
                $compliance['issues'][] = 'Test électrique EN 60335 requis';
                $compliance['overall']  = false;
            }
        }

        // Vérifier l'évaluation des risques
        $highRisks = $equipment->riskEvaluations()
            ->where('is_present', true)
            ->where('risk_category', '>=', 4)
            ->count();

        $compliance['risk_assessment'] = $highRisks === 0;

        if (! $compliance['risk_assessment']) {
            $compliance['issues'][] = "Risques élevés identifiés ({$highRisks})";
            $compliance['overall']  = false;
        }

        return $compliance;
    }

    private function getUpcomingEquipmentActions(Equipment $equipment)
    {
        $actions = [];

        // Inspections manèges
        if ($equipment->equipment_category === 'amusement_ride') {
            $lastInspection = $equipment->amusementRideInspections()
                ->latest('inspection_date')
                ->first();

            if (! $lastInspection || $lastInspection->inspection_date->addMonth()->isPast()) {
                $actions[] = [
                    'type'     => 'inspection',
                    'title'    => 'Inspection EN 13814',
                    'due_date' => $lastInspection?->next_inspection_date ?? now(),
                    'priority' => 'high',
                    'url'      => route('inspections.create', ['equipment' => $equipment]),
                ];
            }
        }

        // Tests électriques
        if ($equipment->equipment_category === 'electrical_system') {
            if ($equipment->is_electrical_test_due) {
                $actions[] = [
                    'type'     => 'electrical_test',
                    'title'    => 'Test électrique EN 60335',
                    'due_date' => $equipment->electrical_test_date?->addYear() ?? now(),
                    'priority' => 'medium',
                    'url'      => route('electrical-tests.create', ['equipment' => $equipment]),
                ];
            }
        }

        // Certifications expirantes
        $expiringCerts = $equipment->certifications()
            ->expiringSoon(60)
            ->get();

        foreach ($expiringCerts as $cert) {
            $actions[] = [
                'type'     => 'certification',
                'title'    => "Renouvellement {$cert->certification_type_label}",
                'due_date' => $cert->expiry_date,
                'priority' => 'medium',
                'url'      => route('certifications.edit', $cert),
            ];
        }

        return $actions;
    }
}
