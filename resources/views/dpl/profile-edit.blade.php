@extends('layouts.app')
@section('title', 'Edit Profil DPL')
@section('content')
<section class="section">
    <div class="section-header">
        <h1>Edit Profil DPL</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Edit Profil</div>
        </div>
    </div>
    <div class="section-body">
        <div class="card">
            <div class="card-header"><h4>Profil Saya</h4></div>
            <div class="card-body">
                <form action="{{ route('dpl.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', auth()->user()->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', auth()->user()->email) }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>No. HP</label>
                                <input type="text" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp', $dpl->no_hp) }}" placeholder="08xxxxxxxxxx">
                                @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-control">
                                    <option value="">Pilih</option>
                                    <option value="laki_laki" {{ old('jenis_kelamin', $dpl->jenis_kelamin) == 'laki_laki' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="perempuan" {{ old('jenis_kelamin', $dpl->jenis_kelamin) == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Foto Profil</label>
                        @if($dpl->foto)
                            <div class="mb-2">
                                <img src="{{ storage_url($dpl->foto) }}" class="rounded-circle" width="80" height="80" style="object-fit:cover;">
                            </div>
                        @else
                            <div class="mb-2">
                                <img src="{{ asset('img/avatar/avatar-1.png') }}" class="rounded-circle" width="80" height="80" style="object-fit:cover;">
                            </div>
                        @endif
                        <input type="file" name="foto" class="form-control-file" accept="image/*">
                        <small class="text-muted">Format: JPG, PNG. Maks 2MB. Kosongkan jika tidak ingin mengubah.</small>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary px-4"><i class="fas fa-save mr-1"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
