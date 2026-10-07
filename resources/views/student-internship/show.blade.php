<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-indigo-600">Espace étudiant</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-900">Mon stage</h1>
            <p class="mt-2 text-sm text-gray-500">Suivez les étapes et l’avancement de votre stage accepté.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div role="status"
                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <section
            class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 via-white to-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <span
                        class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">Candidature
                        acceptée</span>
                    <h2 class="mt-4 text-xl font-bold text-gray-900">{{ $offer['title'] }}</h2>
                    <p class="mt-1 text-sm font-medium text-gray-600">{{ $offer['company'] }}</p>
                    <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Date de début</dt>
                            <dd class="mt-1 font-medium text-gray-900">
                                {{ $internship->start_date?->format('d/m/Y') ?? 'À définir' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Date de fin</dt>
                            <dd class="mt-1 font-medium text-gray-900">
                                {{ $internship->end_date?->format('d/m/Y') ?? 'À définir' }}
                            </dd>
                        </div>
                    </dl>
                </div>
                <div class="w-full rounded-xl border border-white/80 bg-white/80 p-5 sm:max-w-xs">
                    <div class="flex items-end justify-between gap-3">
                        <p class="text-sm font-semibold text-gray-700">Progression globale</p>
                        <p class="text-2xl font-extrabold text-indigo-700">{{ $progress }}%</p>
                    </div>
                    <div class="mt-3 h-3 overflow-hidden rounded-full bg-indigo-100" role="progressbar"
                        aria-label="Progression globale du stage" aria-valuemin="0" aria-valuemax="100"
                        aria-valuenow="{{ $progress }}">
                        <div class="h-full rounded-full bg-indigo-600 transition-all duration-500"
                            style="width: {{ $progress }}%"></div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Calculée à partir de l’avancement de vos tâches.</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-5 shadow-sm sm:p-6"
            aria-labelledby="admin-observation-title">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 id="admin-observation-title" class="text-base font-bold text-gray-900">Observations de
                        l’administrateur</h2>
                    @if (filled($internship->admin_observation))
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-700">
                            {{ $internship->admin_observation }}</p>
                    @else
                        <p class="mt-2 text-sm text-gray-600">Aucune observation pour le moment</p>
                    @endif
                </div>
                @if (filled($internship->admin_observation) && $internship->updated_at)
                    <p class="shrink-0 text-xs text-gray-500">Mis à jour le
                        {{ $internship->updated_at->format('d/m/Y à H:i') }}</p>
                @endif
            </div>
        </section>

        <section class="space-y-4" aria-labelledby="tasks-title">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="tasks-title" class="text-lg font-bold text-gray-900">Étapes de votre stage</h2>
                    <p class="mt-1 text-sm text-gray-500">Mettez à jour uniquement le pourcentage d’avancement de chaque
                        tâche.</p>
                </div>
                <p class="text-sm font-medium text-gray-600">{{ $internship->tasks->where('progress', 100)->count() }}
                    terminée(s) sur {{ $internship->tasks->count() }}</p>
            </div>

            @foreach ($internship->tasks as $task)
                <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-semibold text-gray-900">{{ $task->name }}</h3>
                                @if ($task->progress === 100)
                                    <span
                                        class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Terminée</span>
                                @elseif ($task->progress > 0)
                                    <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">En
                                        cours</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">À
                                        commencer</span>
                                @endif
                            </div>
                            @if ($task->description)
                                <p class="mt-2 text-sm leading-6 text-gray-600">{{ $task->description }}</p>
                            @endif
                            <div class="mt-4 flex items-center gap-3">
                                <div class="h-2 min-w-24 flex-1 overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-full rounded-full {{ $task->progress === 100 ? 'bg-emerald-500' : 'bg-indigo-500' }} transition-all"
                                        style="width: {{ $task->progress }}%"></div>
                                </div>
                                <span
                                    class="w-12 text-right text-sm font-semibold text-gray-700">{{ $task->progress }}%</span>
                            </div>
                            @if ($task->planned_date)
                                <p class="mt-3 text-xs text-gray-500">Date prévue : {{ $task->planned_date->format('d/m/Y') }}
                                </p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('student.internship.tasks.update', $task) }}"
                            class="flex shrink-0 items-end gap-2">
                            @csrf
                            @method('PATCH')
                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold text-gray-600">Avancement</span>
                                <select name="progress"
                                    class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach ([0, 20, 40, 60, 80, 100] as $value)
                                        <option value="{{ $value }}" @selected($task->progress === $value)>{{ $value }}%</option>
                                    @endforeach
                                </select>
                            </label>
                            <button type="submit"
                                class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Enregistrer</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </section>
    </div>
</x-app-layout>