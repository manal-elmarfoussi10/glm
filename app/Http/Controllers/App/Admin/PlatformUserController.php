<?php

namespace App\Http\Controllers\App\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PlatformUserController extends Controller
{
    private function platformRoles(): array
    {
        return ['super_admin', 'support'];
    }

    private function baseQuery()
    {
        return User::whereIn('role', $this->platformRoles());
    }

    public function index(Request $request): View
    {
        $query = $this->baseQuery()
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->search . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)->orWhere('email', 'like', $term);
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->role));

        $stats = [
            'total' => (clone $query)->count(),
            'active' => $this->baseQuery()->where('status', 'active')->count(),
            'suspended' => $this->baseQuery()->where('status', 'suspended')->count(),
        ];

        $users = (clone $query)->orderBy('name')->paginate(15)->withQueryString();

        return view('app.admin.users.index', [
            'title' => 'Utilisateurs plateforme',
            'users' => $users,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        $allowedRoles = auth()->user()->role === 'super_admin'
            ? ['super_admin' => 'Super Admin (accès complet)', 'support' => 'Support (entreprises, pas paramètres)']
            : ['support' => 'Support (entreprises, pas paramètres)'];

        return view('app.admin.users.create', [
            'title' => 'Nouvel utilisateur plateforme',
            'allowedRoles' => $allowedRoles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $allowedRoles = auth()->user()->role === 'super_admin'
            ? ['super_admin', 'support']
            : ['support'];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'string', 'min:8', Password::default()],
            'role' => 'required|string|in:' . implode(',', $allowedRoles),
            'status' => 'required|string|in:active,suspended',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        AuditLog::log('platform_user.created', User::class, (int) $user->id, null, ['email' => $user->email, 'role' => $user->role]);

        return redirect()
            ->route('app.admin.users.index')
            ->with('success', 'Utilisateur créé.');
    }

    public function edit(User $user): View|RedirectResponse
    {
        if (! in_array($user->role, $this->platformRoles(), true)) {
            abort(404, 'Utilisateur plateforme non trouvé.');
        }

        $allowedRoles = auth()->user()->role === 'super_admin'
            ? ['super_admin' => 'Super Admin', 'support' => 'Support']
            : ['support' => 'Support'];
        $canChangeRole = auth()->user()->role === 'super_admin' || $user->role !== 'super_admin';

        return view('app.admin.users.edit', [
            'title' => 'Modifier – ' . $user->name,
            'user' => $user,
            'allowedRoles' => $allowedRoles,
            'canChangeRole' => $canChangeRole,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if (! in_array($user->role, $this->platformRoles(), true)) {
            abort(404);
        }

        $allowedRoles = auth()->user()->role === 'super_admin'
            ? ['super_admin', 'support']
            : ['support'];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|in:' . implode(',', $allowedRoles),
            'status' => 'required|string|in:active,suspended',
        ]);

        if (auth()->user()->role === 'support' && $user->role === 'super_admin') {
            unset($validated['role']);
        }

        $old = $user->only(array_keys($validated));
        $user->update($validated);
        AuditLog::log('platform_user.updated', User::class, (int) $user->id, $old, $validated);

        return redirect()
            ->route('app.admin.users.index')
            ->with('success', 'Utilisateur mis à jour.');
    }

    public function activate(User $user): RedirectResponse
    {
        if (! in_array($user->role, $this->platformRoles(), true)) {
            abort(404);
        }

        $previousStatus = $user->status;
        $user->update(['status' => 'active']);
        AuditLog::log('platform_user.activated', User::class, (int) $user->id, ['status' => $previousStatus], ['status' => 'active']);

        return back()->with('success', 'Compte activé.');
    }

    public function suspend(User $user): RedirectResponse
    {
        if (! in_array($user->role, $this->platformRoles(), true)) {
            abort(404);
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas suspendre votre propre compte.');
        }

        $previousStatus = $user->status;
        $user->update(['status' => 'suspended']);
        AuditLog::log('platform_user.suspended', User::class, (int) $user->id, ['status' => $previousStatus], ['status' => 'suspended']);

        return back()->with('success', 'Compte suspendu.');
    }

    public function forcePasswordReset(User $user): RedirectResponse
    {
        if (! in_array($user->role, $this->platformRoles(), true)) {
            abort(404);
        }

        $newPassword = \Illuminate\Support\Str::random(12);
        $user->update(['password' => Hash::make($newPassword)]);
        AuditLog::log('platform_user.password_reset', User::class, (int) $user->id, null, []);

        return back()->with('success', 'Mot de passe réinitialisé. Transmettez-le à l’utilisateur (affiché une seule fois) :')->with('new_password', $newPassword);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if (! in_array($user->role, $this->platformRoles(), true)) {
            abort(404);
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        if (auth()->user()->role === 'support' && $user->role === 'super_admin') {
            abort(403, 'Le rôle Support ne peut pas supprimer un super administrateur.');
        }

        $superAdminCount = $this->baseQuery()->where('role', 'super_admin')->count();
        if ($user->role === 'super_admin' && $superAdminCount <= 1) {
            return back()->with('error', 'Impossible de supprimer le dernier super administrateur.');
        }

        $email = $user->email;
        $userId = (int) $user->id;
        $user->delete();
        AuditLog::log('platform_user.deleted', User::class, $userId, ['email' => $email], null);

        return redirect()
            ->route('app.admin.users.index')
            ->with('success', 'Utilisateur supprimé.');
    }
}
