@extends('layouts.app')

@section('title', 'Tambah Fakultas')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Tambah Fakultas</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-header">
                <h4>Form Fakultas</h4>
            </div>

            <div class="card-body">

                <form action="{{ route('fakultas-prodi.fakultas.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>Nama Fakultas</label>
                        <input type="text"
                               name="nama_fakultas"
                               class="form-control @error('nama_fakultas') is-invalid @enderror"
                               placeholder="Masukkan nama fakultas">

                        @error('nama_fakultas')
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
                            Simpan Fakultas
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</section>
@endsection
