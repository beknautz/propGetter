<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::e($pageTitle ?? 'Letter') ?> — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { font-family: Georgia, serif; font-size: 12pt; color: #000; background: #fff; }
        .no-print { display: none !important; }
        @media screen {
            body { background: #f0f0f0; padding: 2rem; }
            .letter-page { background: #fff; max-width: 8.5in; margin: 0 auto; padding: 1in; box-shadow: 0 4px 24px rgba(0,0,0,.15); }
            .no-print { display: flex !important; }
        }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
<div class="no-print d-flex gap-2 mb-3" style="max-width:8.5in;margin:0 auto;">
    <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer me-2"></i>Print Letter</button>
    <a href="javascript:history.back()" class="btn btn-outline-secondary">← Back</a>
</div>
<div class="letter-page">
    <?= $content ?>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
