<?php

namespace App\Http\Controllers;

use App\Models\InternshipApplication;
use App\Models\InternshipTask;
use App\Models\StudentInternship;
use App\Services\InternshipOfferCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentInternshipController extends Controller
{
    private const INITIAL_TASKS = [
        ['name' => 'Analyse du projet', 'description' => 'Comprendre le contexte, les objectifs et les besoins du projet.'],
        ['name' => 'UML / conception', 'description' => 'Modéliser la solution et préparer sa conception technique.'],
        ['name' => 'Base de données', 'description' => 'Concevoir et mettre en place la structure des données nécessaire.'],
        ['name' => 'SCRUM / organisation', 'description' => 'Organiser le travail, les priorités et le suivi des tâches.'],
        ['name' => 'Développement / codage', 'description' => 'Réaliser les fonctionnalités prévues pour le stage.'],
        ['name' => 'Tests', 'description' => 'Vérifier le fonctionnement et corriger les anomalies détectées.'],
        ['name' => 'Documentation', 'description' => 'Documenter les choix, les fonctionnalités et les procédures.'],
        ['name' => 'Présentation finale', 'description' => 'Préparer et présenter le bilan final du stage.'],
    ];

    public function show(Request $request, InternshipOfferCatalog $catalog): View
    {
        $user = $request->user();
        abort_unless($user->role === 'student', 403);

        $application = $user->internshipApplications()
            ->where('status', InternshipApplication::STATUS_ACCEPTED)
            ->latest('applied_at')
            ->first();
        abort_unless((bool) $application, 403, 'Une candidature acceptée est nécessaire pour accéder à votre stage.');

        $internship = DB::transaction(function () use ($application, $user) {
            $internship = $application->studentInternship()->firstOrCreate([], ['user_id' => $user->id]);
            $this->ensureInitialTasks($internship);

            return $internship->load('tasks');
        });

        $offer = collect($catalog->all(true))->firstWhere('id', $application->offer_id);
        abort_unless((bool) $offer, 404);

        $progress = $internship->progressPercentage();

        return view('student-internship.show', compact('internship', 'application', 'offer', 'progress'));
    }

    public function updateTaskProgress(Request $request, int $task): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->role === 'student', 403);

        $progress = $request->validate([
            'progress' => ['required', 'integer', Rule::in([0, 20, 40, 60, 80, 100])],
        ])['progress'];

        $application = $user->internshipApplications()
            ->where('status', InternshipApplication::STATUS_ACCEPTED)
            ->latest('applied_at')
            ->first();
        abort_unless((bool) $application, 403);

        $internship = $application->studentInternship;
        abort_unless($internship, 404);

        $internshipTask = $internship->tasks()->findOrFail($task);
        $internshipTask->update(['progress' => $progress]);

        return redirect()->route('student.internship.show')->with('status', 'L’avancement de la tâche a été mis à jour.');
    }

    private function ensureInitialTasks(StudentInternship $internship): void
    {
        foreach (self::INITIAL_TASKS as $task) {
            $internship->tasks()->firstOrCreate(
                ['name' => $task['name']],
                ['description' => $task['description'], 'progress' => 0]
            );
        }
    }
}
