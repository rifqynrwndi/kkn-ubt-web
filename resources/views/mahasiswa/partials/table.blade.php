@forelse($mahasiswas as $mhs)
<tr>
    <td class="text-center">
        {{ $loop->iteration + (($mahasiswas->currentPage() - 1) * $mahasiswas->perPage()) }}
    </td>

    <td class="name-column">
        {{ $mhs->name }}
    </td>

    <td>{{ $mhs->mahasiswa?->npm ?? '-' }}</td>

    <td>{{ $mhs->email }}</td>

    <td class="text-center">
        @if($mhs->email_verified_at)
            <span class="badge badge-success">Verified</span>
        @else
            <span class="badge badge-danger">Not Verified</span>
        @endif
    </td>

    <td class="text-center">
        @if($mhs->mahasiswa?->is_biodata_complete && $mhs->mahasiswa?->dhs_status === 'verified')
            <span class="badge badge-success">Lengkap</span>
        @elseif($mhs->mahasiswa?->is_biodata_complete && $mhs->mahasiswa?->dhs_status === 'rejected')
            <span class="badge badge-danger">DHS Ditolak</span>
        @elseif($mhs->mahasiswa?->is_biodata_complete && $mhs->mahasiswa?->dhs_path)
            <span class="badge badge-warning">Menunggu DHS</span>
        @elseif($mhs->mahasiswa?->is_biodata_complete)
            <span class="badge badge-info">Biodata OK</span>
        @else
            <span class="badge badge-secondary">Belum Lengkap</span>
        @endif
    </td>

    <td>
        @if($mhs->mahasiswa?->pesertaKkn && $mhs->mahasiswa->pesertaKkn->count() > 0)
            <span class="badge badge-outline-info">
                {{ $mhs->mahasiswa->pesertaKkn
                    ->map(fn($p) => $p->gelombang->nama_gelombang)
                    ->join(', ') }}
            </span>
        @else
            <span class="badge badge-outline-secondary">
                Belum Terdaftar
            </span>
        @endif
    </td>

    <td class="text-center action-column">
        <a href="{{ route('mahasiswa.show', $mhs->id) }}"
           class="btn btn-info btn-sm"
           data-toggle="tooltip"
           title="Detail">
            <i class="fas fa-eye"></i>
        </a>

        <a href="{{ route('mahasiswa.edit', $mhs->id) }}"
           class="btn btn-warning btn-sm mx-1"
           data-toggle="tooltip"
           title="Edit">
            <i class="fas fa-edit"></i>
        </a>

        <form action="{{ route('mahasiswa.destroy', $mhs->id) }}"
              method="POST"
              class="d-inline">
            @csrf
            @method('DELETE')

            <button class="btn btn-danger btn-sm"
                    onclick="return confirm('Hapus mahasiswa ini?')"
                    data-toggle="tooltip"
                    title="Hapus">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center text-muted py-4">
        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
        Data tidak ditemukan
    </td>
</tr>
@endforelse

@if($mahasiswas->hasPages())
<tr>
    <td colspan="8">
        <div style="display: flex; justify-content: center;">
            {{ $mahasiswas->appends(request()->query())->links() }}
        </div>
    </td>
</tr>
@endif