@forelse($mahasiswas as $mhs)
<tr>
    <td>{{ $loop->iteration + (($mahasiswas->currentPage() - 1) * $mahasiswas->perPage()) }}</td>
    <td>
        <div class="d-flex align-items-center">
            <div>
                <strong>{{ $mhs->name }}</strong>
                <br>
                <small class="text-muted">{{ $mhs->email }}</small>
            </div>
        </div>
    </td>
    <td>{{ $mhs->mahasiswa?->npm ?? '-' }}</td>
    <td>{{ $mhs->mahasiswa?->prodi?->nama_prodi ?? '-' }}</td>
    <td>
        @if($mhs->email_verified_at)
            <span class="badge badge-success">Verified</span>
        @else
            <span class="badge badge-warning">Unverified</span>
        @endif
    </td>
    <td>
        <a href="{{ route('mahasiswa.show', $mhs) }}" class="btn btn-sm btn-info">
            <i class="fas fa-eye"></i>
        </a>
        <a href="{{ route('mahasiswa.edit', $mhs) }}" class="btn btn-sm btn-warning">
            <i class="fas fa-edit"></i>
        </a>
    </td>
</tr>
@empty
<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data mahasiswa.</td></tr>
@endforelse

@if($mahasiswas->hasPages())
<tr>
    <td colspan="6">
        {{ $mahasiswas->links() }}
    </td>
</tr>
@endif
