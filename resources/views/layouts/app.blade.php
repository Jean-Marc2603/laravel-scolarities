<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
</head>

<body class="font-sans antialiased">
    <x-jet-banner />

    <div class="flex min-h-screen bg-gray-100" x-data="{ sidebarCollapsed: false, mobileSidebarOpen: false }">
        <div x-show="mobileSidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-gray-900/40 md:hidden"
            @click="mobileSidebarOpen = false" aria-hidden="true"></div>

        <aside :class="[
                    sidebarCollapsed ? 'md:w-20' : 'md:w-64',
                    mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full'
                ]"
            class="fixed left-0 top-0 z-50 flex h-screen w-64 flex-col border-r border-gray-200 bg-white transition-all duration-200 md:translate-x-0">
            <div class="flex h-16 items-center justify-between border-b border-gray-100 px-4">
                <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3" aria-label="Tableau de bord">
                    <x-jet-application-mark class="h-8 w-auto shrink-0" />
                    <span x-show="!sidebarCollapsed" x-transition.opacity
                        class="truncate text-sm font-semibold text-gray-800">
                        Gestion de stage
                    </span>
                </a>
                <button type="button" @click="sidebarCollapsed = !sidebarCollapsed"
                    class="hidden h-9 w-9 shrink-0 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 md:inline-flex"
                    :aria-label="sidebarCollapsed ? 'Développer la barre latérale' : 'Réduire la barre latérale'"
                    :title="sidebarCollapsed ? 'Développer la barre latérale' : 'Réduire la barre latérale'">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        aria-hidden="true">
                        <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                        <path stroke-linecap="round" d="M9 6v12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5" aria-label="Navigation principale">
                <a href="{{ route('dashboard') }}" @click="mobileSidebarOpen = false" @class([
                    'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                    'bg-indigo-50 text-indigo-700' => request()->routeIs('dashboard'),
                    'text-gray-600 hover:bg-gray-100 hover:text-gray-900' => !request()->routeIs('dashboard'),
                ]) :class="sidebarCollapsed ? 'md:justify-center md:px-0' : ''"
                    :title="sidebarCollapsed ? 'Tableau de bord' : ''" @if (request()->routeIs('dashboard'))
                    aria-current="page" @endif>
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-6v-7h-4v7H4a1 1 0 0 1-1-1V10Z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity>Tableau de bord</span>
                </a>

                <a href="{{ route('internships.index') }}" @click="mobileSidebarOpen = false" @class([
                    'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                    'bg-indigo-50 text-indigo-700' => request()->routeIs('internships.*'),
                    'text-gray-600 hover:bg-gray-100 hover:text-gray-900' => !request()->routeIs('internships.*'),
                ])
                    :class="sidebarCollapsed ? 'md:justify-center md:px-0' : ''"
                    :title="sidebarCollapsed ? 'Offres de stage' : ''" @if (request()->routeIs('internships.*'))
                    aria-current="page" @endif>
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8" aria-hidden="true">
                        <rect x="3" y="7" width="18" height="14" rx="2" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18m-11 0v2h4v-2" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity>Offres de stage</span>
                </a>

                @php
                    $hasAcceptedInternship = Auth::check()
                        && Auth::user()->role === 'student'
                        && Auth::user()->internshipApplications()
                            ->where('status', \App\Models\InternshipApplication::STATUS_ACCEPTED)
                            ->exists();
                @endphp
                <a @if ($hasAcceptedInternship) href="{{ route('student.internship.show') }}" @else href="#"
                aria-disabled="true" @endif @if (!$hasAcceptedInternship) @click.prevent @endif @class([
                        'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                        'bg-indigo-50 text-indigo-700' => request()->routeIs('student.internship.*'),
                        'text-gray-600 hover:bg-gray-100 hover:text-gray-900' => $hasAcceptedInternship && !request()->routeIs('student.internship.*'),
                        'cursor-not-allowed text-gray-400' => !$hasAcceptedInternship,
                    ]) :class="sidebarCollapsed ? 'md:justify-center md:px-0' : ''"
                    :title="sidebarCollapsed ? 'Mon stage' : ''" @if (request()->routeIs('student.internship.*'))
                    aria-current="page" @endif>
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-3M8 9v.01M8 12v.01M8 15v.01M8 18v.01M16 13v.01M16 16v.01M16 19v.01" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity>Mon stage</span>
                </a>

                <a href="{{ route('applications.index') }}" @click="mobileSidebarOpen = false" @class([
                    'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                    'bg-indigo-50 text-indigo-700' => request()->routeIs('applications.*'),
                    'text-gray-600 hover:bg-gray-100 hover:text-gray-900' => !request()->routeIs('applications.*'),
                ])
                    :class="sidebarCollapsed ? 'md:justify-center md:px-0' : ''"
                    :title="sidebarCollapsed ? 'Mes candidatures' : ''" @if (request()->routeIs('applications.*'))
                    aria-current="page" @endif>
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 4h8l4 4v12H4V4h4Zm0 0v5h8V4m-8 9h8m-8 4h5" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity>Mes candidatures</span>
                </a>

                <a href="{{ route('cv.index') }}" @click="mobileSidebarOpen = false" @class([
                    'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                    'bg-indigo-50 text-indigo-700' => request()->routeIs('cv.*'),
                    'text-gray-600 hover:bg-gray-100 hover:text-gray-900' => !request()->routeIs('cv.*'),
                ]) :class="sidebarCollapsed ? 'md:justify-center md:px-0' : ''"
                    :title="sidebarCollapsed ? 'Mon CV' : ''" @if (request()->routeIs('cv.*')) aria-current="page"
                    @endif>
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5M9 13h6m-6 4h6" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity>Mon CV</span>
                </a>
            </nav>

            <div class="space-y-1 border-t border-gray-100 px-3 py-4">
                <a href="{{ route('profile.show') }}" @click="mobileSidebarOpen = false" @class([
                    'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                    'bg-indigo-50 text-indigo-700' => request()->routeIs('profile.*'),
                    'text-gray-600 hover:bg-gray-100 hover:text-gray-900' => !request()->routeIs('profile.*'),
                ]) :class="sidebarCollapsed ? 'md:justify-center md:px-0' : ''"
                    :title="sidebarCollapsed ? 'Mon profil' : ''" @if (request()->routeIs('profile.*'))
                    aria-current="page" @endif>
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="8" r="4" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 21a8 8 0 0 1 16 0" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity>Mon profil</span>
                </a>
                <a href="{{ route('settings') }}" @click="mobileSidebarOpen = false" @class([
                    'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                    'bg-indigo-50 text-indigo-700' => request()->routeIs('settings*'),
                    'text-gray-600 hover:bg-gray-100 hover:text-gray-900' => !request()->routeIs('settings*'),
                ]) :class="sidebarCollapsed ? 'md:justify-center md:px-0' : ''"
                    :title="sidebarCollapsed ? 'Paramètres' : ''" @if (request()->routeIs('settings*'))
                    aria-current="page" @endif>
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="12" r="3" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m19.4 15 .1.1a1.7 1.7 0 1 1-2.4 2.4l-.1-.1a1.7 1.7 0 0 0-2.9 1.2v.2a1.7 1.7 0 1 1-3.4 0v-.2a1.7 1.7 0 0 0-2.9-1.2l-.1.1a1.7 1.7 0 1 1-2.4-2.4l.1-.1a1.7 1.7 0 0 0-1.2-2.9H4a1.7 1.7 0 1 1 0-3.4h.2a1.7 1.7 0 0 0 1.2-2.9l-.1-.1a1.7 1.7 0 1 1 2.4-2.4l.1.1a1.7 1.7 0 0 0 2.9-1.2V2a1.7 1.7 0 1 1 3.4 0v.2a1.7 1.7 0 0 0 2.9 1.2l.1-.1a1.7 1.7 0 1 1 2.4 2.4l-.1.1a1.7 1.7 0 0 0 1.2 2.9h.2a1.7 1.7 0 1 1 0 3.4h-.2a1.7 1.7 0 0 0-1.2 2.9Z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity>Paramètres</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" @click="mobileSidebarOpen = false"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
                        :class="sidebarCollapsed ? 'md:justify-center md:px-0' : ''"
                        :title="sidebarCollapsed ? 'Déconnexion' : ''">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10 17l5-5-5-5m5 5H3m9-9h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7" />
                        </svg>
                        <span x-show="!sidebarCollapsed" x-transition.opacity>Déconnexion</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="hidden shrink-0 transition-[width] duration-200 md:block"
            :style="sidebarCollapsed ? 'width: 5rem' : 'width: 16rem'" aria-hidden="true"></div>

        <div class="flex min-w-0 flex-1 flex-col">
            <div class="flex h-14 items-center border-b border-gray-200 bg-white px-4 md:hidden">
                <button type="button" @click="mobileSidebarOpen = true"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    aria-label="Ouvrir le menu">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        aria-hidden="true">
                        <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <span class="ml-3 text-sm font-semibold text-gray-800">Navigation</span>
            </div>

            <header class="bg-white shadow-sm">
                <div
                    class="mx-auto flex min-h-16 max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                    <div class="min-w-0 flex-1">
                        @if (isset($header))
                            {{ $header }}
                        @endif
                    </div>
                    @include('offers.partials.theme-toggle')
                </div>
            </header>

            <main class="min-w-0 flex-1">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('modals')

    @livewireScripts
</body>

</html>