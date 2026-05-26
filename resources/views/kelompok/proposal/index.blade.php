@extends('layouts.app')
@section('title', 'Proposal — ' . $kelompok->nama_kelompok)
@push('css')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Proposal KKN</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('kelompok.index') }}">Kelompokku</a></div>
            <div class="breadcrumb-item active">Proposal</div>
        </div>
    </div>

    <div class="section-body">
        @if($proposal)
            <div class="alert alert-{{ $proposal->status === 'disetujui' ? 'success' : ($proposal->status === 'ditolak' ? 'danger' : 'info') }}">
                <strong>Status Proposal:</strong>
                @if($proposal->status === 'draft') Draft — Belum diajukan
                @elseif($proposal->status === 'diajukan') Diajukan — Menunggu review DPL
                @elseif($proposal->status === 'disetujui') Disetujui oleh DPL
                @elseif($proposal->status === 'ditolak') Ditolak — Perlu diperbaiki
                @endif

                @if($proposal->status === 'ditolak' && $proposal->komentar_dpl)
                    <br><small><strong>Komentar DPL:</strong> {{ $proposal->komentar_dpl }}</small>
                @endif
            </div>
        @else
            <div class="alert alert-warning">Belum ada proposal. Ketua kelompok dapat membuat proposal.</div>
        @endif

        @if($isKetua && (!$proposal || in_array($proposal->status, ['draft', 'ditolak'])))
            <div class="mb-3">
                <a href="{{ route('kelompok.proposal.create') }}" class="btn btn-primary">
                    <i class="fas fa-edit mr-1"></i>
                    {{ $proposal ? 'Edit Proposal' : 'Buat Proposal' }}
                </a>
            </div>
        @endif

        @if($proposal)
        <div class="card">
            <div class="card-header"><h4>Isi Proposal</h4></div>
            <div class="card-body">
                @foreach(['pendahuluan' => 'Pendahuluan', 'tujuan' => 'Tujuan', 'manfaat' => 'Manfaat', 'hasil_observasi' => 'Hasil Observasi', 'rancangan_program' => 'Rancangan Program', 'solusi_ide' => 'Solusi / Ide'] as $field => $label)
                <div class="mb-4">
                    <h5 class="font-weight-bold">{{ $label }}</h5>
                    <div class="proposal-content p-3" style="background:#f8f9fa;border-radius:8px;min-height:40px;">
                        {!! $proposal->$field ?: '<span class="text-muted">Belum diisi</span>' !!}
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- DPL REVIEW BUTTONS --}}
        @if($isDpl && $proposal->status === 'diajukan')
        <div class="card">
            <div class="card-header"><h4>Review Proposal (DPL)</h4></div>
            <div class="card-body">
                <form action="{{ route('kelompok.proposal.review', $proposal->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Komentar</label>
                        <textarea name="komentar_dpl" class="form-control" rows="3" placeholder="Komentar (wajib jika menolak)"></textarea>
                    </div>
                    <button type="submit" name="action" value="setujui" class="btn btn-success" onclick="return confirm('Setujui proposal ini?')">
                        <i class="fas fa-check mr-1"></i> Setujui
                    </button>
                    <button type="submit" name="action" value="tolak" class="btn btn-danger" onclick="return confirm('Tolak proposal ini?')">
                        <i class="fas fa-times mr-1"></i> Tolak
                    </button>
                </form>
            </div>
        </div>
        @endif
        @endif
    </div>
</section>
@endsection
