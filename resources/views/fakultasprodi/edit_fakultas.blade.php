@extends('layouts.app')

@section('title', 'Edit Fakultas')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Edit Fakultas</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-header">
                <h4>Form Edit Fakultas</h4>
            </div>

            <div class="card-body">

                <form action="{{ route('fakultas-prodi.fakultas.update', $fakultas->id) }}"
                      method="POST">

                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Nama Fakultas</label>
                        <input type="text"
                               name="nama_fakultas"
                               class="form-control"
                               value="{{ old('nama_fakultas', $fakultas->nama_fakultas) }}"
                               required>
                    </div>

                    <div class="d-flex justify-content-between">

                        <a href="{{ route('fakultas-prodi.index') }}"
                           class="btn btn-outline-secondary">
                            Kembali
                        </a>

                        <button class="btn btn-primary">
                            Update Fakultas
                        </button>

                    </div>

                </form>

            </div>
        </div>

    </div>
</section>
@endsection
