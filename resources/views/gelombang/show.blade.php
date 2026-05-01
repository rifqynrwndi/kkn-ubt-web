@extends('layouts.app')

@section('title', 'Detail Gelombang')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Detail Gelombang</h1>
    </div>

    <div class="section-body">

        <div class="card">

            <div class="card-header">
                <h4>{{ $gelombang->nama_gelombang }}</h4>
            </div>

            <div class="card-body">

                <table class="table table-borderless">

                    <tr>
                        <th>Nama Gelombang</th>
                        <td>{{ $gelombang->nama_gelombang }}</td>
                    </tr>

                    <tr>
                        <th>Tahun</th>
                        <td>{{ $gelombang->tahun }}</td>
                    </tr>

                    <tr>
                        <th>Tanggal Mulai</th>
                        <td>{{ $gelombang->tgl_mulai }}</td>
                    </tr>

                    <tr>
                        <th>Tanggal Akhir</th>
                        <td>{{ $gelombang->tgl_akhir }}</td>
                    </tr>

                    <tr>
                        <th>Kuota Laki-laki</th>
                        <td>{{ $gelombang->kuota_laki }}</td>
                    </tr>

                    <tr>
                        <th>Kuota Perempuan</th>
                        <td>{{ $gelombang->kuota_perempuan }}</td>
                    </tr>

                    <tr>
                        <th>Kuota Total</th>
                        <td><b>{{ $gelombang->kuota_total }}</b></td>
                    </tr>

                    <tr>
                        <th>Status</th>
                        <td>{{ ucfirst($gelombang->status) }}</td>
                    </tr>

                </table>

            </div>

            <div class="card-footer text-right">
                <a href="{{ route('gelombang.index') }}" class="btn btn-outline-secondary">
                    Kembali
                </a>

                <a href="{{ route('gelombang.edit', $gelombang->id) }}"
                   class="btn btn-primary">
                    Edit
                </a>
            </div>

        </div>

    </div>
</section>
@endsection
