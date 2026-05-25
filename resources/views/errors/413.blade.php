<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Terlalu Besar</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
    const isDark = localStorage.getItem('theme') === 'dark' ||
        (localStorage.getItem('theme') === null && window.matchMedia('(prefers-color-scheme: dark)').matches);

    Swal.fire({
        icon: 'error',
        title: 'File Terlalu Besar',
        text: 'Ukuran file melebihi batas maksimal. Silakan kompres atau perkecil file Anda (maks 50MB untuk dokumen, 2MB untuk foto).',
        confirmButtonColor: '#6777ef',
        background: isDark ? '#1f2430' : '#fff',
        color: isDark ? '#d6d9df' : '#545454',
        confirmButtonText: 'Kembali'
    }).then(() => {
        history.back();
    });
</script>
</body>
</html>
