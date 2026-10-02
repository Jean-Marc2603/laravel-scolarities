@auth
    <x-app-layout>
        <x-slot name="header">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-indigo-600">Carrière &amp; formation</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-900">Offres de stage</h1>
                    <p class="mt-2 text-sm text-gray-500">Trouvez une expérience qui vous ressemble et lancez votre parcours
                        professionnel.</p>
                </div>
            </div>
        </x-slot>

        @include('offers.partials.list')
    </x-app-layout>
@else
    <x-guest-layout>
        <div class="min-h-screen bg-gray-50">
            <header class="border-b border-gray-200 bg-white">
                <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 text-sm font-bold text-gray-900">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                aria-hidden="true">
                                <rect x="3" y="7" width="18" height="14" rx="2" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18" />
                            </svg>
                        </span>
                        <span>{{ config('app.name', 'Scolarités') }}</span>
                    </a>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('login') }}"
                            class="rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">Se
                            connecter</a>
                    </div>
                </div>
            </header>
            <main>
                @include('offers.partials.list')
            </main>
        </div>
    </x-guest-layout>
@endauth