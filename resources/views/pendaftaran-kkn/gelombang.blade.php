@extends('layouts.app')

@section('title', 'Daftar Gelombang')

@section('content')
<section class="section">

    <div class="section-header">
        <h1>Daftar Gelombang KKN</h1>
    </div>

    <div class="section-body">

        <div class="row">

            @forelse($gelombangs as $gelombang)

                <div class="col-md-6">
                    <div class="card shadow-sm">

                        <div class="card-body">

                            <h4>
                                {{ $gelombang->nama_gelombang }}
                            </h4>

                            <p class="mb-2 text-muted">
                                Tahun {{ $gelombang->tahun }}
                            </p>

                            <p>
                                {{ \Carbon\Carbon::parse($gelombang->tgl_mulai)->format('d M Y') }}
                                -
                                {{ \Carbon\Carbon::parse($gelombang->tgl_akhir)->format('d M Y') }}
                            </p>

                            <form
                                action="{{ route('pendaftaran-kkn.store') }}"
                                method="POST"
                            >
                                @csrf

                                <button class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i>
                                    Daftar Sekarang
                                </button>

                            </form>

                        </div>

                    </div>
                </div>

            @empty

                <div class="col-12">
                    <div class="alert alert-warning">
                        Belum ada gelombang aktif.
                    </div>
                </div>

            @endforelse

        </div>

    </div>

</section>
@endsection
