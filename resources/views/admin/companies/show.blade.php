<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $company->name }} · Administration</title>
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;500;600;700;800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body x-data="{}" class="admin-dashboard min-h-screen bg-slate-50 font-sans antialiased text-slate-900">
    @include('admin.companies.partials.header', ['pageTitle' => 'Fiche entreprise'])
    <main class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div role="status"
                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}</div>
        @endif
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div
                class="flex flex-col gap-5 border-b border-slate-100 p-6 sm:flex-row sm:items-center sm:justify-between sm:p-8">
                <div class="flex items-center gap-4">
                    <span
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-lg font-extrabold text-indigo-700">{{ mb_strtoupper(mb_substr($company->name, 0, 2)) }}</span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">
                            {{ $company->sector ?: 'Secteur non renseigné' }}</p>
                        <h2 class="mt-1 text-2xl font-extrabold text-slate-900">{{ $company->name }}</h2>
                        <p class="mt-1 text-sm text-slate-500">Entreprise · ajoutée le
                            {{ $company->created_at?->format('d/m/Y') ?? '—' }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.companies.edit', $company) }}"
                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">Modifier</a>
                    @if (($companyOfferCount ?? 0) === 0)
                        <form method="POST" action="{{ route('admin.companies.destroy', $company) }}"
                            onsubmit="return confirm('Supprimer cette entreprise ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">Supprimer</button>
                        </form>
                    @endif
                </div>
            </div>
            <dl class="grid gap-px bg-slate-100 sm:grid-cols-2">
                <div class="bg-white p-6">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Adresse</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $company->address ?: 'Non renseignée' }}
                    </dd>
                </div>
                <div class="bg-white p-6">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Téléphone</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $company->phone ?: 'Non renseigné' }}</dd>
                </div>
                <div class="bg-white p-6">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">E-mail</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $company->email ?: 'Non renseigné' }}</dd>
                </div>
                <div class="bg-white p-6">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Offres publiées</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $publishedOffersCount }} publiée(s) ·
                        {{ $companyOfferCount }} au total</dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 p-5">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Offres associées</h2>
                    <p class="mt-1 text-sm text-slate-500">Les offres existantes sont conservées lors de la gestion de
                        cette entreprise.</p>
                </div><a href="{{ route('admin.offers.create') }}"
                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Ajouter une offre</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($offers as $offer)
                    <div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $offer->title }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $offer->domain }} · {{ $offer->location }} ·
                                {{ $offer->duration }}</p>
                        </div>
                        <div class="flex items-center gap-3"><span
                                class="text-xs font-semibold {{ $offer->is_active ? 'text-emerald-700' : 'text-slate-500' }}">{{ $offer->is_active ? 'Publiée' : 'Brouillon' }}</span><a
                                href="{{ route('admin.offers.edit', $offer) }}"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Modifier</a></div>
                    </div>
                @empty
                    <p class="p-6 text-sm text-slate-500">Aucune offre n’est encore associée à cette entreprise.</p>
                @endforelse
            </div>
        </section>
        <a href="{{ route('admin.companies.index') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Retour
            à la liste des entreprises</a>
    </main>
</body>

</html>