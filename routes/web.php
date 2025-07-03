<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\ElectricalTestController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\PlaygroundController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route d'accueil - redirige selon l'état d'authentification
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard.multi-norm');
    }
    return view('welcome');
})->name('home');

// Route welcome pour les visiteurs non connectés
Route::view('/welcome', 'welcome')->name('welcome');

// Routes d'authentification Laravel Breeze/Volt
Route::middleware('guest')->group(function () {
    Volt::route('register', 'auth.register')
        ->name('register');

    Volt::route('login', 'auth.login')
        ->name('login');

    Volt::route('forgot-password', 'auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'auth.reset-password')
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'auth.verify-email')
        ->middleware('throttle:6,1')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'auth.confirm-password')
        ->name('password.confirm');
});

// Routes d'authentification avec état connecté
Route::middleware('auth')->group(function () {
    // Settings/Profile routes
    Volt::route('settings/profile', 'settings.profile')
        ->name('profile.edit');

    Volt::route('settings/password', 'settings.password')
        ->name('password.edit');

    Volt::route('settings/appearance', 'settings.appearance')
        ->name('appearance.edit');

    // Logout
    Route::post('logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});

// Routes protégées par authentification
Route::middleware(['auth', 'verified'])->group(function () {

    // Tableau de bord principal - redirection vers dashboard multi-normes
    Route::get('/dashboard', function () {
        return redirect()->route('dashboard.multi-norm');
    })->name('dashboard');

    // Tableau de bord multi-normes (page d'accueil principale)
    Route::get('/multi-norm-dashboard', App\Livewire\MultiNormDashboard::class)
        ->name('dashboard.multi-norm');

    // Gestion des installations
    Route::resource('playgrounds', PlaygroundController::class)->names([
        'index'   => 'playgrounds.index',
        'create'  => 'playgrounds.create',
        'store'   => 'playgrounds.store',
        'show'    => 'playgrounds.show',
        'edit'    => 'playgrounds.edit',
        'update'  => 'playgrounds.update',
        'destroy' => 'playgrounds.destroy',
    ]);

    // Gestion des équipements
    Route::resource('equipment', EquipmentController::class)->names([
        'index'   => 'equipment.index',
        'create'  => 'equipment.create',
        'store'   => 'equipment.store',
        'show'    => 'equipment.show',
        'edit'    => 'equipment.edit',
        'update'  => 'equipment.update',
        'destroy' => 'equipment.destroy',
    ]);

    // Analyse de risques multi-normes avec Livewire
    Route::get('/playgrounds/{playground}/risk-analysis', App\Livewire\MultiNormRiskWizard::class)
        ->name('risk-analysis.create');
    Route::get('/equipment/{equipment}/risk-analysis', App\Livewire\MultiNormRiskWizard::class)
        ->name('risk-analysis.equipment');

    // Inspections manèges (EN 13814) avec Livewire
    Route::prefix('inspections')->name('inspections.')->group(function () {
        Route::get('/', [InspectionController::class, 'index'])->name('index');

        // Nouvelles inspections
        Route::get('/create', App\Livewire\AmusementRideInspectionManager::class)->name('create');
        Route::get('/equipment/{equipment}/create', App\Livewire\AmusementRideInspectionManager::class)->name('create.equipment');
        Route::get('/playground/{playground}/create', App\Livewire\AmusementRideInspectionManager::class)->name('create.playground');

        // Affichage et modification
        Route::get('/{inspection}', [InspectionController::class, 'show'])->name('show');
        Route::get('/{inspection}/edit', App\Livewire\AmusementRideInspectionManager::class)->name('edit');
    });

    // Tests électriques (EN 60335) avec Livewire
    Route::prefix('electrical-tests')->name('electrical-tests.')->group(function () {
        Route::get('/', [ElectricalTestController::class, 'index'])->name('index');

        // Nouveaux tests
        Route::get('/create', App\Livewire\ElectricalTestManager::class)->name('create');
        Route::get('/equipment/{equipment}/create', App\Livewire\ElectricalTestManager::class)->name('create.equipment');

        // Affichage et modification
        Route::get('/{test}', [ElectricalTestController::class, 'show'])->name('show');
        Route::get('/{test}/edit', App\Livewire\ElectricalTestManager::class)->name('edit');
    });

    // Gestion des certifications
    Route::resource('certifications', CertificationController::class)->except(['show'])->names([
        'index'   => 'certifications.index',
        'create'  => 'certifications.create',
        'store'   => 'certifications.store',
        'edit'    => 'certifications.edit',
        'update'  => 'certifications.update',
        'destroy' => 'certifications.destroy',
    ]);

    // Gestion de la maintenance
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', [MaintenanceController::class, 'index'])->name('index');
        Route::get('/create', [MaintenanceController::class, 'create'])->name('create');
        Route::post('/', [MaintenanceController::class, 'store'])->name('store');
        Route::get('/{maintenance}', [MaintenanceController::class, 'show'])->name('show');
        Route::get('/{maintenance}/edit', [MaintenanceController::class, 'edit'])->name('edit');
        Route::put('/{maintenance}', [MaintenanceController::class, 'update'])->name('update');
        Route::delete('/{maintenance}', [MaintenanceController::class, 'destroy'])->name('destroy');
    });

    // Gestion des incidents
    Route::prefix('incidents')->name('incidents.')->group(function () {
        Route::get('/', [IncidentController::class, 'index'])->name('index');
        Route::get('/create', [IncidentController::class, 'create'])->name('create');
        Route::post('/', [IncidentController::class, 'store'])->name('store');
        Route::get('/{incident}', [IncidentController::class, 'show'])->name('show');
        Route::get('/{incident}/edit', [IncidentController::class, 'edit'])->name('edit');
        Route::put('/{incident}', [IncidentController::class, 'update'])->name('update');
        Route::delete('/{incident}', [IncidentController::class, 'destroy'])->name('destroy');
    });

    // Rapports et exports
    Route::prefix('reports')->name('reports.')->group(function () {
        // Rapports par installation
        Route::get('/playground/{playground}/risk-analysis', [ReportController::class, 'riskAnalysisReport'])
            ->name('risk-analysis');
        Route::get('/playground/{playground}/compliance', [ReportController::class, 'complianceReport'])
            ->name('compliance');

        // Rapports par équipement/test/inspection
        Route::get('/electrical-test/{test}', [ReportController::class, 'electricalTestReport'])
            ->name('electrical-test');
        Route::get('/inspection/{inspection}', [ReportController::class, 'inspectionReport'])
            ->name('inspection');

        // Rapports globaux
        Route::get('/maintenance-schedule', [ReportController::class, 'maintenanceSchedule'])
            ->name('maintenance-schedule');
        Route::get('/multi-norm-summary', [ReportController::class, 'multiNormSummary'])
            ->name('multi-norm-summary');

        // Rapports de synthèse
        Route::get('/compliance-overview', [ReportController::class, 'complianceOverview'])
            ->name('compliance-overview');
        Route::get('/risk-trends', [ReportController::class, 'riskTrends'])
            ->name('risk-trends');
    });

    // API endpoints pour intégrations AJAX/JavaScript
    Route::prefix('api/v1')->name('api.')->group(function () {
        // Statuts et données temps réel
        Route::get('/playgrounds/{playground}/status', [PlaygroundController::class, 'apiStatus'])
            ->name('playground.status');
        Route::get('/equipment/{equipment}/compliance', [EquipmentController::class, 'apiCompliance'])
            ->name('equipment.compliance');
        Route::get('/dashboard/stats', [PlaygroundController::class, 'apiDashboardStats'])
            ->name('dashboard.stats');

        // Actions rapides
        Route::post('/equipment/{equipment}/quick-test', [ElectricalTestController::class, 'quickTest'])
            ->name('equipment.quick-test');
        Route::post('/maintenance/{maintenance}/complete', [MaintenanceController::class, 'complete'])
            ->name('maintenance.complete');

        // Données pour widgets
        Route::get('/notifications/unread', [NotificationController::class, 'unread'])
            ->name('notifications.unread');
        Route::get('/tasks/upcoming', [TaskController::class, 'upcoming'])
            ->name('tasks.upcoming');
    });

    // Routes d'administration (avec middleware de permission)
    Route::prefix('admin')->name('admin.')->middleware('can:admin')->group(function () {
        Route::get('/danger-categories', [AdminController::class, 'dangerCategories'])
            ->name('danger-categories');
        Route::get('/norm-configuration', [AdminController::class, 'normConfiguration'])
            ->name('norm-configuration');
        Route::get('/system-health', [AdminController::class, 'systemHealth'])
            ->name('system-health');
        Route::get('/user-management', [AdminController::class, 'userManagement'])
            ->name('user-management');
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])
            ->name('audit-logs');
    });
});

// Routes publiques pour organismes de contrôle (sans authentification)
Route::prefix('public')->name('public.')->group(function () {
    // Certificats publics avec token de sécurité
    Route::get('/playground/{playground}/certificate/{token}', [ReportController::class, 'publicCertificate'])
        ->name('certificate');
    Route::get('/equipment/{equipment}/compliance-status/{token}', [ReportController::class, 'publicComplianceStatus'])
        ->name('compliance-status');

    // API publique pour intégrations externes
    Route::get('/playground/{playground}/basic-info', [PlaygroundController::class, 'publicBasicInfo'])
        ->name('playground.basic-info');
});

// Webhooks pour intégrations externes (sans authentification mais avec validation)
Route::prefix('webhooks')->middleware('verify.webhook')->group(function () {
    Route::post('/weather-alert', [WebhookController::class, 'weatherAlert'])
        ->name('webhooks.weather-alert');
    Route::post('/certification-reminder', [WebhookController::class, 'certificationReminder'])
        ->name('webhooks.certification-reminder');
    Route::post('/maintenance-schedule', [WebhookController::class, 'maintenanceSchedule'])
        ->name('webhooks.maintenance-schedule');
});

// Routes de développement (uniquement en mode local)
if (app()->environment('local')) {
    Route::prefix('dev')->name('dev.')->group(function () {
        Route::get('/test-notifications', [DevController::class, 'testNotifications'])
            ->name('test-notifications');
        Route::get('/generate-sample-data', [DevController::class, 'generateSampleData'])
            ->name('generate-sample-data');
        Route::get('/clear-cache', [DevController::class, 'clearCache'])
            ->name('clear-cache');
    });
}

// Fallback route pour les erreurs 404
Route::fallback(function () {
    return view('errors.404');
});
