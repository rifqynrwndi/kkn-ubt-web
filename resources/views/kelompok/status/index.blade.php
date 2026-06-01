<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-center flex-wrap gap-1 mb-3">
            @foreach($stages as $i => $s)
            <div class="text-center px-2" style="min-width:90px;">
                <div style="width:32px;height:32px;border-radius:50%;margin:0 auto 4px;
                    background:{{ $i <= $kelompok->status_tahap ? 'var(--'.($s['color'] === 'dark' ? 'bs-dark' : 'bs-'.$s['color']).')' : '#e0e0e0' }};
                    color:{{ $i <= $kelompok->status_tahap ? '#fff' : '#999' }};
                    font-weight:800;font-size:13px;line-height:32px;">{{ $i }}</div>
                <small style="font-size:10px;color:{{ $i === $kelompok->status_tahap ? '#6777ef' : '#adb5bd' }};font-weight:{{ $i === $kelompok->status_tahap ? '700' : '400' }};">
                    {{ $s['nama'] }}
                </small>
            </div>
            @if($i < 3)
            <div style="width:24px;height:2px;background:{{ $i < $kelompok->status_tahap ? '#6777ef' : '#e0e0e0' }};margin-top:16px;flex-shrink:0;"></div>
            @endif
            @endforeach
        </div>

        @php $c = $current; @endphp
        <div class="alert alert-{{ $c['color'] === 'dark' ? 'secondary' : $c['color'] }} text-center">
            <strong>Tahap Saat Ini:</strong> {{ $c['nama'] }}
            <br><small>{{ $c['desc'] }}</small>
        </div>
    </div>
</div>

@if(($isAdmin || $isDpl) && $kelompok->status_tahap < 3)
<div class="card mb-3">
    <div class="card-header"><h5>Ubah Status</h5></div>
    <div class="card-body">
        <form action="{{ route('kelompok.status.change', $kelompok->id) }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Status Baru</label>
                <select name="stage" class="form-control" required>
                    @foreach($stages as $i => $s)
                    <option value="{{ $i }}" {{ $i === $kelompok->status_tahap ? 'disabled' : '' }}>
                        {{ $i }} - {{ $s['nama'] }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" onclick="return confirm('Ubah status?')">
                <i class="fas fa-check mr-1"></i> Simpan
            </button>
        </form>
    </div>
</div>
@endif

@if($history->count())
<div class="card">
    <div class="card-header"><h5>Riwayat Perubahan Status</h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Dari</th><th>Ke</th><th>Keterangan</th><th>Oleh</th><th>Waktu</th></tr></thead>
                <tbody>
                    @foreach($history as $h)
                    <tr>
                        <td><span class="badge badge-light">{{ $stages[$h->status_lama]['nama'] ?? '?' }}</span></td>
                        <td><span class="badge badge-{{ $stages[$h->status_baru]['color'] ?? 'info' }}">{{ $stages[$h->status_baru]['nama'] ?? '?' }}</span></td>
                        <td>{{ $h->keterangan ?: '-' }}</td>
                        <td>{{ $h->changedBy->name ?? '-' }}</td>
                        <td><small>{{ $h->created_at->diffForHumans() }}</small></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
