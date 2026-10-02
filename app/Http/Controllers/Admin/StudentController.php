<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $students = Student::query()
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

        $studentAccounts = User::query()
            ->where('role', 'student')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(15, ['*'], 'accounts_page')
            ->withQueryString();

        return view('admin.students.index', compact('students', 'studentAccounts', 'search'));
    }

    public function create(): View
    {
        return view('admin.students.create', [
            'student' => new Student(),
            'formAction' => route('admin.students.store'),
            'formMethod' => 'POST',
            'pageTitle' => 'Ajouter un étudiant',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $student = Student::create($this->validatedStudent($request));

        return redirect()->route('admin.students.show', $student)
            ->with('status', 'L’étudiant a été ajouté.');
    }

    public function show(Student $student): View
    {
        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        return view('admin.students.edit', [
            'student' => $student,
            'formAction' => route('admin.students.update', $student),
            'formMethod' => 'PUT',
            'pageTitle' => 'Modifier un étudiant',
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $student->update($this->validatedStudent($request, $student));

        return redirect()->route('admin.students.show', $student)
            ->with('status', 'Les informations de l’étudiant ont été mises à jour.');
    }

    public function editAccount(User $user): View
    {
        abort_unless($user->role === 'student', 404);

        return view('admin.students.edit-account', compact('user'));
    }

    public function updateAccount(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'student', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L’adresse e-mail est obligatoire.',
            'email.email' => 'Saisissez une adresse e-mail valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
        ]);

        $user->update($validated);

        return redirect()->route('admin.students.index')
            ->with('status', 'Le compte étudiant a été mis à jour.');
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
            ->with('status', 'L’étudiant a été supprimé.');
    }

    /** @return array<string, mixed> */
    private function validatedStudent(Request $request, ?Student $student = null): array
    {
        return $request->validate([
            'matricule' => [
                'required',
                'string',
                'max:10',
                Rule::unique('students', 'matricule')->ignore($student?->id),
            ],
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
}
