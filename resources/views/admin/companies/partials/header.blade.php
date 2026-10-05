<header class="sticky top-0 z-10 border-b border-slate-200/80 bg-white/90 backdrop-blur">
    <div class="mx-auto flex min-h-16 max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <div>
            <a href="{{ route('admin.dashboard') }}"
                class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">← Tableau de bord</a>
            <h1 class="mt-1 text-lg font-bold tracking-tight text-slate-900">{{ $pageTitle }}</h1>
        </div>
        <div class="flex items-center gap-3">
            <span class="hidden text-sm font-medium text-slate-500 sm:inline">{{ Auth::user()->name }}</span>
            @include('offers.partials.theme-toggle')
        </div>
    </div>
</header>