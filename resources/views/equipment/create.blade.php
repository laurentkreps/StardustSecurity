{{-- resources/views/equipment/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nouvel équipement')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- En-tête -->
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-2">
            <a href="{{ route('equipment.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Nouvel équipement</h1>
        </div>
        <p class="text-gray-600">Ajout d'un équipement multi-normes (EN 1176, EN 13814, EN 60335)</p>
    </div>

    <!-- Formulaire -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <form action="{{ route('equipment.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Installation -->
                <div class="md:col-span-2">
                    <label for="playground_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Installation <span class="text-red-500">*</span>
                    </label>
                    <select name="playground_id" id="playground_id" class="w-full rounded-md border-gray-300 @error('playground_id') border-red-500 @enderror" required>
                        <option value="">Sélectionner une installation</option>
                        @foreach($playgrounds as $playgroundOption)
                            <option value="{{ $playgroundOption->id }}"
                                {{ (old('playground_id', $playground?->id) == $playgroundOption->id) ? 'selected' : '' }}>
                                {{ $playgroundOption->name }} - {{ $playgroundOption->city }}
                            </option>
                        @endforeach
                    </select>
                    @error('playground_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Code de référence -->
                <div>
                    <label for="reference_code" class="block text-sm font-medium text-gray-700 mb-1">
                        Code de référence <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="reference_code" id="reference_code"
                           value="{{ old('reference_code') }}"
                           class="w-full rounded-md border-gray-300 @error('reference_code') border-red-500 @enderror"
                           placeholder="Ex: JEU-001" required>
                    @error('reference_code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Catégorie d'équipement -->
                <div>
                    <label for="equipment_category" class="block text-sm font-medium text-gray-700 mb-1">
                        Catégorie <span class="text-red-500">*</span>
                    </label>
                    <select name="equipment_category" id="equipment_category"
                            class="w-full rounded-md border-gray-300 @error('equipment_category') border-red-500 @enderror" required>
                        <option value="">Sélectionner une catégorie</option>
                        <option value="playground_equipment" {{ old('equipment_category') === 'playground_equipment' ? 'selected' : '' }}>
                            Équipement aire de jeux (EN 1176)
                        </option>
                        <option value="amusement_ride" {{ old('equipment_category') === 'amusement_ride' ? 'selected' : '' }}>
                            Manège/Attraction (EN 13814)
                        </option>
                        <option value="electrical_system" {{ old('equipment_category') === 'electrical_system' ? 'selected' : '' }}>
                            Système électrique (EN 60335)
                        </option>
                        <option value="infrastructure" {{ old('equipment_category') === 'infrastructure' ? 'selected' : '' }}>
                            Infrastructure
                        </option>
                        <option value="safety_equipment" {{ old('equipment_category') === 'safety_equipment' ? 'selected' : '' }}>
                            Équipement de sécurité
                        </option>
                    </select>
                    @error('equipment_category')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type d'équipement -->
                <div>
                    <label for="equipment_type" class="block text-sm font-medium text-gray-700 mb-1">
                        Type d'équipement <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="equipment_type" id="equipment_type"
                           value="{{ old('equipment_type') }}"
                           class="w-full rounded-md border-gray-300 @error('equipment_type') border-red-500 @enderror"
                           placeholder="Ex: Balançoire, Toboggan, Manège..." required>
                    @error('equipment_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Marque -->
                <div>
                    <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">Marque</label>
                    <input type="text" name="brand" id="brand"
                           value="{{ old('brand') }}"
                           class="w-full rounded-md border-gray-300 @error('brand') border-red-500 @enderror"
                           placeholder="Ex: KOMPAN, Wicksteed...">
                    @error('brand')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="purchase_date" class="block text-sm font-medium text-gray-700 mb-1">Date d'achat</label>
                    <input type="date" name="purchase_date" id="purchase_date"
                           value="{{ old('purchase_date') }}"
                           class="w-full rounded-md border-gray-300 @error('purchase_date') border-red-500 @enderror">
                    @error('purchase_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="installation_date" class="block text-sm font-medium text-gray-700 mb-1">Date d'installation</label>
                    <input type="date" name="installation_date" id="installation_date"
                           value="{{ old('installation_date') }}"
                           class="w-full rounded-md border-gray-300 @error('installation_date') border-red-500 @enderror">
                    @error('installation_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Détails fabricant/fournisseur -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="manufacturer_details" class="block text-sm font-medium text-gray-700 mb-1">Coordonnées fabricant</label>
                    <textarea name="manufacturer_details" id="manufacturer_details" rows="3"
                              class="w-full rounded-md border-gray-300 @error('manufacturer_details') border-red-500 @enderror"
                              placeholder="Nom, adresse, contact...">{{ old('manufacturer_details') }}</textarea>
                    @error('manufacturer_details')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="supplier_details" class="block text-sm font-medium text-gray-700 mb-1">Coordonnées fournisseur</label>
                    <textarea name="supplier_details" id="supplier_details" rows="3"
                              class="w-full rounded-md border-gray-300 @error('supplier_details') border-red-500 @enderror"
                              placeholder="Nom, adresse, contact...">{{ old('supplier_details') }}</textarea>
                    @error('supplier_details')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Caractéristiques techniques (conditionnelles selon la catégorie) -->
            <div id="technical-specs" class="mt-6 space-y-6">
                <!-- Caractéristiques générales -->
                <div class="general-specs">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Caractéristiques techniques</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="height" class="block text-sm font-medium text-gray-700 mb-1">Hauteur (m)</label>
                            <input type="number" name="height" id="height" step="0.01" min="0"
                                   value="{{ old('height') }}"
                                   class="w-full rounded-md border-gray-300 @error('height') border-red-500 @enderror">
                            @error('height')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_passengers" class="block text-sm font-medium text-gray-700 mb-1">Passagers max</label>
                            <input type="number" name="max_passengers" id="max_passengers" min="0"
                                   value="{{ old('max_passengers') }}"
                                   class="w-full rounded-md border-gray-300 @error('max_passengers') border-red-500 @enderror">
                            @error('max_passengers')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Spécifications manèges -->
                <div id="amusement-specs" class="hidden">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Spécifications manège (EN 13814)</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="max_speed" class="block text-sm font-medium text-gray-700 mb-1">Vitesse max (km/h)</label>
                            <input type="number" name="max_speed" id="max_speed" step="0.01" min="0"
                                   value="{{ old('max_speed') }}"
                                   class="w-full rounded-md border-gray-300 @error('max_speed') border-red-500 @enderror">
                            @error('max_speed')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_acceleration" class="block text-sm font-medium text-gray-700 mb-1">Accélération max (g)</label>
                            <input type="number" name="max_acceleration" id="max_acceleration" step="0.01" min="0"
                                   value="{{ old('max_acceleration') }}"
                                   class="w-full rounded-md border-gray-300 @error('max_acceleration') border-red-500 @enderror">
                            @error('max_acceleration')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Spécifications électriques -->
                <div id="electrical-specs" class="hidden">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Spécifications électriques (EN 60335)</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="voltage" class="block text-sm font-medium text-gray-700 mb-1">Tension (V)</label>
                            <input type="number" name="voltage" id="voltage" step="0.01" min="0"
                                   value="{{ old('voltage') }}"
                                   class="w-full rounded-md border-gray-300 @error('voltage') border-red-500 @enderror">
                            @error('voltage')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="current" class="block text-sm font-medium text-gray-700 mb-1">Courant (A)</label>
                            <input type="number" name="current" id="current" step="0.01" min="0"
                                   value="{{ old('current') }}"
                                   class="w-full rounded-md border-gray-300 @error('current') border-red-500 @enderror">
                            @error('current')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="protection_class" class="block text-sm font-medium text-gray-700 mb-1">Classe de protection</label>
                            <select name="protection_class" id="protection_class"
                                    class="w-full rounded-md border-gray-300 @error('protection_class') border-red-500 @enderror">
                                <option value="">Sélectionner</option>
                                <option value="I" {{ old('protection_class') === 'I' ? 'selected' : '' }}>Classe I</option>
                                <option value="II" {{ old('protection_class') === 'II' ? 'selected' : '' }}>Classe II</option>
                                <option value="III" {{ old('protection_class') === 'III' ? 'selected' : '' }}>Classe III</option>
                            </select>
                            @error('protection_class')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="ip_rating" class="block text-sm font-medium text-gray-700 mb-1">Indice IP</label>
                            <input type="text" name="ip_rating" id="ip_rating"
                                   value="{{ old('ip_rating') }}"
                                   class="w-full rounded-md border-gray-300 @error('ip_rating') border-red-500 @enderror"
                                   placeholder="Ex: IP65">
                            @error('ip_rating')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('equipment.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Créer l'équipement
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('equipment_category');
    const amusementSpecs = document.getElementById('amusement-specs');
    const electricalSpecs = document.getElementById('electrical-specs');

    function toggleSpecs() {
        const category = categorySelect.value;

        // Masquer tous les specs
        amusementSpecs.classList.add('hidden');
        electricalSpecs.classList.add('hidden');

        // Afficher les specs appropriés
        if (category === 'amusement_ride') {
            amusementSpecs.classList.remove('hidden');
        } else if (category === 'electrical_system') {
            electricalSpecs.classList.remove('hidden');
        }
    }

    categorySelect.addEventListener('change', toggleSpecs);
    toggleSpecs(); // Exécuter au chargement
});
</script>
@endsection
