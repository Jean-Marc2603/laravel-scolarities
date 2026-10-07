<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternshipApplication;
use App\Models\StudentInternship;
use App\Models\User;
use App\Services\InternshipOfferCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(InternshipOfferCatalog $offerCatalog): View
    {
        $statistics = $this->buildStatistics($offerCatalog);

        return view('admin.dashboard', compact('statistics'));
    }

    public function liveStatistics(InternshipOfferCatalog $offerCatalog): JsonResponse
    {
        $statistics = collect($this->buildStatistics($offerCatalog))
            ->mapWithKeys(fn (array $statistic) => [$statistic['key'] => $statistic['value']]);

        return response()->json([
            'statistics' => $statistics,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    private function buildStatistics(InternshipOfferCatalog $offerCatalog): array
    {
        $offers = $offerCatalog->all();
        $companies = Schema::hasTable('companies')
            ? DB::table('companies')->count()
            : collect($offers)->pluck('company')->unique()->count();

        $applicationCount = Schema::hasTable('internship_applications')
            ? InternshipApplication::count()
            : 0;

        $activeInternshipCount = $this->countActiveInternships();
        $hasInternshipTracking = $this->hasAnyTable(['student_internships', 'internships', 'stages']);
        $conventionCount = $this->countFirstExistingTable(['conventions', 'internship_conventions']);

        $statistics = [
            [
                'key' => 'students',
                'label' => 'Étudiants',
                'value' => User::where('role', 'student')->count(),
                'description' => 'Comptes utilisateurs au rôle étudiant',
                'icon' => 'users',
                'color' => 'indigo',
            ],
            [
                'key' => 'companies',
                'label' => 'Entreprises',
                'value' => $companies,
                'description' => Schema::hasTable('companies') ? 'Entreprises enregistrées' : 'Entreprises représentées dans les offres disponibles',
                'icon' => 'building',
                'color' => 'sky',
            ],
            [
                'key' => 'offers',
                'label' => 'Offres de stage',
                'value' => count($offers),
                'description' => 'Offres actuellement proposées',
                'icon' => 'briefcase',
                'color' => 'violet',
            ],
            [
                'key' => 'applications',
                'label' => 'Candidatures',
                'value' => $applicationCount,
                'description' => 'Candidatures reçues',
                'icon' => 'document',
                'color' => 'amber',
            ],
            [
                'key' => 'internships',
                'label' => 'Stages en cours',
                'value' => $activeInternshipCount,
                'description' => $activeInternshipCount === 0 && ! $hasInternshipTracking ? 'Module de suivi non configuré' : 'Stages actuellement actifs',
                'icon' => 'clock',
                'color' => 'emerald',
            ],
            [
                'key' => 'conventions',
                'label' => 'Conventions',
                'value' => $conventionCount,
                'description' => $conventionCount === 0 && ! $this->hasAnyTable(['conventions', 'internship_conventions']) ? 'Module de conventions non configuré' : 'Conventions enregistrées',
                'icon' => 'shield',
                'color' => 'rose',
            ],
        ];

        return $statistics;
    }

    private function countActiveInternships(): int
    {
        if (Schema::hasTable('student_internships') && Schema::hasTable('internship_applications')) {
            $query = StudentInternship::query()
                ->whereHas('application', fn ($application) => $application->where('status', InternshipApplication::STATUS_ACCEPTED));

            if (Schema::hasTable('internship_tasks')) {
                $query->where(function ($stages) {
                    $stages->whereDoesntHave('tasks')
                        ->orWhereHas('tasks', fn ($tasks) => $tasks->where('progress', '<', 100));
                });
            }

            return $query->count();
        }

        return $this->countFirstExistingTable(['internships', 'stages']);
    }

    private function countFirstExistingTable(array $tables): int
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                return DB::table($table)->count();
            }
        }

        return 0;
    }

    private function hasAnyTable(array $tables): bool
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                return true;
            }
        }

        return false;
    }
}
