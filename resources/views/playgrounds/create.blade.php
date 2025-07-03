{{-- resources/views/playgrounds/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nouvelle installation')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-4">
            <a href="{{ route('playgrounds.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Nouvelle installation</h1>
        </div>
        <p class="text-gray-600">Créer une nouvelle aire de jeux, parc d'attractions ou fête foraine</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border">
        <form action="{{ route('playgrounds.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Informations de base -->
            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations générales</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nom de l'installation *
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Nom de l'installation">
                        @error('name')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Type d'installation *
                        </label>
                        <select name="facility_type" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Sélectionner le type</option>
                            <option value="playground" {{ old('facility_type') === 'playground' ? 'selected' : '' }}>
                                Aire de jeux (EN 1176/1177)
                            </option>
                            <option value="amusement_park" {{ old('facility_type') === 'amusement_park' ? 'selected' : '' }}>
                                Parc d'attractions (EN 13814)
                            </option>
                            <option value="fairground" {{ old('facility_type') === 'fairground' ? 'selected' : '' }}>
                                Fête foraine (EN 13814)
                            </option>
                            <option value="mixed_facility" {{ old('facility_type') === 'mixed_facility' ? 'selected' : '' }}>
                                Installation mixte
                            </option>
                        </select>
                        @error('facility_type')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Localisation -->
            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Localisation</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                        <textarea name="address" rows="2"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Adresse complète">{{ old('address') }}</textarea>
                        @error('address')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                            <input type="text" name="city" value="{{ old('city') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('city')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Code postal</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="0000">
                            @error('postal_code')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Responsable -->
            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Responsable de l'installation</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom du responsable</label>
                        <input type="text" name="manager_name" value="{{ old('manager_name') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Nom complet">
                        @error('manager_name')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact</label>
                        <input type="email" name="manager_contact" value="{{ old('manager_contact') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="email@exemple.com">
                        @error('manager_contact')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Caractéristiques -->
            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Caractéristiques</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date d'installation</label>
                        <input type="date" name="installation_date" value="{{ old('installation_date') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('installation_date')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Capacité maximale</label>
                        <input type="number" name="capacity" value="{{ old('capacity') }}" min="1"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Nombre de personnes">
                        @error('capacity')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tranche d'âge</label>
                        <input type="text" name="age_range" value="{{ old('age_range') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Ex: 3-12 ans">
                        @error('age_range')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Caractéristiques booléennes -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_fenced" value="1"
                               {{ old('is_fenced') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">Installation clôturée</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="has_lighting" value="1"
                               {{ old('has_lighting') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">Éclairage présent</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_permanent" value="1" checked
                               {{ old('is_permanent', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">Installation permanente</label>
                    </div>
                </div>
            </div>

            <!-- Licence d'exploitation -->
            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Licence d'exploitation</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Numéro de licence</label>
                        <input type="text" name="operating_license" value="{{ old('operating_license') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Numéro de licence">
                        @error('operating_license')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date d'expiration</label>
                        <input type="date" name="license_expiry" value="{{ old('license_expiry') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('license_expiry')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Statut -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Statut</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut initial *</label>
                    <select name="status" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
                            Inactive
                        </option>
                        <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>
                            En maintenance
                        </option>
                        <option value="seasonal_closure" {{ old('status') === 'seasonal_closure' ? 'selected' : '' }}>
                            Fermeture saisonnière
                        </option>
                    </select>
                    @error('status')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('playgrounds.index') }}"
                   class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Créer l'installation
                </button>
            </div>
        </form>
    </div>

    <!-- Information sur les normes -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="font-medium text-blue-900 mb-2">Normes applicables selon le type d'installation</h3>
        <div class="text-sm text-blue-800 space-y-1">
            <p><strong>Aire de jeux :</strong> EN 1176 (équipements) + EN 1177 (sols amortissants)</p>
            <p><strong>Parc d'attractions / Fête foraine :</strong> EN 13814 (manèges et attractions)</p>
            <p><strong>Système électrique :</strong> EN 60335 (sécurité électrique) pour tous types</p>
            <p><strong>Installation mixte :</strong> Combinaison de plusieurs normes selon les équipements</p>
        </div>
    </div>
</div>
@endsection
