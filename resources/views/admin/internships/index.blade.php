<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Stages · Administration</title>
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;500;600;700;800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body x-data="{}" class="admin-dashboard min-h-screen bg-slate-50 font-sans antialiased text-slate-900">
    @include('admin.companies.partials.header', ['pageTitle' => 'Gestion des stages'])

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div role="status"
                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <section
            class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-800">{{ $internships->count() }} stage(s)</p>
                <p class="mt-1 text-sm text-slate-500">Liste des stages actuellement enregistrés dans le système.</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <form method="GET" action="{{ route('admin.internships.index') }}"
                class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:flex-wrap sm:items-end">
                <label class="relative min-w-0 flex-1 basis-48">
                    <span class="sr-only">Rechercher un stage</span>
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path stroke-linecap="round" d="m16 16 4 4" />
                    </svg>
                    <input name="q" type="search" value="{{ $search }}"
                        placeholder="Étudiant, entreprise, offre, statut…"
                        class="w-full rounded-xl border-slate-300 bg-white py-2.5 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>

                <label class="min-w-0 basis-40">
                    <span
                        class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Étudiant</span>
                    <select name="student"
                        class="w-full rounded-xl border-slate-300 bg-white py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Tous</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" {{ $studentFilter == $student->id ? 'selected' : '' }}>
                                {{ $student->name }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="min-w-0 basis-40">
                    <span
                        class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Entreprise</span>
                    <select name="company"
                        class="w-full rounded-xl border-slate-300 bg-white py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Toutes</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" {{ $companyFilter == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="min-w-0 basis-40">
                    <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Statut</span>
                    <select name="status"
                        class="w-full rounded-xl border-slate-300 bg-white py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Tous</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" {{ $statusFilter === $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <div class="flex gap-2">
                    <button type="submit"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Filtrer</button>
                    <a href="{{ route('admin.internships.index') }}"
                        class="inline-flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-semibold text-indigo-600 hover:text-indigo-800">Réinitialiser</a>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Étudiant</th>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Entreprise</th>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Offre</th>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Début</th>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Fin</th>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Progression</th>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Statut</th>
                            <th scope="col"
                                class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">
                                Détails</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($internships as $internship)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-5 py-4 text-sm font-semibold text-slate-900">{{ $internship['student_name'] }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ $internship['company_name'] }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ $internship['offer_title'] ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {{ $internship['start_date'] ? \Illuminate\Support\Carbon::parse($internship['start_date'])->format('d/m/Y') : '—' }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {{ $internship['end_date'] ? \Illuminate\Support\Carbon::parse($internship['end_date'])->format('d/m/Y') : '—' }}
                                </td>
                                <td class="min-w-40 px-5 py-4">
                                    @if ($internship['progress'] !== null)
                                        <div class="flex items-center gap-3">
                                            <div class="h-2 w-20 overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-full rounded-full bg-indigo-600"
                                                    style="width: {{ $internship['progress'] }}%"></div>
                                            </div>
                                            <span
                                                class="text-sm font-semibold text-slate-700">{{ $internship['progress'] }}%</span>
                                        </div>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $internship['completed_tasks'] }}/{{ $internship['total_tasks'] }} tâches</p>
                                    @else
                                        <span class="text-sm text-slate-500">Non renseignée</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    @php
                                        $statusMap = [
                                            'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                            'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                            'completed' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
                                            'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
                                        ];
                                    @endphp
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $statusMap[$internship['status']] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                        {{ ['active' => 'En cours', 'completed' => 'Terminé', 'accepted' => 'Acceptée'][$internship['status']] ?? ucfirst(str_replace('_', ' ', $internship['status'] ?? 'non_renseigne')) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <a href="{{ route('admin.internships.show', $internship['id']) }}"
                                        class="rounded-lg px-3 py-2 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-50">Consulter</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-14 text-center">
                                    <p class="text-sm font-semibold text-slate-800">Aucun stage trouvé</p>
                                    <p class="mt-1 text-sm text-slate-500">Aucun stage ne correspond à la recherche ou aux
                                        filtres actuels.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>

</html>