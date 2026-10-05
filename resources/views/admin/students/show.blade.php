<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $student->nom }} {{ $student->prenom }} · Administration</title>
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;500;600;700;800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body x-data="{}" class="admin-dashboard min-h-screen bg-slate-50 font-sans antialiased text-slate-900">
    @include('admin.students.partials.header', ['pageTitle' => 'Dossier étudiant'])
    <main class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div role="status"
                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div
                class="flex flex-col gap-5 border-b border-slate-100 p-6 sm:flex-row sm:items-center sm:justify-between sm:p-8">
                <div class="flex items-center gap-4">
                    <span
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-lg font-extrabold text-indigo-700">{{ mb_strtoupper(mb_substr($student->prenom, 0, 1) . mb_substr($student->nom, 0, 1)) }}</span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">{{ $student->matricule }}
                        </p>
                        <h2 class="mt-1 text-2xl font-extrabold text-slate-900">{{ $student->nom }}
                            {{ $student->prenom }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">Dossier étudiant · ajouté le
                            {{ $student->created_at?->format('d/m/Y') ?? '—' }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.students.edit', $student) }}"
                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">Modifier</a>
                    <form method="POST" action="{{ route('admin.students.destroy', $student) }}"
                        onsubmit="return confirm('Supprimer ce dossier étudiant ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">Supprimer</button>
                    </form>
                </div>
            </div>
            <dl class="grid gap-px bg-slate-100 sm:grid-cols-2">
                <div class="bg-white p-6">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Nom</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $student->nom }}</dd>
                </div>
                <div class="bg-white p-6">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Prénom</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $student->prenom }}</dd>
                </div>
                <div class="bg-white p-6">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Date de naissance</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">
                        {{ \Illuminate\Support\Carbon::parse($student->naissance)->format('d/m/Y') }}
                    </dd>
                </div>
                <div class="bg-white p-6">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Contact parent/tuteur</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $student->contact_parent }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Compte utilisateur associé</h2>
                    <p class="mt-1 text-sm text-slate-500">Les identifiants de connexion sont gérés dans la section
                        Utilisateurs.</p>
                </div>
                @if ($student->user)
                    <a href="{{ route('admin.users.edit', $student->user) }}"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">Gérer
                        le compte</a>
                @endif
            </div>
            @if ($student->user)
                <dl class="mt-5 grid gap-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Nom du compte</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $student->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">E-mail</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $student->user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Statut</dt>
                        <dd
                            class="mt-1 text-sm font-semibold {{ $student->user->is_active ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $student->user->is_active ? 'Actif' : 'Désactivé' }} · {{ $student->user->role }}</dd>
                    </div>
                </dl>
            @else
                <div
                    class="mt-5 flex flex-col items-start gap-3 rounded-xl bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600">Aucun compte de connexion n’est associé à ce dossier.</p>
                    <a href="{{ route('admin.students.edit', $student) }}"
                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Associer un compte</a>
                </div>
            @endif
        </section>

        <section class="grid gap-5 lg:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-900">Informations académiques</h2>
                @if ($currentAttribution)
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Année scolaire</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $schoolYear?->school_year ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Classe</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $class?->libelle ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Niveau</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $class?->level?->libelle ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Inscription</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">
                                {{ $currentAttribution->created_at?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-500">Aucune attribution scolaire n’est
                        enregistrée pour ce dossier.</p>
                @endif
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-900">Frais de scolarité et paiements</h2>
                @if ($fees)
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Frais de l’année</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">
                                {{ number_format($fees->montant, 0, ',', ' ') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Total payé</dt>
                            <dd class="mt-1 text-sm font-semibold text-emerald-700">
                                {{ number_format($paidAmount, 0, ',', ' ') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Reste à payer</dt>
                            <dd
                                class="mt-1 text-sm font-semibold {{ $remainingFees > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                                {{ number_format($remainingFees, 0, ',', ' ') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Nombre de paiements</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $payments->count() }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-500">Aucune attribution ou aucun frais
                        défini pour l’année scolaire de cet étudiant.</p>
                    <p class="mt-2 text-xs text-slate-500">Paiements enregistrés : {{ $student->payments->count() }}</p>
                @endif
            </article>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Candidatures aux stages</h2>
                    <p class="mt-1 text-sm text-slate-500">Candidatures enregistrées avec le compte associé.</p>
                </div>
                <span
                    class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">{{ $applications->count() }}</span>
            </div>
            @if ($applications->isNotEmpty())
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach ($applications as $application)
                        <div class="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">
                                    {{ $application->offer['title'] ?? 'Offre supprimée ou indisponible' }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $application->offer['company'] ?? '' }} ·
                                    {{ $application->applied_at?->format('d/m/Y') }}</p>
                            </div>
                            <span class="text-xs font-semibold text-slate-600">{{ $application->statusLabel() }} ·
                                {{ $application->compatibility_score !== null ? $application->compatibility_score . ' %' : 'Score non évalué' }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-500">Aucune candidature disponible pour ce
                    dossier.</p>
            @endif
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-900">Stage actuel</h2>
            @if ($currentStage)
                <pre
                    class="mt-4 overflow-x-auto rounded-xl bg-slate-50 p-4 text-xs text-slate-700">{{ json_encode($currentStage, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            @else
                <p class="mt-3 text-sm text-slate-500">Aucun stage en cours n’est enregistré dans le système.</p>
            @endif
        </section>

        <a href="{{ route('admin.students.index') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Retour
            à la liste des étudiants</a>
    </main>
</body>

</html>