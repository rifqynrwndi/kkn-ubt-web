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
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
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

    /**
     * Create a new user instance after a valid registration.
     *
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

        $user->assignRole('mahasiswa');

        // auto create data mahasiswa
        \App\Models\Mahasiswa::create([
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
    }

    protected function registered(Request $request, $user)
    {
        Auth::logout();

        return redirect()->route('login')
            ->with('success', 'Registrasi berhasil. Silakan login.');
    }

    public function showRegistrationForm()
    {
        $prodis = ProgramStudi::all();

        return view('auth.register', compact('prodis'));
    }
}
