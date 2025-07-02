<?php

// routes/web.php
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\ElectricalTestController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\PlaygroundController;
use App\Http\Controllers\ReportController;

Route::middleware(['auth'])->group(function () {
    // Tableau de bord principal
    Route::get('/', App\Livewire\MultiNormDashboard::class)->name('dashboard');
    Route::get('/dashboard', App\Livewire\MultiNormDashboard::class)->name('dashboard.multi-norm');

    // Gestion des installations
    Route::resource('playgrounds', PlaygroundController::class);

    // Gestion des équipements
    Route::resource('equipment', EquipmentController::class);

    // Analyse de risques multi-normes
    Route::get('/playgrounds/{playground}/risk-analysis', App\Livewire\MultiNormRiskWizard::class)
        ->name('risk-analysis.create');
    Route::get('/equipment/{equipment}/risk-analysis', App\Livewire\MultiNormRiskWizard::class)
        ->name('risk-analysis.equipment');

    // Inspections manèges (EN 13814)
    Route::prefix('inspections')->name('inspections.')->group(function () {
        Route::get('/', [InspectionController::class, 'index'])->name('index');
        Route::get('/create', App\Livewire\AmusementRideInspectionManager::class)->name('create');
        Route::get('/{inspection}', [InspectionController::class, 'show'])->name('show');
        Route::get('/{inspection}/edit', App\Livewire\AmusementRideInspectionManager::class)->name('edit');
    });

    // Tests électriques (EN 60335)
    Route::prefix('electrical-tests')->name('electrical-tests.')->group(function () {
        Route::get('/', [ElectricalTestController::class, 'index'])->name('index');
        Route::get('/create', App\Livewire\ElectricalTestManager::class)->name('create');
        Route::get('/{test}', [ElectricalTestController::class, 'show'])->name('show');
        Route::get('/{test}/edit', App\Livewire\ElectricalTestManager::class)->name('edit');
    });

    // Gestion des certifications
    Route::resource('certifications', CertificationController::class);

    // Rapports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/risk-analysis/{playground}', [ReportController::class, 'riskAnalysisReport'])->name('risk-analysis');
        Route::get('/compliance/{playground}', [ReportController::class, 'complianceReport'])->name('compliance');
        Route::get('/maintenance-schedule', [ReportController::class, 'maintenanceSchedule'])->name('maintenance-schedule');
        Route::get('/electrical-test/{test}', [ReportController::class, 'electricalTestReport'])->name('electrical-test');
        Route::get('/inspection/{inspection}', [ReportController::class, 'inspectionReport'])->name('inspection');
        Route::get('/multi-norm-summary', [ReportController::class, 'multiNormSummary'])->name('multi-norm-summary');
    });

    // API endpoints pour intégrations
    Route::prefix('api/v1')->name('api.')->group(function () {
        Route::get('/playgrounds/{playground}/status', [PlaygroundController::class, 'apiStatus'])->name('playground.status');
        Route::get('/equipment/{equipment}/compliance', [EquipmentController::class, 'apiCompliance'])->name('equipment.compliance');
        Route::post('/equipment/{equipment}/quick-test', [ElectricalTestController::class, 'quickTest'])->name('equipment.quick-test');
    });

    // Routes d'administration
    Route::prefix('admin')->name('admin.')->middleware('can:admin')->group(function () {
        Route::get('/danger-categories', [AdminController::class, 'dangerCategories'])->name('danger-categories');
        Route::get('/norm-configuration', [AdminController::class, 'normConfiguration'])->name('norm-configuration');
        Route::get('/system-health', [AdminController::class, 'systemHealth'])->name('system-health');
    });
});

// Routes publiques pour organismes de contrôle
Route::prefix('public')->name('public.')->group(function () {
    Route::get('/playground/{playground}/certificate', [ReportController::class, 'publicCertificate'])
        ->name('certificate');
    Route::get('/equipment/{equipment}/compliance-status', [ReportController::class, 'publicComplianceStatus'])
        ->name('compliance-status');
});

// Webhooks pour intégrations externes
Route::prefix('webhooks')->group(function () {
    Route::post('/weather-alert', [WebhookController::class, 'weatherAlert']);
    Route::post('/certification-reminder', [WebhookController::class, 'certificationReminder']);
});
