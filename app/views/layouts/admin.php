<?php
/** Layout panel admin. Tidak pernah diindeks mesin pencari (§8). */
$peran = Auth::peran();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($meta['judul'] ?? 'Panel Admin') ?> | Admin Pariwisata Sikka</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="<?= e(aset('assets/img/favicon.svg')) ?>" type="image/svg+xml">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
<link rel="stylesheet" href="<?= e(aset('assets/css/gaya.css')) ?>">
<?= $isiKepala ?? '' ?>
</head>
<body>

<div class="admin-tata">
  <nav class="admin-samping" aria-label="Navigasi admin">
    <div class="px-3 pb-3 border-bottom border-secondary">
      <a class="d-flex align-items-center gap-2 text-decoration-none text-white" href="<?= e(url('/')) ?>">
        <span class="brand-mark">SK</span>
        <span class="small lh-sm">
          <strong class="d-block">Panel Admin</strong>
          <span class="opacity-75">Pariwisata Sikka</span>
        </span>
      </a>
    </div>

    <div class="menu-gulir">
      <a class="<?= App::uri() === '/admin' ? 'aktif' : '' ?>" href="<?= e(url('/admin')) ?>">Dasbor</a>

      <?php if (Auth::adalah('super_admin', 'admin_konten')): ?>
        <span class="kelompok">Konten</span>
        <a class="<?= str_starts_with(App::uri(), '/admin/destinasi') ? 'aktif' : '' ?>"
           href="<?= e(url('/admin/destinasi')) ?>">Destinasi &amp; Pin Peta</a>
        <a class="<?= str_starts_with(App::uri(), '/admin/event') ? 'aktif' : '' ?>"
           href="<?= e(url('/admin/event')) ?>">Event &amp; Budaya</a>
        <a class="<?= str_starts_with(App::uri(), '/admin/artikel') ? 'aktif' : '' ?>"
           href="<?= e(url('/admin/artikel')) ?>">Artikel</a>
      <?php endif; ?>

      <span class="kelompok">Mitra</span>
      <a class="<?= str_starts_with(App::uri(), '/admin/umkm') ? 'aktif' : '' ?>"
         href="<?= e(url('/admin/umkm')) ?>">UMKM &amp; Akomodasi</a>

      <?php if (Auth::adalah('super_admin', 'admin_konten')): ?>
        <span class="kelompok">Masukan Publik</span>
        <a class="<?= str_starts_with(App::uri(), '/admin/pengaduan') ? 'aktif' : '' ?>"
           href="<?= e(url('/admin/pengaduan')) ?>">
          Pengaduan
          <?php $baru = Pengaduan::jumlahBaru(); if ($baru > 0): ?>
            <span class="badge text-bg-danger"><?= (int) $baru ?></span>
          <?php endif; ?>
        </a>
        <a class="<?= str_starts_with(App::uri(), '/admin/ulasan') ? 'aktif' : '' ?>"
           href="<?= e(url('/admin/ulasan')) ?>">
          Ulasan
          <?php if (Ulasan::aktif() && ($m = Ulasan::jumlahMenunggu()) > 0): ?>
            <span class="badge text-bg-warning"><?= (int) $m ?></span>
          <?php endif; ?>
        </a>

        <span class="kelompok">Data</span>
        <a class="<?= str_starts_with(App::uri(), '/admin/statistik') ? 'aktif' : '' ?>"
           href="<?= e(url('/admin/statistik')) ?>">Statistik Kunjungan</a>
      <?php endif; ?>

      <?php if (Auth::adalah('super_admin')): ?>
        <span class="kelompok">Sistem</span>
        <a class="<?= str_starts_with(App::uri(), '/admin/pengguna') ? 'aktif' : '' ?>"
           href="<?= e(url('/admin/pengguna')) ?>">Pengguna</a>
        <a class="<?= str_starts_with(App::uri(), '/admin/pengaturan') ? 'aktif' : '' ?>"
           href="<?= e(url('/admin/pengaturan')) ?>">Pengaturan Situs</a>
        <a class="<?= str_starts_with(App::uri(), '/admin/log') ? 'aktif' : '' ?>"
           href="<?= e(url('/admin/log')) ?>">Log Aktivitas</a>
      <?php endif; ?>
    </div>
  </nav>

  <div class="admin-isi">
    <header class="bg-white border-bottom">
      <div class="d-flex justify-content-between align-items-center px-3 px-lg-4 py-2 flex-wrap gap-2">
        <h1 class="h5 mb-0"><?= e($meta['judul'] ?? 'Panel Admin') ?></h1>
        <div class="d-flex align-items-center gap-2 small">
          <a class="text-decoration-none" href="<?= e(url('/')) ?>" target="_blank" rel="noopener">Lihat situs ↗</a>
          <span class="text-secondary">|</span>
          <span>
            <strong><?= e($pengguna['nama'] ?? '') ?></strong>
            <span class="badge text-bg-light border"><?= e(Pengguna::LABEL_PERAN[$peran] ?? $peran) ?></span>
          </span>
          <form method="post" action="<?= e(url('/admin/logout')) ?>" class="d-inline">
            <?= Csrf::field() ?>
            <button class="btn btn-sm btn-outline-secondary" type="submit">Keluar</button>
          </form>
        </div>
      </div>
    </header>

    <?php if (!empty($flash)): ?>
    <div class="px-3 px-lg-4 pt-3" role="status" aria-live="polite">
      <?php foreach ($flash as $f): ?>
      <div class="alert alert-<?= $f['tipe'] === 'sukses' ? 'success' : ($f['tipe'] === 'error' ? 'danger' : 'info') ?> alert-dismissible fade show">
        <?= e($f['pesan']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <main class="p-3 p-lg-4">
      <?= $isiHalaman ?>
    </main>

    <footer class="px-3 px-lg-4 pb-4 small text-secondary">
      Sistem Informasi Pariwisata Kabupaten Sikka &middot;
      &copy; <?= date('Y') ?> <?= e(Pengaturan::ambil('hak_cipta', 'Karunia Bunda IT Training Center Maumere')) ?>
    </footer>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous" defer></script>
<script src="<?= e(aset('assets/js/admin.js')) ?>" defer></script>
<?= $isiSkrip ?? '' ?>
</body>
</html>
