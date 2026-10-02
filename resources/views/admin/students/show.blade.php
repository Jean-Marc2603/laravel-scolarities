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
                {{ session('status') }}</div>
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
                            {{ $student->prenom }}</h2>
                        <p class="mt-1 text-sm text-slate-500">Dossier étudiant · ajouté le
                            {{ $student->created_at?->format('d/m/Y') ?? '—' }}</p>
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
                        {{ \Illuminate\Support\Carbon::parse($student->naissance)->format('d/m/Y') }}</dd>
                </div>
                <div class="bg-white p-6">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Contact parent/tuteur</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $student->contact_parent }}</dd>
                </div>
            </dl>
        </section>
        <a href="{{ route('admin.students.index') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Retour
            à la liste des étudiants</a>
    </main>
</body>

</html>