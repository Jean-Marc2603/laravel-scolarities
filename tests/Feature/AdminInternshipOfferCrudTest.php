<?php

namespace Tests\Feature;

use App\Models\InternshipApplication;
use App\Models\InternshipOffer;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminInternshipOfferCrudTest extends TestCase
{
    private int $companyId;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('role')->default('student');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('sector')->nullable();
            $table->timestamps();
        });

        $companyId = DB::table('companies')->insertGetId([
            'name' => 'Tech Exemple', 'created_at' => now(), 'updated_at' => now(),
        ]);

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

        $this->companyId = $companyId;

        Schema::create('internship_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('offer_id');
            $table->timestamp('applied_at');
            $table->unsignedTinyInteger('compatibility_score')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    private function validOfferPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Développeur Laravel',
            'company_id' => $this->companyId,
            'domain' => 'Développement web',
            'location' => 'Antananarivo',
            'duration' => '3 mois',
            'description' => 'Participation au développement d’applications web et aux tests de fonctionnalités.',
            'skills' => "Laravel, PHP\nMySQL; Git",
            'deadline' => '2027-03-31',
            'details' => 'Le stagiaire travaillera avec une équipe produit sur les fonctionnalités, tests et documentation technique.',
            'is_active' => '1',
        ], $overrides);
    }

    public function test_guest_and_student_cannot_manage_offers(): void
    {
        $this->get(route('admin.offers.index'))->assertRedirect(route('login'));

        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student)->get(route('admin.offers.index'))->assertForbidden();
    }

    public function test_admin_can_create_edit_search_publish_and_delete_an_offer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->get(route('admin.offers.index'))
            ->assertOk()
            ->assertSee('Gestion des offres');

        $this->post(route('admin.offers.store'), $this->validOfferPayload())
            ->assertRedirect();

        $offer = InternshipOffer::where('title', 'Développeur Laravel')->firstOrFail();
        $this->assertSame(['Laravel', 'PHP', 'MySQL', 'Git'], $offer->skills);
        $this->assertDatabaseHas('internship_offers', [
            'id' => $offer->id,
            'slug' => 'developpeur-laravel',
            'is_active' => 1,
        ]);

        $this->get(route('admin.offers.index', ['q' => 'Tech Exemple']))
            ->assertOk()
            ->assertSee('Développeur Laravel');

        $this->put(route('admin.offers.update', $offer), $this->validOfferPayload([
            'title' => 'Développeur Laravel confirmé',
            'is_active' => '0',
        ]))->assertRedirect(route('admin.offers.index'));

        $this->assertDatabaseHas('internship_offers', [
            'id' => $offer->id,
            'title' => 'Développeur Laravel confirmé',
            'is_active' => 0,
        ]);

        $this->delete(route('admin.offers.destroy', $offer))->assertRedirect(route('admin.offers.index'));
        $this->assertDatabaseMissing('internship_offers', ['id' => $offer->id]);
    }

    public function test_offer_with_applications_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'student']);
        $offer = InternshipOffer::create([
            'slug' => 'stage-a-conserver',
            'title' => 'Stage à conserver',
            'company' => 'Tech Exemple',
            'company_id' => $this->companyId,
            'domain' => 'Développement web',
            'location' => 'Antananarivo',
            'duration' => '3 mois',
            'description' => 'Description de test suffisamment longue.',
            'skills' => ['PHP'],
            'deadline' => '2027-03-31',
            'details' => 'Informations détaillées sur cette offre de stage pour les tests.',
            'is_active' => true,
        ]);
        InternshipApplication::create([
            'user_id' => $applicant->id,
            'offer_id' => $offer->slug,
            'applied_at' => now(),
            'status' => InternshipApplication::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.offers.destroy', $offer))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('internship_offers', ['id' => $offer->id]);
    }
}
