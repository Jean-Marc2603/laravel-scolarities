<?php

namespace Tests\Feature;

use Tests\TestCase;

class InternshipOffersTest extends TestCase
{
    public function test_offers_page_is_public_and_displays_sample_offers(): void
    {
        $response = $this->get(route('internships.index'));

        $response->assertOk();
        $response->assertSee('Développeur Web Laravel');
        $response->assertSee('Passer au mode sombre');
        $response->assertSee('offre-20', false);
        $response->assertDontSee('offre-21', false);
        $response->assertSee('Postuler');
    }

    public function test_offers_can_be_filtered_by_domain_location_and_duration(): void
    {
        $response = $this->get(route('internships.index', [
            'domaine' => 'Cybersécurité',
            'localisation' => 'Antananarivo',
            'duree' => '5 mois',
        ]));

        $response->assertOk();
        $response->assertSee('Analyste junior en cybersécurité');
        $response->assertDontSee('Assistant data analyst');
    }

    public function test_search_matches_offer_titles_and_domains(): void
    {
        $response = $this->get(route('internships.index', ['q' => 'marketing digital']));

        $response->assertOk();
        $response->assertSee('Assistant marketing digital');
        $response->assertSee('Stagiaire SEO et contenu');
        $response->assertDontSee('Développeur Web Laravel');
    }
}
