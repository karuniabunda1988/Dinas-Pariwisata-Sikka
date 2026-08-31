<?php
/** Layout minimal - dipakai halaman login admin. */
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($meta['judul_penuh'] ?? 'Panel Admin') ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="<?= e(aset('assets/img/favicon.svg')) ?>" type="image/svg+xml">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
<link rel="stylesheet" href="<?= e(aset('assets/css/gaya.css')) ?>">
</head>
<body class="bg-body-tertiary">

<?php if (!empty($flash)): ?>
<div class="container mt-3" style="max-width:460px" role="status" aria-live="polite">
  <?php foreach ($flash as $f): ?>
  <div class="alert alert-<?= $f['tipe'] === 'sukses' ? 'success' : 'danger' ?>"><?= e($f['pesan']) ?></div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?= $isiHalaman ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous" defer></script>
</body>
</html>
