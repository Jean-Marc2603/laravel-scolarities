<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Détails du stage · Administration</title>
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;500;600;700;800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body x-data="{}" class="admin-dashboard min-h-screen bg-slate-50 font-sans antialiased text-slate-900">
    @include('admin.companies.partials.header', ['pageTitle' => 'Détails du stage'])

    <main class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Stage</p>
                    <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900">
                        {{ $stage['offer_title'] ?? 'Offre non renseignée' }}</h2>
                    <p class="mt-2 text-sm text-slate-500">{{ $stage['company_name'] }} · {{ $stage['student_name'] }}
                    </p>
                </div>
                @php
                    $statusMap = [
                        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
                        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                        'completed' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
                        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
                    ];
                @endphp
                <span
                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $statusMap[$stage['status']] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                    {{ ucfirst(str_replace('_', ' ', $stage['status'] ?? 'non_renseigne')) }}
                </span>
            </div>
        </section>

        <section class="grid gap-6 md:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-900">Informations du stage</h3>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="font-semibold text-slate-500">Étudiant</dt>
                        <dd class="mt-1 text-slate-900">{{ $stage['student_name'] }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Entreprise</dt>
                        <dd class="mt-1 text-slate-900">{{ $stage['company_name'] }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Offre</dt>
                        <dd class="mt-1 text-slate-900">{{ $stage['offer_title'] ?? 'Offre non renseignée' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Date de début</dt>
                        <dd class="mt-1 text-slate-900">
                            {{ $stage['start_date'] ? \Illuminate\Support\Carbon::parse($stage['start_date'])->format('d/m/Y') : '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Date de fin</dt>
                        <dd class="mt-1 text-slate-900">
                            {{ $stage['end_date'] ? \Illuminate\Support\Carbon::parse($stage['end_date'])->format('d/m/Y') : '—' }}
                        </dd>
                    </div>
                </dl>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-900">Informations importantes</h3>
                <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-700">
                    {{ $stage['important_information'] ?: 'Aucune information supplémentaire n’a été enregistrée pour ce stage.' }}
                </div>
            </article>
        </section>

        <div class="flex justify-start">
            <a href="{{ route('admin.internships.index') }}"
                class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800">←
                Retour à la liste des stages</a>
        </div>
    </main>
</body>

</html>