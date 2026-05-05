@extends('layouts.app')

@section('title', 'Dokumen Pendaftaran')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Dokumen Pendaftaran KKN</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-header">
                <h4>Upload Dokumen</h4>
            </div>

            <div class="card-body">

                <form action="{{ route('dokumen-pendaftaran.store') }}"
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label>Jenis Dokumen</label>
                        <select name="jenis_dokumen" class="form-control" required>
                            <option value="">Pilih Dokumen</option>
                            <option value="ktm">KTM</option>
                            <option value="transkrip">Transkrip</option>
                            <option value="surat_sehat">Surat Sehat</option>
                            <option value="pas_foto">Pas Foto</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>File</label>
                        <input type="file"
                               name="file"
                               class="form-control"
                               required>
                    </div>

                    <button class="btn btn-primary">
                        Upload Dokumen
                    </button>
                </form>

            </div>
        </div>


        <div class="card">
            <div class="card-header">
                <h4>Dokumen Terupload</h4>
            </div>

            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>File</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dokumen as $item)
                            <tr>
                                <td>{{ $item->jenis_dokumen_label }}</td>
                                <td>{{ ucfirst($item->status_verifikasi) }}</td>
                                <td>
                                    <a href="{{ route('dokumen-pendaftaran.show', $item->id) }}"
                                    target="_blank">
                                        Lihat File
                                    </a>
                                </td>
                                <td>
                                    <form action="{{ route('dokumen-pendaftaran.destroy', $item->id) }}"
                                          method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-danger btn-sm">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>
@endsection
