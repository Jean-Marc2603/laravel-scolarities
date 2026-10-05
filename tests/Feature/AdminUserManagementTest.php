<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
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
        Schema::create('cv_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedInteger('size_bytes');
            $table->json('analysis_results')->nullable();
            $table->timestamp('last_analyzed_at')->nullable();
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

    public function test_admin_can_list_search_filter_create_update_and_delete_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['name' => 'Miora Student', 'role' => 'student']);
        $company = User::factory()->create(['name' => 'Société Exemple', 'role' => 'company']);
        $this->actingAs($admin);

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Miora Student')
            ->assertSee('Société Exemple')
            ->assertSee('Actif');

        $this->get(route('admin.users.index', ['q' => 'Société', 'role' => 'company']))
            ->assertOk()
            ->assertSee('Société Exemple')
            ->assertDontSee('Miora Student');

        $this->post(route('admin.users.store'), [
            'name' => 'Superviseur Test',
            'email' => 'supervisor@example.test',
            'role' => 'supervisor',
            'is_active' => '1',
            'password' => 'Example1234',
            'password_confirmation' => 'Example1234',
        ])->assertRedirect();

        $newUser = User::where('email', 'supervisor@example.test')->firstOrFail();
        $this->assertSame('supervisor', $newUser->role);
        $this->assertTrue(Hash::check('Example1234', $newUser->password));

        $this->put(route('admin.users.update', $student), [
            'name' => 'Miora Modifiée',
            'email' => $student->email,
            'role' => 'student',
            'is_active' => '0',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['id' => $student->id, 'name' => 'Miora Modifiée', 'role' => 'student', 'is_active' => 0]);

        $this->delete(route('admin.users.destroy', $company))->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $company->id]);
    }

    public function test_admin_accounts_cannot_be_deleted_and_last_active_admin_cannot_be_disabled(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin);

        $this->delete(route('admin.users.destroy', $admin))->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);

        $this->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'admin',
            'is_active' => '0',
            'password' => '',
            'password_confirmation' => '',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_active' => 1]);
    }

    public function test_accounts_linked_to_student_data_are_preserved_and_can_be_deactivated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $studentUser = User::factory()->create(['role' => 'student']);
        $student = Student::create([
            'matricule' => 'STU000001',
            'nom' => 'Rakoto',
            'prenom' => 'Miora',
            'naissance' => '2004-05-06',
            'contact_parent' => '0340000000',
            'user_id' => $studentUser->id,
        ]);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $studentUser))->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $studentUser->id]);
        $this->assertDatabaseHas('students', ['id' => $student->id, 'user_id' => $studentUser->id]);

        $this->put(route('admin.users.update', $studentUser), [
            'name' => $studentUser->name,
            'email' => $studentUser->email,
            'role' => 'company',
            'is_active' => '1',
            'password' => '',
            'password_confirmation' => '',
        ])->assertSessionHasErrors('role');

        $this->put(route('admin.users.update', $studentUser), [
            'name' => $studentUser->name,
            'email' => $studentUser->email,
            'role' => 'student',
            'is_active' => '0',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['id' => $studentUser->id, 'is_active' => 0]);
    }

    public function test_disabled_account_cannot_login(): void
    {
        $disabled = User::factory()->create(['email' => 'disabled@example.test', 'is_active' => false]);

        $this->post('/login', ['email' => 'disabled@example.test', 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($disabled)->get(route('applications.index'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_active_administrator_accounts_are_redirected_to_the_admin_dashboard(): void
    {
        $firstAdmin = User::factory()->create(['email' => 'admin.one@example.test', 'role' => 'admin', 'is_active' => true]);
        $secondAdmin = User::factory()->create(['email' => 'admin.two@example.test', 'role' => 'admin', 'is_active' => true]);

        foreach ([$firstAdmin, $secondAdmin] as $admin) {
            $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
                ->assertRedirect(route('admin.dashboard'));
            $this->assertAuthenticatedAs($admin);
            $this->post('/logout');
            $this->assertGuest();
        }
    }
}
