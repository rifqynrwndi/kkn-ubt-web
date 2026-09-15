<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\DhsUploadedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class DhsController extends Controller
{
    public function show()
    {
        abort_if(! auth()->user()->hasRole('mahasiswa'), 403, 'Halaman ini hanya untuk mahasiswa.');

        $mahasiswa = auth()->user()->mahasiswa;

        return view('profile.dhs', compact('mahasiswa'));
    }

    public function store(Request $request)
    {
        abort_if(! auth()->user()->hasRole('mahasiswa'), 403, 'Halaman ini hanya untuk mahasiswa.');

        $mahasiswa = auth()->user()->mahasiswa;

        // Allow re-upload if rejected
        if ($mahasiswa->dhs_status === 'verified') {
            return redirect()
                ->route('biodata.edit')
                ->with('info', 'DHS Anda sudah terverifikasi.');
        }

        $request->validate([
            'dhs_file' => 'required|file|mimes:pdf|max:2048',
        ]);

        // Delete old file if exists
        if ($mahasiswa->dhs_path && Storage::exists($mahasiswa->dhs_path)) {
            Storage::delete($mahasiswa->dhs_path);
        }

        // Store new file (ikut default disk dari .env: local/public/s3)
        $path = $request->file('dhs_file')->store('dokumen-dhs');

        $mahasiswa->update([
            'dhs_path' => $path,
            'dhs_status' => 'pending',
            'dhs_catatan' => null,
        ]);

        // Notify all superadmins
        $adminIds = \DB::table('model_has_roles')
            ->where('role_id', \DB::table('roles')->where('name', 'superadmin')->first()->id)
            ->where('model_type', 'App\\Models\\User')
            ->pluck('model_id');

        $admins = User::whereIn('id', $adminIds)->get();
        Notification::send($admins, new DhsUploadedNotification($mahasiswa));

        return redirect()
            ->route('biodata.edit')
            ->with('success', 'DHS berhasil diunggah. Menunggu verifikasi admin.');
    }
}
