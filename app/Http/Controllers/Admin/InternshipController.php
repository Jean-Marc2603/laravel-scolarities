<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\InternshipApplication;
use App\Models\InternshipOffer;
use App\Models\Student;
use App\Models\StudentInternship;
use App\Services\InternshipOfferCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InternshipController extends Controller
{
    public function index(Request $request, InternshipOfferCatalog $catalog): View
    {
        $search = trim((string) $request->query('q', ''));
        $studentFilter = (string) $request->query('student', '');
        $companyFilter = (string) $request->query('company', '');
        $statusFilter = (string) $request->query('status', '');

        $internships = collect();
        $students = collect();
        $companies = collect();
        $statuses = collect();

        $internships = $this->studentProgressStages($catalog)->concat($this->legacyStages());
        $students = $internships->map(fn (array $stage) => (object) [
            'id' => $stage['student_id'],
            'name' => $stage['student_name'],
        ])->unique('id')->sortBy('name')->values();
        $companies = $internships->map(fn (array $stage) => (object) [
            'id' => $stage['company_id'] ?? $stage['company_name'],
            'name' => $stage['company_name'],
        ])->unique('id')->sortBy('name')->values();
        $statuses = $internships->pluck('status')->filter()->unique()->sort()->values();

        $internships = $internships->filter(function ($internship) use ($search, $studentFilter, $companyFilter, $statusFilter) {
            $matchesSearch = $search === '' || str_contains(mb_strtolower($this->searchableText($internship)), mb_strtolower($search));
            $matchesStudent = $studentFilter === '' || (string) $internship['student_id'] === $studentFilter;
            $matchesCompany = $companyFilter === '' || (string) ($internship['company_id'] ?? $internship['company_name']) === $companyFilter;
            $matchesStatus = $statusFilter === '' || $internship['status'] === $statusFilter;

            return $matchesSearch && $matchesStudent && $matchesCompany && $matchesStatus;
        })->sortByDesc('sort_date')->values();

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

    public function show(string $internship, InternshipOfferCatalog $catalog): View
    {
        if (str_starts_with($internship, 'progress-')) {
            $studentInternshipId = substr($internship, strlen('progress-'));
            abort_unless(ctype_digit($studentInternshipId), 404);

            $studentInternship = StudentInternship::query()
                ->with(['user.studentProfile', 'application', 'tasks'])
                ->whereHas('application', fn ($query) => $query->where('status', InternshipApplication::STATUS_ACCEPTED))
                ->findOrFail($studentInternshipId);
            abort_unless($studentInternship->application->user_id === $studentInternship->user_id, 404);

            $offer = collect($catalog->all(true))->firstWhere('id', $studentInternship->application->offer_id);
            $student = $studentInternship->user->studentProfile;
            $progress = $studentInternship->progressPercentage();
            $completedTasks = $studentInternship->tasks->where('progress', 100)->count();
            $stage = [
                'id' => $internship,
                'student_id' => $student?->id ?? $studentInternship->user_id,
                'student_name' => $student ? trim(($student->prenom ?? '').' '.($student->nom ?? '')) : $studentInternship->user->name,
                'student_email' => $studentInternship->user->email,
                'student_matricule' => $student?->matricule,
                'student_phone' => $student?->contact_parent,
                'company_id' => null,
                'company_name' => $offer['company'] ?? 'Entreprise inconnue',
                'offer_id' => $studentInternship->application->offer_id,
                'offer_title' => $offer['title'] ?? 'Offre indisponible',
                'start_date' => $studentInternship->start_date,
                'end_date' => $studentInternship->end_date,
                'status' => $progress === 100 ? 'completed' : 'active',
                'important_information' => $offer['details'] ?? null,
                'progress' => $progress,
                'completed_tasks' => $completedTasks,
                'total_tasks' => $studentInternship->tasks->count(),
                'tasks' => $studentInternship->tasks,
                'admin_observation' => $studentInternship->admin_observation,
                'student_internship' => $studentInternship,
            ];

            return view('admin.internships.show', compact('stage'));
        }

        $legacyId = $internship;
        if (str_starts_with($internship, 'legacy-')) {
            $prefix = 'legacy-'.$this->resolveTable().'-';
            abort_unless(str_starts_with($internship, $prefix), 404);
            $legacyId = substr($internship, strlen($prefix));
        }

        abort_unless(ctype_digit((string) $legacyId), 404);
        $table = $this->resolveTable();
        abort_unless((bool) $table, 404);

        $row = DB::table($table)->where('id', $legacyId)->first();
        abort_unless((bool) $row, 404);

        $stage = $this->hydrateStage($row);
        $stage['progress'] = null;
        $stage['completed_tasks'] = 0;
        $stage['total_tasks'] = 0;
        $stage['tasks'] = collect();
        $stage['student_email'] = null;
        $stage['student_matricule'] = null;
        $stage['student_phone'] = null;
        $stage['admin_observation'] = null;
        $stage['student_internship'] = null;

        return view('admin.internships.show', compact('stage'));
    }

    public function updateObservation(Request $request, int $studentInternship): RedirectResponse
    {
        $data = $request->validate([
            'admin_observation' => ['nullable', 'string', 'max:5000'],
        ]);

        $stage = StudentInternship::query()
            ->whereHas('application', fn ($query) => $query->where('status', InternshipApplication::STATUS_ACCEPTED))
            ->findOrFail($studentInternship);
        abort_unless($stage->application()->where('user_id', $stage->user_id)->exists(), 404);

        $stage->update(['admin_observation' => $data['admin_observation'] ?? null]);

        return redirect()->route('admin.internships.show', 'progress-'.$stage->id)
            ->with('status', 'L’observation du stage a été enregistrée.');
    }

    public function updateDates(Request $request, int $studentInternship): RedirectResponse
    {
        $dates = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        if (
            ! empty($dates['start_date'])
            && ! empty($dates['end_date'])
            && \Illuminate\Support\Carbon::parse($dates['end_date'])->lt(\Illuminate\Support\Carbon::parse($dates['start_date']))
        ) {
            throw ValidationException::withMessages([
                'end_date' => 'La date de fin doit être égale ou postérieure à la date de début.',
            ]);
        }

        $stage = StudentInternship::query()
            ->whereHas('application', fn ($query) => $query->where('status', InternshipApplication::STATUS_ACCEPTED))
            ->findOrFail($studentInternship);
        abort_unless($stage->application()->where('user_id', $stage->user_id)->exists(), 404);

        $stage->update([
            'start_date' => $dates['start_date'] ?? null,
            'end_date' => $dates['end_date'] ?? null,
        ]);

        return redirect()->route('admin.internships.show', 'progress-'.$stage->id)
            ->with('status', 'Les dates du stage ont été enregistrées.');
    }

    private function studentProgressStages(InternshipOfferCatalog $catalog)
    {
        if (! Schema::hasTable('student_internships')) {
            return collect();
        }

        $offers = collect($catalog->all(true));

        return StudentInternship::query()
            ->with(['user.studentProfile', 'application', 'tasks'])
            ->whereHas('application', fn ($query) => $query->where('status', InternshipApplication::STATUS_ACCEPTED))
            ->get()
            ->filter(fn (StudentInternship $stage) => $stage->application && $stage->application->user_id === $stage->user_id)
            ->map(function (StudentInternship $stage) use ($offers) {
                $offer = $offers->firstWhere('id', $stage->application->offer_id);
                $student = $stage->user?->studentProfile;
                $progress = $stage->progressPercentage();

                return [
                    'id' => 'progress-'.$stage->id,
                    'student_id' => $student?->id ?? $stage->user_id,
                    'student_name' => $student ? trim(($student->prenom ?? '').' '.($student->nom ?? '')) : ($stage->user?->name ?? 'Étudiant inconnu'),
                    'company_id' => null,
                    'company_name' => $offer['company'] ?? 'Entreprise inconnue',
                    'offer_id' => $stage->application->offer_id,
                    'offer_title' => $offer['title'] ?? 'Offre indisponible',
                    'start_date' => $stage->start_date,
                    'end_date' => $stage->end_date,
                    'status' => $progress === 100 ? 'completed' : 'active',
                    'progress' => $progress,
                    'completed_tasks' => $stage->tasks->where('progress', 100)->count(),
                    'total_tasks' => $stage->tasks->count(),
                    'sort_date' => $stage->start_date ?? $stage->created_at,
                    'important_information' => null,
                ];
            });
    }

    private function legacyStages()
    {
        $table = $this->resolveTable();
        if (! $table || ! Schema::hasColumn($table, 'id')) {
            return collect();
        }

        return DB::table($table)->orderByDesc('id')->get()
            ->map(fn ($row) => $this->hydrateStage($row))
            ->filter()
            ->map(function (array $stage) use ($table) {
                $stage['id'] = 'legacy-'.$table.'-'.$stage['id'];
                $stage['progress'] = null;
                $stage['completed_tasks'] = 0;
                $stage['total_tasks'] = 0;
                $stage['sort_date'] = $stage['start_date'] ?? null;

                return $stage;
            });
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

        $query = InternshipOffer::query();

        return is_numeric($offerId)
            ? $query->find($offerId) ?? InternshipOffer::query()->where('slug', $offerId)->first()
            : $query->where('slug', $offerId)->first();
    }

    private function resolveOfferTitle($offerId): ?string
    {
        if ($offerId === null || ! Schema::hasTable('internship_offers')) {
            return null;
        }

        $offer = is_numeric($offerId)
            ? DB::table('internship_offers')->where('id', $offerId)->first()
                ?? DB::table('internship_offers')->where('slug', $offerId)->first()
            : DB::table('internship_offers')->where('slug', $offerId)->first();

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
