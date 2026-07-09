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

                        {{-- KABUPATEN --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kabupaten</label>
                                <input type="text" name="kabupaten" class="form-control @error('kabupaten') is-invalid @enderror" value="{{ old('kabupaten', $desa->kecamatan->kabupaten ?? '') }}" placeholder="Nama kabupaten">
                                @error('kabupaten')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                    </div>

                    <hr>

                    {{-- RELASI DESA GELOMBANG --}}
                    <h6 class="mb-3">
                        Penempatan Gelombang
                    </h6>

                    <div class="row">

                        {{-- GELOMBANG --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gelombang</label>

                                <select name="gelombang_id"
                                        class="form-control @error('gelombang_id') is-invalid @enderror">

                                    <option value="">
                                        Belum Dipilih
                                    </option>

                                    @foreach($gelombang as $item)
                                        <option value="{{ $item->id }}"
                                            {{ old('gelombang_id', $desaGelombang->gelombang_id ?? '') == $item->id ? 'selected' : '' }}>
                                            {{ $item->nama_gelombang }}
                                        </option>
                                    @endforeach

                                </select>

                                @error('gelombang_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        {{-- KUOTA --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kuota Total</label>

                                <input type="number"
                                       name="kuota_total"
                                       class="form-control @error('kuota_total') is-invalid @enderror"
                                       value="{{ old('kuota_total', $desaGelombang->kuota_total ?? 12) }}"
                                       min="1">

                                @error('kuota_total')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                    </div>

                    <hr>

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
