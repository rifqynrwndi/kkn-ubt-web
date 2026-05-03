@extends('layouts.app')

@section('title', 'Send Notification')

@section('main')
<div class="main-content">
    <section class="section">

        <div class="section-header">
            <h1>Send Notification</h1>
        </div>

        <div class="section-body">
            <div class="row justify-content-center">
                <div class="col-lg-8">

                    <div class="card">
                        <div class="card-header">
                            <h4>Notification Form</h4>
                        </div>

                        <div class="card-body">
                            <form method="POST" action="{{ route('notifications.send') }}">
                                @csrf

                                {{-- TITLE --}}
                                <div class="form-group">
                                    <label>Judul</label>
                                    <input type="text"
                                           name="title"
                                           class="form-control"
                                           required>
                                </div>

                                {{-- MESSAGE --}}
                                <div class="form-group">
                                    <label>Pesan Notifikasi</label>
                                    <textarea name="message"
                                              rows="4"
                                              class="form-control"
                                              required></textarea>
                                </div>

                                {{-- TYPE --}}
                                <div class="form-group">
                                    <label>Jenis Notifikasi</label>
                                    <select name="type" class="form-control">
                                        <option value="info">Informasi</option>
                                        <option value="success">Success</option>
                                        <option value="warning">Warning</option>
                                        <option value="danger">Danger</option>
                                    </select>
                                </div>

                                <hr>

                                {{-- RECIPIENT GROUP --}}
                                <div class="form-group">
                                    <label>Recipient Group</label>
                                    <select name="recipient_mode"
                                            id="recipient_mode"
                                            class="form-control"
                                            onchange="toggleManualUsers()">
                                        <option value="all_mahasiswa">All Mahasiswa</option>
                                        <option value="unverified_mahasiswa">Mahasiswa Belum Verifikasi Email</option>
                                        <option value="incomplete_biodata">Mahasiswa Belum Lengkapi Biodata</option>
                                        <option value="manual">Pilih Manual</option>
                                    </select>
                                </div>

                                {{-- MANUAL USER PICKER --}}
                                <div id="manual-users-section" style="display:none;">
                                    <div class="form-group">
                                        <label>Search User</label>

                                        <input type="text"
                                               id="user-search"
                                               class="form-control mb-3"
                                               placeholder="Cari nama mahasiswa...">

                                        <div class="border rounded p-3"
                                             style="max-height: 300px; overflow-y:auto;">

                                            <div class="row" id="user-list">
                                                @foreach($users as $user)
                                                    <div class="col-md-6 mb-2 user-item">
                                                        <label class="border rounded px-3 py-2 w-100 d-flex align-items-center">
                                                            <input type="checkbox"
                                                                   name="users[]"
                                                                   value="{{ $user->id }}"
                                                                   class="mr-2 user-checkbox">

                                                            <span class="user-name">
                                                                {{ $user->name }}
                                                            </span>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                {{-- BUTTON --}}
                                <div class="text-right mt-4">
                                    <a href="{{ route('notifications.index') }}"
                                       class="btn btn-outline-secondary">
                                        Kembali
                                    </a>

                                    <button class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i>
                                        Send Notification
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </section>
</div>
@endsection


@push('scripts')
<script>
function toggleManualUsers() {
    const mode = document.getElementById('recipient_mode').value;
    const section = document.getElementById('manual-users-section');

    section.style.display = mode === 'manual' ? 'block' : 'none';
}

document.getElementById('user-search').addEventListener('keyup', function() {
    let keyword = this.value.toLowerCase();

    document.querySelectorAll('.user-item').forEach(item => {
        let name = item.querySelector('.user-name').innerText.toLowerCase();

        item.style.display = name.includes(keyword) ? 'block' : 'none';
    });
});
</script>
@endpush
