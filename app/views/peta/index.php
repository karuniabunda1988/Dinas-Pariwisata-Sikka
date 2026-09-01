<?php
/**
 * Peta interaktif layar penuh - fitur inti (§9.2, §10).
 * FR-MAP-01 s/d 09 diimplementasikan di sini + public/assets/js/peta.js
 */
$peta = App::config('peta');
/*
 * Peta adalah isi utama halaman ini, jadi berkasnya diberi prioritas unduh
 * tertinggi. Pada koneksi 3G seluruh berkas berebut bandwidth yang sama;
 * tanpa petunjuk ini Leaflet mengantre di belakang skrip lain dan peta baru
 * bisa dipakai beberapa detik lebih lambat.
 */
$isiKepala = '
<link rel="preload" as="script" fetchpriority="high" href="' . e(aset('assets/vendor/leaflet/leaflet.js')) . '">
<link rel="preload" as="script" href="' . e(aset('assets/vendor/markercluster/leaflet.markercluster.js')) . '">
<link rel="stylesheet" href="' . e(aset('assets/vendor/leaflet/leaflet.css')) . '">
<link rel="stylesheet" href="' . e(aset('assets/vendor/markercluster/MarkerCluster.css')) . '">
<link rel="stylesheet" href="' . e(aset('assets/vendor/markercluster/MarkerCluster.Default.css')) . '">';
?>

<div class="peta-halaman">
  <!-- Panel kendali: pencarian + filter (FR-MAP-03, FR-MAP-04, FR-MAP-06) -->
  <aside class="peta-panel" aria-label="<?= e(Lang::teks('filter')) ?>">
    <div class="p-3 border-bottom">
      <h1 class="h5 mb-1"><?= e(Lang::teks('peta_wisata')) ?></h1>
      <p class="small text-secondary mb-3">
        <span id="peta-jumlah"><?= count($pin) ?></span>
        <?= e(Lang::inggris() ? 'destinations mapped across 21 districts' : 'destinasi terpetakan di 21 kecamatan') ?>
      </p>

      <div class="position-relative">
        <label class="visually-hidden" for="peta-cari"><?= e(Lang::teks('cari_destinasi')) ?></label>
        <input class="form-control" type="search" id="peta-cari" autocomplete="off"
               role="combobox" aria-expanded="false" aria-autocomplete="list"
               aria-controls="peta-saran"
               placeholder="🔍 <?= e(Lang::teks('cari_destinasi')) ?>">
        <ul class="saran-daftar list-unstyled" id="peta-saran" role="listbox" hidden></ul>
      </div>

      <button class="btn btn-sm btn-outline-teal w-100 mt-2" type="button" id="peta-lokasi-saya">
        <span aria-hidden="true">📍</span> <?= e(Lang::teks('lokasi_saya')) ?>
      </button>

      <!-- Fallback bila izin geolokasi ditolak (FR-MAP-06) -->
      <div class="mt-2" id="peta-fallback-kecamatan" hidden>
        <label class="form-label small mb-1" for="pilih-kecamatan">
          <?= e(Lang::inggris() ? 'Or choose a district manually' : 'Atau pilih kecamatan secara manual') ?>
        </label>
        <select class="form-select form-select-sm" id="pilih-kecamatan">
          <option value=""><?= e(Lang::teks('semua_kecamatan')) ?></option>
          <?php foreach ($kecamatan as $kc): ?>
            <?php if ($kc['latitude'] !== null): ?>
            <option value="<?= e($kc['latitude']) ?>,<?= e($kc['longitude']) ?>"><?= e($kc['nama']) ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Filter kategori + legenda selalu terlihat (FR-MAP-07) -->
    <div class="p-3 border-bottom">
      <h2 class="h6 text-uppercase text-secondary small mb-2"><?= e(Lang::teks('kategori')) ?> &amp; <?= e(Lang::teks('legenda')) ?></h2>
      <div class="d-flex flex-wrap gap-2" role="group" aria-label="<?= e(Lang::teks('filter')) ?>">
        <button class="chip-filter aktif" type="button" data-kategori=""><?= e(Lang::teks('semua_kategori')) ?></button>
        <?php foreach ($kategori as $k): ?>
        <button class="chip-filter" type="button"
                data-kategori="<?= e($k['slug']) ?>"
                style="--warna: <?= e($k['warna']) ?>">
          <span class="titik-kategori" style="background: <?= e($k['warna']) ?>" aria-hidden="true"></span>
          <?= e($k['ikon']) ?> <?= e(Lang::inggris() && $k['nama_en'] !== '' ? $k['nama_en'] : $k['nama']) ?>
          <span class="text-secondary">(<?= (int) $k['jumlah'] ?>)</span>
        </button>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="p-3 border-bottom">
      <label class="form-label small text-uppercase text-secondary mb-1" for="filter-kecamatan">
        <?= e(Lang::teks('kecamatan')) ?>
      </label>
      <select class="form-select form-select-sm" id="filter-kecamatan">
        <option value=""><?= e(Lang::teks('semua_kecamatan')) ?></option>
        <?php foreach ($kecamatan as $kc): ?>
        <option value="<?= e($kc['slug']) ?>"><?= e($kc['nama']) ?> (<?= (int) $kc['jumlah'] ?>)</option>
        <?php endforeach; ?>
      </select>

      <button class="btn btn-sm btn-link px-0 mt-2" type="button" id="peta-reset"><?= e(Lang::teks('reset')) ?></button>
    </div>

    <!-- FR-MAP-11: unduh titik untuk GPS / aplikasi trekking & selam -->
    <div class="p-3 border-bottom">
      <h2 class="h6 text-uppercase text-secondary small mb-2">
        <?= e(Lang::inggris() ? 'Download points' : 'Unduh Titik') ?>
      </h2>
      <p class="small text-body-secondary mb-2">
        <?= e(Lang::inggris()
              ? 'Save the destinations currently shown for use in a handheld GPS or trekking app - useful where there is no mobile signal.'
              : 'Simpan destinasi yang sedang tampil untuk dipakai di GPS genggam atau aplikasi trekking - berguna di lokasi tanpa sinyal.') ?>
      </p>
      <div class="d-flex gap-2">
        <a class="btn btn-sm btn-outline-secondary" id="unduh-gpx"
           href="<?= e(url('/ekspor/gpx')) ?>" download>GPX</a>
        <a class="btn btn-sm btn-outline-secondary" id="unduh-kml"
           href="<?= e(url('/ekspor/kml')) ?>" download>KML</a>
      </div>
    </div>

    <!-- Hasil terlihat: juga berfungsi sebagai daftar teks (§10.7) -->
    <div class="peta-hasil p-3" id="peta-hasil" aria-live="polite"></div>
  </aside>

  <div class="peta-wadah">
    <div id="peta-utama" class="peta-utama" role="application"
         aria-label="<?= e(Lang::inggris() ? 'Interactive map of Sikka tourist destinations' : 'Peta interaktif destinasi wisata Sikka') ?>">
      <div class="peta-skeleton" aria-hidden="true"></div>
    </div>

    <button class="btn btn-sm btn-dark peta-toggle-panel d-lg-none" type="button" id="peta-toggle-panel">
      <?= e(Lang::teks('filter')) ?>
    </button>
  </div>
</div>

<!--
  Fallback daftar teks (§10.7 & FR-MAP tanpa JavaScript).
  Selalu ada di HTML sehingga wisatawan di area sinyal lemah - dan mesin
  pencari - tetap mendapat informasi inti meski peta gagal dimuat.
-->
<section class="container py-4" id="daftar-teks-destinasi">
  <h2 class="h5 mb-1"><?= e(Lang::teks('daftar_teks')) ?></h2>
  <p class="small text-secondary">
    <?= e(Lang::inggris()
          ? 'Full list of destinations, also usable when the map cannot load.'
          : 'Daftar lengkap destinasi, tetap dapat dipakai bila peta gagal dimuat.') ?>
  </p>

  <?php if ($daftarTeks === []): ?>
    <div class="alert alert-light border"><?= e(Lang::teks('belum_ada_data')) ?></div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead>
        <tr>
          <th scope="col"><?= e(Lang::teks('destinasi')) ?></th>
          <th scope="col"><?= e(Lang::teks('kategori')) ?></th>
          <th scope="col"><?= e(Lang::teks('kecamatan')) ?></th>
          <th scope="col"><?= e(Lang::teks('jam_operasional')) ?></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($daftarTeks as $d): ?>
        <tr>
          <th scope="row" class="fw-normal">
            <a href="<?= e(url('/destinasi/' . $d['slug'])) ?>"><?= e(Lang::kolom($d, 'nama')) ?></a>
          </th>
          <td>
            <span class="chip-kategori" style="--warna: <?= e($d['kategori_warna']) ?>">
              <?= e(Lang::inggris() && $d['kategori_nama_en'] !== '' ? $d['kategori_nama_en'] : $d['kategori_nama']) ?>
            </span>
          </td>
          <td class="small"><?= e($d['kecamatan_nama'] ?? '-') ?></td>
          <td class="small"><?= e($d['jam_operasional'] !== '' ? $d['jam_operasional'] : '-') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</section>

<?php
$isiSkrip = '
<script src="' . e(aset('assets/vendor/leaflet/leaflet.js')) . '" defer></script>
<script src="' . e(aset('assets/vendor/markercluster/leaflet.markercluster.js')) . '" defer></script>
<script>
window.SIKKA_PETA = ' . json_skrip([
    'pin'      => $pin,
    'lat'      => (float) Pengaturan::ambil('peta_lat_awal', (string) $peta['lat_awal']),
    'lng'      => (float) Pengaturan::ambil('peta_lng_awal', (string) $peta['lng_awal']),
    'zoom'     => (int) Pengaturan::ambil('peta_zoom_awal', (string) $peta['zoom_awal']),
    'zoomMaks' => (int) $peta['zoom_maks'],
    'tile'     => $peta['tile_url'],
    'atribusi' => $peta['tile_atribusi'],
    'urlCari'  => url('/api/cari'),
    'urlGpx'   => url('/ekspor/gpx'),
    'urlKml'   => url('/ekspor/kml'),
    'urlPeta'  => url('/peta'),
    'terpilih' => $terpilih !== null ? $terpilih['slug'] : null,
    'lapisan'  => $lapisan,
    'urlBatas' => $urlBatas,
    'teks'     => [
        'lihatDetail'  => Lang::teks('lihat_detail'),
        'ruteKeSini'   => Lang::teks('rute_ke_sini'),
        'bagikan'      => Lang::teks('bagikan'),
        'jam'          => Lang::teks('jam_operasional'),
        'tarif'        => Lang::teks('kisaran_tarif'),
        'kecamatan'    => Lang::teks('kecamatan'),
        'tidakAda'     => Lang::teks('belum_ada_data'),
        'lokasiDitolak'=> Lang::inggris()
            ? 'Location access denied. Choose a district manually instead.'
            : 'Izin lokasi ditolak. Silakan pilih kecamatan secara manual.',
        'lokasiAnda'   => Lang::inggris() ? 'Your location' : 'Lokasi Anda',
        'tautanDisalin'=> Lang::inggris() ? 'Link copied.' : 'Tautan disalin.',
        'hasil'        => Lang::inggris() ? 'result(s)' : 'hasil',
        'lapisanPeta'  => Lang::inggris() ? 'Map layer' : 'Lapisan peta',
        'batasKec'     => Lang::inggris() ? 'District boundaries' : 'Batas Kecamatan',
        'unduhTitik'   => Lang::inggris() ? 'Download points' : 'Unduh titik',
    ],
]) . ';
</script>
<script src="' . e(aset('assets/js/peta.js')) . '" defer></script>';
?>
