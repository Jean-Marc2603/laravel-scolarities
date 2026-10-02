<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Administration · {{ config('app.name', 'Scolarités') }}</title>
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;500;600;700;800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{}" class="admin-dashboard min-h-screen bg-slate-50 font-sans antialiased text-slate-900">
    <div class="min-h-screen lg:flex">
        <aside class="flex w-full flex-col border-b border-slate-200 bg-white lg:fixed lg:inset-y-0 lg:left-0 lg:w-64 lg:border-b-0 lg:border-r">
            <div class="flex h-16 items-center gap-3 border-b border-slate-100 px-5">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm shadow-indigo-200">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5m0 14h16M8 15l3-4 3 2 5-7" /></svg>
                </span>
                <div>
                    <p class="text-sm font-extrabold tracking-tight text-slate-900">Scolarités</p>
                    <p class="text-xs font-medium text-slate-500">Administration</p>
                </div>
            </div>

            <nav class="flex gap-2 overflow-x-auto px-3 py-4 lg:flex-1 lg:flex-col" aria-label="Navigation administrateur">
                <a href="{{ route('admin.dashboard') }}" aria-current="page" class="flex shrink-0 items-center gap-3 rounded-xl bg-indigo-50 px-3 py-2.5 text-sm font-semibold text-indigo-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                    <span>Vue d’ensemble</span>
                </a>
                <div class="hidden border-t border-slate-100 pt-3 lg:block">
                    <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Gestion à venir</p>
                </div>
                @foreach ([
                    ['Utilisateurs', 'users'],
                    ['Étudiants', 'students'],
                    ['Entreprises', 'companies'],
                    ['Offres', 'offers'],
                    ['Candidatures', 'applications'],
                    ['Stages', 'internships'],
                    ['Frais de scolarité', 'fees'],
                ] as [$label, $key])
                    <a href="#{{ $key }}" aria-disabled="true" title="Cette section sera disponible prochainement" class="flex shrink-0 cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-500 transition hover:bg-slate-50 hover:text-slate-700">
                        <span class="flex h-5 w-5 items-center justify-center rounded-md border border-slate-200 text-[10px] font-bold text-slate-400">{{ mb_substr($label, 0, 1) }}</span>
                        <span>{{ $label }}</span>
                        <span class="ml-auto hidden rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-400 lg:inline">À venir</span>
                    </a>
                @endforeach
            </nav>

            <div class="hidden border-t border-slate-100 p-4 lg:block">
                <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">{{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-800">{{ Auth::user()->name }}</p>
                        <p class="text-xs font-medium text-indigo-600">Administrateur</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg p-2 text-slate-400 transition hover:bg-white hover:text-slate-700" aria-label="Déconnexion" title="Déconnexion">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5m5 5H3m9-9h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7" /></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="min-w-0 flex-1 lg:ml-64">
            <header class="sticky top-0 z-10 border-b border-slate-200/80 bg-white/90 backdrop-blur">
                <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Espace administrateur</p>
                        <h1 class="text-lg font-bold tracking-tight text-slate-900">Tableau de bord</h1>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="hidden text-sm text-slate-500 sm:inline">{{ now()->locale('fr')->translatedFormat('l j F Y') }}</span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Système actif</span>
                        @include('offers.partials.theme-toggle')
                    </div>
                </div>
            </header>

            <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
                <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-700 via-indigo-600 to-violet-600 p-6 text-white shadow-lg shadow-indigo-200/60 sm:p-8">
                    <div class="absolute -right-12 -top-20 h-64 w-64 rounded-full border-[32px] border-white/10" aria-hidden="true"></div>
                    <div class="relative max-w-2xl">
                        <p class="text-sm font-semibold text-indigo-100">Bonjour, {{ Auth::user()->name }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">Voici l’activité de votre plateforme.</h2>
                        <p class="mt-3 text-sm leading-6 text-indigo-100">Retrouvez les principaux indicateurs de suivi. Les espaces de gestion détaillée seront ajoutés ultérieurement.</p>
                    </div>
                </section>

                <section aria-labelledby="statistics-title">
                    <div class="mb-4 flex items-end justify-between gap-4">
                        <div>
                            <h2 id="statistics-title" class="text-lg font-bold text-slate-900">Indicateurs clés</h2>
                            <p class="mt-1 text-sm text-slate-500">Données actuellement disponibles dans l’application.</p>
                        </div>
                        <span class="hidden rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-slate-500 shadow-sm ring-1 ring-slate-200 sm:inline">Mis à jour en direct</span>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($statistics as $statistic)
                            @php
                                $iconColors = [
                                    'indigo' => 'bg-indigo-50 text-indigo-600 ring-indigo-100',
                                    'sky' => 'bg-sky-50 text-sky-600 ring-sky-100',
                                    'violet' => 'bg-violet-50 text-violet-600 ring-violet-100',
                                    'amber' => 'bg-amber-50 text-amber-600 ring-amber-100',
                                    'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
                                    'rose' => 'bg-rose-50 text-rose-600 ring-rose-100',
                                ];
                            @endphp
                            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-500">{{ $statistic['label'] }}</p>
                                        <p class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($statistic['value']) }}</p>
                                    </div>
                                    <span class="flex h-11 w-11 items-center justify-center rounded-xl ring-1 {{ $iconColors[$statistic['color']] }}">
                                        @switch($statistic['icon'])
                                            @case('users')
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m16 0v-2a4 4 0 0 0-3-3.87M14 3.13a4 4 0 0 1 0 7.75M14 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" /></svg>
                                                @break
                                            @case('building')
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-3M8 9v.01M8 12v.01M8 15v.01M8 18v.01M16 13v.01M16 16v.01M16 19v.01" /></svg>
                                                @break
                                            @case('briefcase')
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="7" width="18" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18m-11 0v2h4v-2" /></svg>
                                                @break
                                            @case('document')
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 3h7l5 5v13H4V7a4 4 0 0 1 4-4Zm7 0v5h5M8 13h8m-8 4h8" /></svg>
                                                @break
                                            @case('clock')
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" /></svg>
                                                @break
                                            @default
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4" /></svg>
                                        @endswitch
                                    </span>
                                </div>
                                <p class="mt-4 border-t border-slate-100 pt-3 text-xs font-medium text-slate-500">{{ $statistic['description'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section aria-labelledby="shortcuts-title">
                    <div class="mb-4">
                        <h2 id="shortcuts-title" class="text-lg font-bold text-slate-900">Accès rapides</h2>
                        <p class="mt-1 text-sm text-slate-500">Les écrans de gestion seront disponibles dans une prochaine étape.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ([
                            ['Utilisateurs', 'user'],
                            ['Étudiants', 'student'],
                            ['Entreprises', 'company'],
                            ['Offres', 'offer'],
                            ['Candidatures', 'application'],
                            ['Stages', 'internship'],
                            ['Frais de scolarité', 'fees'],
                        ] as [$label, $icon])
                            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-sm font-bold text-slate-600">{{ mb_strtoupper(mb_substr($label, 0, 1)) }}</span>
                                <span class="min-w-0 flex-1 text-sm font-semibold text-slate-800">{{ $label }}</span>
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">À venir</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                <footer class="flex flex-col gap-3 border-t border-slate-200 pt-5 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                    <span>Tableau de bord administrateur · {{ config('app.name', 'Scolarités') }}</span>
                    <a href="{{ route('internships.index') }}" class="font-semibold text-indigo-600 hover:text-indigo-800">Voir le portail des offres</a>
                </footer>
            </div>
        </main>
    </div>
</body>
</html>
