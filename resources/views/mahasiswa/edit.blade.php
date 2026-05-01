@extends('layouts.app')

@section('title', 'Edit Mahasiswa')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Edit Data Mahasiswa</h1>
    </div>

    <div class="section-body">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('mahasiswa.update', $mahasiswa->id) }}"
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">

                        <div class="form-group col-md-6">
                            <label>Nama Lengkap</label>
                            <input type="text" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $mahasiswa->name) }}"
                                required>

                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Email</label>
                            <input type="email" name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $mahasiswa->email) }}"
                                required>

                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Jenis Kelamin</label>
                            <select name="jenis_kelamin"
                                    class="form-control @error('jenis_kelamin') is-invalid @enderror"
                                    required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="L"
                                    {{ old('jenis_kelamin', $mahasiswa->mahasiswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>
                                    Laki-laki
                                </option>
                                <option value="P"
                                    {{ old('jenis_kelamin', $mahasiswa->mahasiswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>
                                    Perempuan
                                </option>
                            </select>

                            @error('jenis_kelamin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>NPM</label>
                            <input type="text" name="npm"
                                class="form-control @error('npm') is-invalid @enderror"
                                value="{{ old('npm', $mahasiswa->mahasiswa->npm) }}"
                                required>

                            @error('npm')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Program Studi</label>
                            <select name="prodi_id" class="form-control">
                                @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id }}"
                                        {{ $mahasiswa->mahasiswa->prodi_id == $prodi->id ? 'selected' : '' }}>
                                        {{ $prodi->nama_prodi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label>No HP</label>
                            <input type="text" name="no_hp"
                                   class="form-control"
                                   value="{{ old('no_hp', $mahasiswa->mahasiswa->no_hp) }}">
                        </div>

                        <div class="form-group col-md-6">
                            <label>Nama Orang Tua / Wali</label>
                            <input type="text"
                                name="nama_ortu"
                                class="form-control @error('nama_ortu') is-invalid @enderror"
                                value="{{ old('nama_ortu', $mahasiswa->mahasiswa->nama_ortu) }}">

                            @error('nama_ortu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>No HP Orang Tua / Wali</label>
                            <input type="text"
                                name="no_hp_ortu"
                                class="form-control @error('no_hp_ortu') is-invalid @enderror"
                                value="{{ old('no_hp_ortu', $mahasiswa->mahasiswa->no_hp_ortu) }}">

                            @error('no_hp_ortu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-12">
                            <label>Alamat Orang Tua / Wali</label>
                            <textarea name="alamat_ortu"
                                    rows="4"
                                    class="form-control @error('alamat_ortu') is-invalid @enderror">{{ old('alamat_ortu', $mahasiswa->mahasiswa->alamat_ortu) }}</textarea>

                            @error('alamat_ortu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Password Baru (Opsional)</label>
                            <input type="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror">

                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Konfirmasi Password</label>
                            <input type="password" name="password_confirmation"
                                   class="form-control">
                        </div>

                        <div class="form-group col-md-6">
                            <label>Foto Saat Ini</label>

                            <div class="mb-2">
                                <img
                                    src="{{ $mahasiswa->mahasiswa->foto
                                        ? asset('storage/'.$mahasiswa->mahasiswa->foto)
                                        : asset('img/avatar/avatar-1.png') }}"
                                    width="120"
                                    height="120"
                                    class="rounded-circle shadow"
                                    style="object-fit: cover;"
                                >
                            </div>

                            <input type="file"
                                name="foto"
                                class="form-control @error('foto') is-invalid @enderror"
                                accept=".png,.jpg,.jpeg,image/png,image/jpeg">

                            @error('foto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="text-muted">
                                Format: PNG / JPG / JPEG (Max 2MB)
                            </small>
                        </div>

                    </div>

                    <div class="text-right">
                        <a href="{{ route('mahasiswa.index') }}"
                           class="btn btn-outline-secondary">
                            Batal
                        </a>

                        <button type="submit" class="btn btn-primary">
                            Update Mahasiswa
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</section>
@endsection
