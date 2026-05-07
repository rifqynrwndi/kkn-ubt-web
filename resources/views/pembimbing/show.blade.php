@extends('layouts.app')

@section('title', 'Detail DPL')

@section('content')
<section class="section">

    <div class="section-header d-flex justify-content-between">
        <h1>Detail DPL</h1>

        <a href="{{ route('pembimbing-lapangan.index') }}"
           class="btn btn-outline-secondary">
            Kembali
        </a>
    </div>

    <div class="section-body">

        <div class="card shadow-sm">
            <div class="card-body">

                <table class="table">

                    <tr>
                        <th width="220">Nama</th>
                        <td>{{ $dpl->user->name }}</td>
                    </tr>

                    <tr>
                        <th>Email</th>
                        <td>{{ $dpl->user->email }}</td>
                    </tr>

                    <tr>
                        <th>NIDN</th>
                        <td>{{ $dpl->nidn ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th>Fakultas</th>
                        <td>{{ $dpl->fakultas?->nama_fakultas ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th>Status</th>
                        <td>
                            @if($dpl->status === 'aktif')
                                <span class="badge badge-success">
                                    Aktif
                                </span>
                            @else
                                <span class="badge badge-secondary">
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                    </tr>

                </table>

            </div>
        </div>

    </div>
</section>
@endsection
