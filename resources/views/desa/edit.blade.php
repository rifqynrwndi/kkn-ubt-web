@extends('layouts.app')

@section('title', 'Edit Desa')

@section('content')
<section class="section">

    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Edit Desa</h1>

        <div>
            <a href="{{ route('desa.show', $desa->id) }}"
               class="btn btn-info">
                <i class="fas fa-eye mr-1"></i>
                Detail
            </a>

            <a href="{{ route('desa.index') }}"
               class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Kembali
            </a>
        </div>
    </div>

    <div class="section-body">

        <div class="card shadow-sm">

            <div class="card-header">
                <h4>Form Edit Desa</h4>
            </div>

            <div class="card-body">

                <form action="{{ route('desa.update', $desa->id) }}"
                      method="POST">

                    @csrf
                    @method('PUT')

                    <div class="row">

                        {{-- NAMA DESA --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Desa</label>

                                <input type="text"
                                       name="nama_desa"
                                       class="form-control @error('nama_desa') is-invalid @enderror"
                                       value="{{ old('nama_desa', $desa->nama_desa) }}"
                                       placeholder="Masukkan nama desa"
                                       required>

                                @error('nama_desa')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        {{-- KECAMATAN --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kecamatan</label>

                                <select name="kecamatan_id"
                                        class="form-control @error('kecamatan_id') is-invalid @enderror"
                                        required>

                                    <option value="">
                                        Pilih Kecamatan
                                    </option>

                                    @foreach($kecamatan as $item)
                                        <option value="{{ $item->id }}"
                                            {{ old('kecamatan_id', $desa->kecamatan_id) == $item->id ? 'selected' : '' }}>
                                            {{ $item->nama_kecamatan }}
                                        </option>
                                    @endforeach

                                </select>

                                @error('kecamatan_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                    </div>

                    {{-- ALAMAT --}}
                    <div class="form-group">
                        <label>Alamat</label>

                        <textarea name="alamat"
                                  rows="4"
                                  class="form-control @error('alamat') is-invalid @enderror"
                                  placeholder="Masukkan alamat desa">{{ old('alamat', $desa->alamat) }}</textarea>

                        @error('alamat')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- STATUS --}}
                    <div class="form-group">
                        <label>Status Desa</label>

                        <select name="aktif"
                                class="form-control @error('aktif') is-invalid @enderror">

                            <option value="1"
                                {{ old('aktif', $desa->aktif) == 1 ? 'selected' : '' }}>
                                Aktif
                            </option>

                            <option value="0"
                                {{ old('aktif', $desa->aktif) == 0 ? 'selected' : '' }}>
                                Nonaktif
                            </option>

                        </select>

                        @error('aktif')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end">

                        <button type="submit"
                                class="btn btn-primary px-4">
                            <i class="fas fa-save mr-1"></i>
                            Simpan Perubahan
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>
</section>
@endsection
