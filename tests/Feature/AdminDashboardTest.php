<?php

namespace Tests\Feature;

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

        User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Tableau de bord');
        $response->assertSee('Passer au mode sombre');
        $response->assertSee('Comptes utilisateurs au rôle étudiant');
        $response->assertSee('Entreprises représentées dans les offres disponibles');
        $response->assertSee('Offres actuellement proposées');
        $response->assertSee('Candidatures reçues');
        $response->assertSee('Module de suivi non configuré');
        $response->assertSee('Module de conventions non configuré');
        $response->assertSee('Utilisateurs');
        $response->assertSee('Étudiants');
        $response->assertDontSee('Frais de scolarité');
        $response->assertSee('>1</p>', false);
        $response->assertSee('>20</p>', false);
    }

    public function test_admin_live_statistics_endpoint_returns_current_counts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        DB::table('internship_applications')->insert([
            'user_id' => $student->id,
            'offer_id' => 'developpeur-web-laravel',
            'applied_at' => now(),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.dashboard.statistics'));
        $response->assertOk()
            ->assertJsonPath('statistics.students', 1)
            ->assertJsonPath('statistics.applications', 1)
            ->assertJsonStructure(['statistics' => ['students', 'companies', 'offers', 'applications', 'internships', 'conventions'], 'updated_at']);

        User::factory()->create(['role' => 'student']);
        $this->getJson(route('admin.dashboard.statistics'))
            ->assertOk()
            ->assertJsonPath('statistics.students', 2);
    }

    public function test_non_admin_cannot_read_live_statistics(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->getJson(route('admin.dashboard.statistics'))->assertForbidden();
    }

    public function test_live_dashboard_counts_student_stages_using_task_completion(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $applicationId = DB::table('internship_applications')->insertGetId([
            'user_id' => $student->id,
            'offer_id' => 'developpeur-web-laravel',
            'applied_at' => now(),
            'status' => 'accepted',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('student_internships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_application_id')->constrained('internship_applications');
            $table->foreignId('user_id')->constrained();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('admin_observation')->nullable();
            $table->timestamps();
        });
        Schema::create('internship_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_internship_id')->constrained('student_internships');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->date('planned_date')->nullable();
            $table->timestamps();
        });

        $stageId = DB::table('student_internships')->insertGetId([
            'internship_application_id' => $applicationId,
            'user_id' => $student->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->getJson(route('admin.dashboard.statistics'))
            ->assertOk()
            ->assertJsonPath('statistics.internships', 1);

        DB::table('internship_tasks')->insert([
            'student_internship_id' => $stageId,
            'name' => 'Étape terminée',
            'progress' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson(route('admin.dashboard.statistics'))
            ->assertOk()
            ->assertJsonPath('statistics.internships', 0);
    }
}
