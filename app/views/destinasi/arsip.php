<?php
/**
 * Arsip destinasi - juga berfungsi sebagai landing page SEO per kategori
 * (FR-DEST-01). Wisatawan yang mencari "pantai dekat Maumere" mendarat di
 * sini, bukan di beranda generik (§7 perjalanan pengguna).
 */
$kategoriAktif  = $kategoriAktif  ?? null;
$kecamatanAktif = $kecamatanAktif ?? null;
$q    = get_param('q');
$urut = get_param('urut', 'unggulan');
?>

<header class="bg-brand-soft py-4">
  <div class="container">
    <nav aria-label="Remah roti">
      <ol class="breadcrumb small mb-2">
        <li class="breadcrumb-item"><a href="<?= e(url('/')) ?>"><?= e(Lang::teks('beranda')) ?></a></li>
        <li class="breadcrumb-item"><a href="<?= e(url('/destinasi')) ?>"><?= e(Lang::teks('destinasi')) ?></a></li>
        <?php if ($kategoriAktif !== null): ?>
          <li class="breadcrumb-item active" aria-current="page"><?= e($kategoriAktif['nama']) ?></li>
        <?php elseif ($kecamatanAktif !== null): ?>
          <li class="breadcrumb-item active" aria-current="page">Kec. <?= e($kecamatanAktif['nama']) ?></li>
        <?php endif; ?>
      </ol>
    </nav>

    <h1 class="h3 mb-1"><?= e($judul) ?></h1>
    <p class="text-body-secondary mb-0"><?= e($meta['deskripsi']) ?></p>
    <p class="small text-secondary mt-2 mb-0">
      <?= e(angka($total)) ?> <?= e(Lang::inggris() ? 'destinations found' : 'destinasi ditemukan') ?>
      &middot; <a href="<?= e(url('/peta', $kategoriAktif !== null ? ['kategori' => $kategoriAktif['slug']] : [])) ?>">
        <?= e(Lang::inggris() ? 'view on map' : 'lihat di peta') ?> &rarr;
      </a>
    </p>
  </div>
</header>

<div class="container py-4">
  <form class="row g-2 align-items-end mb-4" method="get" role="search">
    <div class="col-12 col-md-4">
      <label class="form-label small" for="f-q"><?= e(Lang::teks('cari')) ?></label>
      <input class="form-control" type="search" id="f-q" name="q" value="<?= e($q) ?>"
             placeholder="<?= e(Lang::teks('cari_destinasi')) ?>">
    </div>

    <?php if ($kategoriAktif === null): ?>
    <div class="col-6 col-md-3">
      <label class="form-label small" for="f-kategori"><?= e(Lang::teks('kategori')) ?></label>
      <select class="form-select" id="f-kategori" name="kategori">
        <option value=""><?= e(Lang::teks('semua_kategori')) ?></option>
        <?php foreach ($kategoriList as $k): ?>
        <option value="<?= e($k['slug']) ?>" <?= get_param('kategori') === $k['slug'] ? 'selected' : '' ?>>
          <?= e(Lang::inggris() && $k['nama_en'] !== '' ? $k['nama_en'] : $k['nama']) ?> (<?= (int) $k['jumlah'] ?>)
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>

    <?php if ($kecamatanAktif === null): ?>
    <div class="col-6 col-md-3">
      <label class="form-label small" for="f-kecamatan"><?= e(Lang::teks('kecamatan')) ?></label>
      <select class="form-select" id="f-kecamatan" name="kecamatan">
        <option value=""><?= e(Lang::teks('semua_kecamatan')) ?></option>
        <?php foreach ($kecamatanList as $kc): ?>
        <option value="<?= e($kc['slug']) ?>" <?= get_param('kecamatan') === $kc['slug'] ? 'selected' : '' ?>>
          <?= e($kc['nama']) ?> (<?= (int) $kc['jumlah'] ?>)
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>

    <div class="col-12 col-md-2 d-grid">
      <button class="btn btn-teal" type="submit"><?= e(Lang::teks('filter')) ?></button>
    </div>
  </form>

  <?php if ($daftar === []): ?>
    <div class="alert alert-light border">
      <?= e(Lang::teks('belum_ada_data')) ?>
      <?php if ($q !== ''): ?>
        <a href="<?= e(url(App::uri())) ?>"><?= e(Lang::teks('reset')) ?></a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
      <?php foreach ($daftar as $d): ?>
        <div class="col"><?php require dirname(__DIR__) . '/partials/kartu-destinasi.php'; ?></div>
      <?php endforeach; ?>
    </div>

    <?php if ($totalHalaman > 1): ?>
    <nav class="mt-4" aria-label="Navigasi halaman">
      <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
          <?php $qs = $_GET; $qs['hal'] = $i; ?>
          <li class="page-item <?= $i === $halaman ? 'active' : '' ?>">
            <a class="page-link" href="<?= e(url(App::uri(), $qs)) ?>"
               <?= $i === $halaman ? 'aria-current="page"' : '' ?>><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php if ($kategoriAktif !== null): ?>
<!-- Tautan silang antar kategori: membantu SEO internal & penjelajahan -->
<section class="bg-body-tertiary py-4">
  <div class="container">
    <h2 class="h6 text-uppercase text-secondary mb-3"><?= e(Lang::inggris() ? 'Other categories' : 'Kategori lainnya') ?></h2>
    <div class="d-flex flex-wrap gap-2">
      <?php foreach ($kategoriList as $k): ?>
        <?php if ($k['slug'] === $kategoriAktif['slug']) { continue; } ?>
        <a class="btn btn-sm btn-light border" href="<?= e(url('/destinasi/kategori/' . $k['slug'])) ?>">
          <?= e($k['ikon']) ?> <?= e(Lang::inggris() && $k['nama_en'] !== '' ? $k['nama_en'] : $k['nama']) ?>
          <span class="text-secondary">(<?= (int) $k['jumlah'] ?>)</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
