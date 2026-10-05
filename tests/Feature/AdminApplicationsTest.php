<?php

namespace Tests\Feature;

use App\Models\InternshipApplication;
use App\Models\User;
use App\Services\InternshipOfferCatalog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminApplicationsTest extends TestCase
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
            $table->string('profile_photo_path', 2048)->nullable();
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
            $table->unique(['user_id', 'offer_id']);
        });
    }

    public function test_admin_can_list_applications_and_update_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Test']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'Alice Étudiant']);
        $offer = app(InternshipOfferCatalog::class)->all()[0];

        $application = InternshipApplication::create([
            'user_id' => $student->id,
            'offer_id' => $offer['id'],
            'applied_at' => now(),
            'compatibility_score' => 82,
            'status' => InternshipApplication::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.applications.index'))
            ->assertOk()
            ->assertSee('Candidatures')
            ->assertSee('Alice Étudiant')
            ->assertSee($offer['title'])
            ->assertSee('En attente');

        $this->actingAs($admin)
            ->patch(route('admin.applications.updateStatus', $application), ['status' => InternshipApplication::STATUS_ACCEPTED])
            ->assertRedirect(route('admin.applications.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('internship_applications', [
            'id' => $application->id,
            'status' => InternshipApplication::STATUS_ACCEPTED,
        ]);
    }
}
