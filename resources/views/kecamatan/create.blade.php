@extends('layouts.app')

@section('title', 'Tambah Kecamatan')

@section('content')
<section class="section">

    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Tambah Kecamatan</h1>

        <a href="{{ route('desa.index') }}"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i>
            Kembali
        </a>
    </div>

    <div class="section-body">

        <div class="card shadow-sm">

            <div class="card-header">
                <h4>Form Kecamatan</h4>
            </div>

            <div class="card-body">

                <form action="{{ route('desa.storeKecamatan') }}"
                      method="POST">

                    @csrf

                    <div class="form-group">
                        <label>Nama Kecamatan</label>

                        <input type="text"
                               name="nama_kecamatan"
                               class="form-control @error('nama_kecamatan') is-invalid @enderror"
                               value="{{ old('nama_kecamatan') }}"
                               placeholder="Masukkan nama kecamatan"
                               required>

                        @error('nama_kecamatan')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Kota/Kabupaten</label>

                        <input type="text"
                               name="kabupaten"
                               class="form-control @error('kabupaten') is-invalid @enderror"
                               value="{{ old('kabupaten') }}"
                               placeholder="Masukkan nama kabupaten"
                               required>

                        @error('kabupaten')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary px-4">
                            <i class="fas fa-save mr-1"></i>
                            Simpan Kecamatan
                        </button>
                    </div>

                </form>

            </div>

        </div>

    </div>

</section>
@endsection
