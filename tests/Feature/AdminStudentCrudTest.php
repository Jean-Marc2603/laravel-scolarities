<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('matricule', 10);
            $table->string('nom');
            $table->string('prenom');
            $table->date('naissance');
            $table->string('contact_parent');
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

    public function test_existing_student_user_accounts_are_listed_and_editable_without_changing_their_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $studentAccount = User::factory()->create([
            'name' => 'Étudiant existant',
            'email' => 'etudiant@example.test',
            'role' => 'student',
        ]);
        $anotherAdmin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('Comptes étudiants')
            ->assertSee('Étudiant existant')
            ->assertSee('etudiant@example.test')
            ->assertDontSee($anotherAdmin->email);

        $this->get(route('admin.students.accounts.edit', $studentAccount))
            ->assertOk()
            ->assertSee('Modifier un compte étudiant');

        $this->put(route('admin.students.accounts.update', $studentAccount), [
            'name' => 'Nom mis à jour',
            'email' => 'nouveau@example.test',
        ])->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('users', [
            'id' => $studentAccount->id,
            'name' => 'Nom mis à jour',
            'email' => 'nouveau@example.test',
            'role' => 'student',
        ]);
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
        Schema::create('attributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
        });

        $admin = User::factory()->create(['role' => 'admin']);
        $student = Student::create([
            'matricule' => 'ETU00001',
            'nom' => 'Rakoto',
            'prenom' => 'Miora',
            'naissance' => '2004-05-06',
            'contact_parent' => '0340000000',
        ]);
        DB::table('attributions')->insert(['student_id' => $student->id]);

        $this->actingAs($admin)
            ->delete(route('admin.students.destroy', $student))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('students', ['id' => $student->id]);
    }
}
