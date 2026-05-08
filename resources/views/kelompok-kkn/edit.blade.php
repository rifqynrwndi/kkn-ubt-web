@extends('layouts.app')

@section('title', 'Edit Kelompok KKN')

@section('content')
<section class="section">

    {{-- HEADER --}}
    <div class="section-header d-flex justify-content-between align-items-center">

        <h1 class="mb-0">
            Edit Kelompok KKN
        </h1>

        <a href="{{ route('kelompok-kkn.index') }}"
           class="btn btn-outline-secondary">

            <i class="fas fa-arrow-left mr-1"></i>
            Kembali

        </a>

    </div>

    <div class="section-body">

        <div class="card shadow-sm border-0">

            {{-- CARD HEADER --}}
            <div class="card-header">

                <h4 class="mb-0">
                    Form Edit Kelompok KKN
                </h4>

            </div>

            {{-- CARD BODY --}}
            <div class="card-body">

                <form action="{{ route('kelompok-kkn.update', $kelompok_kkn->id) }}"
                      method="POST">

                    @csrf
                    @method('PUT')

                    {{-- DESA & DPL --}}
                    <div class="row">

                        {{-- DESA GELOMBANG --}}
                        <div class="col-md-6">

                            <div class="form-group">

                                <label for="desa_gelombang_id">
                                    Desa & Gelombang
                                </label>

                                <select
                                    id="desa_gelombang_id"
                                    name="desa_gelombang_id"
                                    class="form-control @error('desa_gelombang_id') is-invalid @enderror"
                                    required>

                                    <option value="">
                                        Pilih Desa Penempatan
                                    </option>

                                    @forelse($desaGelombang as $item)

                                        <option value="{{ $item->id }}"
                                            {{ old('desa_gelombang_id', $kelompok_kkn->desa_gelombang_id) == $item->id ? 'selected' : '' }}>

                                            {{ $item->desa->nama_desa }}
                                            —
                                            {{ $item->gelombang->nama_gelombang }}

                                        </option>

                                    @empty

                                        <option disabled>
                                            Belum ada data desa gelombang
                                        </option>

                                    @endforelse

                                </select>

                                @error('desa_gelombang_id')

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

                                <small class="text-muted">
                                    Data desa penempatan diambil dari master desa gelombang.
                                </small>

                            </div>

                        </div>

                        {{-- DPL --}}
                        <div class="col-md-6">

                            <div class="form-group">

                                <label for="dosen_pembimbing_lapangan_id">
                                    Dosen Pembimbing Lapangan
                                </label>

                                <select
                                    id="dosen_pembimbing_lapangan_id"
                                    name="dosen_pembimbing_lapangan_id"
                                    class="form-control @error('dosen_pembimbing_lapangan_id') is-invalid @enderror">

                                    <option value="">
                                        Belum Ditentukan
                                    </option>

                                    @foreach($dpl as $item)

                                        <option value="{{ $item->id }}"
                                            {{ old('dosen_pembimbing_lapangan_id', $kelompok_kkn->dosen_pembimbing_lapangan_id) == $item->id ? 'selected' : '' }}>

                                            {{ $item->user->name }}

                                        </option>

                                    @endforeach

                                </select>

                                @error('dosen_pembimbing_lapangan_id')

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                        </div>

                    </div>

                    <hr>

                    {{-- DETAIL KELOMPOK --}}
                    <div class="row">

                        {{-- KUOTA --}}
                        <div class="col-md-6">

                            <div class="form-group">

                                <label for="kuota">
                                    Kuota Anggota
                                </label>

                                <input
                                    type="number"
                                    id="kuota"
                                    name="kuota"
                                    class="form-control @error('kuota') is-invalid @enderror"
                                    value="{{ old('kuota', $kelompok_kkn->kuota) }}"
                                    min="1"
                                    max="30"
                                    placeholder="Masukkan kuota anggota"
                                    required>

                                @error('kuota')

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

                                <small class="text-muted">
                                    Maksimal jumlah anggota dalam kelompok KKN.
                                </small>

                            </div>

                        </div>

                        {{-- STATUS --}}
                        <div class="col-md-6">

                            <div class="form-group">

                                <label for="status">
                                    Status Kelompok
                                </label>

                                <select
                                    id="status"
                                    name="status"
                                    class="form-control @error('status') is-invalid @enderror"
                                    required>

                                    <option value="draft"
                                        {{ old('status', $kelompok_kkn->status) == 'draft' ? 'selected' : '' }}>
                                        Draft
                                    </option>

                                    <option value="dibuka"
                                        {{ old('status', $kelompok_kkn->status) == 'dibuka' ? 'selected' : '' }}>
                                        Dibuka
                                    </option>

                                    <option value="ditutup"
                                        {{ old('status', $kelompok_kkn->status) == 'ditutup' ? 'selected' : '' }}>
                                        Ditutup
                                    </option>

                                    <option value="penuh"
                                        {{ old('status', $kelompok_kkn->status) == 'penuh' ? 'selected' : '' }}>
                                        Penuh
                                    </option>

                                </select>

                                @error('status')

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                        </div>

                    </div>

                    {{-- INFO --}}
                    <div class="alert alert-light border mt-3">

                        <div class="d-flex align-items-center">

                            <i class="fas fa-info-circle text-primary mr-2"></i>

                            <span>
                                Nama kelompok akan diperbarui otomatis berdasarkan desa dan gelombang yang dipilih.
                            </span>

                        </div>

                    </div>

                    {{-- ACTION BUTTON --}}
                    <div class="d-flex justify-content-end mt-4">

                        <button type="submit"
                                class="btn btn-primary px-4">

                            <i class="fas fa-save mr-1"></i>
                            Update Kelompok

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</section>
@endsection
