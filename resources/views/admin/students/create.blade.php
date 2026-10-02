<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }} · Administration</title>
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;500;600;700;800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body x-data="{}" class="admin-dashboard min-h-screen bg-slate-50 font-sans antialiased text-slate-900">
    @include('admin.students.partials.header', ['pageTitle' => $pageTitle])
    <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            <div class="mb-6">
                <p class="text-sm font-medium text-indigo-600">Dossier étudiant</p>
                <h2 class="mt-1 text-xl font-bold text-slate-900">Informations de l’étudiant</h2>
                <p class="mt-1 text-sm text-slate-500">Les champs marqués sont nécessaires pour créer le dossier.</p>
            </div>
            @include('admin.students.partials.form')
        </section>
    </main>
</body>

</html>