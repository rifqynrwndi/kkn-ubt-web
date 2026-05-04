@extends('layouts.app')

@section('title', 'Fakultas & Program Studi')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Fakultas & Program Studi</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-header justify-content-between">
                <h4>Data Program Studi</h4>

                <div>
                    <a href="{{ route('fakultas-prodi.fakultas.create') }}" class="btn btn-primary btn-sm">
                        + Fakultas
                    </a>

                    <a href="{{ route('fakultas-prodi.prodi.create') }}" class="btn btn-success btn-sm">
                        + Program Studi
                    </a>
                </div>
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover table-borderless">

                        <thead>
                            <tr>
                                <th width="50">No.</th>
                                <th>Program Studi</th>
                                <th>Fakultas</th>
                                <th width="180">Aksi Fakultas</th>
                                <th width="180">Aksi Prodi</th>
                            </tr>
                        </thead>

                        <tbody>

                            {{-- PRODI --}}
                            @foreach($prodi as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td><strong>{{ $item->nama_prodi }}</strong></td>

                                    <td>{{ $item->fakultas->nama_fakultas }}</td>

                                    {{-- AKSI FAKULTAS --}}
                                    <td class="text-center">

                                        {{-- EDIT FAKULTAS --}}
                                        <a href="{{ route('fakultas-prodi.fakultas.edit', $item->fakultas->id) }}"
                                        class="btn btn-info btn-sm"
                                        data-toggle="tooltip"
                                        data-placement="top"
                                        title="Edit Fakultas">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        {{-- DELETE FAKULTAS --}}
                                        <form action="{{ route('fakultas-prodi.fakultas.delete', $item->fakultas->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Hapus fakultas ini? semua prodi ikut terhapus!')">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Hapus Fakultas" >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>

                                    </td>

                                    {{-- AKSI PRODI --}}
                                    <td class="text-center">

                                        {{-- EDIT PRODI (ke blade edit) --}}
                                        <a href="{{ route('fakultas-prodi.prodi.edit', $item->id) }}"
                                           class="btn btn-info btn-sm"
                                           data-toggle="tooltip"
                                           data-placement="top"
                                           title="Edit Prodi">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        {{-- DELETE PRODI --}}
                                        <form action="{{ route('fakultas-prodi.prodi.delete', $item->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Hapus prodi?')">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Hapus Prodi">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>

                                    </td>
                                </tr>
                            @endforeach

                            {{-- FAKULTAS TANPA PRODI --}}
                            @foreach($fakultas as $fak)
                                @if($fak->prodi->count() == 0)
                                    <tr>
                                        <td>-</td>

                                        <td>
                                            <span class="text-muted">Belum ada prodi</span>
                                        </td>

                                        <td>{{ $fak->nama_fakultas }}</td>

                                        <td class="text-center">

                                            {{-- EDIT FAKULTAS --}}
                                            <a href="{{ route('fakultas-prodi.fakultas.edit', $fak->id) }}"
                                               class="btn btn-info btn-sm"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="Edit Fakultas">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            {{-- DELETE FAKULTAS --}}
                                            <form action="{{ route('fakultas-prodi.fakultas.delete', $fak->id) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Hapus fakultas ini?')">
                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Hapus Fakultas">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>

                                        </td>

                                        <td class="text-center">
                                            <a href="{{ route('fakultas-prodi.prodi.create') }}"
                                               class="btn btn-success btn-sm">
                                                + Tambah Prodi
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach

                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    </div>
</section>
@endsection
