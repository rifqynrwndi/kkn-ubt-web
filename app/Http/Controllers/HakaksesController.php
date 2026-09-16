<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class HakaksesController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->status === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        $hakakses = $query->orderBy('name')->paginate(15)->withQueryString();
        $roles = Role::orderBy('name')->pluck('name');

        return view('layouts.hakakses.index', compact('hakakses', 'roles'));
    }

    public function edit(int $id): View
    {
        $hakakses = User::findOrFail($id);

        return view('layouts.hakakses.edit', compact('hakakses'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'string', 'in:mahasiswa,pembimbing,superadmin,admin_lppm,admin_prodi'],
        ]);

        $user = User::findOrFail($id);
        $user->syncRoles([$request->role]);

        return redirect()->route('hakakses.index')
            ->with('success', 'Role pengguna berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->route('hakakses.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('hakakses.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
