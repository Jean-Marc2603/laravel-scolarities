<?php

namespace App\Http\Controllers;

use App\Services\InternshipCompatibilityScorer;
use App\Services\InternshipOfferCatalog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InternshipOfferController extends Controller
{
    /**
     * Display the sample internship offers with simple query-string filters.
     */
    public function index(Request $request, InternshipCompatibilityScorer $scorer, InternshipOfferCatalog $catalog): View
    {
        $offers = $catalog->all();

        $search = trim((string) $request->query('q', ''));
        $domain = (string) $request->query('domaine', '');
        $location = (string) $request->query('localisation', '');
        $duration = (string) $request->query('duree', '');

        $cvAnalysis = $request->user()?->cvDocument?->analysis_results;
        $hasCvAnalysis = is_array($cvAnalysis);
        $cvSkills = $hasCvAnalysis ? ($cvAnalysis['skills'] ?? []) : [];
        $sortByCompatibility = $hasCvAnalysis && $request->query('tri') === 'compatibilite';

        $filteredOffers = array_values(array_filter($offers, function (array $offer) use ($search, $domain, $location, $duration) {
            $matchesSearch = $search === '' ||
                mb_stripos($offer['title'], $search) !== false ||
                mb_stripos($offer['domain'], $search) !== false;

            return $matchesSearch &&
                ($domain === '' || $offer['domain'] === $domain) &&
                ($location === '' || $offer['location'] === $location) &&
                ($duration === '' || $offer['duration'] === $duration);
        }));

        if ($hasCvAnalysis) {
            foreach ($filteredOffers as &$offer) {
                $offer['compatibility'] = $scorer->compare($cvSkills, $offer['skills']);
            }
            unset($offer);
        }

        if ($sortByCompatibility) {
            $filteredOffers = $scorer->sortOffersByScore($filteredOffers);
        }

        $applicationsByOffer = $request->user()
            ? $request->user()->internshipApplications()->get(['id', 'offer_id', 'status'])->keyBy('offer_id')
            : collect();

        return view('offers.index', [
            'offers' => $filteredOffers,
            'applicationsByOffer' => $applicationsByOffer,
            'filters' => compact('search', 'domain', 'location', 'duration') + ['sort' => $sortByCompatibility ? 'compatibilite' : ''],
            'hasCvAnalysis' => $hasCvAnalysis,
            'domains' => array_values(array_unique(array_column($offers, 'domain'))),
            'locations' => array_values(array_unique(array_column($offers, 'location'))),
            'durations' => array_values(array_unique(array_column($offers, 'duration'))),
        ]);
    }
}
