@extends('layouts.app')

@section('title', 'Manajemen Mahasiswa')

@section('content')

<style>
    .table td, .table th {
        vertical-align: middle !important;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .name-column {
        white-space: normal !important;
        min-width: 150px;
        max-width: 200px;
        word-break: break-word;
        line-height: 1.4;
    }

    .action-column {
        white-space: nowrap !important;
        min-width: 130px;
    }
</style>

<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Data Mahasiswa</h1>

        <a href="{{ route('mahasiswa.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Mahasiswa
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            {{-- Search + Filter --}}
            <form id="filter-form" method="GET" class="mb-3">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label class="form-label font-weight-bold">Cari Mahasiswa</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama / npm / email..." value="{{ request('search') }}">
                            <div class="input-group-append"><button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button></div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label font-weight-bold">Status</label>
                        <select name="status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="verified" {{ request('status')=='verified'?'selected':'' }}>Email Verified</option>
                            <option value="unverified" {{ request('status')=='unverified'?'selected':'' }}>Email Belum Verified</option>
                            <option value="biodata_incomplete" {{ request('status')=='biodata_incomplete'?'selected':'' }}>Biodata Belum Lengkap</option>
                            <option value="dhs_pending" {{ request('status')=='dhs_pending'?'selected':'' }}>DHS Belum Diverifikasi</option>
                            <option value="dhs_rejected" {{ request('status')=='dhs_rejected'?'selected':'' }}>DHS Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label font-weight-bold">Gelombang</label>
                        <div class="input-group">
                            <select name="gelombang_id" id="filter-gelombang" class="form-control">
                                <option value="">Semua Gelombang</option>
                                @foreach(\App\Models\Gelombang::orderBy('tahun','desc')->get() as $g)
                                <option value="{{ $g->id }}" {{ request('gelombang_id')==$g->id?'selected':'' }}>{{ $g->nama_gelombang }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <a href="{{ route('mahasiswa.export') }}" class="btn btn-success" id="export-btn">
                                    <i class="fas fa-download mr-1"></i> Export
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-md">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">No</th>
                            <th>Nama</th>
                            <th>NPM</th>
                            <th>Email</th>
                            <th class="text-center">Verifikasi</th>
                            <th class="text-center">Biodata</th>
                            <th>Gelombang</th>
                            <th class="text-center action-column">Aksi</th>
                        </tr>
                    </thead>

                    <tbody id="mahasiswa-table-body">
                        @include('mahasiswa.partials.table')
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</section>

@push('scripts')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();

        // AJAX form submission for filters
        $('#filter-form').on('submit change', 'select[name="status"], select[name="gelombang_id"]', function(e) {
            e.preventDefault();
            submitFilterForm();
        });

        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            submitFilterForm();
        });

        function submitFilterForm() {
            const form = $('#filter-form');
            const url = form.attr('action') || window.location.pathname;
            const data = form.serialize();

            $.ajax({
                url: url,
                method: 'GET',
                data: data,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                beforeSend: function() {
                    $('#mahasiswa-table-body').html('<tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></td></tr>');
                },
                success: function(html) {
                    $('#mahasiswa-table-body').html(html);
                    $('[data-toggle="tooltip"]').tooltip();
                    updateExportButton();
                },
                error: function() {
                    $('#mahasiswa-table-body').html('<tr><td colspan="8" class="text-center text-danger py-4">Gagal memuat data</td></tr>');
                }
            });
        }

        function updateExportButton() {
            const gelombangId = $('#filter-gelombang').val();
            const exportBtn = $('#export-btn');
            const baseUrl = exportBtn.data('base-url') || exportBtn.attr('href').split('?')[0];
            exportBtn.attr('href', baseUrl + (gelombangId ? '?gelombang_id=' + gelombangId : ''));
        }

        // Initialize export button base URL
        $('#export-btn').data('base-url', $('#export-btn').attr('href').split('?')[0]);
        updateExportButton();
    });
</script>
@endpush

@endsection
