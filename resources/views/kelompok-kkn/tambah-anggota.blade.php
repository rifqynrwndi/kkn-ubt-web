@extends('layouts.app')

@section('title', 'Tambah Anggota Kelompok')

@section('content')
<section class="section">

    <div class="section-header d-flex justify-content-between align-items-center">

        <div>

            <h1 class="mb-0">
                Tambah Anggota
            </h1>

            <small class="text-muted">
                {{ $kelompok_kkn->nama_kelompok }}
            </small>

        </div>

        <a href="{{ route('kelompok-kkn.show', $kelompok_kkn->id) }}"
           class="btn btn-outline-secondary">

            <i class="fas fa-arrow-left mr-1"></i>
            Kembali

        </a>

    </div>

    <div class="section-body">

        <div class="card shadow-sm">

            <div class="card-header">
                <h4 class="mb-0">
                    Pilih Peserta
                </h4>
            </div>

            <div class="card-body">

                @if($peserta->count())

                    <form action="{{ route('kelompok-kkn.anggota.store', $kelompok_kkn->id) }}"
                          method="POST">

                        @csrf

                        <div class="form-group">

                            <label>
                                Peserta KKN
                            </label>

                            <select name="peserta_kkn_id"class="form-control" required>

                                <option value="">
                                    Pilih Peserta
                                </option>

                                @foreach($peserta as $item)

                                    <option value="{{ $item->id }}">

                                        {{ $item->mahasiswa?->user?->name }}
                                        -
                                        {{ $item->mahasiswa?->npm }}
                                        -
                                        {{ $item->mahasiswa?->prodi?->nama_prodi }}

                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="text-right">

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus mr-1"></i>
                                Tambahkan
                            </button>

                        </div>

                    </form>

                @else

                    <div class="alert alert-warning mb-0">

                        Belum ada peserta yang dapat ditambahkan.

                    </div>

                @endif

            </div>

        </div>

    </div>

</section>
@endsection
