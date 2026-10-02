<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Modifier un compte étudiant · Administration</title>
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;500;600;700;800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body x-data="{}" class="admin-dashboard min-h-screen bg-slate-50 font-sans antialiased text-slate-900">
    @include('admin.students.partials.header', ['pageTitle' => 'Modifier un compte étudiant'])
    <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            <div class="mb-6">
                <p class="text-sm font-medium text-indigo-600">Compte utilisateur · rôle étudiant</p>
                <h2 class="mt-1 text-xl font-bold text-slate-900">{{ $user->name }}</h2>
                <p class="mt-1 text-sm text-slate-500">La modification conserve le rôle student et ne change pas son mot
                    de passe.</p>
            </div>

            @if ($errors->any())
                <div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.students.accounts.update', $user) }}" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700">Nom affiché</label>
                    <input id="name" name="name" type="text" maxlength="255" required
                        value="{{ old('name', $user->name) }}"
                        class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700">Adresse e-mail</label>
                    <input id="email" name="email" type="email" maxlength="255" required
                        value="{{ old('email', $user->email) }}"
                        class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-between">
                    <a href="{{ route('admin.students.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Annuler</a>
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Enregistrer</button>
                </div>
            </form>
        </section>
    </main>
</body>

</html>