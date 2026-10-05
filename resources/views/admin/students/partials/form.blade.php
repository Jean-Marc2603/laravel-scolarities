<form method="POST" action="{{ $formAction }}" class="space-y-6"
    x-data="{ accountAction: @js(old('account_action', $accountAction ?? 'none')) }">
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

    <section class="space-y-4 rounded-2xl border border-indigo-100 bg-indigo-50/60 p-5">
        <div>
            <h3 class="text-sm font-bold text-slate-900">Compte utilisateur étudiant</h3>
            <p class="mt-1 text-xs leading-5 text-slate-600">Les informations d’identité du compte sont conservées dans
                users. Le dossier scolaire reste dans students et lui est relié par une relation.</p>
        </div>
        <div>
            <label for="account_action" class="block text-sm font-semibold text-slate-700">Association du compte</label>
            <select id="account_action" name="account_action" x-model="accountAction" required
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @if ($formMethod !== 'POST')
                    <option value="keep">Conserver l’association actuelle</option>
                    <option value="none">Aucun compte associé</option>
                @else
                    <option value="none">Créer le dossier sans compte</option>
                @endif
                <option value="link">Associer un compte student existant</option>
                <option value="create">Créer un nouveau compte student</option>
            </select>
        </div>

        <div x-cloak x-show="accountAction === 'link'" x-transition>
            <label for="account_user_id" class="block text-sm font-semibold text-slate-700">Compte étudiant
                existant</label>
            <select id="account_user_id" name="account_user_id" :required="accountAction === 'link'"
                class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Choisir un compte</option>
                @foreach ($availableAccounts as $account)
                    <option value="{{ $account->id }}" @selected((string) old('account_user_id', $student->user_id) === (string) $account->id)>{{ $account->name }} · {{ $account->email }}</option>
                @endforeach
            </select>
            @if ($availableAccounts->isEmpty())
                <p class="mt-2 text-xs text-amber-700">Aucun compte étudiant disponible pour l’association. Créez d’abord un
                    compte depuis la section Utilisateurs.</p>
            @endif
        </div>

        <div x-cloak x-show="accountAction === 'create'" x-transition class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-slate-700">Nom du compte</label>
                <p class="mt-1 text-sm text-slate-600">Il sera généré à partir du prénom et du nom du dossier étudiant.
                </p>
            </div>
            <div>
                <label for="account_email" class="block text-sm font-semibold text-slate-700">E-mail du compte</label>
                <input id="account_email" name="account_email" type="email" maxlength="255"
                    :required="accountAction === 'create'" value="{{ old('account_email') }}"
                    class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="account_password" class="block text-sm font-semibold text-slate-700">Mot de passe (8
                    caractères min.)</label>
                <input id="account_password" name="account_password" type="password" autocomplete="new-password"
                    :required="accountAction === 'create'"
                    class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="sm:col-span-2">
                <label for="account_password_confirmation" class="block text-sm font-semibold text-slate-700">Confirmer
                    le mot de passe</label>
                <input id="account_password_confirmation" name="account_password_confirmation" type="password"
                    autocomplete="new-password" :required="accountAction === 'create'"
                    class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-between">
        <a href="{{ route('admin.students.index') }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Annuler</a>
        <button type="submit"
            class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ $formMethod === 'POST' ? 'Créer l’étudiant' : 'Enregistrer les modifications' }}</button>
    </div>
</form>