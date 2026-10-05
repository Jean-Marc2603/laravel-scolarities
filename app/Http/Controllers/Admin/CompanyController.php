<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\InternshipOffer;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $companyRelationExists = Schema::hasTable('internship_offers') && Schema::hasColumn('internship_offers', 'company_id');

        $companies = Company::query()
            ->when($companyRelationExists, function ($query) {
                $query->withCount(['internshipOffers as published_offers_count' => function ($query) {
                    $query->where('is_active', true);
                }])
                    ->withCount('internshipOffers');
            })
            ->orderBy('name')
            ->get();

        $legacyCompanies = [];
        if (Schema::hasTable('internship_offers')) {
            $legacyNames = InternshipOffer::query()
                ->whereNotNull('company')
                ->where('company', '<>', '')
                ->select('company')
                ->distinct()
                ->pluck('company')
                ->map(fn ($name) => trim((string) $name))
                ->filter()
                ->values();

            foreach ($legacyNames as $legacyName) {
                $companyExists = $companies->first(fn ($company) => strtolower($company->name) === strtolower($legacyName));
                if ($companyExists) {
                    continue;
                }

                $legacyCompanies[] = (object) [
                    'id' => null,
                    'name' => $legacyName,
                    'address' => null,
                    'phone' => null,
                    'email' => null,
                    'sector' => null,
                    'published_offers_count' => InternshipOffer::query()->where('company', $legacyName)->where('is_active', true)->count(),
                    'internship_offers_count' => InternshipOffer::query()->where('company', $legacyName)->count(),
                    'is_virtual' => true,
                ];
            }
        }

        $mergedCompanies = $companies->merge($legacyCompanies)->sortBy('name')->values();
        $filteredCompanies = $mergedCompanies;

        if ($search !== '') {
            $filteredCompanies = $mergedCompanies->filter(function ($company) use ($search) {
                $haystack = collect([$company->name, $company->email, $company->sector, $company->address])->filter()->implode(' ');

                return str_contains(strtolower($haystack), strtolower($search));
            })->values();
        }

        $page = max(1, (int) $request->query('page', 1));
        $perPage = 15;
        $items = $filteredCompanies->slice(($page - 1) * $perPage, $perPage)->values();

        $companies = new LengthAwarePaginator(
            $items,
            $filteredCompanies->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.companies.index', compact('companies', 'search'));
    }

    public function create(): View
    {
        return view('admin.companies.create', [
            'company' => new Company(),
            'formAction' => route('admin.companies.store'),
            'formMethod' => 'POST',
            'pageTitle' => 'Ajouter une entreprise',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = Company::create($this->validatedCompany($request));

        return redirect()->route('admin.companies.show', $company)
            ->with('status', 'L’entreprise a été ajoutée.');
    }

    public function show(Company $company): View
    {
        if (Schema::hasTable('internship_offers') && Schema::hasColumn('internship_offers', 'company_id')) {
            $company->load(['internshipOffers' => function ($query) {
                $query->orderByDesc('is_active')->orderBy('deadline');
            }]);
            $offers = $company->internshipOffers;
        } else {
            $offers = InternshipOffer::query()
                ->where('company', $company->name)
                ->orderByDesc('is_active')
                ->orderBy('deadline')
                ->get();
        }

        $publishedOffersCount = $offers->where('is_active', true)->count();
        $companyOfferCount = $offers->count();

        return view('admin.companies.show', compact('company', 'offers', 'publishedOffersCount', 'companyOfferCount'));
    }

    public function edit(Company $company): View
    {
        $companyOfferCount = Schema::hasTable('internship_offers') && Schema::hasColumn('internship_offers', 'company_id')
            ? $company->internshipOffers()->count()
            : InternshipOffer::query()->where('company', $company->name)->count();

        return view('admin.companies.edit', [
            'company' => $company,
            'companyOfferCount' => $companyOfferCount,
            'formAction' => route('admin.companies.update', $company),
            'formMethod' => 'PUT',
            'pageTitle' => 'Modifier une entreprise',
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $data = $this->validatedCompany($request, $company);
        $oldName = $company->name;

        DB::transaction(function () use ($company, $data, $oldName) {
            $company->update($data);

            if ($oldName !== $company->name && Schema::hasTable('internship_offers') && Schema::hasColumn('internship_offers', 'company_id')) {
                $company->internshipOffers()->update(['company' => $company->name]);
            }
        });

        return redirect()->route('admin.companies.show', $company)
            ->with('status', 'Les informations de l’entreprise ont été mises à jour.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $hasOffers = Schema::hasTable('internship_offers')
            && (
                (Schema::hasColumn('internship_offers', 'company_id') && $company->internshipOffers()->exists())
                || InternshipOffer::query()->where('company', $company->name)->exists()
            );

        if ($hasOffers) {
            return redirect()->route('admin.companies.index')
                ->with('error', 'Cette entreprise possède des offres associées. Réassignez-les avant de supprimer l’entreprise.');
        }

        try {
            $company->delete();
        } catch (QueryException $exception) {
            return redirect()->route('admin.companies.index')
                ->with('error', 'La suppression est impossible car l’entreprise est liée à des données.');
        }

        return redirect()->route('admin.companies.index')
            ->with('status', 'L’entreprise a été supprimée.');
    }

    /** @return array<string, mixed> */
    private function validatedCompany(Request $request, ?Company $company = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('companies', 'name')->ignore($company?->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'sector' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'La raison sociale est obligatoire.',
            'name.unique' => 'Une entreprise portant ce nom existe déjà.',
            'email.email' => 'Saisissez une adresse e-mail valide.',
        ]);
    }
}
