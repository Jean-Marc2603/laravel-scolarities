<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-indigo-600">Espace étudiant</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-900">Mes candidatures</h1>
                <p class="mt-2 text-sm text-gray-500">Retrouvez vos demandes de stage et leur statut.</p>
            </div>
            <a href="{{ route('internships.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Parcourir les offres</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
        @endif

        @forelse ($applications as $application)
            @php($offer = $application->offer)
            <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col gap-5 p-5 sm:flex-row sm:items-start sm:justify-between sm:p-6">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $offer['domain'] }}</span>
                            @if ($application->status === \App\Models\InternshipApplication::STATUS_ACCEPTED)
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">Acceptée</span>
                            @elseif ($application->status === \App\Models\InternshipApplication::STATUS_REJECTED)
                                <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800">Refusée</span>
                            @else
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-900">En attente</span>
                            @endif
                        </div>
                        <h2 class="mt-3 text-lg font-bold text-gray-900">{{ $offer['title'] }}</h2>
                        <p class="mt-1 text-sm font-medium text-gray-600">{{ $offer['company'] }} · {{ $offer['location'] }} · {{ $offer['duration'] }}</p>
                        <p class="mt-3 text-sm leading-6 text-gray-600">{{ $offer['description'] }}</p>
                        <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Date de candidature</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-900">{{ $application->applied_at->format('d/m/Y à H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Compatibilité CV</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-900">{{ $application->compatibility_score !== null ? $application->compatibility_score . ' %' : 'Non évaluée (CV non analysé)' }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div class="flex shrink-0 flex-wrap items-center gap-2 sm:flex-col sm:items-stretch">
                        <a href="{{ route('applications.show', $application->id) }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Voir l’offre</a>
                        @if ($application->status === \App\Models\InternshipApplication::STATUS_PENDING)
                            <form method="POST" action="{{ route('applications.destroy', $application->id) }}" onsubmit="return confirm('Voulez-vous vraiment annuler cette candidature ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full rounded-lg px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">Annuler</button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <section class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-500">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 4h8l4 4v12H4V4h4Zm0 0v5h8V4m-8 9h8m-8 4h5" /></svg>
                </div>
                <h2 class="mt-4 text-base font-semibold text-gray-900">Vous n’avez pas encore postulé</h2>
                <p class="mt-2 text-sm text-gray-500">Parcourez les offres disponibles et envoyez votre première candidature.</p>
                <a href="{{ route('internships.index') }}" class="mt-5 inline-flex rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">Voir les offres</a>
            </section>
        @endforelse
    </div>
</x-app-layout>
