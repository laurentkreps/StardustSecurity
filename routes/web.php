<?php

// routes/web.php - VERSION COMPLÈTE
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\ElectricalTestController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\PlaygroundController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Routes d'authentification (Breeze/Jetstream)
require __DIR__ . '/auth.php';

Route::get('/home', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // =============================================================================
    // TABLEAU DE BORD PRINCIPAL
    // =============================================================================
    Route::get('/', App\Livewire\MultiNormDashboard::class)->name('dashboard');
    Route::get('/dashboard', App\Livewire\MultiNormDashboard::class)->name('dashboard.multi-norm');

    // =============================================================================
    // GESTION DES INSTALLATIONS
    // =============================================================================
    Route::resource('playgrounds', PlaygroundController::class);

    // =============================================================================
    // GESTION DES ÉQUIPEMENTS
    // =============================================================================
    Route::resource('equipment', EquipmentController::class);

    // =============================================================================
    // ANALYSE DE RISQUES MULTI-NORMES
    // =============================================================================
    Route::get('/playgrounds/{playground}/risk-analysis', App\Livewire\MultiNormRiskWizard::class)
        ->name('risk-analysis.create');
    Route::get('/equipment/{equipment}/risk-analysis', App\Livewire\MultiNormRiskWizard::class)
        ->name('risk-analysis.equipment');

    // =============================================================================
    // INSPECTIONS MANÈGES (EN 13814)
    // =============================================================================
    Route::prefix('inspections')->name('inspections.')->group(function () {
        Route::get('/', [InspectionController::class, 'index'])->name('index');
        Route::get('/create', App\Livewire\AmusementRideInspectionManager::class)->name('create');
        Route::get('/equipment/{equipment}', App\Livewire\AmusementRideInspectionManager::class)->name('create.equipment');
        Route::get('/{inspection}', [InspectionController::class, 'show'])->name('show');
        Route::get('/{inspection}/edit', App\Livewire\AmusementRideInspectionManager::class)->name('edit');
    });

    // =============================================================================
    // TESTS ÉLECTRIQUES (EN 60335)
    // =============================================================================
    Route::prefix('electrical-tests')->name('electrical-tests.')->group(function () {
        Route::get('/', [ElectricalTestController::class, 'index'])->name('index');
        Route::get('/create', App\Livewire\ElectricalTestManager::class)->name('create');
        Route::get('/equipment/{equipment}', App\Livewire\ElectricalTestManager::class)->name('create.equipment');
        Route::get('/{test}', [ElectricalTestController::class, 'show'])->name('show');
        Route::get('/{test}/edit', App\Livewire\ElectricalTestManager::class)->name('edit');
    });

    // =============================================================================
    // GESTION DES CERTIFICATIONS
    // =============================================================================
    Route::resource('certifications', CertificationController::class);

    // =============================================================================
    // RAPPORTS ET EXPORTS
    // =============================================================================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/risk-analysis/{playground}', [ReportController::class, 'riskAnalysisReport'])->name('risk-analysis');
        Route::get('/compliance/{playground}', [ReportController::class, 'complianceReport'])->name('compliance');
        Route::get('/maintenance-schedule', [ReportController::class, 'maintenanceSchedule'])->name('maintenance-schedule');
        Route::get('/electrical-test/{test}', [ReportController::class, 'electricalTestReport'])->name('electrical-test');
        Route::get('/inspection/{inspection}', [ReportController::class, 'inspectionReport'])->name('inspection');
        Route::get('/multi-norm-summary', [ReportController::class, 'multiNormSummary'])->name('multi-norm-summary');
    });

    // =============================================================================
    // PARAMÈTRES UTILISATEUR
    // =============================================================================
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/profile', function () {
            return view('livewire.settings.profile');
        })->name('profile');

        Route::get('/password', function () {
            return view('livewire.settings.password');
        })->name('password');

        Route::get('/appearance', function () {
            return view('livewire.settings.appearance');
        })->name('appearance');

        // Alternative avec des composants Livewire directs
        // Route::get('/profile', App\Livewire\Settings\Profile::class)->name('profile');
        // Route::get('/password', App\Livewire\Settings\Password::class)->name('password');
        // Route::get('/appearance', App\Livewire\Settings\Appearance::class)->name('appearance');
    });

    // =============================================================================
    // API ENDPOINTS POUR INTÉGRATIONS
    // =============================================================================
    Route::prefix('api/v1')->name('api.')->group(function () {
        Route::get('/playgrounds/{playground}/status', [PlaygroundController::class, 'apiStatus'])->name('playground.status');
        Route::get('/equipment/{equipment}/compliance', [EquipmentController::class, 'apiCompliance'])->name('equipment.compliance');
        Route::post('/equipment/{equipment}/quick-test', [ElectricalTestController::class, 'quickTest'])->name('equipment.quick-test');
    });

    // =============================================================================
    // ROUTES D'ADMINISTRATION
    // =============================================================================
    Route::prefix('admin')->name('admin.')->middleware('can:admin')->group(function () {
        Route::get('/danger-categories', function () {
            return view('admin.danger-categories');
        })->name('danger-categories');

        Route::get('/norm-configuration', function () {
            return view('admin.norm-configuration');
        })->name('norm-configuration');

        Route::get('/system-health', function () {
            return view('admin.system-health');
        })->name('system-health');
    });
});

// =============================================================================
// ROUTES PUBLIQUES POUR ORGANISMES DE CONTRÔLE
// =============================================================================
Route::prefix('public')->name('public.')->group(function () {
    Route::get('/playground/{playground}/certificate', [ReportController::class, 'publicCertificate'])
        ->name('certificate');
    Route::get('/equipment/{equipment}/compliance-status', [ReportController::class, 'publicComplianceStatus'])
        ->name('compliance-status');
});

// =============================================================================
// WEBHOOKS POUR INTÉGRATIONS EXTERNES
// =============================================================================
Route::prefix('webhooks')->group(function () {
    Route::post('/weather-alert', function () {
        // Placeholder pour webhook météo
        return response()->json(['status' => 'received']);
    });
    Route::post('/certification-reminder', function () {
        // Placeholder pour rappel certifications
        return response()->json(['status' => 'received']);
    });
});

// =============================================================================
// ROUTE DE FALLBACK
// =============================================================================
Route::fallback(function () {
    return redirect()->route('dashboard');
});
