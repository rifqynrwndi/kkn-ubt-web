<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Fakultas;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use App\Models\DosenPembimbingLapangan;

class DosenPembimbingLapanganController extends Controller
{
    public function index(): View
    {
        $dpl = DosenPembimbingLapangan::with([
            'user',
            'fakultas'
        ])
        ->latest()
        ->paginate(10);

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
        ]);

        DB::transaction(function () use ($request) {

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,

                // default password
                'password' => Hash::make('password'),
            ]);

            $user->assignRole('pembimbing');

            DosenPembimbingLapangan::create([
                'user_id' => $user->id,

                'nidn' => $request->nidn,

                'fakultas_id' => $request->fakultas_id,

                'no_hp' => $request->no_hp,

                'jenis_kelamin' => $request->jenis_kelamin,

                'alamat' => $request->alamat,

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
        ]);

        DB::transaction(function () use ($request, $dpl) {

            $dpl->user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            $dpl->update([
                'nidn' => $request->nidn,
                'fakultas_id' => $request->fakultas_id,
                'status' => $request->status,
            ]);
        });

        return redirect()
            ->route('pembimbing-lapangan.index')
            ->with('success', 'Data DPL berhasil diperbarui.');
    }

    public function destroy($id): RedirectResponse
    {
        $dpl = DosenPembimbingLapangan::with('user')
            ->findOrFail($id);

        $dpl->user->delete();

        return redirect()
            ->route('pembimbing-lapangan.index')
            ->with('success', 'DPL berhasil dihapus.');
    }
}
