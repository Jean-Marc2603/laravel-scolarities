<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Étudiants · Administration</title>
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;500;600;700;800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body x-data="{}" class="admin-dashboard min-h-screen bg-slate-50 font-sans antialiased text-slate-900">
    @include('admin.students.partials.header', ['pageTitle' => 'Gestion des étudiants'])

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div role="status"
                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        <section
            class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-800">{{ $students->total() }} dossier(s) étudiant(s)</p>
                <p class="mt-1 text-sm text-slate-500">Chaque dossier peut être associé à un compte dans la section
                    Utilisateurs.</p>
            </div>
            <a href="{{ route('admin.students.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    aria-hidden="true">
                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                </svg>
                Ajouter un étudiant
            </a>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-4 sm:p-5">
                <form method="GET" action="{{ route('admin.students.index') }}" class="flex flex-col gap-3 sm:flex-row">
                    <label class="relative min-w-0 flex-1">
                        <span class="sr-only">Rechercher un étudiant</span>
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="11" cy="11" r="7" />
                            <path stroke-linecap="round" d="m16 16 4 4" />
                        </svg>
                        <input name="q" type="search" value="{{ $search }}" placeholder="Matricule, nom ou prénom…"
                            class="w-full rounded-xl border-slate-300 bg-white py-2.5 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </label>
                    <button type="submit"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Rechercher</button>
                    @if ($search !== '')
                        <a href="{{ route('admin.students.index') }}"
                            class="inline-flex items-center justify-center px-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800">Effacer</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Matricule</th>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Étudiant</th>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Compte utilisateur</th>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Naissance</th>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Contact parent</th>
                            <th scope="col"
                                class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($students as $student)
                            <tr class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-indigo-700">
                                    {{ $student->matricule }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-900">{{ $student->nom }}
                                        {{ $student->prenom }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-slate-500">Dossier étudiant</p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    @if ($student->user)
                                        <p class="text-sm font-medium text-slate-800">{{ $student->user->email }}</p>
                                        <span
                                            class="text-xs {{ $student->user->is_active ? 'text-emerald-700' : 'text-rose-600' }}">{{ $student->user->is_active ? 'Compte actif' : 'Compte désactivé' }}</span>
                                    @else
                                        <span class="text-xs font-medium text-slate-400">Aucun compte lié</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {{ \Illuminate\Support\Carbon::parse($student->naissance)->format('d/m/Y') }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {{ $student->contact_parent }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.students.show', $student) }}"
                                            class="rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">Voir</a>
                                        <a href="{{ route('admin.students.edit', $student) }}"
                                            class="rounded-lg px-3 py-2 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-50">Modifier</a>
                                        <form method="POST" action="{{ route('admin.students.destroy', $student) }}"
                                            onsubmit="return confirm('Supprimer le dossier de {{ addslashes($student->nom . ' ' . $student->prenom) }} ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">Supprimer</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center">
                                    <p class="text-sm font-semibold text-slate-800">Aucun étudiant trouvé</p>
                                    <p class="mt-1 text-sm text-slate-500">Ajoutez un dossier étudiant ou modifiez votre
                                        recherche.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($students->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $students->links() }}</div>
            @endif
        </section>
    </main>
</body>

</html>