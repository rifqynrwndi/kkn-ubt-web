@extends('layouts.app')

@section('title', 'Buat Sesi Plotting Baru')

@section('content')
<section class="section">
    <div class="section-header">
        <div class="section-header-back">
            <a href="{{ route('admin.war.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Buat Sesi Plotting Baru</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.war.index') }}">Plotting Kelompok</a></div>
            <div class="breadcrumb-item active">Buat Sesi</div>
        </div>
    </div>

    <div class="section-body">
        <h2 class="section-title">Form Sesi Plotting</h2>
        <p class="section-lead">
            Isi formulir di bawah ini untuk menjadwalkan sesi plotting (WAR) kelompok baru.
        </p>

        <div class="row">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card">
                    <form action="{{ route('admin.war.store') }}" method="POST">
                        @csrf
                        <div class="card-header">
                            <h4>Data Sesi Plotting</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="font-weight-bold">Nama Sesi Plotting <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="Contoh: Plotting Kelompok KKN Gelombang 1 – 2025">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label class="font-weight-bold">Gelombang KKN <span class="text-danger">*</span></label>
                                <select name="gelombang_id" class="form-control select2 @error('gelombang_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Gelombang --</option>
                                    @foreach($gelombangs as $gel)
                                        <option value="{{ $gel->id }}" {{ old('gelombang_id') == $gel->id ? 'selected' : '' }}>
                                            {{ $gel->nama_gelombang }} ({{ $gel->tahun }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('gelombang_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label class="font-weight-bold">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="start_at" class="form-control @error('start_at') is-invalid @enderror" value="{{ old('start_at') }}" required>
                                @error('start_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label class="font-weight-bold">Waktu Selesai <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="end_at" class="form-control @error('end_at') is-invalid @enderror" value="{{ old('end_at') }}" required>
                                @error('end_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <a href="{{ route('admin.war.index') }}" class="btn btn-secondary mr-2">
                                <i class="fas fa-times mr-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Simpan Sesi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    if(jQuery().select2) {
        $(".select2").select2();
    }
</script>
@endpush
