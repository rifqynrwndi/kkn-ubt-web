@extends('layouts.app')

@section('title', 'Pendaftaran KKN')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Pendaftaran KKN</h1>
    </div>

    <div class="section-body">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif


        @if(!$gelombang)
            <div class="alert alert-warning">
                Belum ada gelombang pendaftaran aktif saat ini.
            </div>
        @else
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4>{{ $gelombang->nama_gelombang }}</h4>
                </div>

                <div class="card-body">

                    <div class="mb-3">
                        <strong>Tahun:</strong> {{ $gelombang->tahun }}
                    </div>

                    <div class="mb-3">
                        <strong>Periode:</strong><br>
                        {{ \Carbon\Carbon::parse($gelombang->tgl_mulai)->format('d M Y') }}
                        -
                        {{ \Carbon\Carbon::parse($gelombang->tgl_akhir)->format('d M Y') }}
                    </div>

                    @if($pendaftaran)
                        <div class="alert alert-info">
                            Anda sudah terdaftar pada gelombang ini.
                            <br>
                            Status Pendaftaran:
                            @if($pendaftaran->status_pendaftaran === 'draft')
                                <strong>Draft - Dokumen Belum Lengkap</strong>
                            @elseif($pendaftaran->status_pendaftaran === 'pending_documents')
                                <strong>Dokumen Belum Lengkap - Dokumen Sedang Diproses</strong>
                            @elseif($pendaftaran->status_pendaftaran === 'revision')
                                <strong>Terdapat Revisi - Dokumen Sedang Diproses</strong>
                            @elseif($pendaftaran->status_pendaftaran === 'verified')
                                <strong>Verified - Dokumen Lengkap</strong>
                            @elseif($pendaftaran->status_pendaftaran === 'rejected')
                                <strong>Rejected - Dokumen Tidak Lengkap</strong>
                            @else
                                <span class="badge badge-info">{{ ucwords(str_replace('_', ' ', $pendaftaran->status_pendaftaran)) }}</span>
                            @endif

                        </div>

                        @if(
                            $pendaftaran->status_pendaftaran === 'draft' ||
                            $pendaftaran->status_pendaftaran === 'pending_documents' ||
                            $pendaftaran->status_pendaftaran === 'revision'
                        )
                            <div class="mt-3">
                                <a href="{{ route('dokumen-pendaftaran.index') }}"
                                class="btn btn-primary">
                                    <i class="fas fa-file-upload"></i>
                                    Lengkapi Dokumen Pendaftaran
                                </a>
                            </div>
                        @endif
                    @else
                        <form action="{{ route('pendaftaran-kkn.store') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                                Daftar KKN Sekarang
                            </button>
                        </form>
                    @endif

                </div>
            </div>
        @endif

    </div>
</section>
@endsection
