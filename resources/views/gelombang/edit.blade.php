@extends('layouts.app')

@section('title', 'Edit Gelombang')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Edit Gelombang</h1>
    </div>

    <div class="section-body">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('gelombang.update', $gelombang->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">

                        <div class="form-group col-md-6">
                            <label>Nama Gelombang</label>
                            <input type="text" name="nama_gelombang"
                                   class="form-control"
                                   value="{{ old('nama_gelombang', $gelombang->nama_gelombang) }}">
                        </div>

                        <div class="form-group col-md-6">
                            <label>Tahun</label>
                            <input type="number" name="tahun"
                                   class="form-control"
                                   value="{{ old('tahun', $gelombang->tahun) }}">
                        </div>

                        <div class="form-group col-md-6">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tgl_mulai"
                                   class="form-control"
                                   value="{{ old('tgl_mulai', $gelombang->tgl_mulai) }}">
                        </div>

                        <div class="form-group col-md-6">
                            <label>Tanggal Akhir</label>
                            <input type="date" name="tgl_akhir"
                                   class="form-control"
                                   value="{{ old('tgl_akhir', $gelombang->tgl_akhir) }}">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Kuota Laki-laki</label>
                            <input type="number" name="kuota_laki"
                                   class="form-control"
                                   value="{{ old('kuota_laki', $gelombang->kuota_laki) }}">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Kuota Perempuan</label>
                            <input type="number" name="kuota_perempuan"
                                   class="form-control"
                                   value="{{ old('kuota_perempuan', $gelombang->kuota_perempuan) }}">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                @foreach(['persiapan','pendaftaran','berjalan','selesai'] as $status)
                                    <option value="{{ $status }}"
                                        {{ $gelombang->status == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="text-right">
                        <a href="{{ route('gelombang.index') }}" class="btn btn-outline-secondary">
                            Batal
                        </a>
                        <button class="btn btn-primary">
                            Update
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</section>
@endsection
