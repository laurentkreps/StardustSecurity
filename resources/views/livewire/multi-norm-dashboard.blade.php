{{-- resources/views/livewire/multi-norm-dashboard.blade.php --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- En-tête du tableau de bord -->
    <div class="mb-8">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Tableau de Bord Multi-Normes</h1>
                <p class="text-gray-600 mt-1">Supervision globale de la conformité EN 1176, EN 1177, EN 13814 et EN 60335</p>
            </div>

            <!-- Filtres -->
            <div class="flex gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Installation</label>
                    <select wire:model.live="selectedPlayground" class="rounded-md border-gray-300 text-sm">
                        <option value="">Toutes les installations</option>
                        @foreach($playgrounds as $playground)
                            <option value="{{ $playground->id }}">{{ $playground->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Norme</label>
                    <select wire:model.live="selectedNorm" class="rounded-md border-gray-300 text-sm">
                        <option value="all">Toutes les normes</option>
                        <option value="EN 1176">EN 1176</option>
                        <option value="EN 1177">EN 1177</option>
                        <option value="EN 13814">EN 13814</option>
                        <option value="EN 60335">EN 60335</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Période</label>
                    <select wire:model.live="selectedTimeframe" class="rounded-md border-gray-300 text-sm">
                        <option value="7">7 derniers jours</option>
                        <option value="30">30 derniers jours</option>
                        <option value="90">3 derniers mois</option>
                        <option value="365">Dernière année</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertes critiques -->
    @if(count($criticalAlerts) > 0)
        <div class="mb-8 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center mb-3">
                <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <h3 class="text-lg font-medium text-red-900">Alertes critiques ({{ count($criticalAlerts) }})</h3>
            </div>
            <div class="space-y-2">
                @foreach($criticalAlerts as $alert)
                    <div class="flex justify-between items-center bg-white p-3 rounded border">
                        <div>
                            <div class="font-medium text-red-900">{{ $alert['title'] }}</div>
                            <div class="text-sm text-red-700">{{ $alert['description'] }}</div>
                        </div>
                        <a href="{{ $alert['action_url'] }}"
                           class="text-red-600 hover:text-red-800 text-sm font-medium">
                            Action requise →
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Statistiques principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Installations</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_playgrounds'] }}</p>
                    <p class="text-xs text-green-600">{{ $stats['active_playgrounds'] }} actives</p>
                </div>
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Équipements</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_equipment'] }}</p>
                    <p class="text-xs text-green-600">{{ $stats['active_equipment'] }} en service</p>
                </div>
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Risques identifiés</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_risks'] }}</p>
                    <p class="text-xs {{ $stats['high_risks'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $stats['high_risks'] }} élevés, {{ $stats['critical_risks'] }} critiques
                    </p>
                </div>
                <svg class="w-8 h-8 {{ $stats['high_risks'] > 0 ? 'text-red-500' : 'text-yellow-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Conformité globale</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['compliance_rate'] }}%</p>
                    <p class="text-xs {{ $stats['overdue_maintenance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $stats['overdue_maintenance'] }} maintenances en retard
                    </p>
                </div>
                <svg class="w-8 h-8 {{ $stats['compliance_rate'] >= 80 ? 'text-green-500' : 'text-orange-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Statistiques par norme -->
    <div class="bg-white rounded-lg shadow-sm border mb-8">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Conformité par norme européenne</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                @foreach($normStats as $norm => $stats)
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h4 class="font-medium text-gray-900">{{ $norm }}</h4>
                                <p class="text-sm text-gray-600">
                                    @switch($norm)
                                        @case('EN 1176')
                                            Équipements d'aires de jeux
                                            @break
                                        @case('EN 1177')
                                            Sols amortissants
                                            @break
                                        @case('EN 13814')
                                            Manèges et attractions
                                            @break
                                        @case('EN 60335')
                                            Sécurité électrique
                                            @break
                                    @endswitch
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold {{ $stats['compliance_rate'] >= 80 ? 'text-green-600' : ($stats['compliance_rate'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $stats['compliance_rate'] }}%
                                </div>
                                <div class="text-xs text-gray-500">conformité</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <div class="text-gray-600">Installations concernées</div>
                                <div class="font-medium">{{ $stats['applicable_facilities'] }}</div>
                            </div>
                            <div>
                                <div class="text-gray-600">Conformes</div>
                                <div class="font-medium text-green-600">{{ $stats['compliant_facilities'] }}</div>
                            </div>
                            <div>
                                <div class="text-gray-600">Équipements</div>
                                <div class="font-medium">{{ $stats['total_equipment'] }}</div>
                            </div>
                            <div>
                                <div class="text-gray-600">Risques élevés</div>
                                <div class="font-medium {{ $stats['high_risk_count'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $stats['high_risk_count'] }}
                                </div>
                            </div>
                        </div>

                        <!-- Barre de progression -->
                        <div class="mt-4">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $stats['compliance_rate'] >= 80 ? 'bg-green-500' : ($stats['compliance_rate'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                     style="width: {{ $stats['compliance_rate'] }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Tâches à venir -->
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Tâches prioritaires</h3>
            </div>
            <div class="max-h-96 overflow-y-auto">
                @if(count($upcomingTasks) > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($upcomingTasks as $task)
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 mt-1">
                                            <div class="w-2 h-2 rounded-full
                                                {{ $task['priority'] === 'high' ? 'bg-red-500' :
                                                   ($task['priority'] === 'medium' ? 'bg-yellow-500' : 'bg-blue-500') }}">
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">{{ $task['title'] }}</p>
                                            <p class="text-xs text-gray-600">{{ $task['description'] }}</p>
                                            <p class="text-xs text-gray-400 mt-1">
                                                Échéance: {{ $task['due_date']->format('d/m/Y') }}
                                                @if($task['due_date']->isPast())
                                                    <span class="text-red-600 font-medium">- En retard</span>
                                                @elseif($task['due_date']->isToday())
                                                    <span class="text-orange-600 font-medium">- Aujourd'hui</span>
                                                @elseif($task['due_date']->isTomorrow())
                                                    <span class="text-yellow-600 font-medium">- Demain</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <a href="{{ $task['url'] }}"
                                       class="text-blue-600 hover:text-blue-800 text-xs font-medium ml-2">
                                        Action →
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>Aucune tâche urgente</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Activité récente -->
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Activité récente</h3>
            </div>
            <div class="max-h-96 overflow-y-auto">
                @if(count($recentActivity) > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($recentActivity as $activity)
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-1">
                                        @switch($activity['type'])
                                            @case('risk_analysis')
                                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                                @break
                                            @case('inspection')
                                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                                @break
                                            @case('electrical_test')
                                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M11 3a1 1 0 10-2 0v1a1 1 0 10-2 0v1a1 1 0 10-2 0v1H3a3 3 0 00-3 3v5a3 3 0 003 3h14a3 3 0 003-3V9a3 3 0 00-3-3h-2V5a1 1 0 10-2 0v1a1 1 0 10-2 0V4a1 1 0 10-2 0v1a1 1 0 10-2 0V3z"/>
                                                    </svg>
                                                </div>
                                                @break
                                            @default
                                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                        @endswitch
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 {{ $activity['severity'] === 'high' ? 'text-red-900' : '' }}">
                                            {{ $activity['title'] }}
                                        </p>
                                        <p class="text-xs text-gray-600">{{ $activity['description'] }}</p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            {{ $activity['date']->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                    <a href="{{ $activity['url'] }}"
                                       class="text-blue-600 hover:text-blue-800 text-xs">
                                        →
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>Aucune activité récente</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="mt-8 bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions rapides</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('playgrounds.create') }}"
               class="flex items-center justify-center p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <div class="text-sm font-medium text-gray-900">Nouvelle installation</div>
                </div>
            </a>

            <a href="{{ route('reports.multi-norm-summary') }}"
               class="flex items-center justify-center p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <div class="text-sm font-medium text-gray-900">Rapport de synthèse</div>
                </div>
            </a>

            <a href="{{ route('reports.maintenance-schedule') }}"
               class="flex items-center justify-center p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <div class="text-sm font-medium text-gray-900">Planning maintenance</div>
                </div>
            </a>

            <a href="{{ route('playgrounds.index') }}"
               class="flex items-center justify-center p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <div class="text-sm font-medium text-gray-900">Gérer installations</div>
                </div>
            </a>
        </div>
    </div>
</div>
