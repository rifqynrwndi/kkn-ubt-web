@extends('layouts.app')

@section('title', 'Tambah Program Studi')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Tambah Program Studi</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-header">
                <h4>Form Program Studi</h4>
            </div>

            <div class="card-body">

                <form action="{{ route('fakultas-prodi.prodi.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>Fakultas</label>
                        <select name="fakultas_id"
                                class="form-control @error('fakultas_id') is-invalid @enderror"
                                required>

                            <option value="">-- Pilih Fakultas --</option>

                            @foreach($fakultas as $fak)
                                <option value="{{ $fak->id }}">
                                    {{ $fak->nama_fakultas }}
                                </option>
                            @endforeach

                        </select>

                        @error('fakultas_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Nama Program Studi</label>
                        <input type="text"
                               name="nama_prodi"
                               class="form-control @error('nama_prodi') is-invalid @enderror"
                               placeholder="Masukkan nama program studi">

                        @error('nama_prodi')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('fakultas-prodi.index') }}" class="btn btn-outline-secondary">
                            Kembali
                        </a>

                        <button class="btn btn-primary">
                            Simpan Prodi
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</section>
@endsection
