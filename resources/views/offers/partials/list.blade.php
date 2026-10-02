<div class="mx-auto max-w-7xl px-4 pb-16 pt-8 sm:px-6 lg:px-8" x-data="{ toast: '' }">
    @if (session('status'))
        <div role="status"
            class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <section class="relative overflow-hidden rounded-3xl bg-gray-950 px-6 py-8 text-white shadow-xl sm:px-10 sm:py-10">
        <div class="absolute -right-16 -top-24 h-72 w-72 rounded-full bg-indigo-500/20 blur-3xl" aria-hidden="true">
        </div>
        <div class="absolute -bottom-28 right-1/3 h-56 w-56 rounded-full bg-sky-400/10 blur-3xl" aria-hidden="true">
        </div>
        <div class="relative max-w-3xl">
            <span
                class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-indigo-100">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                Des opportunités pour votre avenir
            </span>
            <h2 class="mt-5 text-3xl font-bold tracking-tight sm:text-4xl">Le prochain chapitre de votre carrière
                commence ici.</h2>
            <p class="mt-4 max-w-2xl text-sm leading-6 text-gray-300 sm:text-base">Explorez des stages dans plusieurs
                domaines, développez vos compétences et trouvez une équipe prête à vous accueillir.</p>
            <div class="mt-7 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-300">
                <span class="inline-flex items-center gap-2"><svg class="h-4 w-4 text-indigo-300" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                    </svg>Offres dans 10 domaines</span>
                <span class="inline-flex items-center gap-2"><svg class="h-4 w-4 text-indigo-300" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                    </svg>Candidature bientôt disponible</span>
            </div>
        </div>
    </section>

    <section class="mt-8 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5"
        aria-label="Rechercher et filtrer les offres">
        <form method="GET" action="{{ route('internships.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <label class="relative block lg:col-span-1">
                <span class="sr-only">Rechercher par titre ou domaine</span>
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" />
                    <path stroke-linecap="round" d="m16 16 4 4" />
                </svg>
                <input type="search" name="q" value="{{ $filters['search'] }}" placeholder="Titre ou domaine…"
                    class="w-full rounded-xl border-gray-200 py-3 pl-10 pr-3 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </label>
            <label>
                <span class="sr-only">Domaine</span>
                <select name="domaine"
                    class="w-full rounded-xl border-gray-200 py-3 text-sm text-gray-700 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Tous les domaines</option>
                    @foreach ($domains as $option)
                        <option value="{{ $option }}" @selected($filters['domain'] === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="sr-only">Localisation</span>
                <select name="localisation"
                    class="w-full rounded-xl border-gray-200 py-3 text-sm text-gray-700 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Toutes les localisations</option>
                    @foreach ($locations as $option)
                        <option value="{{ $option }}" @selected($filters['location'] === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="sr-only">Durée</span>
                <select name="duree"
                    class="w-full rounded-xl border-gray-200 py-3 text-sm text-gray-700 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Toutes les durées</option>
                    @foreach ($durations as $option)
                        <option value="{{ $option }}" @selected($filters['duration'] === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex flex-wrap items-center gap-3 sm:col-span-2 lg:col-span-4">
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path stroke-linecap="round" d="m16 16 4 4" />
                    </svg>
                    Rechercher
                </button>
                @if ($hasCvAnalysis)
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="whitespace-nowrap">Trier par</span>
                        <select name="tri"
                            class="rounded-xl border-gray-200 py-2.5 text-sm text-gray-700 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Ordre initial</option>
                            <option value="compatibilite" @selected($filters['sort'] === 'compatibilite')>Meilleure
                                compatibilité</option>
                        </select>
                    </label>
                @endif
                @if ($filters['search'] !== '' || $filters['domain'] !== '' || $filters['location'] !== '' || $filters['duration'] !== '')
                    <a href="{{ route('internships.index') }}"
                        class="text-sm font-medium text-gray-500 transition hover:text-gray-900">Effacer les filtres</a>
                @endif
                <p class="ml-auto text-sm text-gray-500"><span
                        class="font-semibold text-gray-900">{{ count($offers) }}</span>
                    {{ count($offers) > 1 ? 'offres trouvées' : 'offre trouvée' }}
                </p>
            </div>
        </form>
    </section>

    @auth
        @if (!$hasCvAnalysis)
            <div
                class="mt-6 flex flex-col gap-3 rounded-2xl border border-indigo-100 bg-indigo-50 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold text-indigo-900">Comparez les offres avec votre profil</p>
                    <p class="mt-1 text-sm text-indigo-800">Déposez et analysez votre CV pour afficher les scores, les
                        compétences correspondantes et celles à développer.</p>
                </div>
                <a href="{{ route('cv.index') }}"
                    class="inline-flex shrink-0 items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">Analyser
                    mon CV</a>
            </div>
        @endif
    @else
        <div class="mt-6 rounded-2xl border border-indigo-100 bg-indigo-50 p-5">
            <p class="font-semibold text-indigo-900">Comparez les offres avec votre profil</p>
            <p class="mt-1 text-sm text-indigo-800">Connectez-vous et analysez votre CV pour obtenir vos scores de
                compatibilité personnalisés.</p>
        </div>
    @endauth

    @if (count($offers))
        <section class="mt-8 grid items-stretch gap-5 lg:grid-cols-2" aria-label="Offres de stage">
            @foreach ($offers as $offer)
                <article id="offre-{{ $loop->iteration }}" x-data="{ expanded: false }"
                    class="flex h-full flex-col rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-start gap-3.5">
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-sm font-bold text-indigo-700">
                                {{ collect(explode(' ', $offer['company']))->map(fn($part) => mb_substr($part, 0, 1))->take(2)->join('') }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold leading-6 text-gray-900">{{ $offer['title'] }}</h3>
                                <p class="mt-1 truncate text-sm font-medium text-gray-600">{{ $offer['company'] }}</p>
                            </div>
                        </div>
                        <span
                            class="shrink-0 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">Stage</span>
                    </div>

                    @if (isset($offer['compatibility']))
                        <div class="mt-5 rounded-xl border border-indigo-100 bg-indigo-50/70 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-gray-900">Compatibilité avec votre CV</p>
                                <span
                                    class="rounded-full bg-white px-3 py-1 text-sm font-bold text-indigo-700 shadow-sm">{{ $offer['compatibility']['score'] }}
                                    %</span>
                            </div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-white" role="progressbar"
                                aria-label="Compatibilité avec votre CV" aria-valuemin="0" aria-valuemax="100"
                                aria-valuenow="{{ $offer['compatibility']['score'] }}">
                                <div class="h-full rounded-full bg-indigo-600 transition-all"
                                    style="width: {{ $offer['compatibility']['score'] }}%"></div>
                            </div>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Compétences
                                        correspondantes</p>
                                    @if (count($offer['compatibility']['matched']))
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            @foreach ($offer['compatibility']['matched'] as $skill)
                                                <span
                                                    class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800">{{ $skill }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="mt-2 text-xs text-gray-500">Aucune compétence commune détectée.</p>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Compétences manquantes
                                    </p>
                                    @if (count($offer['compatibility']['missing']))
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            @foreach ($offer['compatibility']['missing'] as $skill)
                                                <span
                                                    class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-900">{{ $skill }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="mt-2 text-xs text-gray-500">Toutes les compétences demandées sont détectées.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-2">
                        <span
                            class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">{{ $offer['domain'] }}</span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-xs font-medium text-gray-500">
                        <span class="inline-flex items-center gap-1.5"><svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                <circle cx="12" cy="10" r="2.5" />
                            </svg>{{ $offer['location'] }}</span>
                        <span class="inline-flex items-center gap-1.5"><svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />
                            </svg>{{ $offer['duration'] }}</span>
                    </div>

                    <p class="mt-4 flex-1 text-sm leading-6 text-gray-600">{{ $offer['description'] }}</p>

                    <div class="mt-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Compétences recherchées</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($offer['skills'] as $skill)
                                <span
                                    class="rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-700">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="expanded" x-transition class="mt-4 rounded-xl bg-gray-50 p-4 text-sm leading-6 text-gray-600">
                        <p class="font-semibold text-gray-800">À propos du stage</p>
                        <p class="mt-1">{{ $offer['details'] }}</p>
                    </div>

                    <div
                        class="mt-5 flex flex-col gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-gray-500">Candidatures jusqu’au <time
                                datetime="{{ $offer['deadline']->toDateString() }}"
                                class="font-semibold text-gray-700">{{ $offer['deadline']->locale('fr')->translatedFormat('j F Y') }}</time>
                        </p>
                        <div class="flex shrink-0 items-center gap-2">
                            <button type="button" @click="expanded = !expanded"
                                class="rounded-lg px-3 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
                                x-text="expanded ? 'Réduire' : 'Voir l’offre'">Voir l’offre</button>
                            <button type="button"
                                @click="toast = 'La fonctionnalité de candidature sera disponible prochainement.'; window.setTimeout(() => toast = '', 4000)"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Postuler</button>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @else
        <section class="mt-8 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-500">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    aria-hidden="true">
                    <circle cx="11" cy="11" r="7" />
                    <path stroke-linecap="round" d="m16 16 4 4" />
                </svg>
            </div>
            <h3 class="mt-4 text-base font-semibold text-gray-900">Aucune offre ne correspond à votre recherche</h3>
            <p class="mt-2 text-sm text-gray-500">Essayez un autre titre, domaine ou filtre de recherche.</p>
            <a href="{{ route('internships.index') }}"
                class="mt-5 inline-flex rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700">Voir
                toutes les offres</a>
        </section>
    @endif

    <div x-cloak x-show="toast" x-transition role="status" aria-live="polite"
        class="fixed bottom-5 right-5 z-50 flex max-w-sm items-start gap-3 rounded-xl bg-gray-900 px-5 py-4 text-sm font-medium text-white shadow-2xl">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
        </svg>
        <span x-text="toast"></span>
    </div>
</div>