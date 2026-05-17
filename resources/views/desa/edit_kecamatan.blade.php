@extends('layouts.app')

@section('title', 'Edit Kecamatan')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Edit Kecamatan</h1>
    </div>
    <div class="section-body">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('desa.updateKecamatan', $kecamatan) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label>Nama Kecamatan</label>
                        <input type="text" name="nama_kecamatan" value="{{ old('nama_kecamatan', $kecamatan->nama_kecamatan) }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Kabupaten</label>
                        <input type="text" name="kabupaten" value="{{ old('kabupaten', $kecamatan->kabupaten) }}" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('desa.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
