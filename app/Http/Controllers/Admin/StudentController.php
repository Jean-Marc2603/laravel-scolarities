<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolFees;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use App\Services\InternshipOfferCatalog;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $students = Student::query()
            ->with('user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('matricule', 'like', '%'.$search.'%')
                        ->orWhere('nom', 'like', '%'.$search.'%')
                        ->orWhere('prenom', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('nom')
            ->orderBy('prenom')
            ->paginate(15)
            ->withQueryString();

        return view('admin.students.index', compact('students', 'search'));
    }

    public function create(): View
    {
        return view('admin.students.create', [
            'student' => new Student(),
            'formAction' => route('admin.students.store'),
            'formMethod' => 'POST',
            'pageTitle' => 'Ajouter un étudiant',
            'availableAccounts' => $this->availableStudentAccounts(),
            'accountAction' => 'none',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $studentData = $this->validatedStudent($request);
        $accountChoice = $this->validatedAccountChoice($request, null, true);

        try {
            $student = DB::transaction(function () use ($studentData, $accountChoice) {
                $user = $this->resolveAccount($accountChoice, $studentData);
                if (Schema::hasColumn('students', 'user_id')) {
                    $studentData['user_id'] = $user?->id;
                }

                return Student::create($studentData);
            });
        } catch (QueryException $exception) {
            return back()->withInput()->withErrors([
                'account_user_id' => 'Ce compte étudiant est déjà associé à un dossier.',
            ]);
        }

        return redirect()->route('admin.students.show', $student)
            ->with('status', 'L’étudiant a été ajouté.');
    }

    public function show(Student $student, InternshipOfferCatalog $offerCatalog): View
    {
        $student->load(['user', 'attributions.classe.level', 'attributions.schoolYear', 'payments.classe', 'payments.schoolYear']);

        $activeSchoolYear = SchoolYear::where('active', 1)->first();
        $currentAttribution = $activeSchoolYear
            ? $student->attributions->firstWhere('school_year_id', $activeSchoolYear->id)
            : $student->attributions->sortByDesc('id')->first();

        $schoolYear = $currentAttribution?->schoolYear;
        $class = $currentAttribution?->classe;
        $fees = $class && $schoolYear
            ? SchoolFees::where('level_id', $class->level_id)->where('school_year_id', $schoolYear->id)->first()
            : null;
        $payments = $schoolYear
            ? $student->payments->where('school_year_id', $schoolYear->id)
            : $student->payments;
        $paidAmount = $payments->sum('montant');
        $remainingFees = $fees ? max(0, $fees->montant - $paidAmount) : null;

        $applications = collect();
        if ($student->user && Schema::hasTable('internship_applications')) {
            $applications = $student->user->internshipApplications()->latest('applied_at')->get()
                ->map(function ($application) use ($offerCatalog) {
                    $application->offer = $offerCatalog->find($application->offer_id);

                    return $application;
                });
        }

        $currentStage = null;
        foreach (['internships', 'stages'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'student_id')) {
                $currentStage = DB::table($table)->where('student_id', $student->id)->orderByDesc('id')->first();
                break;
            }
        }

        return view('admin.students.show', compact(
            'student', 'currentAttribution', 'schoolYear', 'class', 'fees', 'payments',
            'paidAmount', 'remainingFees', 'applications', 'currentStage'
        ));
    }

    public function edit(Student $student): View
    {
        $student->load('user');

        return view('admin.students.edit', [
            'student' => $student,
            'formAction' => route('admin.students.update', $student),
            'formMethod' => 'PUT',
            'pageTitle' => 'Modifier un étudiant',
            'availableAccounts' => $this->availableStudentAccounts($student),
            'accountAction' => $student->user_id ? 'link' : 'none',
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $studentData = $this->validatedStudent($request, $student);
        $accountChoice = $this->validatedAccountChoice($request, $student, false);

        try {
            DB::transaction(function () use ($student, $studentData, $accountChoice) {
                $user = $this->resolveAccount($accountChoice, $studentData);
                if ($accountChoice['action'] !== 'keep' && Schema::hasColumn('students', 'user_id')) {
                    $studentData['user_id'] = $user?->id;
                }
                $student->update($studentData);

                $linkedUserId = $accountChoice['action'] === 'keep' ? $student->user_id : $user?->id;
                if ($linkedUserId) {
                    User::whereKey($linkedUserId)->update([
                        'name' => trim($studentData['prenom'].' '.$studentData['nom']),
                    ]);
                }
            });
        } catch (QueryException $exception) {
            return back()->withInput()->withErrors([
                'account_user_id' => 'Ce compte étudiant est déjà associé à un dossier.',
            ]);
        }

        return redirect()->route('admin.students.show', $student)
            ->with('status', 'Les informations de l’étudiant ont été mises à jour.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        foreach (['attributions', 'payments', 'parent_student'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'student_id') &&
                DB::table($table)->where('student_id', $student->id)->exists()) {
                return redirect()->route('admin.students.index')
                    ->with('error', 'Cet étudiant est lié à des inscriptions, paiements ou informations familiales et ne peut pas être supprimé.');
            }
        }

        try {
            $student->delete();
        } catch (QueryException $exception) {
            return redirect()->route('admin.students.index')
                ->with('error', 'La suppression est impossible car cet étudiant est utilisé par d’autres données.');
        }

        return redirect()->route('admin.students.index')
            ->with('status', 'L’étudiant a été supprimé. Le compte utilisateur associé, s’il existe, est conservé.');
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, User> */
    private function availableStudentAccounts(?Student $student = null)
    {
        $query = User::where('role', 'student');
        if ($student && $student->user_id) {
            $query->where(function ($query) use ($student) {
                $query->whereDoesntHave('studentProfile')->orWhereKey($student->user_id);
            });
        } else {
            $query->whereDoesntHave('studentProfile');
        }

        return $query->orderBy('name')->get();
    }

    /** @return array<string, mixed> */
    private function validatedStudent(Request $request, ?Student $student = null): array
    {
        return $request->validate([
            'matricule' => ['required', 'string', 'max:10', Rule::unique('students', 'matricule')->ignore($student?->id)],
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'naissance' => ['required', 'date', 'before:today'],
            'contact_parent' => ['required', 'string', 'max:255'],
        ], [
            'matricule.required' => 'Le matricule est obligatoire.',
            'matricule.unique' => 'Ce matricule est déjà attribué.',
            'matricule.max' => 'Le matricule ne peut pas dépasser 10 caractères.',
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'naissance.required' => 'La date de naissance est obligatoire.',
            'naissance.before' => 'La date de naissance doit être antérieure à aujourd’hui.',
            'contact_parent.required' => 'Le contact du parent est obligatoire.',
        ]);
    }

    /** @return array<string, mixed> */
    private function validatedAccountChoice(Request $request, ?Student $student, bool $creating): array
    {
        $actions = $creating ? ['none', 'link', 'create'] : ['keep', 'none', 'link', 'create'];
        $data = $request->validate(['account_action' => ['required', Rule::in($actions)]]);
        $action = $data['account_action'];

        if ($action === 'link') {
            $data = $request->validate([
                'account_user_id' => ['required', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'student'))],
            ]);
            $user = User::findOrFail($data['account_user_id']);
            if ($user->studentProfile && $user->studentProfile->id !== $student?->id) {
                throw ValidationException::withMessages(['account_user_id' => 'Ce compte est déjà associé à un autre dossier étudiant.']);
            }

            return ['action' => 'link', 'user' => $user];
        }

        if ($action === 'create') {
            $data = $request->validate([
                'account_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                'account_password' => ['required', 'string', 'min:8', 'confirmed'],
            ], [
                'account_email.required' => 'L’adresse e-mail du compte est obligatoire.',
                'account_email.unique' => 'Cette adresse e-mail est déjà utilisée.',
                'account_password.required' => 'Le mot de passe du compte est obligatoire.',
                'account_password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',
                'account_password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            ]);

            return ['action' => 'create', 'email' => $data['account_email'], 'password' => $data['account_password']];
        }

        return ['action' => $action];
    }

    /** @param  array<string, mixed>  $studentData */
    private function resolveAccount(array $choice, array $studentData): ?User
    {
        if (in_array($choice['action'], ['keep', 'none'], true)) {
            return null;
        }

        if ($choice['action'] === 'link') {
            $choice['user']->forceFill([
                'name' => trim($studentData['prenom'].' '.$studentData['nom']),
            ])->save();

            return $choice['user'];
        }

        $user = new User();
        $user->forceFill([
            'name' => trim($studentData['prenom'].' '.$studentData['nom']),
            'email' => $choice['email'],
            'password' => Hash::make($choice['password']),
            'role' => 'student',
            'is_active' => true,
        ]);
        $user->save();

        return $user;
    }
}
