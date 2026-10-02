<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
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
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('matricule');
            $table->string('nom');
            $table->string('prenom');
            $table->date('naissance');
            $table->string('contact_parent');
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

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_non_admin_user_is_forbidden(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_can_view_dashboard_with_database_and_available_offer_statistics(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        Student::create([
            'matricule' => 'ETU-001',
            'nom' => 'Test',
            'prenom' => 'Etudiant',
            'naissance' => '2000-01-01',
            'contact_parent' => '0000000000',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Tableau de bord');
        $response->assertSee('Étudiants enregistrés');
        $response->assertSee('Entreprises représentées dans les offres disponibles');
        $response->assertSee('Offres actuellement proposées');
        $response->assertSee('Candidatures reçues');
        $response->assertSee('Module de suivi non configuré');
        $response->assertSee('Module de conventions non configuré');
        $response->assertSee('Utilisateurs');
        $response->assertSee('Étudiants');
        $response->assertSee('Frais de scolarité');
        $response->assertSee('>1</p>', false);
        $response->assertSee('>20</p>', false);
    }
}
