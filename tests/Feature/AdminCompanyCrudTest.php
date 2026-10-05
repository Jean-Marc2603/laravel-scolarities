<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\InternshipOffer;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminCompanyCrudTest extends TestCase
{
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
            $table->boolean('is_active')->default(true);
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

    private function companyData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Entreprise Exemple',
            'address' => '12 rue Centrale',
            'phone' => '+261 20 00 000 00',
            'email' => 'contact@exemple.test',
            'sector' => 'Informatique',
        ], $overrides);
    }

    public function test_guest_and_student_cannot_manage_companies(): void
    {
        $this->get(route('admin.companies.index'))->assertRedirect(route('login'));

        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student)->get(route('admin.companies.index'))->assertForbidden();
    }

    public function test_admin_can_search_create_view_edit_and_delete_an_unlinked_company(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $existing = Company::create($this->companyData());
        $this->actingAs($admin);

        $this->get(route('admin.companies.index', ['q' => 'Informatique']))
            ->assertOk()
            ->assertSee('Entreprise Exemple')
            ->assertSee('contact@exemple.test');

        $this->get(route('admin.companies.show', $existing))
            ->assertOk()
            ->assertSee('12 rue Centrale')
            ->assertSee('Offres associées');

        $this->post(route('admin.companies.store'), $this->companyData([
            'name' => 'Nouvelle Entreprise',
            'email' => 'nouvelle@example.test',
        ]))->assertRedirect();

        $newCompany = Company::where('name', 'Nouvelle Entreprise')->firstOrFail();
        $this->put(route('admin.companies.update', $newCompany), $this->companyData([
            'name' => 'Entreprise Modifiée',
            'email' => 'modifiee@example.test',
        ]))->assertRedirect(route('admin.companies.show', $newCompany));
        $this->assertDatabaseHas('companies', ['id' => $newCompany->id, 'name' => 'Entreprise Modifiée']);

        $this->delete(route('admin.companies.destroy', $newCompany))->assertRedirect(route('admin.companies.index'));
        $this->assertDatabaseMissing('companies', ['id' => $newCompany->id]);
    }

    public function test_company_with_multiple_offers_cannot_be_deleted_and_offer_data_remains(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $company = Company::create($this->companyData());
        $firstOffer = $this->createOffer($company, 'offre-a', 'Offre A');
        $secondOffer = $this->createOffer($company, 'offre-b', 'Offre B');

        $this->actingAs($admin)
            ->get(route('admin.companies.show', $company))
            ->assertOk()
            ->assertSee('2 au total')
            ->assertSee('Offre A')
            ->assertSee('Offre B');

        $this->delete(route('admin.companies.destroy', $company))->assertSessionHas('error');
        $this->assertDatabaseHas('companies', ['id' => $company->id]);
        $this->assertDatabaseHas('internship_offers', ['id' => $firstOffer->id, 'company_id' => $company->id]);
        $this->assertDatabaseHas('internship_offers', ['id' => $secondOffer->id, 'company_id' => $company->id]);
    }

    private function createOffer(Company $company, string $slug, string $title): InternshipOffer
    {
        return InternshipOffer::create([
            'slug' => $slug,
            'title' => $title,
            'company' => $company->name,
            'company_id' => $company->id,
            'domain' => 'Développement web',
            'location' => 'Antananarivo',
            'duration' => '3 mois',
            'description' => 'Participation au développement de services numériques et de plateformes web.',
            'skills' => ['PHP', 'Laravel'],
            'deadline' => '2027-03-31',
            'details' => 'Le stage contribuera à des projets numériques en équipe.',
            'is_active' => true,
        ]);
    }
}
