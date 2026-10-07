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

class StudentInternshipTest extends TestCase
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
            $table->string('stage_title')->nullable();
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
            $table->unique(['student_internship_id', 'name']);
        });
    }

    public function test_student_without_an_accepted_application_cannot_access_mon_stage(): void
    {
        $student = $this->student();

        $this->actingAs($student)->get(route('student.internship.show'))->assertForbidden();
        $this->assertDatabaseCount('student_internships', 0);
    }

    public function test_accepted_student_sees_the_corresponding_offer_and_initial_tasks(): void
    {
        $student = $this->student();
        $application = $this->application($student, InternshipApplication::STATUS_ACCEPTED);

        $this->actingAs($student)->get(route('student.internship.show'))
            ->assertOk()
            ->assertSee('Mon stage')
            ->assertSee('Développeur Web Laravel')
            ->assertSee('Tech Solutions')
            ->assertSee('Aucune observation pour le moment')
            ->assertSee('Analyse du projet')
            ->assertSee('Présentation finale')
            ->assertSee('0%');

        $internship = StudentInternship::where('internship_application_id', $application->id)->firstOrFail();
        $this->assertDatabaseCount('internship_tasks', 8);
        $this->assertSame($student->id, $internship->user_id);
    }

    public function test_student_can_update_only_task_progress_and_global_progress_is_calculated(): void
    {
        $student = $this->student();
        $this->application($student, InternshipApplication::STATUS_ACCEPTED);
        $this->actingAs($student)->get(route('student.internship.show'))->assertOk();
        $task = InternshipTask::firstOrFail();

        $this->patch(route('student.internship.tasks.update', $task), ['progress' => 100])
            ->assertRedirect(route('student.internship.show'));

        $this->assertDatabaseHas('internship_tasks', ['id' => $task->id, 'progress' => 100, 'name' => 'Analyse du projet']);
        $this->get(route('student.internship.show'))->assertOk()->assertSee('13%');

        $this->patch(route('student.internship.tasks.update', $task), ['progress' => 50])
            ->assertSessionHasErrors('progress');
        $this->assertDatabaseHas('internship_tasks', ['id' => $task->id, 'progress' => 100]);
    }

    public function test_student_sees_admin_stage_title_override_or_linked_offer_title_by_default(): void
    {
        $student = $this->student();
        $application = $this->application($student, InternshipApplication::STATUS_ACCEPTED);

        $this->actingAs($student)->get(route('student.internship.show'))
            ->assertOk()
            ->assertSee('Développeur Web Laravel');

        $internship = StudentInternship::where('internship_application_id', $application->id)->firstOrFail();
        $internship->update(['stage_title' => 'Poste personnalisé par l’administration']);

        $this->get(route('student.internship.show'))
            ->assertOk()
            ->assertSee('Poste personnalisé par l’administration');
    }

    public function test_student_can_only_read_their_own_stage_observation_and_last_update(): void
    {
        $student = $this->student();
        $otherStudent = $this->student();
        $application = $this->application($student, InternshipApplication::STATUS_ACCEPTED);
        $otherApplication = $this->application($otherStudent, InternshipApplication::STATUS_ACCEPTED);

        $ownStage = StudentInternship::create([
            'internship_application_id' => $application->id,
            'user_id' => $student->id,
            'admin_observation' => 'Votre observation de suivi.',
        ]);
        $ownStage->timestamps = false;
        $ownStage->forceFill(['updated_at' => '2026-10-06 14:30:00'])->save();
        $this->assertSame('Votre observation de suivi.', $ownStage->fresh()->admin_observation);
        StudentInternship::create([
            'internship_application_id' => $otherApplication->id,
            'user_id' => $otherStudent->id,
            'admin_observation' => 'Observation confidentielle autre étudiant.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($student)->get(route('student.internship.show'))
            ->assertOk()
            ->assertSee('Observations de l’administrateur')
            ->assertSee('Votre observation de suivi.')
            ->assertSee('Mis à jour le 06/10/2026 à 14:30')
            ->assertDontSee('Observation confidentielle autre étudiant.')
            ->assertDontSee('<textarea name="admin_observation"', false);

        $this->assertSame($student->id, $ownStage->fresh()->user_id);
    }

    public function test_student_cannot_change_another_students_task(): void
    {
        $student = $this->student();
        $otherStudent = $this->student();
        $this->application($student, InternshipApplication::STATUS_ACCEPTED);
        $this->application($otherStudent, InternshipApplication::STATUS_ACCEPTED);

        $this->actingAs($student)->get(route('student.internship.show'))->assertOk();
        $this->actingAs($otherStudent)->get(route('student.internship.show'))->assertOk();
        $otherTask = InternshipTask::whereHas('internship', fn ($query) => $query->where('user_id', $otherStudent->id))->firstOrFail();

        $this->actingAs($student);
        $this->patch(route('student.internship.tasks.update', $otherTask), ['progress' => 60])->assertNotFound();
        $this->assertDatabaseHas('internship_tasks', ['id' => $otherTask->id, 'progress' => 0]);
    }

    private function student(): User
    {
        $student = User::factory()->create();
        $student->forceFill(['role' => 'student'])->save();

        return $student;
    }

    private function application(User $student, string $status): InternshipApplication
    {
        return $student->internshipApplications()->create([
            'offer_id' => 'developpeur-web-laravel',
            'applied_at' => now(),
            'status' => $status,
        ]);
    }
}
