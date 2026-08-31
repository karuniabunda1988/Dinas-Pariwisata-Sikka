<?php
/**
 * Kartu ringkas destinasi - dipakai beranda, arsip, dan blok "terdekat".
 * @var array $d
 */
$nama    = Lang::kolom($d, 'nama');
$ringkas = ringkas(Lang::kolom($d, 'deskripsi_singkat'), 110);
?>
<article class="card h-100 kartu-destinasi">
  <a href="<?= e(url('/destinasi/' . $d['slug'])) ?>" class="text-decoration-none">
    <div class="rasio-foto">
      <img src="<?= e(unggahan((string) $d['foto_utama'])) ?>"
           alt="<?= e($nama) ?>" loading="lazy" width="400" height="260">
    </div>
  </a>
  <div class="card-body d-flex flex-column">
    <div class="mb-2">
      <span class="chip-kategori" style="--warna: <?= e($d['kategori_warna']) ?>">
        <?= e($d['kategori_ikon'] ?? '') ?>
        <?= e(Lang::inggris() && !empty($d['kategori_nama_en']) ? $d['kategori_nama_en'] : $d['kategori_nama']) ?>
      </span>
      <?php if (!empty($d['unggulan'])): ?>
        <span class="badge text-bg-warning">Unggulan</span>
      <?php endif; ?>
    </div>

    <h3 class="h6 card-title mb-1">
      <a class="stretched-link text-decoration-none text-body"
         href="<?= e(url('/destinasi/' . $d['slug'])) ?>"><?= e($nama) ?></a>
    </h3>

    <p class="small text-secondary mb-2">
      <?php if (!empty($d['kecamatan_nama'])): ?>
        Kec. <?= e($d['kecamatan_nama']) ?>
      <?php else: ?>
        <span class="text-warning-emphasis"><?= e(Lang::teks('perlu_verifikasi')) ?></span>
      <?php endif; ?>
      <?php if (isset($d['jarak_km'])): ?>
        &middot; <?= e($d['jarak_km']) ?> <?= e(Lang::teks('km')) ?>
      <?php elseif (!empty($d['waktu_tempuh_menit'])): ?>
        &middot; <?= (int) $d['waktu_tempuh_menit'] ?> <?= e(Lang::teks('menit')) ?> <?= e(Lang::teks('dari_maumere')) ?>
      <?php endif; ?>
    </p>

    <?php if ($ringkas !== ''): ?>
      <p class="small text-body-secondary mb-0"><?= e($ringkas) ?></p>
    <?php endif; ?>

    <?php if (!empty($d['jam_operasional'])): ?>
      <p class="small text-body-secondary mt-auto pt-2 mb-0">
        <span aria-hidden="true">🕒</span> <?= e($d['jam_operasional']) ?>
      </p>
    <?php endif; ?>
  </div>
</article>
