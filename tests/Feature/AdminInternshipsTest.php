<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminInternshipsTest extends TestCase
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
            $table->string('role')->default('student');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('nom');
            $table->string('prenom');
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('internship_offers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->timestamps();
        });

        Schema::create('internships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('offer_id')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('active');
            $table->text('important_information')->nullable();
            $table->timestamps();
        });

        $student = DB::table('students')->insertGetId([
            'user_id' => 1,
            'nom' => 'Dupont',
            'prenom' => 'Alice',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $company = DB::table('companies')->insertGetId([
            'name' => 'Tech Solutions',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $offer = DB::table('internship_offers')->insertGetId([
            'title' => 'Développeuse Web',
            'company_id' => $company,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('internships')->insert([
            'student_id' => $student,
            'company_id' => $company,
            'offer_id' => $offer,
            'start_date' => '2026-01-10',
            'end_date' => '2026-03-10',
            'status' => 'active',
            'important_information' => 'Période de stage suivie par l’équipe produit.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_admin_can_view_existing_stages_and_read_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.internships.index'))
            ->assertOk()
            ->assertSee('Stages')
            ->assertSee('Alice Dupont')
            ->assertSee('Tech Solutions')
            ->assertSee('Développeuse Web');

        $internship = DB::table('internships')->first();

        $this->actingAs($admin)
            ->get(route('admin.internships.show', $internship->id))
            ->assertOk()
            ->assertSee('Détails du stage')
            ->assertSee('Période de stage suivie par l’équipe produit.');
    }
}
