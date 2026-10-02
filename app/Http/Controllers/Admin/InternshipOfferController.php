<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternshipApplication;
use App\Models\InternshipOffer;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InternshipOfferController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $offers = InternshipOffer::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('company', 'like', '%'.$search.'%')
                        ->orWhere('domain', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('is_active')
            ->orderBy('deadline')
            ->paginate(15)
            ->withQueryString();

        return view('admin.offers.index', compact('offers', 'search'));
    }

    public function create(): View
    {
        return view('admin.offers.create', [
            'offer' => new InternshipOffer(['is_active' => true, 'deadline' => now()->addMonth()->toDateString()]),
            'formAction' => route('admin.offers.store'),
            'formMethod' => 'POST',
            'pageTitle' => 'Ajouter une offre',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedOffer($request);
        $data['slug'] = $this->uniqueSlug($data['title']);

        $offer = InternshipOffer::create($data);

        return redirect()->route('admin.offers.edit', $offer)
            ->with('status', 'L’offre de stage a été créée.');
    }

    public function edit(InternshipOffer $offer): View
    {
        return view('admin.offers.edit', [
            'offer' => $offer,
            'formAction' => route('admin.offers.update', $offer),
            'formMethod' => 'PUT',
            'pageTitle' => 'Modifier une offre',
        ]);
    }

    public function update(Request $request, InternshipOffer $offer): RedirectResponse
    {
        $offer->update($this->validatedOffer($request, $offer));

        return redirect()->route('admin.offers.index')
            ->with('status', 'L’offre de stage a été mise à jour.');
    }

    public function destroy(InternshipOffer $offer): RedirectResponse
    {
        if (InternshipApplication::where('offer_id', $offer->slug)->exists()) {
            return redirect()->route('admin.offers.index')
                ->with('error', 'Cette offre possède des candidatures et ne peut pas être supprimée. Vous pouvez la désactiver à la place.');
        }

        try {
            $offer->delete();
        } catch (QueryException $exception) {
            return redirect()->route('admin.offers.index')
                ->with('error', 'La suppression est impossible car cette offre est liée à des données existantes.');
        }

        return redirect()->route('admin.offers.index')
            ->with('status', 'L’offre a été supprimée.');
    }

    /** @return array<string, mixed> */
    private function validatedOffer(Request $request, ?InternshipOffer $offer = null): array
    {
        $request->merge([
            'skills' => collect(preg_split('/[\r\n,;]+/u', (string) $request->input('skills', '')) ?: [])
                ->map(fn ($skill) => trim($skill))
                ->filter()
                ->unique()
                ->values()
                ->all(),
        ]);

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'duration' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:3000'],
            'skills' => ['required', 'array', 'min:1'],
            'skills.*' => ['required', 'string', 'max:100'],
            'deadline' => ['required', 'date'],
            'details' => ['required', 'string', 'max:10000'],
            'is_active' => ['required', 'boolean'],
        ], [
            'title.required' => 'Le titre de l’offre est obligatoire.',
            'company.required' => 'Le nom de l’entreprise est obligatoire.',
            'domain.required' => 'Le domaine est obligatoire.',
            'location.required' => 'La localisation est obligatoire.',
            'duration.required' => 'La durée du stage est obligatoire.',
            'description.required' => 'La description courte est obligatoire.',
            'skills.required' => 'Ajoutez au moins une compétence recherchée.',
            'skills.min' => 'Ajoutez au moins une compétence recherchée.',
            'deadline.required' => 'La date limite de candidature est obligatoire.',
            'details.required' => 'Les détails de l’offre sont obligatoires.',
        ]);
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'offre-stage';
        $slug = $base;
        $suffix = 2;

        while (InternshipOffer::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
