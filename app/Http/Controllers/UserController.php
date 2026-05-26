<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function authorizeAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->canManageUsers(), 403, 'Hanya Administrator yang dapat mengelola pengguna.');
    }

    public function index()
    {
        $this->authorizeAdmin();
        $users = User::orderBy('name')->get();
        $roles = UserRole::cases();
        return view('users.index', compact('users', 'roles'));
    }

    public function edit(User $user)
    {
        $this->authorizeAdmin();
        $roles = UserRole::cases();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeAdmin();
        $validated = $request->validate([
            'role'  => 'required|in:' . implode(',', array_column(UserRole::cases(), 'value')),
            'title' => 'nullable|string|max:100',
        ]);

        $user->update($validated);

        return redirect()->route('users.index')->with('success', "Role {$user->name} berhasil diperbarui.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'new_password.min'       => 'Password minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user->update(['password' => Hash::make($validated['new_password'])]);

        return redirect()->route('users.index')
            ->with('success', "Password {$user->name} berhasil direset.");
    }

    // ── Employee Login Account Management ──────────────────────────────────

    public function createEmployeeAccount(Request $request, Employee $employee)
    {
        $this->authorizeAdmin();

        if ($employee->hasLoginAccount()) {
            return back()->with('error', 'Karyawan ini sudah memiliki akun login.');
        }

        $validated = $request->validate([
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique'           => 'Email sudah digunakan oleh pengguna lain.',
            'password.min'           => 'Password minimal 8 karakter.',
            'password.confirmed'     => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name'     => $employee->name,
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => UserRole::Employee->value,
            'title'    => $employee->position,
        ]);

        $employee->update(['user_id' => $user->id]);

        return redirect()->route('employees.show', $employee)
            ->with('success', "Akun login untuk {$employee->name} berhasil dibuat.");
    }

    public function revokeEmployeeAccount(Employee $employee)
    {
        $this->authorizeAdmin();

        if (!$employee->hasLoginAccount()) {
            return back()->with('error', 'Karyawan ini tidak memiliki akun login.');
        }

        $user = $employee->user;
        $employee->update(['user_id' => null]);

        if ($user && $user->role === UserRole::Employee) {
            $user->delete();
        }

        return redirect()->route('employees.show', $employee)
            ->with('success', "Akun login {$employee->name} berhasil dihapus.");
    }

    public function toggleActive(User $user)
    {
        $this->authorizeAdmin();
        abort_if($user->id === auth()->id(), 422, 'Tidak dapat menonaktifkan akun sendiri.');

        $user->update(['is_active' => !$user->is_active]);
        $label = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()->with('success', "Akun {$user->name} berhasil {$label}.");
    }
}
