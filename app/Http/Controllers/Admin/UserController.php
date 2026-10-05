<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    private const ROLES = ['admin', 'student', 'company', 'supervisor'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $role = (string) $request->query('role', '');
        $status = (string) $request->query('status', '');

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when(in_array($role, self::ROLES, true), fn ($query) => $query->where('role', $role))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'role', 'status'));
    }

    public function create(): View
    {
        $user = new User();
        $user->forceFill(['role' => 'student', 'is_active' => true]);

        return view('admin.users.create', [
            'user' => $user,
            'pageTitle' => 'Ajouter un utilisateur',
            'formAction' => route('admin.users.store'),
            'formMethod' => 'POST',
            'roles' => self::ROLES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedUser($request, null, true);
        $password = $data['password'];
        unset($data['password']);

        $user = new User();
        $user->forceFill($data + ['password' => Hash::make($password)]);
        $user->save();

        return redirect()->route('admin.users.edit', $user)->with('status', 'Le compte utilisateur a été créé.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'pageTitle' => 'Modifier un utilisateur',
            'formAction' => route('admin.users.update', $user),
            'formMethod' => 'PUT',
            'roles' => self::ROLES,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validatedUser($request, $user, false);
        $password = $data['password'] ?? null;
        unset($data['password']);

        if ($user->studentProfile()->exists() && $data['role'] !== 'student') {
            return back()->withInput()->withErrors([
                'role' => 'Ce compte est associé à un dossier étudiant : son rôle doit rester student.',
            ]);
        }

        $studentProfile = $user->studentProfile;
        if ($studentProfile) {
            $data['name'] = trim($studentProfile->prenom.' '.$studentProfile->nom);
        }

        if ($user->is($request->user()) && (! $data['is_active'] || $data['role'] !== 'admin')) {
            return back()->withInput()->withErrors([
                'role' => 'Vous ne pouvez pas désactiver votre propre compte administrateur ni changer votre propre rôle ici.',
            ]);
        }

        $wouldRemoveActiveAdmin = $user->role === 'admin' && $user->is_active &&
            ($data['role'] !== 'admin' || ! $data['is_active']);
        if ($wouldRemoveActiveAdmin && User::where('role', 'admin')->where('is_active', true)->where('id', '!=', $user->id)->count() === 0) {
            return back()->withInput()->withErrors([
                'role' => 'Impossible de désactiver ou rétrograder le dernier compte administrateur actif.',
            ]);
        }

        if ($password !== null && $password !== '') {
            $data['password'] = Hash::make($password);
        }

        if ($data['email'] !== $user->email) {
            $data['email_verified_at'] = null;
        }

        $user->forceFill($data)->save();

        return redirect()->route('admin.users.index')->with('status', 'Le compte utilisateur a été mis à jour.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.users.index')
                ->with('error', 'La suppression des comptes administrateurs est protégée. Désactivez un compte si nécessaire.');
        }

        if ($user->studentProfile()->exists() || $user->cvDocument()->exists() || $user->internshipApplications()->exists()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Ce compte est lié à un dossier étudiant, un CV ou des candidatures. Désactivez-le pour préserver ces données.');
        }

        try {
            $user->delete();
        } catch (QueryException $exception) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Ce compte est encore lié à des données. Désactivez-le plutôt que de le supprimer.');
        }

        return redirect()->route('admin.users.index')->with('status', 'Le compte utilisateur a été supprimé.');
    }

    /** @return array<string, mixed> */
    private function validatedUser(Request $request, ?User $user, bool $creating): array
    {
        $passwordRules = $creating
            ? ['required', 'string', 'min:8', 'confirmed']
            : ['nullable', 'string', 'min:8', 'confirmed'];

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in(self::ROLES)],
            'is_active' => ['required', 'boolean'],
            'password' => $passwordRules,
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L’adresse e-mail est obligatoire.',
            'email.email' => 'Saisissez une adresse e-mail valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'role.in' => 'Sélectionnez un rôle autorisé.',
            'password.required' => 'Le mot de passe est obligatoire pour créer le compte.',
            'password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);
    }
}
