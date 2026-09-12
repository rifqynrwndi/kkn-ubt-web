<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/login';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'npm' => ['required', 'string', 'max:20', 'unique:mahasiswa,npm'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'prodi_id' => ['required', 'exists:program_studi,id'],
        ]);
    }

    protected function create(array $data)
    {
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole('mahasiswa');

            Mahasiswa::create([
                'user_id' => $user->id,
                'npm' => $data['npm'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'prodi_id' => $data['prodi_id'],
                'no_hp' => null,
                'foto' => null,
                'nama_ortu' => null,
                'no_hp_ortu' => null,
                'alamat_ortu' => null,
                'is_biodata_complete' => false,
            ]);

            return $user;
        } catch (\Exception $e) {
            report($e);
            return back()->withInput()->with('error', 'Registrasi gagal: ' . $e->getMessage());
        }
    }

    protected function registered(Request $request, $user)
    {
        return redirect()->route('login')
            ->with('success', 'Registrasi berhasil. Silakan login.');
    }

    public function showRegistrationForm()
    {
        $prodis = ProgramStudi::all();
        return view('auth.register', compact('prodis'));
    }
}
