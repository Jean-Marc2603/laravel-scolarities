<form method="POST" action="{{ $formAction }}" class="space-y-6">
    @csrf
    @if ($formMethod !== 'POST')
        @method($formMethod)
    @endif

    @if ($errors->any())
        <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <p class="font-semibold">Veuillez vérifier les champs signalés :</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="matricule" class="block text-sm font-semibold text-slate-700">Matricule</label>
            <input id="matricule" name="matricule" type="text" maxlength="10" required
                value="{{ old('matricule', $student->matricule) }}"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="nom" class="block text-sm font-semibold text-slate-700">Nom</label>
            <input id="nom" name="nom" type="text" maxlength="255" required value="{{ old('nom', $student->nom) }}"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="prenom" class="block text-sm font-semibold text-slate-700">Prénom</label>
            <input id="prenom" name="prenom" type="text" maxlength="255" required
                value="{{ old('prenom', $student->prenom) }}"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="naissance" class="block text-sm font-semibold text-slate-700">Date de naissance</label>
            <input id="naissance" name="naissance" type="date" required
                value="{{ old('naissance', $student->naissance) }}"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div class="sm:col-span-2">
            <label for="contact_parent" class="block text-sm font-semibold text-slate-700">Contact du parent</label>
            <input id="contact_parent" name="contact_parent" type="text" maxlength="255" required
                value="{{ old('contact_parent', $student->contact_parent) }}"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <p class="mt-1.5 text-xs text-slate-500">Numéro ou coordonnées de contact du parent/tuteur.</p>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-between">
        <a href="{{ route('admin.students.index') }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Annuler</a>
        <button type="submit"
            class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ $formMethod === 'POST' ? 'Créer l’étudiant' : 'Enregistrer les modifications' }}</button>
    </div>
</form>