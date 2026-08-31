<?php /** Direktori UMKM & akomodasi (FR-UMKM-01). */ ?>
<header class="bg-brand-soft py-4">
  <div class="container">
    <h1 class="h3 mb-1"><?= e($judul) ?></h1>
    <p class="text-body-secondary mb-0"><?= e($meta['deskripsi']) ?></p>
  </div>
</header>

<div class="container py-4">
  <form class="row g-2 align-items-end mb-4" method="get" role="search">
    <div class="col-12 col-md-5">
      <label class="form-label small" for="u-q"><?= e(Lang::teks('cari')) ?></label>
      <input class="form-control" type="search" id="u-q" name="q" value="<?= e(get_param('q')) ?>">
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label small" for="u-jenis"><?= e(Lang::inggris() ? 'Type' : 'Jenis') ?></label>
      <select class="form-select" id="u-jenis" name="jenis">
        <option value="">Semua</option>
        <?php foreach (Umkm::JENIS as $kode => $label): ?>
        <option value="<?= e($kode) ?>" <?= $jenisAktif === $kode ? 'selected' : '' ?>>
          <?= e(Umkm::labelJenis($kode)) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-2">
      <label class="form-label small" for="u-kec"><?= e(Lang::teks('kecamatan')) ?></label>
      <select class="form-select" id="u-kec" name="kecamatan">
        <option value="">Semua</option>
        <?php foreach ($kecamatanList as $kc): ?>
        <option value="<?= e($kc['slug']) ?>" <?= get_param('kecamatan') === $kc['slug'] ? 'selected' : '' ?>>
          <?= e($kc['nama']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12 col-md-2 d-grid">
      <button class="btn btn-teal" type="submit"><?= e(Lang::teks('filter')) ?></button>
    </div>
  </form>

  <p class="small text-secondary">
    <?= e(angka($total)) ?> <?= e(Lang::inggris() ? 'verified entries' : 'entri terverifikasi') ?>
  </p>

  <?php if ($daftar === []): ?>
    <div class="alert alert-light border">
      <?= e(Lang::teks('belum_ada_data')) ?>
      <p class="small mb-0 mt-2">
        <?= e(Lang::inggris()
              ? 'Local business owners can register through the Tourism Office to appear here free of charge.'
              : 'Pelaku UMKM dapat mendaftar melalui Dinas Pariwisata untuk tampil di direktori ini tanpa biaya.') ?>
      </p>
    </div>
  <?php else: ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3">
      <?php foreach ($daftar as $u): ?>
      <div class="col">
        <article class="card h-100">
          <div class="rasio-foto">
            <img src="<?= e(unggahan((string) $u['foto'], 'umkm')) ?>" alt="<?= e($u['nama']) ?>"
                 loading="lazy" width="400" height="260">
          </div>
          <div class="card-body">
            <span class="badge text-bg-light border mb-1"><?= e(Umkm::labelJenis((string) $u['jenis'])) ?></span>
            <h2 class="h6 mb-1">
              <a class="text-decoration-none text-body stretched-link"
                 href="<?= e(url('/umkm/' . $u['slug'])) ?>"><?= e($u['nama']) ?></a>
            </h2>
            <?php if ((string) $u['alamat'] !== ''): ?>
              <p class="small text-secondary mb-1"><?= e(ringkas((string) $u['alamat'], 70)) ?></p>
            <?php endif; ?>
            <?php if ($u['destinasi_nama'] !== null): ?>
              <p class="small mb-0 text-body-secondary">
                <?= e(Lang::inggris() ? 'Near' : 'Dekat') ?>: <?= e($u['destinasi_nama']) ?>
              </p>
            <?php endif; ?>
          </div>
        </article>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($totalHalaman > 1): ?>
    <nav class="mt-4" aria-label="Navigasi halaman">
      <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
          <?php $qs = $_GET; $qs['hal'] = $i; ?>
          <li class="page-item <?= $i === $halaman ? 'active' : '' ?>">
            <a class="page-link" href="<?= e(url('/umkm', $qs)) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>
