<?php
/**
 * Layout publik utama. Mobile-first (§12) - Bootstrap 5 dari CDN dengan
 * berkas gaya sendiri untuk identitas warna kategori.
 * @var string $isiHalaman
 * @var array  $meta
 */
$peta = App::config('peta');
?>
<!doctype html>
<html lang="<?= e(Lang::aktif()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($meta['judul_penuh']) ?></title>
<meta name="description" content="<?= e($meta['deskripsi']) ?>">
<link rel="canonical" href="<?= e($meta['kanonik']) ?>">
<?php if (!empty($meta['noindex'])): ?>
<meta name="robots" content="noindex, nofollow">
<?php endif; ?>

<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($meta['judul']) ?>">
<meta property="og:description" content="<?= e($meta['deskripsi']) ?>">
<meta property="og:url" content="<?= e($meta['kanonik']) ?>">
<meta property="og:locale" content="<?= Lang::inggris() ? 'en_US' : 'id_ID' ?>">
<?php if (!empty($meta['gambar'])): ?>
<meta property="og:image" content="<?= e($meta['gambar']) ?>">
<meta name="twitter:card" content="summary_large_image">
<?php else: ?>
<meta name="twitter:card" content="summary">
<?php endif; ?>

<link rel="alternate" hreflang="id" href="<?= e(base_origin() . url_bahasa('id')) ?>">
<link rel="alternate" hreflang="en" href="<?= e(base_origin() . url_bahasa('en')) ?>">

<link rel="icon" href="<?= e(aset('assets/img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(aset('assets/vendor/bootstrap/bootstrap.min.css')) ?>">
<link rel="stylesheet" href="<?= e(aset('assets/css/gaya.css')) ?>">
<?= $isiKepala ?? '' ?>

<?php if (!empty($meta['jsonld'])): ?>
<script type="application/ld+json">
<?= json_skrip($meta['jsonld'], true) ?>
</script>
<?php endif; ?>
<script type="application/ld+json">
<?= json_skrip([
    '@context' => 'https://schema.org',
    '@type'    => 'GovernmentOrganization',
    'name'     => Pengaturan::ambil('instansi', 'Dinas Pariwisata Kabupaten Sikka'),
    'url'      => url_absolut('/'),
    'address'  => [
        '@type'           => 'PostalAddress',
        'addressLocality' => 'Maumere',
        'addressRegion'   => 'Nusa Tenggara Timur',
        'addressCountry'  => 'ID',
    ],
]) ?>
</script>
</head>
<body>

<a class="skip-link" href="#konten-utama">Lewati ke konten utama</a>

<?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

<?php if (!empty($flash)): ?>
<div class="container mt-3" role="status" aria-live="polite">
  <?php foreach ($flash as $f): ?>
  <div class="alert alert-<?= $f['tipe'] === 'sukses' ? 'success' : ($f['tipe'] === 'error' ? 'danger' : 'info') ?> alert-dismissible fade show">
    <?= e($f['pesan']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<main id="konten-utama">
<?= $isiHalaman ?>
</main>

<?php require dirname(__DIR__) . '/partials/footer.php'; ?>

<?php
/* Skrip khusus halaman (mis. Leaflet di halaman peta) sengaja ditaruh SEBELUM
   Bootstrap. Keduanya defer sehingga urutan eksekusi tetap terjaga, tetapi
   pada koneksi lambat berkas yang disebut lebih dulu memenangkan perebutan
   bandwidth - dan yang dinanti pengguna di halaman peta adalah petanya,
   bukan menu dropdown. */
?>
<?= $isiSkrip ?? '' ?>
<script src="<?= e(aset('assets/vendor/bootstrap/bootstrap.bundle.min.js')) ?>" defer fetchpriority="low"></script>
</body>
</html>
