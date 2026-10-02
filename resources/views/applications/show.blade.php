<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-indigo-600">Détail de la candidature</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-900">{{ $offer['title'] }}</h1>
                <p class="mt-2 text-sm text-gray-500">{{ $offer['company'] }} · {{ $offer['domain'] }}</p>
            </div>
            <a href="{{ route('applications.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Retour
                à mes candidatures</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('error'))
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ session('error') }}</div>
        @endif

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-wrap gap-2">
                <span
                    class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $offer['domain'] }}</span>
                <span
                    class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">{{ $offer['location'] }}</span>
                <span
                    class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">{{ $offer['duration'] }}</span>
            </div>
            <h2 class="mt-5 text-lg font-semibold text-gray-900">À propos de l’offre</h2>
            <p class="mt-2 text-sm leading-7 text-gray-600">{{ $offer['details'] }}</p>
            <p class="mt-3 text-sm leading-7 text-gray-600">{{ $offer['description'] }}</p>

            <h3 class="mt-6 text-sm font-semibold text-gray-900">Compétences recherchées</h3>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($offer['skills'] as $skill)
                    <span
                        class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700">{{ $skill }}</span>
                @endforeach
            </div>

            <dl class="mt-7 grid gap-4 border-t border-gray-100 pt-5 sm:grid-cols-3">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Entreprise</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $offer['company'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Candidature envoyée</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">
                        {{ $application->applied_at->format('d/m/Y à H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Compatibilité CV</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">
                        {{ $application->compatibility_score !== null ? $application->compatibility_score . ' %' : 'Non évaluée' }}
                    </dd>
                </div>
            </dl>
        </section>

        <section
            class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Statut de votre candidature</p>
                @if ($application->status === \App\Models\InternshipApplication::STATUS_ACCEPTED)
                    <p
                        class="mt-2 inline-flex rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-800">
                        Acceptée</p>
                @elseif ($application->status === \App\Models\InternshipApplication::STATUS_REJECTED)
                    <p class="mt-2 inline-flex rounded-full bg-rose-100 px-3 py-1 text-sm font-semibold text-rose-800">
                        Refusée</p>
                @else
                    <p class="mt-2 inline-flex rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-900">En
                        attente</p>
                @endif
            </div>
            @if ($application->status === \App\Models\InternshipApplication::STATUS_PENDING)
                <form method="POST" action="{{ route('applications.destroy', $application->id) }}"
                    onsubmit="return confirm('Voulez-vous vraiment annuler cette candidature ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="rounded-lg border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">Annuler
                        la candidature</button>
                </form>
            @endif
        </section>
    </div>
</x-app-layout>