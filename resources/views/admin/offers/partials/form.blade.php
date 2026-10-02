<form method="POST" action="{{ $formAction }}" class="space-y-6">
    @csrf
    @if ($formMethod !== 'POST')
        @method($formMethod)
    @endif

    @if ($errors->any())
        <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <p class="font-semibold">Veuillez corriger les champs signalés :</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="title" class="block text-sm font-semibold text-slate-700">Titre de l’offre</label>
            <input id="title" name="title" type="text" maxlength="255" required
                value="{{ old('title', $offer->title) }}"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="company" class="block text-sm font-semibold text-slate-700">Entreprise</label>
            <input id="company" name="company" type="text" maxlength="255" required
                value="{{ old('company', $offer->company) }}"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="domain" class="block text-sm font-semibold text-slate-700">Domaine</label>
            <select id="domain" name="domain" required
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Choisir un domaine</option>
                @foreach (['Développement web', 'Développement logiciel', 'Réseaux et systèmes', 'Cybersécurité', 'Data / Intelligence artificielle', 'Marketing digital', 'Finance / Comptabilité', 'Ressources humaines', 'Gestion de projet', 'Design / UI-UX', 'Autre'] as $domain)
                    <option value="{{ $domain }}" @selected(old('domain', $offer->domain) === $domain)>{{ $domain }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="location" class="block text-sm font-semibold text-slate-700">Localisation</label>
            <input id="location" name="location" type="text" maxlength="255" required
                value="{{ old('location', $offer->location) }}"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="duration" class="block text-sm font-semibold text-slate-700">Durée</label>
            <input id="duration" name="duration" type="text" maxlength="100" required placeholder="Ex. 3 mois"
                value="{{ old('duration', $offer->duration) }}"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div class="sm:col-span-2">
            <label for="description" class="block text-sm font-semibold text-slate-700">Description courte</label>
            <textarea id="description" name="description" rows="3" maxlength="3000" required
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $offer->description) }}</textarea>
        </div>
        <div class="sm:col-span-2">
            <label for="skills" class="block text-sm font-semibold text-slate-700">Compétences recherchées</label>
            <textarea id="skills" name="skills" rows="2" required placeholder="Ex. Laravel, PHP, MySQL, Git"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('skills', is_array($offer->skills) ? implode(', ', $offer->skills) : $offer->skills) }}</textarea>
            <p class="mt-1.5 text-xs text-slate-500">Séparez les compétences par une virgule, un point-virgule ou un
                retour à la ligne ; elles serviront aussi à la comparaison CV/offre.</p>
        </div>
        <div>
            <label for="deadline" class="block text-sm font-semibold text-slate-700">Date limite de candidature</label>
            <input id="deadline" name="deadline" type="date" required
                value="{{ old('deadline', $offer->deadline ? \Illuminate\Support\Carbon::parse($offer->deadline)->format('Y-m-d') : '') }}"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
            <input id="is_active" name="is_active" type="hidden" value="0">
            <input id="is_active_checkbox" name="is_active" type="checkbox" value="1" @checked((bool) old('is_active', $offer->is_active ?? true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            <label for="is_active_checkbox" class="text-sm font-semibold text-slate-700">Offre publiée et visible aux
                étudiants</label>
        </div>
        <div class="sm:col-span-2">
            <label for="details" class="block text-sm font-semibold text-slate-700">Détails de l’offre</label>
            <textarea id="details" name="details" rows="5" maxlength="10000" required
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('details', $offer->details) }}</textarea>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-between">
        <a href="{{ route('admin.offers.index') }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Annuler</a>
        <button type="submit"
            class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ $formMethod === 'POST' ? 'Créer l’offre' : 'Enregistrer les modifications' }}</button>
    </div>
</form>