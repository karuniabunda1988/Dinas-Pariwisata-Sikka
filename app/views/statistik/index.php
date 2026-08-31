<?php
/**
 * Dasbor statistik terbuka (§9.7).
 * Grafik digambar dengan SVG/CSS sederhana - tidak menarik pustaka chart
 * tambahan demi menjaga halaman tetap ringan di koneksi 3G (§12).
 */
$maks = 0;
foreach ($tren as $t) {
    $maks = max($maks, (int) $t['jumlah']);
}
$namaBulanSingkat = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
?>

<header class="bg-brand-soft py-4">
  <div class="container">
    <h1 class="h3 mb-1"><?= e($judul) ?></h1>
    <p class="text-body-secondary mb-0"><?= e($meta['deskripsi']) ?></p>
  </div>
</header>

<div class="container py-4">

  <!-- Ringkasan cakupan platform: angka ini dihitung sistem, bukan input manual -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
      <div class="kartu-angka">
        <div class="nilai"><?= e(angka($ringkasan['aktif'])) ?></div>
        <div class="label"><?= e(Lang::inggris() ? 'Published destinations' : 'Destinasi terpublikasi') ?></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="kartu-angka">
        <div class="nilai"><?= (int) $kecamatanTercakup ?>/<?= (int) $kecamatanTotal ?></div>
        <div class="label"><?= e(Lang::inggris() ? 'Districts covered' : 'Kecamatan tercakup') ?></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="kartu-angka">
        <div class="nilai"><?= e(angka($umkm['terverifikasi'])) ?></div>
        <div class="label"><?= e(Lang::inggris() ? 'Verified businesses' : 'UMKM terverifikasi') ?></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="kartu-angka">
        <div class="nilai"><?= e(angka($ringkasan['ter_pin'])) ?></div>
        <div class="label"><?= e(Lang::inggris() ? 'Pins on the map' : 'Titik terpetakan') ?></div>
      </div>
    </div>
  </div>

  <?php if (!$adaData): ?>
    <div class="alert alert-light border">
      <h2 class="h6"><?= e(Lang::inggris() ? 'No visitor data entered yet' : 'Data kunjungan belum diinput') ?></h2>
      <p class="small mb-0">
        <?= e(Lang::inggris()
              ? 'Visitor figures are entered manually by the Tourism Office from data already collected by regional agencies. This system does not track visitors automatically.'
              : 'Angka kunjungan diinput manual oleh Dinas Pariwisata dari data yang sudah dikumpulkan OPD. Sistem ini tidak melacak wisatawan secara otomatis.') ?>
      </p>
    </div>
  <?php else: ?>

    <section class="mb-5">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0"><?= e(Lang::inggris() ? 'Monthly visitor trend' : 'Tren Kunjungan Bulanan') ?></h2>
      </div>

      <div class="card">
        <div class="card-body">
          <?php if ($tren === []): ?>
            <p class="small text-secondary mb-0"><?= e(Lang::teks('belum_ada_data')) ?></p>
          <?php else: ?>
          <div class="table-responsive">
            <div class="d-flex align-items-end gap-2" style="height: 220px; min-width: <?= count($tren) * 44 ?>px">
              <?php foreach ($tren as $t): ?>
                <?php
                $nilai  = (int) $t['jumlah'];
                $tinggi = $maks > 0 ? max(2, (int) round($nilai / $maks * 100)) : 2;
                $label  = $namaBulanSingkat[(int) $t['bulan']] . ' ' . substr((string) $t['tahun'], 2);
                ?>
                <div class="text-center" style="flex: 1 0 36px">
                  <div class="small text-secondary" style="font-size:.65rem"><?= e(angka($nilai)) ?></div>
                  <div style="height: <?= $tinggi ?>%; min-height:3px; background: var(--sikka-teal); border-radius:3px 3px 0 0"
                       role="img"
                       aria-label="<?= e($label) ?>: <?= e(angka($nilai)) ?> kunjungan"></div>
                  <div class="small text-secondary mt-1" style="font-size:.65rem"><?= e($label) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="mb-5">
      <form class="d-flex gap-2 align-items-end mb-3" method="get">
        <div>
          <label class="form-label small mb-1" for="s-tahun"><?= e(Lang::inggris() ? 'Year' : 'Tahun') ?></label>
          <select class="form-select form-select-sm" id="s-tahun" name="tahun" onchange="this.form.submit()">
            <?php foreach ($tahunTersedia as $th): ?>
              <option value="<?= (int) $th ?>" <?= $th === $tahun ? 'selected' : '' ?>><?= (int) $th ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <noscript><button class="btn btn-sm btn-teal" type="submit">Tampilkan</button></noscript>
      </form>

      <h2 class="h5 mb-3">
        <?= e(Lang::inggris() ? 'Distribution by destination category' : 'Sebaran per Kategori Destinasi') ?>
        <?= (int) $tahun ?>
        <small class="text-secondary">&middot; total <?= e(angka($totalTahun)) ?></small>
      </h2>

      <?php if ($perKategori === []): ?>
        <p class="small text-secondary"><?= e(Lang::teks('belum_ada_data')) ?></p>
      <?php else: ?>
        <?php $maksKat = max(array_map(static fn($k) => (int) $k['jumlah'], $perKategori)); ?>
        <?php foreach ($perKategori as $k): ?>
          <?php $persen = $maksKat > 0 ? round((int) $k['jumlah'] / $maksKat * 100) : 0; ?>
          <div class="mb-2">
            <div class="d-flex justify-content-between small">
              <span><span class="titik-kategori" style="background: <?= e($k['warna']) ?>"></span><?= e($k['nama']) ?></span>
              <strong><?= e(angka((int) $k['jumlah'])) ?></strong>
            </div>
            <div class="progress" style="height: 10px" role="img"
                 aria-label="<?= e($k['nama']) ?>: <?= e(angka((int) $k['jumlah'])) ?> kunjungan">
              <div class="progress-bar" style="width: <?= $persen ?>%; background: <?= e($k['warna']) ?>"></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  <?php endif; ?>

  <div class="alert alert-light border small">
    <strong><?= e(Lang::inggris() ? 'About this data' : 'Tentang data ini') ?>.</strong>
    <?= e(Lang::inggris()
          ? 'Visitor figures are entered manually by Tourism Office staff, each row recording its own source. This platform does not automatically count visitors at destinations - it publishes figures already collected by regional agencies so they can be used for planning and budget advocacy.'
          : 'Angka kunjungan diinput manual oleh staf Dinas dan setiap baris mencatat sumber datanya sendiri. Platform ini tidak menghitung wisatawan secara otomatis di lokasi - melainkan mempublikasikan data yang sudah dikumpulkan OPD agar dapat dipakai untuk perencanaan dan advokasi anggaran.') ?>
  </div>
</div>
