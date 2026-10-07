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

    <main class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Suivi du stage</p>
                    <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900">{{ $stage['offer_title'] ?? 'Offre non renseignée' }}</h2>
                    <p class="mt-2 text-sm text-slate-600">{{ $stage['company_name'] }} · {{ $stage['student_name'] }}</p>
                </div>
                @php
                    $statusMap = [
                        'pending' => ['bg-amber-50 text-amber-700 ring-amber-200', 'En attente'],
                        'active' => ['bg-emerald-50 text-emerald-700 ring-emerald-200', 'En cours'],
                        'completed' => ['bg-indigo-50 text-indigo-700 ring-indigo-200', 'Terminé'],
                        'cancelled' => ['bg-rose-50 text-rose-700 ring-rose-200', 'Annulé'],
                    ];
                    [$statusClass, $statusLabel] = $statusMap[$stage['status']] ?? ['bg-slate-100 text-slate-600 ring-slate-200', ucfirst(str_replace('_', ' ', $stage['status'] ?? 'non_renseigne'))];
                @endphp
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>
        </section>

        <section class="grid gap-6 md:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-900">Informations de l’étudiant</h3>
                <dl class="mt-4 space-y-4 text-sm">
                    <div><dt class="font-semibold text-slate-500">Nom</dt><dd class="mt-1 text-slate-900">{{ $stage['student_name'] }}</dd></div>
                    @if ($stage['student_email'])
                        <div><dt class="font-semibold text-slate-500">E-mail</dt><dd class="mt-1 text-slate-900">{{ $stage['student_email'] }}</dd></div>
                    @endif
                    @if ($stage['student_matricule'])
                        <div><dt class="font-semibold text-slate-500">Matricule</dt><dd class="mt-1 text-slate-900">{{ $stage['student_matricule'] }}</dd></div>
                    @endif
                    @if ($stage['student_phone'])
                        <div><dt class="font-semibold text-slate-500">Contact</dt><dd class="mt-1 text-slate-900">{{ $stage['student_phone'] }}</dd></div>
                    @endif
                </dl>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-900">Entreprise et période</h3>
                <dl class="mt-4 space-y-4 text-sm">
                    <div><dt class="font-semibold text-slate-500">Entreprise</dt><dd class="mt-1 text-slate-900">{{ $stage['company_name'] }}</dd></div>
                    <div x-data="{ editingTitle: false }">
                        <dt class="font-semibold text-slate-500">Offre / poste</dt>
                        <dd class="mt-1 text-slate-900" x-show="!editingTitle">{{ $stage['offer_title'] ?? 'Offre non renseignée' }}</dd>
                        @if ($stage['student_internship'])
                            <form method="POST" action="{{ route('admin.internships.title.update', $stage['student_internship']->id) }}" class="mt-2 flex flex-wrap items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <input type="text" name="stage_title" value="{{ old('stage_title', $stage['offer_title'] ?? '') }}" maxlength="255" required x-show="editingTitle" x-cloak class="min-w-0 flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" aria-label="Titre exact du poste ou de l’offre de stage">
                                <button type="button" x-show="!editingTitle" @click="editingTitle = true; $nextTick(() => $el.closest('div').querySelector('input').focus())" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Modifier</button>
                                <button type="submit" x-show="editingTitle" x-cloak class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-indigo-700">Enregistrer</button>
                                <button type="button" x-show="editingTitle" x-cloak @click="editingTitle = false" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">Annuler</button>
                            </form>
                            @error('stage_title')
                                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                    <div><dt class="font-semibold text-slate-500">Date de début</dt><dd class="mt-1 text-slate-900">{{ $stage['start_date'] ? \Illuminate\Support\Carbon::parse($stage['start_date'])->format('d/m/Y') : '—' }}</dd></div>
                    <div><dt class="font-semibold text-slate-500">Date de fin</dt><dd class="mt-1 text-slate-900">{{ $stage['end_date'] ? \Illuminate\Support\Carbon::parse($stage['end_date'])->format('d/m/Y') : '—' }}</dd></div>
                </dl>
                @if ($stage['student_internship'])
                    <form method="POST" action="{{ route('admin.internships.dates.update', $stage['student_internship']->id) }}" class="mt-6 border-t border-slate-100 pt-5">
                        @csrf
                        @method('PATCH')
                        <h4 class="text-sm font-semibold text-slate-800">Définir les dates du stage</h4>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <label class="block text-sm font-medium text-slate-600">
                                Date de début
                                <input type="date" name="start_date" value="{{ $stage['start_date']?->format('Y-m-d') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </label>
                            <label class="block text-sm font-medium text-slate-600">
                                Date de fin
                                <input type="date" name="end_date" value="{{ $stage['end_date']?->format('Y-m-d') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </label>
                        </div>
                        @error('start_date')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        @error('end_date')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        <button type="submit" class="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Enregistrer les dates</button>
                    </form>
                @endif
            </article>
        </section>

        @if ($stage['progress'] !== null)
            <section class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div>
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Progression globale : {{ $stage['progress'] }} %</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $stage['completed_tasks'] }} tâche(s) terminée(s) sur {{ $stage['total_tasks'] }} · même calcul que dans l’espace étudiant.</p>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">{{ $stage['progress'] }}%</span>
                    </div>
                    <div class="mt-3 h-3 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="Progression globale du stage" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $stage['progress'] }}">
                        <div class="h-full rounded-full bg-indigo-600 transition-all" style="width: {{ $stage['progress'] }}%"></div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200">
                    <div class="hidden grid-cols-[minmax(0,1fr)_7rem_8rem_12rem] gap-4 bg-slate-50 px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500 sm:grid">
                        <span>Tâche</span><span>Avancement</span><span>Statut</span><span>Dernière mise à jour</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($stage['tasks'] as $task)
                            @php
                                if ($task->progress === 100) {
                                    [$taskStatusClass, $taskStatus] = ['bg-emerald-50 text-emerald-700', 'Terminé'];
                                } elseif ($task->progress > 0) {
                                    [$taskStatusClass, $taskStatus] = ['bg-indigo-50 text-indigo-700', 'En cours'];
                                } else {
                                    [$taskStatusClass, $taskStatus] = ['bg-slate-100 text-slate-600', 'À commencer'];
                                }
                            @endphp
                            <div class="grid gap-3 px-4 py-4 sm:grid-cols-[minmax(0,1fr)_7rem_8rem_12rem] sm:items-center sm:gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $task->name }}</p>
                                    @if ($task->description)
                                        <p class="mt-1 text-xs leading-5 text-slate-500">{{ $task->description }}</p>
                                    @endif
                                    @if ($task->planned_date)
                                        <p class="mt-1 text-xs text-slate-500">Date prévue : {{ $task->planned_date->format('d/m/Y') }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-16 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full {{ $task->progress === 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}" style="width: {{ $task->progress }}%"></div></div>
                                    <span class="text-sm font-semibold text-slate-700">{{ $task->progress }}%</span>
                                </div>
                                <span class="w-fit rounded-full px-2.5 py-1 text-xs font-semibold {{ $taskStatusClass }}">{{ $taskStatus }}</span>
                                <span class="text-xs text-slate-500">{{ $task->updated_at?->format('d/m/Y à H:i') ?? '—' }}</span>
                            </div>
                        @empty
                            <p class="px-4 py-8 text-center text-sm text-slate-500">Aucune tâche n’est enregistrée pour ce stage.</p>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-900">Observation de l’administrateur</h3>
                <p class="mt-1 text-sm text-slate-500">Cette note est indépendante des avancements saisis par l’étudiant.</p>
                <form method="POST" action="{{ route('admin.internships.observation.update', $stage['student_internship']->id) }}" class="mt-4 space-y-3">
                    @csrf
                    @method('PATCH')
                    <textarea name="admin_observation" rows="4" maxlength="5000" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ajouter une observation...">{{ old('admin_observation', $stage['admin_observation']) }}</textarea>
                    @error('admin_observation')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">Enregistrer l’observation</button>
                </form>
            </section>
        @else
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-900">Informations importantes</h3>
                <p class="mt-3 rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-700">{{ $stage['important_information'] ?: 'Aucune information supplémentaire n’a été enregistrée pour ce stage.' }}</p>
            </section>
        @endif

        <a href="{{ route('admin.internships.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Retour à la liste des stages</a>
    </main>
</body>

</html>