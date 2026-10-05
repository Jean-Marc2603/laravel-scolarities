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
            <label for="name" class="block text-sm font-semibold text-slate-700">Raison sociale / nom de
                l’entreprise</label>
            <input id="name" name="name" type="text" maxlength="255" required value="{{ old('name', $company->name) }}"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div class="sm:col-span-2">
            <label for="address" class="block text-sm font-semibold text-slate-700">Adresse</label>
            <input id="address" name="address" type="text" maxlength="255"
                value="{{ old('address', $company->address) }}"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="phone" class="block text-sm font-semibold text-slate-700">Téléphone</label>
            <input id="phone" name="phone" type="tel" maxlength="50" value="{{ old('phone', $company->phone) }}"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700">E-mail</label>
            <input id="email" name="email" type="email" maxlength="255" value="{{ old('email', $company->email) }}"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div class="sm:col-span-2">
            <label for="sector" class="block text-sm font-semibold text-slate-700">Secteur d’activité</label>
            <input id="sector" name="sector" type="text" maxlength="255" value="{{ old('sector', $company->sector) }}"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-between">
        <a href="{{ route('admin.companies.index') }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Annuler</a>
        <button type="submit"
            class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ $formMethod === 'POST' ? 'Ajouter l’entreprise' : 'Enregistrer les modifications' }}</button>
    </div>
</form>