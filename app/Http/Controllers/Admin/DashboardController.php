<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternshipApplication;
use App\Models\Student;
use App\Services\InternshipOfferCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(InternshipOfferCatalog $offerCatalog): View
    {
        $offers = $offerCatalog->all();
        $companies = Schema::hasTable('companies')
            ? DB::table('companies')->count()
            : collect($offers)->pluck('company')->unique()->count();

        $applicationCount = Schema::hasTable('internship_applications')
            ? InternshipApplication::count()
            : 0;

        $activeInternshipCount = $this->countFirstExistingTable(['internships', 'stages']);
        $conventionCount = $this->countFirstExistingTable(['conventions', 'internship_conventions']);

        $statistics = [
            [
                'label' => 'Étudiants',
                'value' => Student::count(),
                'description' => 'Étudiants enregistrés',
                'icon' => 'users',
                'color' => 'indigo',
            ],
            [
                'label' => 'Entreprises',
                'value' => $companies,
                'description' => Schema::hasTable('companies') ? 'Entreprises enregistrées' : 'Entreprises représentées dans les offres disponibles',
                'icon' => 'building',
                'color' => 'sky',
            ],
            [
                'label' => 'Offres de stage',
                'value' => count($offers),
                'description' => 'Offres actuellement proposées',
                'icon' => 'briefcase',
                'color' => 'violet',
            ],
            [
                'label' => 'Candidatures',
                'value' => $applicationCount,
                'description' => 'Candidatures reçues',
                'icon' => 'document',
                'color' => 'amber',
            ],
            [
                'label' => 'Stages en cours',
                'value' => $activeInternshipCount,
                'description' => $activeInternshipCount === 0 && ! $this->hasAnyTable(['internships', 'stages']) ? 'Module de suivi non configuré' : 'Stages actuellement actifs',
                'icon' => 'clock',
                'color' => 'emerald',
            ],
            [
                'label' => 'Conventions',
                'value' => $conventionCount,
                'description' => $conventionCount === 0 && ! $this->hasAnyTable(['conventions', 'internship_conventions']) ? 'Module de conventions non configuré' : 'Conventions enregistrées',
                'icon' => 'shield',
                'color' => 'rose',
            ],
        ];

        return view('admin.dashboard', compact('statistics'));
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
