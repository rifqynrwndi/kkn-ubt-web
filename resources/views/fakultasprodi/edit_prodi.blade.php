@extends('layouts.app')

@section('title', 'Edit Program Studi')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Edit Program Studi</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-header">
                <h4>Form Edit Program Studi</h4>
            </div>

            <div class="card-body">

                <form action="{{ route('fakultas-prodi.prodi.update', $prodi->id) }}"
                      method="POST">

                    @csrf
                    @method('PUT')

                    {{-- Fakultas --}}
                    <div class="form-group">
                        <label>Fakultas</label>
                        <select name="fakultas_id" class="form-control" required>
                            @foreach($fakultas as $fak)
                                <option value="{{ $fak->id }}"
                                    {{ $prodi->fakultas_id == $fak->id ? 'selected' : '' }}>
                                    {{ $fak->nama_fakultas }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Nama Prodi --}}
                    <div class="form-group">
                        <label>Nama Program Studi</label>
                        <input type="text"
                               name="nama_prodi"
                               class="form-control"
                               value="{{ old('nama_prodi', $prodi->nama_prodi) }}"
                               required>
                    </div>

                    <div class="d-flex justify-content-between">

                        <a href="{{ route('fakultas-prodi.index') }}"
                           class="btn btn-outline-secondary">
                            Kembali
                        </a>

                        <button class="btn btn-primary">
                            Update Prodi
                        </button>

                    </div>

                </form>

            </div>
        </div>

    </div>
</section>
@endsection
