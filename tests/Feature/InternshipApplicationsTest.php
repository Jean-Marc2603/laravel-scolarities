<?php

namespace Tests\Feature;

use App\Models\CvDocument;
use App\Models\InternshipApplication;
use App\Models\User;
use App\Services\InternshipOfferCatalog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InternshipApplicationsTest extends TestCase
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
            $table->timestamps();
        });

        Schema::create('cv_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type', 150);
            $table->unsignedInteger('size_bytes');
            $table->json('analysis_results')->nullable();
            $table->timestamp('last_analyzed_at')->nullable();
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

    public function test_authenticated_student_can_apply_and_the_cv_score_is_saved(): void
    {
        $user = User::factory()->create();
        CvDocument::create([
            'user_id' => $user->id,
            'file_path' => 'cvs/'.$user->id.'/cv.pdf',
            'original_name' => 'cv.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 100,
            'analysis_results' => ['skills' => ['Laravel', 'PHP', 'MySQL']],
            'last_analyzed_at' => now(),
        ]);
        $offer = app(InternshipOfferCatalog::class)->all()[0];

        $response = $this->actingAs($user)->post(route('internships.apply', $offer['id']));

        $response->assertRedirect(route('applications.index'));
        $this->assertDatabaseHas('internship_applications', [
            'user_id' => $user->id,
            'offer_id' => $offer['id'],
            'compatibility_score' => 75,
            'status' => InternshipApplication::STATUS_PENDING,
        ]);
    }

    public function test_student_cannot_apply_to_the_same_offer_twice(): void
    {
        $user = User::factory()->create();
        $offer = app(InternshipOfferCatalog::class)->all()[0];
        $this->actingAs($user)->post(route('internships.apply', $offer['id']));

        $response = $this->post(route('internships.apply', $offer['id']));

        $response->assertRedirect(route('applications.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('internship_applications', 1);
    }

    public function test_offer_page_shows_apply_action_or_existing_application_state(): void
    {
        $user = User::factory()->create();
        $offer = app(InternshipOfferCatalog::class)->all()[0];
        $this->actingAs($user)
            ->get(route('internships.index'))
            ->assertOk()
            ->assertSee(route('internships.apply', $offer['id']), false)
            ->assertSee('Postuler');

        $application = $user->internshipApplications()->create([
            'offer_id' => $offer['id'],
            'applied_at' => now(),
            'status' => InternshipApplication::STATUS_PENDING,
        ]);

        $this->get(route('internships.index'))
            ->assertOk()
            ->assertSee('Déjà postulé')
            ->assertSee(route('applications.show', $application->id), false);
    }

    public function test_student_can_list_and_open_only_their_own_applications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $offer = app(InternshipOfferCatalog::class)->all()[0];
        $application = $user->internshipApplications()->create([
            'offer_id' => $offer['id'],
            'applied_at' => now(),
            'compatibility_score' => 75,
            'status' => InternshipApplication::STATUS_PENDING,
        ]);
        $otherApplication = $otherUser->internshipApplications()->create([
            'offer_id' => $offer['id'],
            'applied_at' => now(),
            'compatibility_score' => null,
            'status' => InternshipApplication::STATUS_PENDING,
        ]);

        $this->actingAs($user)->get(route('applications.index'))
            ->assertOk()
            ->assertSee($offer['title'])
            ->assertSee('75 %');

        $this->get(route('applications.show', $application->id))
            ->assertOk()
            ->assertSee($offer['details']);

        $this->get(route('applications.show', $otherApplication->id))->assertNotFound();
    }

    public function test_pending_application_can_be_cancelled_but_accepted_one_cannot(): void
    {
        $user = User::factory()->create();
        $offers = app(InternshipOfferCatalog::class)->all();
        $pending = $user->internshipApplications()->create([
            'offer_id' => $offers[0]['id'],
            'applied_at' => now(),
            'status' => InternshipApplication::STATUS_PENDING,
        ]);
        $accepted = $user->internshipApplications()->create([
            'offer_id' => $offers[1]['id'],
            'applied_at' => now(),
            'status' => InternshipApplication::STATUS_ACCEPTED,
        ]);

        $this->actingAs($user)->delete(route('applications.destroy', $pending->id))
            ->assertRedirect(route('applications.index'));
        $this->assertDatabaseMissing('internship_applications', ['id' => $pending->id]);

        $this->delete(route('applications.destroy', $accepted->id))->assertSessionHas('error');
        $this->assertDatabaseHas('internship_applications', ['id' => $accepted->id]);
    }

    public function test_guest_is_redirected_to_login_when_applying(): void
    {
        $offer = app(InternshipOfferCatalog::class)->all()[0];

        $this->post(route('internships.apply', $offer['id']))
            ->assertRedirect(route('login'));
    }
}
