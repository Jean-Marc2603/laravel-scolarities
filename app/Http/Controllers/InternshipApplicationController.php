<?php

namespace App\Http\Controllers;

use App\Models\InternshipApplication;
use App\Services\InternshipCompatibilityScorer;
use App\Services\InternshipOfferCatalog;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InternshipApplicationController extends Controller
{
    public function index(Request $request, InternshipOfferCatalog $catalog): View
    {
        $applications = $request->user()
            ->internshipApplications()
            ->latest('applied_at')
            ->get()
            ->map(function (InternshipApplication $application) use ($catalog) {
                $application->offer = $catalog->find($application->offer_id);

                return $application;
            })
            ->filter(fn (InternshipApplication $application) => $application->offer !== null)
            ->values();

        return view('applications.index', compact('applications'));
    }

    public function show(Request $request, int $application, InternshipOfferCatalog $catalog): View
    {
        $application = $request->user()->internshipApplications()->findOrFail($application);
        $offer = $catalog->find($application->offer_id);
        abort_if($offer === null, 404);

        return view('applications.show', compact('application', 'offer'));
    }

    public function store(
        Request $request,
        string $offer,
        InternshipOfferCatalog $catalog,
        InternshipCompatibilityScorer $scorer
    ): RedirectResponse {
        $offerData = $catalog->find($offer);
        abort_if($offerData === null, 404);

        $user = $request->user();
        if ($user->internshipApplications()->where('offer_id', $offer)->exists()) {
            return redirect()->route('applications.index')
                ->with('error', 'Vous avez déjà postulé à cette offre.');
        }

        $analysis = $user->cvDocument?->analysis_results;
        $compatibilityScore = is_array($analysis)
            ? $scorer->compare($analysis['skills'] ?? [], $offerData['skills'])['score']
            : null;

        try {
            $user->internshipApplications()->create([
                'offer_id' => $offerData['id'],
                'applied_at' => now(),
                'compatibility_score' => $compatibilityScore,
                'status' => InternshipApplication::STATUS_PENDING,
            ]);
        } catch (QueryException $exception) {
            if (! $user->internshipApplications()->where('offer_id', $offer)->exists()) {
                throw $exception;
            }

            return redirect()->route('applications.index')
                ->with('error', 'Vous avez déjà postulé à cette offre.');
        }

        return redirect()->route('applications.index')
            ->with('status', 'Votre candidature a bien été envoyée.');
    }

    public function destroy(Request $request, int $application): RedirectResponse
    {
        $application = $request->user()->internshipApplications()->findOrFail($application);

        if ($application->status !== InternshipApplication::STATUS_PENDING) {
            return back()->with('error', 'Seules les candidatures en attente peuvent être annulées.');
        }

        $application->delete();

        return redirect()->route('applications.index')
            ->with('status', 'Votre candidature a été annulée.');
    }
}
