<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }} · Administration</title>
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700;800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body x-data="{}" class="admin-dashboard min-h-screen bg-slate-50 font-sans antialiased text-slate-900">
    @include('admin.users.partials.header', ['pageTitle' => $pageTitle])
    <main class="mx-auto max-w-3xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('error'))
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ session('error') }}</div>
        @endif
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            <p class="mb-6 text-sm text-slate-500">Modifiez l’identité, le rôle ou le statut. Laissez les champs de mot
                de passe vides pour le conserver.</p>
            @include('admin.users.partials.form')
        </section>
    </main>
</body>

</html>