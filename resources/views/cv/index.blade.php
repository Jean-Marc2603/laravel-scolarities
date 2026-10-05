<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-indigo-600">Espace étudiant</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-900">Mon CV</h1>
            <p class="mt-2 text-sm text-gray-500">Déposez votre CV et obtenez un aperçu intelligent de votre profil.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8"
        x-data="{ analyzing: false, submitAnalysis(form) { if (this.analyzing) return; this.analyzing = true; setTimeout(() => form.submit(), 5000); } }">
        @if (session('status'))
            <div role="status"
                class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->has('cv') || $errors->has('analysis'))
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                @foreach ($errors->get('cv') as $message)
                    <p>{{ $message }}</p>
                @endforeach
                @foreach ($errors->get('analysis') as $message)
                    <p>{{ $message }}</p>
                @endforeach
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-5 sm:px-8">
                <h2 class="text-lg font-semibold text-gray-900">Votre document</h2>
                <p class="mt-1 text-sm text-gray-500">Formats acceptés : PDF et DOCX, jusqu’à 10 Mo.</p>
            </div>

            <div class="space-y-5 p-6 sm:p-8">
                @if ($cv)
                    <div
                        class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-gray-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-center gap-4">
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5M9 13h6m-6 4h6" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-900">{{ $cv->original_name }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ number_format($cv->size_bytes / 1048576, 2) }} Mo ·
                                    ajouté le {{ $cv->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            <a href="{{ route('cv.download') }}"
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3" />
                                </svg>
                                Ouvrir le CV
                            </a>
                            <form method="POST" action="{{ route('cv.destroy') }}"
                                onsubmit="return confirm('Supprimer le CV enregistré et son analyse ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="rounded-lg px-3 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50">Supprimer</button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-8 text-center">
                        <div
                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 16V4m0 0L8 8m4-4 4 4M5 14v5h14v-5" />
                            </svg>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-gray-900">Aucun CV enregistré</p>
                        <p class="mt-1 text-sm text-gray-500">Déposez votre document pour commencer.</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('cv.store') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <label for="cv"
                        class="block text-sm font-medium text-gray-700">{{ $cv ? 'Remplacer votre CV' : 'Déposer votre CV' }}</label>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <input id="cv" name="cv" type="file"
                            accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                            required
                            class="block w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-700 file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-gray-700 hover:file:bg-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                        <button type="submit"
                            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 16V4m0 0L8 8m4-4 4 4M5 14v5h14v-5" />
                            </svg>
                            {{ $cv ? 'Remplacer le CV' : 'Enregistrer le CV' }}
                        </button>
                    </div>
                    <p class="text-xs text-gray-500">Votre document reste privé et n’est accessible qu’à votre compte.
                    </p>
                </form>
            </div>
        </section>

        @if ($cv)
            <section
                class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 via-white to-white p-6 shadow-sm sm:p-8">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m12 3 1.7 5.3L19 10l-5.3 1.7L12 17l-1.7-5.3L5 10l5.3-1.7L12 3Zm6 12 .9 2.1L21 18l-2.1.9L18 21l-.9-2.1L15 18l2.1-.9L18 15Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Analyse de votre CV</h2>
                            <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-600">L’analyse intelligente extrait les
                                informations du document et repère les compétences à l’aide d’une liste de mots-clés
                                intégrée. Aucune API d’intelligence artificielle externe n’est utilisée.</p>
                            @if ($cv->last_analyzed_at)
                                <p class="mt-2 text-xs text-gray-500">Dernière analyse :
                                    {{ $cv->last_analyzed_at->format('d/m/Y à H:i') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <form method="POST" action="{{ route('cv.analyze') }}" @submit.prevent="submitAnalysis($el)">
                        @csrf
                        <button type="submit" :disabled="analyzing"
                            class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-wait disabled:opacity-75 sm:w-auto">
                            <svg x-show="!analyzing" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m12 3 1.7 5.3L19 10l-5.3 1.7L12 17l-1.7-5.3L5 10l5.3-1.7L12 3Z" />
                            </svg>
                            <svg x-show="analyzing" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"
                                aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 0 1 8-8V1C5.9 1 1 5.9 1 12h3Zm2.3 5.7A8 8 0 0 1 4 12H1c0 3 1.2 5.7 3.3 7.8l2-2.1Z" />
                            </svg>
                            <span x-text="analyzing ? 'Analyse en cours...' : 'Analyser mon CV'">Analyser mon CV</span>
                        </button>
                    </form>
                </div>
            </section>
        @endif

        @if ($cv && $cv->analysis_results)
        @php($analysis = $cv->analysis_results)
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div
                class="flex flex-col gap-3 border-b border-gray-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Résultats</p>
                    <h2 class="mt-1 text-xl font-bold text-gray-900">Profil détecté</h2>
                </div>
                <span
                    class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Profil analysé avec succès
                </span>
            </div>

            <div class="grid gap-6 p-6 sm:grid-cols-2 sm:p-8">
                <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-5 sm:col-span-2">
                    <h3 class="text-sm font-semibold text-gray-900">Informations du profil</h3>
                    <dl class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ([
                                'Nom' => $analysis['name'] ?? null,
                                'Email' => $analysis['email'] ?? null,
                                'Téléphone' => $analysis['phone'] ?? null,
                                'Formation' => $analysis['formation'] ?? null,
                                'Niveau d’études' => $analysis['study_level'] ?? null,
                                'Domaine' => $analysis['domain'] ?? null,
                            ] as $label => $value)
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $label }}</dt>
                                <dd class="mt-1 break-words text-sm font-medium text-gray-900">{{ $value ?: 'Non détecté' }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="rounded-xl border border-gray-100 p-5">
                    <h3 class="text-sm font-semibold text-gray-900">Compétences détectées</h3>
                    @if (!empty($analysis['skills']))
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($analysis['skills'] as $skill)
                                <span
                                    class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">{{ $skill }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 text-sm text-gray-500">Aucune compétence de la liste n’a été détectée.</p>
                    @endif
                </div>

                <div class="rounded-xl border border-gray-100 p-5">
                    <h3 class="text-sm font-semibold text-gray-900">Technologies détectées</h3>
                    @if (!empty($analysis['technologies']))
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($analysis['technologies'] as $technology)
                                <span
                                    class="rounded-full bg-sky-50 px-3 py-1 text-xs font-medium text-sky-700">{{ $technology }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 text-sm text-gray-500">Aucune technologie de la liste n’a été détectée.</p>
                    @endif
                </div>

                <div class="rounded-xl border border-gray-100 p-5">
                    <h3 class="text-sm font-semibold text-gray-900">Expériences</h3>
                    @if (!empty($analysis['experiences']))
                        <ul class="mt-3 space-y-2 text-sm leading-6 text-gray-600">
                            @foreach ($analysis['experiences'] as $experience)
                                <li class="flex gap-2"><span
                                        class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-500"></span><span>{{ $experience }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-3 text-sm text-gray-500">Aucune expérience clairement identifiée.</p>
                    @endif
                </div>

                <div class="rounded-xl border border-gray-100 p-5">
                    <h3 class="text-sm font-semibold text-gray-900">Langues</h3>
                    @if (!empty($analysis['languages']))
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($analysis['languages'] as $language)
                                <span
                                    class="rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-800">{{ $language }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 text-sm text-gray-500">Aucune langue connue détectée.</p>
                    @endif
                </div>

                @if (!empty($analysis['interests']))
                    <div class="rounded-xl border border-gray-100 p-5 sm:col-span-2">
                        <h3 class="text-sm font-semibold text-gray-900">Centres d’intérêt</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-600">{{ $analysis['interests'] }}</p>
                    </div>
                @endif
            </div>
            <p class="border-t border-gray-100 px-6 py-4 text-xs leading-5 text-gray-500 sm:px-8">Les informations sont
                extraites automatiquement du texte du document. Vérifiez-les, car une mise en page complexe ou un PDF
                scanné peut limiter la détection.</p>
        </section>
        @endif
    </div>
</x-app-layout>