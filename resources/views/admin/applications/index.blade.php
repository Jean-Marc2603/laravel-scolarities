<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Candidatures · Administration</title>
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;500;600;700;800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body x-data="{}" class="admin-dashboard min-h-screen bg-slate-50 font-sans antialiased text-slate-900">
    @include('admin.companies.partials.header', ['pageTitle' => 'Gestion des candidatures'])

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
                <p class="text-sm font-semibold text-slate-800">{{ $applications->total() }} candidature(s)</p>
                <p class="mt-1 text-sm text-slate-500">Liste des candidatures enregistrées dans le système et leur
                    statut actuel.</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <form method="GET" action="{{ route('admin.applications.index') }}"
                class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:p-5">
                <label class="relative min-w-0 flex-1">
                    <span class="sr-only">Rechercher une candidature</span>
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path stroke-linecap="round" d="m16 16 4 4" />
                    </svg>
                    <input name="q" type="search" value="{{ $search }}" placeholder="Nom ou e-mail de l’étudiant…"
                        class="w-full rounded-xl border-slate-300 bg-white py-2.5 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <button type="submit"
                    class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Rechercher</button>
                @if ($search !== '')
                    <a href="{{ route('admin.applications.index') }}"
                        class="inline-flex items-center justify-center px-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800">Effacer</a>
                @endif
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Étudiant</th>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Offre</th>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Date</th>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Score</th>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Statut</th>
                            <th scope="col"
                                class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($applications as $application)
                            <tr class="transition hover:bg-slate-50">
                                <td class="min-w-52 px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $application->user?->name ?? 'Utilisateur inconnu' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $application->user?->email ?? '—' }}</p>
                                </td>
                                <td class="min-w-64 px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $application->offer['title'] ?? 'Offre indisponible' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $application->offer['company'] ?? 'Entreprise inconnue' }}</p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {{ $application->applied_at?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {{ $application->compatibility_score !== null ? $application->compatibility_score . ' %' : '—' }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    @php
                                        $statusClasses = [
                                            \App\Models\InternshipApplication::STATUS_PENDING => 'bg-amber-50 text-amber-700 ring-amber-200',
                                            \App\Models\InternshipApplication::STATUS_ACCEPTED => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                            \App\Models\InternshipApplication::STATUS_REJECTED => 'bg-rose-50 text-rose-700 ring-rose-200',
                                        ];
                                    @endphp
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $statusClasses[$application->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                        {{ $application->statusLabel() }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <form method="POST"
                                        action="{{ route('admin.applications.updateStatus', $application) }}"
                                        class="flex justify-end gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status"
                                            class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            aria-label="Changer le statut de la candidature">
                                            <option value="{{ \App\Models\InternshipApplication::STATUS_PENDING }}" {{ $application->status === \App\Models\InternshipApplication::STATUS_PENDING ? 'selected' : '' }}>En attente</option>
                                            <option value="{{ \App\Models\InternshipApplication::STATUS_ACCEPTED }}" {{ $application->status === \App\Models\InternshipApplication::STATUS_ACCEPTED ? 'selected' : '' }}>Acceptée</option>
                                            <option value="{{ \App\Models\InternshipApplication::STATUS_REJECTED }}" {{ $application->status === \App\Models\InternshipApplication::STATUS_REJECTED ? 'selected' : '' }}>Refusée</option>
                                        </select>
                                        <button type="submit"
                                            class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700">Valider</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center">
                                    <p class="text-sm font-semibold text-slate-800">Aucune candidature trouvée</p>
                                    <p class="mt-1 text-sm text-slate-500">Aucune candidature n’est actuellement enregistrée
                                        ou la recherche ne retourne aucun résultat.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($applications->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $applications->links() }}</div>
            @endif
        </section>
    </main>
</body>

</html>