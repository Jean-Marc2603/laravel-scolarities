<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Utilisateurs · Administration</title>
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;500;600;700;800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body x-data="{}" class="admin-dashboard min-h-screen bg-slate-50 font-sans antialiased text-slate-900">
    @include('admin.users.partials.header', ['pageTitle' => 'Gestion des utilisateurs'])
    <main class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div role="status"
                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ session('error') }}</div>
        @endif

        <section
            class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-800">{{ $users->total() }} compte(s) utilisateur(s)</p>
                <p class="mt-1 text-sm text-slate-500">Rôles disponibles : admin, student, company et supervisor.</p>
            </div>
            <a href="{{ route('admin.users.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    aria-hidden="true">
                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                </svg>
                Ajouter un utilisateur
            </a>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <form method="GET" action="{{ route('admin.users.index') }}"
                class="grid gap-3 border-b border-slate-100 p-4 sm:grid-cols-2 lg:grid-cols-4 sm:p-5">
                <label class="relative min-w-0 lg:col-span-2">
                    <span class="sr-only">Rechercher par nom ou e-mail</span>
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path stroke-linecap="round" d="m16 16 4 4" />
                    </svg>
                    <input name="q" type="search" value="{{ $search }}" placeholder="Nom ou adresse e-mail…"
                        class="w-full rounded-xl border-slate-300 bg-white py-2.5 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <label>
                    <span class="sr-only">Filtrer par rôle</span>
                    <select name="role"
                        class="w-full rounded-xl border-slate-300 bg-white py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Tous les rôles</option>
                        @foreach (['admin', 'student', 'company', 'supervisor'] as $option)
                            <option value="{{ $option }}" @selected($role === $option)>{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="sr-only">Filtrer par statut</span>
                    <select name="status"
                        class="w-full rounded-xl border-slate-300 bg-white py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Tous les statuts</option>
                        <option value="active" @selected($status === 'active')>Actifs</option>
                        <option value="inactive" @selected($status === 'inactive')>Désactivés</option>
                    </select>
                </label>
                <div class="flex items-center gap-3 sm:col-span-2 lg:col-span-4">
                    <button type="submit"
                        class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">Filtrer</button>
                    @if ($search !== '' || $role !== '' || $status !== '')
                        <a href="{{ route('admin.users.index') }}"
                            class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Effacer</a>
                    @endif
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Utilisateur</th>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Rôle</th>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Statut</th>
                            <th scope="col" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">
                                E-mail vérifié</th>
                            <th scope="col"
                                class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            <tr class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $user->email }}</p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4"><span
                                        class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ ucfirst($user->role) }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    @if ($user->is_active)
                                        <span
                                            class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Actif</span>
                                    @else
                                        <span
                                            class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600">Désactivé</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {{ $user->email_verified_at ? 'Oui' : 'Non' }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                            class="rounded-lg px-3 py-2 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-50">Modifier</a>
                                        @if ($user->role !== 'admin')
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                onsubmit="return confirm('Supprimer le compte de {{ addslashes($user->name) }} ? Les comptes liés à un dossier, CV ou candidature doivent plutôt être désactivés.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">Supprimer</button>
                                            </form>
                                        @else
                                            <span class="px-3 py-2 text-xs font-medium text-slate-400">Admin protégé</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14 text-center">
                                    <p class="text-sm font-semibold text-slate-800">Aucun utilisateur trouvé</p>
                                    <p class="mt-1 text-sm text-slate-500">Modifiez les filtres ou créez un compte.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $users->links() }}</div>
            @endif
        </section>
    </main>
</body>

</html>