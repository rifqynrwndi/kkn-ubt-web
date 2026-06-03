<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Fakultas;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use App\Models\DosenPembimbingLapangan;

class DosenPembimbingLapanganController extends Controller
{
    public function index(Request $request): View
    {
        $query = DosenPembimbingLapangan::with([
            'user',
            'fakultas'
        ]);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('user', fn($uq) => $uq->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%'))
                  ->orWhere('nidn', 'like', '%' . $request->search . '%')
                  ->orWhere('no_hp', 'like', '%' . $request->search . '%')
                  ->orWhereHas('fakultas', fn($fq) => $fq->where('nama_fakultas', 'like', '%' . $request->search . '%'));
            });
        }

        $dpl = $query->latest()->paginate(10)->withQueryString();

        return view('pembimbing.index', compact('dpl'));
    }

    public function create(): View
    {
        $fakultas = Fakultas::all();

        return view('pembimbing.create', compact('fakultas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',

            'nidn' => 'nullable|string|max:50',
            'nip' => 'nullable|string|max:50',

            'fakultas_id' => 'nullable|exists:fakultas,id',

            'no_hp' => 'nullable|string|max:20',

            'jenis_kelamin' => 'nullable|in:laki_laki,perempuan',

            'alamat' => 'nullable|string',

            'status' => 'required|in:aktif,nonaktif',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::transaction(function () use ($request) {

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make('kknubt2026'),
            'email_verified_at' => now(),
        ]);

            $user->assignRole('pembimbing');

            $fotoPath = $request->hasFile('foto')
                ? $request->file('foto')->store('foto-dpl', 'public')
                : null;

            DosenPembimbingLapangan::create([
                'user_id' => $user->id,
                'nidn' => $request->nidn,
                'fakultas_id' => $request->fakultas_id,
                'no_hp' => $request->no_hp,
                'jenis_kelamin' => $request->jenis_kelamin,
                'alamat' => $request->alamat,
                'foto' => $fotoPath,
                'status' => $request->status,
            ]);
        });

        return redirect()
            ->route('pembimbing-lapangan.index')
            ->with('success', 'DPL berhasil ditambahkan.');
    }

    public function show($id): View
    {
        $dpl = DosenPembimbingLapangan::with([
            'user',
            'fakultas'
        ])->findOrFail($id);

        return view('pembimbing.show', compact('dpl'));
    }

    public function edit($id): View
    {
        $dpl = DosenPembimbingLapangan::with('user')
            ->findOrFail($id);

        $fakultas = Fakultas::all();

        return view('pembimbing.edit', compact(
            'dpl',
            'fakultas'
        ));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $dpl = DosenPembimbingLapangan::with('user')
            ->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',

            'email' => 'required|email|unique:users,email,' . $dpl->user->id,

            'nidn' => 'nullable|string|max:50',

            'fakultas_id' => 'nullable|exists:fakultas,id',

            'status' => 'required|in:aktif,nonaktif',
            'password' => 'nullable|string|min:8',
            'no_hp' => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:laki_laki,perempuan',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::transaction(function () use ($request, $dpl) {

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $dpl->user->update($userData);

            $dplData = [
                'nidn' => $request->nidn,
                'fakultas_id' => $request->fakultas_id,
                'status' => $request->status,
                'no_hp' => $request->no_hp,
                'jenis_kelamin' => $request->jenis_kelamin,
                'alamat' => $request->alamat,
            ];

            if ($request->hasFile('foto')) {
                if ($dpl->foto) {
                    Storage::disk('public')->delete($dpl->foto);
                }
                $dplData['foto'] = $request->file('foto')->store('foto-dpl', 'public');
            }

            $dpl->update($dplData);
        });

        return redirect()
            ->route('pembimbing-lapangan.index')
            ->with('success', 'Data DPL berhasil diperbarui.');
    }

    public function destroy($id): RedirectResponse
    {
        $dpl = DosenPembimbingLapangan::with('user')
            ->findOrFail($id);

        if ($dpl->kelompokKkn()->exists()) {
            return back()->with('error', 'DPL tidak dapat dihapus karena masih membimbing kelompok.');
        }

        $dpl->user->delete();

        return redirect()
            ->route('pembimbing-lapangan.index')
            ->with('success', 'DPL berhasil dihapus.');
    }
}
