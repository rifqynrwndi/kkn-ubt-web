@extends('layouts.app')

@section('title', 'Tambah Gelombang')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Tambah Gelombang</h1>
    </div>

    <div class="section-body">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('gelombang.store') }}" method="POST">
                    @csrf

                    <div class="row">

                        <div class="form-group col-md-6">
                            <label>Nama Gelombang</label>
                            <input type="text" name="nama_gelombang"
                                   class="form-control @error('nama_gelombang') is-invalid @enderror"
                                   value="{{ old('nama_gelombang') }}">
                            @error('nama_gelombang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Tahun</label>
                            <input type="number" name="tahun"
                                   class="form-control @error('tahun') is-invalid @enderror"
                                   value="{{ old('tahun', date('Y')) }}">
                            @error('tahun')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tgl_mulai"
                                   class="form-control @error('tgl_mulai') is-invalid @enderror"
                                   value="{{ old('tgl_mulai') }}">
                            @error('tgl_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Tanggal Akhir</label>
                            <input type="date" name="tgl_akhir"
                                   class="form-control @error('tgl_akhir') is-invalid @enderror"
                                   value="{{ old('tgl_akhir') }}">
                            @error('tgl_akhir')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-4">
                            <label>Kuota Laki-laki</label>
                            <input type="number" name="kuota_laki"
                                   class="form-control"
                                   value="{{ old('kuota_laki', 0) }}">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Kuota Perempuan</label>
                            <input type="number" name="kuota_perempuan"
                                   class="form-control"
                                   value="{{ old('kuota_perempuan', 0) }}">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="persiapan">Persiapan</option>
                                <option value="pendaftaran">Pendaftaran</option>
                                <option value="berjalan">Berjalan</option>
                                <option value="selesai">Selesai</option>
                            </select>
                        </div>

                    </div>

                    <div class="text-right">
                        <a href="{{ route('gelombang.index') }}" class="btn btn-outline-secondary">
                            Batal
                        </a>
                        <button class="btn btn-primary">
                            Simpan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</section>
@endsection
