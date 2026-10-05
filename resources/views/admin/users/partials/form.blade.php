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
            <label for="name" class="block text-sm font-semibold text-slate-700">Nom affiché</label>
            <input id="name" name="name" type="text" maxlength="255" required @readonly($user->studentProfile)
                value="{{ old('name', $user->studentProfile ? trim($user->studentProfile->prenom . ' ' . $user->studentProfile->nom) : $user->name) }}"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @if ($user->studentProfile)
                <p class="mt-1.5 text-xs text-slate-500">Ce nom est géré dans le dossier étudiant associé.</p>
            @endif
        </div>
        <div class="sm:col-span-2">
            <label for="email" class="block text-sm font-semibold text-slate-700">Adresse e-mail</label>
            <input id="email" name="email" type="email" maxlength="255" required
                value="{{ old('email', $user->email) }}"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="role" class="block text-sm font-semibold text-slate-700">Rôle</label>
            <select id="role" name="role" required
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ ucfirst($role) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
            <input type="hidden" name="is_active" value="0">
            <input id="is_active" name="is_active" type="checkbox" value="1" @checked((bool) old('is_active', $user->is_active ?? true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            <label for="is_active" class="text-sm font-semibold text-slate-700">Compte actif, connexion
                autorisée</label>
        </div>
        <div>
            <label for="password"
                class="block text-sm font-semibold text-slate-700">{{ $formMethod === 'POST' ? 'Mot de passe' : 'Nouveau mot de passe (facultatif)' }}</label>
            <input id="password" name="password" type="password" autocomplete="new-password"
                @required($formMethod === 'POST')
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @if ($formMethod !== 'POST')
                <p class="mt-1.5 text-xs text-slate-500">Laissez vide pour garder le mot de passe actuel.</p>
            @endif
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Confirmer le mot de
                passe</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                @required($formMethod === 'POST')
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-between">
        <a href="{{ route('admin.users.index') }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Annuler</a>
        <button type="submit"
            class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ $formMethod === 'POST' ? 'Créer l’utilisateur' : 'Enregistrer' }}</button>
    </div>
</form>