@extends('layouts.app')

@section('title', 'Tambah Mahasiswa')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Tambah Mahasiswa</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('mahasiswa.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>NPM</label>
                    <input type="text" name="npm" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control" required>
                        <option value="">
                            Pilih Jenis Kelamin
                        </option>
                        <option value="L">
                            Laki-laki
                        </option>
                        <option value="P">
                            Perempuan
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Email Aktif</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <div class="text-right">
                        <a href="{{ route('mahasiswa.index') }}"
                           class="btn btn-outline-secondary">
                            Batal
                        </a>

                        <button type="submit" class="btn btn-primary">
                            Simpan
                        </button>
                    </div>
            </form>
        </div>
    </div>
</section>
@endsection
