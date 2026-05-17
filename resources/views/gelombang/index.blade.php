@extends('layouts.app')

@section('title', 'Data Gelombang')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Data Gelombang</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-header justify-content-between">
                <h4>List Gelombang</h4>

                <a href="{{ route('gelombang.create') }}" class="btn btn-primary">
                    + Tambah
                </a>
            </div>

            <div class="card-body">

                <form method="GET" class="mb-3">
                    <div class="input-group">
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Cari nama / tahun..."
                               value="{{ request('search') }}">

                        <div class="input-group-append">
                            <button class="btn btn-primary">
                                Search
                            </button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-borderless table-hover">

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Tahun</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Akhir</th>
                                <th>Jumlah Peserta</th>
                                <th>Status</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($gelombang as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->nama_gelombang }}</td>
                                    <td>{{ $item->tahun }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->tgl_mulai)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->tgl_akhir)->format('d M Y') }}</td>
                                    <td>
                                        <i class="fas fa-male text-primary mr-1"></i> {{ $item->total_pria }}
                                        <br>
                                        <i class="fas fa-female text-danger mr-1"></i> {{ $item->total_wanita }}
                                        <br>
                                        <b>Total: {{ $item->total_peserta }}</b>
                                    </td>

                                    <td>
                                        @if($item->status == 'persiapan')
                                            <span class="badge badge-outline-secondary">Persiapan</span>
                                        @elseif($item->status == 'pendaftaran')
                                            <span class="badge badge-outline-warning">Pendaftaran</span>
                                        @elseif($item->status == 'berjalan')
                                            <span class="badge badge-outline-success">Berjalan</span>
                                        @else
                                            <span class="badge badge-outline-dark">Selesai</span>
                                        @endif
                                    </td>

                                    <td class="text-center" style="white-space: nowrap;">
                                        <a href="{{ route('gelombang.show', $item->id) }}"
                                           class="btn btn-info btn-sm"
                                           data-toggle="tooltip"
                                           data-placement="top"
                                           title="Lihat Gelombang">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a href="{{ route('gelombang.edit', $item->id) }}"
                                           class="btn btn-primary btn-sm"
                                           data-toggle="tooltip"
                                           data-placement="top"
                                           title="Edit Gelombang">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('gelombang.destroy', $item->id) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger btn-sm"
                                                    data-toggle="tooltip"
                                                    data-placement="top"
                                                    title="Hapus Gelombang"
                                                    onclick="return confirm('Hapus data?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

                <div class="float-right">
                    {{ $gelombang->withQueryString()->links() }}
                </div>

            </div>
        </div>

    </div>
</section>
@endsection
