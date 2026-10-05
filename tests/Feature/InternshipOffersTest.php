<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\InternshipOffer;
use App\Services\InternshipOfferCatalog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
        $response->assertDontSee('Compatibilité avec votre CV');
        $response->assertDontSee('Meilleure compatibilité');
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

    public function test_student_list_reads_managed_active_offers_from_database(): void
    {
        if (! Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('address')->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('email')->nullable();
                $table->string('sector')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('internship_offers')) {
            Schema::create('internship_offers', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('title');
                $table->string('company');
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
                $table->string('domain');
                $table->string('location');
                $table->string('duration');
                $table->text('description');
                $table->json('skills');
                $table->date('deadline');
                $table->text('details');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        foreach (app(InternshipOfferCatalog::class)->defaults() as $offer) {
            $company = Company::firstOrCreate(['name' => $offer['company']]);
            InternshipOffer::create([
                'slug' => $offer['id'],
                'title' => $offer['title'],
                'company' => $offer['company'],
                'company_id' => $company->id,
                'domain' => $offer['domain'],
                'location' => $offer['location'],
                'duration' => $offer['duration'],
                'description' => $offer['description'],
                'skills' => $offer['skills'],
                'deadline' => $offer['deadline']->toDateString(),
                'details' => $offer['details'],
                'is_active' => true,
            ]);
        }

        InternshipOffer::where('slug', 'developpeur-web-laravel')->update(['title' => 'Offre mise à jour par admin']);
        InternshipOffer::where('slug', 'assistant-comptable')->update(['is_active' => false]);

        $this->get(route('internships.index'))
            ->assertOk()
            ->assertSee('Offre mise à jour par admin')
            ->assertDontSee('Assistant comptable')
            ->assertSee('19 offres trouvées');
    }
}
