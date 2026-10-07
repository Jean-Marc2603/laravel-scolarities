<?php

namespace Tests\Feature;

use App\Models\InternshipApplication;
use App\Models\InternshipTask;
use App\Models\StudentInternship;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminStageProgressTest extends TestCase
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
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->string('role')->default('student');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('nom');
            $table->string('prenom');
            $table->string('matricule')->nullable();
            $table->string('contact_parent')->nullable();
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('internship_offers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('company');
            $table->foreignId('company_id')->nullable();
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
            $table->string('offer_id', 191);
            $table->timestamp('applied_at');
            $table->unsignedTinyInteger('compatibility_score')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });

        Schema::create('student_internships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_application_id')->unique()->constrained('internship_applications')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('admin_observation')->nullable();
            $table->timestamps();
        });

        Schema::create('internship_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_internship_id')->constrained('student_internships')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->date('planned_date')->nullable();
            $table->timestamps();
        });
    }

    public function test_admin_sees_the_same_stage_progress_tasks_and_task_updates_as_the_student(): void
    {
        [$student, $application, $stage] = $this->createAcceptedStage();
        $this->createTask($stage, 'Analyse du projet', 100);
        $this->createTask($stage, 'UML / conception', 80);
        $this->createTask($stage, 'Tests', 0);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.internships.index'))
            ->assertOk()
            ->assertSee('Amboise Ravel')
            ->assertSee('Nova Studio')
            ->assertSee('Stage développeur')
            ->assertSee('60%')
            ->assertSee('1/3 tâches');

        $this->get(route('admin.internships.show', 'progress-'.$stage->id))
            ->assertOk()
            ->assertSee('Progression globale : 60 %')
            ->assertSee('1 tâche(s) terminée(s) sur 3')
            ->assertSee('Analyse du projet')
            ->assertSee('UML / conception')
            ->assertSee('Terminé')
            ->assertSee('En cours')
            ->assertSee('Date prévue : 20/06/2026')
            ->assertSee(now()->format('d/m/Y à H:i'));

        $this->assertSame(60, $stage->fresh()->progressPercentage());
    }

    public function test_admin_can_save_an_observation_without_changing_student_task_progress(): void
    {
        [, , $stage] = $this->createAcceptedStage();
        $task = $this->createTask($stage, 'Analyse du projet', 40);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.internships.observation.update', $stage->id), [
                'admin_observation' => 'Point de suivi à planifier.',
            ])
            ->assertRedirect(route('admin.internships.show', 'progress-'.$stage->id));

        $this->assertDatabaseHas('student_internships', ['id' => $stage->id, 'admin_observation' => 'Point de suivi à planifier.']);
        $this->assertDatabaseHas('internship_tasks', ['id' => $task->id, 'progress' => 40]);
    }

    public function test_admin_can_set_stage_dates_without_changing_task_progress(): void
    {
        [, , $stage] = $this->createAcceptedStage();
        $task = $this->createTask($stage, 'Analyse du projet', 40);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.internships.dates.update', $stage->id), [
                'start_date' => '2026-07-01',
                'end_date' => '2026-09-30',
            ])
            ->assertRedirect(route('admin.internships.show', 'progress-'.$stage->id));

        $savedStage = $stage->fresh();
        $this->assertSame('2026-07-01', $savedStage->start_date->format('Y-m-d'));
        $this->assertSame('2026-09-30', $savedStage->end_date->format('Y-m-d'));
        $this->assertDatabaseHas('internship_tasks', ['id' => $task->id, 'progress' => 40]);

        $this->patch(route('admin.internships.dates.update', $stage->id), [
            'start_date' => '2026-10-01',
            'end_date' => '2026-09-30',
        ])->assertSessionHasErrors('end_date');
    }

    public function test_only_admin_can_view_or_update_admin_stage_tracking(): void
    {
        [, , $stage] = $this->createAcceptedStage();
        $student = User::where('role', 'student')->firstOrFail();

        $this->actingAs($student)->get(route('admin.internships.index'))->assertForbidden();
        $this->patch(route('admin.internships.observation.update', $stage->id), ['admin_observation' => 'Not allowed'])->assertForbidden();
        $this->patch(route('admin.internships.dates.update', $stage->id), [])->assertForbidden();
    }

    private function createAcceptedStage(): array
    {
        $student = User::factory()->create(['name' => 'Amboise Ravel', 'role' => 'student']);
        DB::table('students')->insert([
            'user_id' => $student->id,
            'nom' => 'Ravel',
            'prenom' => 'Amboise',
            'matricule' => 'ETU-01',
            'contact_parent' => '0340000000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $companyId = DB::table('companies')->insertGetId([
            'name' => 'Nova Studio',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('internship_offers')->insert([
            'slug' => 'stage-developpeur',
            'title' => 'Stage développeur',
            'company' => 'Nova Studio',
            'company_id' => $companyId,
            'domain' => 'Développement',
            'location' => 'Antananarivo',
            'duration' => '3 mois',
            'description' => 'Stage de développement logiciel.',
            'skills' => json_encode(['PHP']),
            'deadline' => now()->addMonth()->toDateString(),
            'details' => 'Description du stage.',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $application = $student->internshipApplications()->create([
            'offer_id' => 'stage-developpeur',
            'applied_at' => now(),
            'status' => InternshipApplication::STATUS_ACCEPTED,
        ]);
        $stage = StudentInternship::create([
            'internship_application_id' => $application->id,
            'user_id' => $student->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-08-31',
        ]);

        return [$student, $application, $stage];
    }

    private function createTask(StudentInternship $stage, string $name, int $progress): InternshipTask
    {
        return $stage->tasks()->create([
            'name' => $name,
            'description' => 'Description : '.$name,
            'progress' => $progress,
            'planned_date' => '2026-06-20',
        ]);
    }
}
