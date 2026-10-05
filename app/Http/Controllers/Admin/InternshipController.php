<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\InternshipOffer;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class InternshipController extends Controller
{
    public function index(Request $request): View
    {
        $table = $this->resolveTable();

        $search = trim((string) $request->query('q', ''));
        $studentFilter = (string) $request->query('student', '');
        $companyFilter = (string) $request->query('company', '');
        $statusFilter = (string) $request->query('status', '');

        $internships = collect();
        $students = collect();
        $companies = collect();
        $statuses = collect();

        if ($table) {
            $rows = DB::table($table)
                ->orderByDesc('start_date')
                ->orderByDesc('id')
                ->get();

            $internships = $rows->map(fn ($row) => $this->hydrateStage($row))->filter();

            $students = DB::table('students')
                ->select('id', 'nom', 'prenom', 'user_id')
                ->get()
                ->map(function ($student) {
                    $studentName = trim(($student->prenom ?? '').' '.($student->nom ?? '')) ?: 'Étudiant inconnu';

                    return (object) [
                        'id' => $student->id,
                        'name' => $studentName,
                    ];
                })
                ->sortBy('name')
                ->values();

            $companies = DB::table('companies')
                ->select('id', 'name')
                ->get()
                ->map(fn ($company) => (object) ['id' => $company->id, 'name' => $company->name])
                ->sortBy('name')
                ->values();

            $statuses = $internships->pluck('status')->filter()->unique()->sort()->values();

            $internships = $internships->filter(function ($internship) use ($search, $studentFilter, $companyFilter, $statusFilter) {
                $matchesSearch = $search === '' || str_contains(strtolower($this->searchableText($internship)), strtolower($search));
                $matchesStudent = $studentFilter === '' || (string) $internship['student_id'] === $studentFilter;
                $matchesCompany = $companyFilter === '' || (string) $internship['company_id'] === $companyFilter;
                $matchesStatus = $statusFilter === '' || $internship['status'] === $statusFilter;

                return $matchesSearch && $matchesStudent && $matchesCompany && $matchesStatus;
            })->values();
        }

        return view('admin.internships.index', compact(
            'internships',
            'search',
            'studentFilter',
            'companyFilter',
            'statusFilter',
            'students',
            'companies',
            'statuses'
        ));
    }

    public function show($internship): View
    {
        $table = $this->resolveTable();
        abort_unless($table, 404);

        $row = DB::table($table)->where('id', $internship)->first();
        abort_unless($row, 404);

        $stage = $this->hydrateStage($row);

        return view('admin.internships.show', compact('stage'));
    }

    private function resolveTable(): ?string
    {
        foreach (['internships', 'stages'] as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return null;
    }

    private function hydrateStage(object $row): ?array
    {
        $studentId = $row->student_id ?? null;
        $companyId = $row->company_id ?? null;
        $offerId = $row->offer_id ?? null;

        $student = $studentId !== null && Schema::hasTable('students')
            ? Student::query()->find($studentId)
            : null;

        $studentName = $student ? trim(($student->prenom ?? '').' '.($student->nom ?? '')) : 'Étudiant inconnu';

        $company = $companyId !== null && Schema::hasTable('companies')
            ? Company::query()->find($companyId)
            : null;

        $offer = $offerId !== null && Schema::hasTable('internship_offers')
            ? $this->findOfferById($offerId)
            : null;

        $dateStart = $row->start_date ?? $row->date_debut ?? null;
        $dateEnd = $row->end_date ?? $row->date_fin ?? null;
        $status = $row->status ?? null;
        $importantInformation = $row->important_information ?? $row->information_important ?? $row->details ?? null;

        return [
            'id' => $row->id,
            'student_id' => $studentId,
            'student_name' => $studentName,
            'company_id' => $companyId,
            'company_name' => $company?->name ?? 'Entreprise inconnue',
            'offer_id' => $offerId,
            'offer_title' => $offer?->title ?? $this->resolveOfferTitle($row->offer_id ?? null),
            'start_date' => $dateStart,
            'end_date' => $dateEnd,
            'status' => $status ?? 'non_renseigne',
            'important_information' => $importantInformation,
            'raw' => $row,
        ];
    }

    private function findOfferById($offerId): ?InternshipOffer
    {
        if ($offerId === null) {
            return null;
        }

        $offer = InternshipOffer::query()->find($offerId);

        if ($offer) {
            return $offer;
        }

        return DB::table('internship_offers')->where('id', $offerId)->first()
            ? InternshipOffer::query()->find($offerId)
            : null;
    }

    private function resolveOfferTitle($offerId): ?string
    {
        if ($offerId === null || ! Schema::hasTable('internship_offers')) {
            return null;
        }

        $offer = DB::table('internship_offers')->where('id', $offerId)->first();

        return $offer?->title ?? null;
    }

    private function searchableText(array $internship): string
    {
        return collect([
            $internship['student_name'] ?? '',
            $internship['company_name'] ?? '',
            $internship['offer_title'] ?? '',
            $internship['status'] ?? '',
            $internship['important_information'] ?? '',
        ])->filter()->implode(' ');
    }
}
