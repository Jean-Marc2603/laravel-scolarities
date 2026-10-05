<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminStudentCrudTest extends TestCase
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
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('matricule', 10);
            $table->string('nom');
            $table->string('prenom');
            $table->date('naissance');
            $table->string('contact_parent');
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->string('school_year');
            $table->string('current_Year');
            $table->boolean('active')->default(false);
            $table->timestamps();
        });

        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('libelle');
            $table->integer('scolarite')->default(0);
            $table->unsignedBigInteger('school_year_id')->nullable();
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->unsignedBigInteger('level_id')->nullable();
            $table->timestamps();
        });

        Schema::create('attributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('classe_id');
            $table->unsignedBigInteger('school_year_id');
            $table->text('comments')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('classe_id');
            $table->unsignedBigInteger('school_year_id');
            $table->integer('montant');
            $table->timestamps();
        });
    }

    public function test_guest_and_non_admin_cannot_access_student_management(): void
    {
        $this->get(route('admin.students.index'))->assertRedirect(route('login'));

        $studentUser = User::factory()->create(['role' => 'student']);
        $this->actingAs($studentUser)->get(route('admin.students.index'))->assertForbidden();
    }

    public function test_admin_can_list_create_show_update_and_delete_a_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('Gestion des étudiants');

        $this->post(route('admin.students.store'), [
            'account_action' => 'none',
            'matricule' => 'ETU00001',
            'nom' => 'Rakoto',
            'prenom' => 'Miora',
            'naissance' => '2004-05-06',
            'contact_parent' => '0340000000',
        ])->assertRedirect();

        $student = Student::where('matricule', 'ETU00001')->firstOrFail();
        $this->assertDatabaseHas('students', ['id' => $student->id, 'nom' => 'Rakoto']);

        $this->get(route('admin.students.show', $student))
            ->assertOk()
            ->assertSee('Miora')
            ->assertSee('0340000000');

        $this->put(route('admin.students.update', $student), [
            'account_action' => 'keep',
            'matricule' => 'ETU00001',
            'nom' => 'Rabe',
            'prenom' => 'Miora',
            'naissance' => '2004-05-06',
            'contact_parent' => '0341111111',
        ])->assertRedirect(route('admin.students.show', $student));

        $this->assertDatabaseHas('students', ['id' => $student->id, 'nom' => 'Rabe', 'contact_parent' => '0341111111']);

        $this->delete(route('admin.students.destroy', $student))->assertRedirect(route('admin.students.index'));
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_student_dossier_page_keeps_accounts_in_the_separate_users_section(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create([
            'name' => 'Étudiant existant',
            'email' => 'etudiant@example.test',
            'role' => 'student',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertDontSee('Comptes étudiants')
            ->assertDontSee('etudiant@example.test');
    }

    public function test_creating_a_student_can_create_and_link_a_student_user_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.students.store'), [
            'matricule' => 'STU000002',
            'nom' => 'Rakoto',
            'prenom' => 'Miora',
            'naissance' => '2004-05-06',
            'contact_parent' => '0340000000',
            'account_action' => 'create',
            'account_email' => 'miora@example.test',
            'account_password' => 'Student1234',
            'account_password_confirmation' => 'Student1234',
        ])->assertRedirect();

        $student = Student::where('matricule', 'STU000002')->firstOrFail();
        $account = User::where('email', 'miora@example.test')->firstOrFail();

        $this->assertSame($account->id, $student->user_id);
        $this->assertSame('student', $account->role);
        $this->assertSame('Miora Rakoto', $account->name);
        $this->assertTrue(Hash::check('Student1234', $account->password));
    }

    public function test_creating_a_student_can_associate_an_existing_student_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $account = User::factory()->create(['name' => 'Old Account Name', 'email' => 'existing.student@example.test', 'role' => 'student']);

        $this->actingAs($admin)->post(route('admin.students.store'), [
            'matricule' => 'STU000004',
            'nom' => 'Rabe',
            'prenom' => 'Hery',
            'naissance' => '2003-07-08',
            'contact_parent' => '0342222222',
            'account_action' => 'link',
            'account_user_id' => $account->id,
        ])->assertRedirect();

        $student = Student::where('matricule', 'STU000004')->firstOrFail();
        $this->assertSame($account->id, $student->user_id);
        $this->assertDatabaseHas('users', ['id' => $account->id, 'role' => 'student', 'name' => 'Hery Rabe']);
    }

    public function test_student_show_displays_account_academic_financial_and_application_data(): void
    {
        Schema::create('internship_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('offer_id');
            $table->timestamp('applied_at');
            $table->unsignedTinyInteger('compatibility_score')->nullable();
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('school_fees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('level_id');
            $table->unsignedBigInteger('school_year_id');
            $table->integer('montant');
            $table->timestamps();
        });

        $admin = User::factory()->create(['role' => 'admin']);
        $account = User::factory()->create(['name' => 'Miora Rakoto', 'email' => 'miora@example.test', 'role' => 'student']);
        $student = Student::create([
            'matricule' => 'STU000003',
            'nom' => 'Rakoto',
            'prenom' => 'Miora',
            'naissance' => '2004-05-06',
            'contact_parent' => '0340000000',
            'user_id' => $account->id,
        ]);
        $yearId = DB::table('school_years')->insertGetId([
            'school_year' => '2025-2026', 'current_Year' => '2025', 'active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $levelId = DB::table('levels')->insertGetId([
            'code' => 'L1', 'libelle' => 'Première année', 'scolarite' => 0, 'school_year_id' => $yearId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $classId = DB::table('classes')->insertGetId([
            'libelle' => 'L1-A', 'level_id' => $levelId, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('attributions')->insert([
            'student_id' => $student->id, 'classe_id' => $classId, 'school_year_id' => $yearId,
            'comments' => null, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('school_fees')->insert([
            'level_id' => $levelId, 'school_year_id' => $yearId, 'montant' => 1000,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('payments')->insert([
            'student_id' => $student->id, 'classe_id' => $classId, 'school_year_id' => $yearId, 'montant' => 250,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('internship_applications')->insert([
            'user_id' => $account->id, 'offer_id' => 'developpeur-web-laravel', 'applied_at' => now(),
            'compatibility_score' => 75, 'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('admin.students.show', $student))
            ->assertOk()
            ->assertSee('Miora Rakoto')
            ->assertSee('miora@example.test')
            ->assertSee('Première année')
            ->assertSee('L1-A')
            ->assertSee('250')
            ->assertSee('75 %');
    }

    public function test_student_form_validates_required_fields_and_duplicate_matricules(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        Student::create([
            'matricule' => 'ETU00001',
            'nom' => 'Rakoto',
            'prenom' => 'Miora',
            'naissance' => '2004-05-06',
            'contact_parent' => '0340000000',
        ]);

        $this->from(route('admin.students.create'))
            ->post(route('admin.students.store'), [
                'matricule' => 'ETU00001',
                'nom' => 'Rabe',
                'prenom' => 'Hery',
                'naissance' => '2003-02-03',
                'contact_parent' => '0341111111',
            ])
            ->assertSessionHasErrors('matricule');
    }

    public function test_student_with_existing_school_records_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = Student::create([
            'matricule' => 'ETU00001',
            'nom' => 'Rakoto',
            'prenom' => 'Miora',
            'naissance' => '2004-05-06',
            'contact_parent' => '0340000000',
        ]);
        DB::table('attributions')->insert([
            'student_id' => $student->id,
            'classe_id' => 1,
            'school_year_id' => 1,
            'comments' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.students.destroy', $student))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('students', ['id' => $student->id]);
    }
}
