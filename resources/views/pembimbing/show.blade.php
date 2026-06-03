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
                        <th width="220">Foto</th>
                        <td>
                            @if($dpl->foto)
                                <img src="{{ asset('storage/'.$dpl->foto) }}" class="rounded-circle" width="80" height="80" style="object-fit:cover;">
                            @else
                                <img src="{{ asset('img/avatar/avatar-1.png') }}" class="rounded-circle" width="80" height="80" style="object-fit:cover;">
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>Nama</th>
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
                        <th>No. HP</th>
                        <td>{{ $dpl->no_hp ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th>Jenis Kelamin</th>
                        <td>{{ $dpl->jenis_kelamin ? ($dpl->jenis_kelamin === 'laki_laki' ? 'Laki-laki' : 'Perempuan') : '-' }}</td>
                    </tr>

                    <tr>
                        <th>Fakultas</th>
                        <td>{{ $dpl->fakultas?->nama_fakultas ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th>Status</th>
                        <td>
                            @if($dpl->status === 'aktif')
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-primary">Nonaktif</span>
                            @endif
                        </td>
                    </tr>

                </table>

            </div>
        </div>

    </div>
</section>
@endsection
