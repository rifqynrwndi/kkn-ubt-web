<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Terlalu Besar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast/dist/css/iziToast.min.css">
    <script src="https://cdn.jsdelivr.net/npm/izitoast/dist/js/iziToast.min.js"></script>
</head>
<body>
<script>
    iziToast.error({
        title: 'File Terlalu Besar',
        message: 'Ukuran file melebihi batas maksimal. Maks 50MB untuk dokumen, 2MB untuk foto.',
        position: 'topRight',
        timeout: 8000,
        onClosed: function() { history.back(); }
    });
</script>
</body>
</html>
