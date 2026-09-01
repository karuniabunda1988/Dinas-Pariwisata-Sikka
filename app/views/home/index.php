<?php
/**
 * Beranda. FR-HOME-01: hero memuat peta ringkas INTERAKTIF, bukan gambar
 * statis - pengguna bisa langsung menggeser dan mengklik pin.
 */
$peta = App::config('peta');
// CSS Leaflet tetap dimuat di awal (kecil, ~4 KB setelah kompresi) agar peta
// tidak "melompat" saat skripnya menyusul. Skrip Leaflet-nya dimuat malas.
$isiKepala = '<link rel="stylesheet" href="' . e(aset('assets/vendor/leaflet/leaflet.css')) . '">';
?>

<section class="hero-beranda">
  <div class="container py-4 py-lg-5">
    <div class="row align-items-center g-4">
      <div class="col-lg-5">
        <p class="text-uppercase small fw-semibold text-teal mb-2">Kabupaten Sikka &middot; Flores &middot; NTT</p>
        <h1 class="display-6 fw-bold mb-3">
          <?= e(Lang::inggris()
                ? Pengaturan::ambil('tagline_en', 'Official Tourism Map & Information of Sikka Regency')
                : Pengaturan::ambil('tagline', 'Peta & Informasi Resmi Wisata Kabupaten Sikka')) ?>
        </h1>
        <p class="lead text-body-secondary">
          <?= e(Lang::inggris()
                ? 'Find destinations, opening hours, costs and how to get there - mapped across all 21 districts.'
                : 'Temukan destinasi, jam buka, kisaran biaya, dan cara mencapainya - terpetakan di seluruh 21 kecamatan.') ?>
        </p>

        <form class="mt-4" action="<?= e(url('/destinasi')) ?>" method="get" role="search">
          <label class="visually-hidden" for="cari-beranda"><?= e(Lang::teks('cari_destinasi')) ?></label>
          <div class="input-group input-group-lg">
            <input class="form-control" type="search" id="cari-beranda" name="q"
                   placeholder="<?= e(Lang::teks('cari_destinasi')) ?>">
            <button class="btn btn-teal" type="submit"><?= e(Lang::teks('cari')) ?></button>
          </div>
        </form>

        <div class="d-flex flex-wrap gap-2 mt-3">
          <a class="btn btn-outline-teal" href="<?= e(url('/peta')) ?>">
            <span aria-hidden="true">🗺</span> <?= e(Lang::teks('buka_peta')) ?>
          </a>
          <?php foreach (array_slice($kategori, 0, 3) as $k): ?>
            <a class="btn btn-sm btn-light border align-self-center"
               href="<?= e(url('/destinasi/kategori/' . $k['slug'])) ?>">
              <?= e($k['ikon']) ?> <?= e(Lang::inggris() && $k['nama_en'] !== '' ? $k['nama_en'] : $k['nama']) ?>
              <span class="text-secondary">(<?= (int) $k['jumlah'] ?>)</span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="col-lg-7">
        <!-- FR-HOME-01: peta ringkas interaktif, klik pin menuju /peta -->
        <div class="peta-ringkas-bungkus shadow-sm">
          <div id="peta-ringkas" class="peta-ringkas" role="application"
               aria-label="<?= e(Lang::inggris() ? 'Summary map of Sikka tourist destinations' : 'Peta ringkas destinasi wisata Sikka') ?>">
            <div class="peta-skeleton" aria-hidden="true"></div>
          </div>
          <noscript>
            <div class="p-3 bg-body-tertiary small">
              Peta interaktif memerlukan JavaScript.
              <a href="<?= e(url('/destinasi')) ?>">Lihat daftar destinasi dalam bentuk teks</a>.
            </div>
          </noscript>
          <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-body-tertiary small">
            <span class="text-secondary">
              <?= (int) $statistik['destinasi_ter_pin'] ?>
              <?= e(Lang::inggris() ? 'pins on the map' : 'titik terpetakan') ?>
            </span>
            <a href="<?= e(url('/peta')) ?>"><?= e(Lang::teks('buka_peta')) ?> &rarr;</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FR-HOME-03: statistik ringkas + running strip event -->
<section class="bg-brand-soft py-3">
  <div class="container">
    <div class="row g-3 text-center">
      <div class="col-6 col-lg-3">
        <div class="angka-ringkas"><?= e(angka($statistik['destinasi_aktif'])) ?></div>
        <div class="small text-secondary"><?= e(Lang::inggris() ? 'Published destinations' : 'Destinasi terpublikasi') ?></div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="angka-ringkas"><?= (int) $statistik['kecamatan_tercakup'] ?>/<?= (int) $statistik['kecamatan_total'] ?></div>
        <div class="small text-secondary"><?= e(Lang::inggris() ? 'Districts covered' : 'Kecamatan tercakup') ?></div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="angka-ringkas"><?= e(angka($statistik['umkm'])) ?></div>
        <div class="small text-secondary"><?= e(Lang::inggris() ? 'Verified businesses' : 'UMKM terverifikasi') ?></div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="angka-ringkas"><?= count($kategori) ?></div>
        <div class="small text-secondary"><?= e(Lang::teks('kategori')) ?></div>
      </div>
    </div>
  </div>
</section>

<?php if (($rt = Pengaturan::ambil('running_text', '')) !== '' || $eventDekat !== []): ?>
<section class="strip-event py-2">
  <div class="container d-flex align-items-center gap-3 small">
    <span class="badge text-bg-dark flex-shrink-0"><?= e(Lang::teks('event_terdekat')) ?></span>
    <div class="strip-teks">
      <?php if ($eventDekat !== []): ?>
        <?php foreach ($eventDekat as $ev): ?>
          <a class="me-4 text-decoration-none" href="<?= e(url('/event/' . $ev['slug'])) ?>">
            <strong><?= e(tanggal_lokal((string) $ev['tanggal_mulai'], false)) ?></strong>
            &middot; <?= e($ev['nama']) ?>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <?= e($rt) ?>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- FR-HOME-02: destinasi unggulan (10 kelas prioritas studi Puspar UGM) -->
<section class="container py-5">
  <div class="d-flex justify-content-between align-items-end mb-3">
    <div>
      <h2 class="h4 mb-1"><?= e(Lang::teks('destinasi_unggulan')) ?></h2>
      <p class="text-secondary small mb-0">
        <?= e(Lang::inggris()
              ? 'Priority-class destinations identified by the Puspar UGM study.'
              : 'Destinasi kelas prioritas hasil studi Puspar UGM.') ?>
      </p>
    </div>
    <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('/destinasi')) ?>"><?= e(Lang::teks('lihat_semua')) ?></a>
  </div>

  <?php if ($unggulan === []): ?>
    <div class="alert alert-light border"><?= e(Lang::teks('belum_ada_data')) ?></div>
  <?php else: ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
      <?php foreach ($unggulan as $d): ?>
        <div class="col"><?php require dirname(__DIR__) . '/partials/kartu-destinasi.php'; ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<!-- Jelajah per kategori: landing page SEO (FR-DEST-01) -->
<section class="bg-body-tertiary py-5">
  <div class="container">
    <h2 class="h4 mb-3"><?= e(Lang::inggris() ? 'Browse by Category' : 'Jelajahi per Kategori') ?></h2>
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
      <?php foreach ($kategori as $k): ?>
      <div class="col">
        <a class="kartu-kategori text-decoration-none" style="--warna: <?= e($k['warna']) ?>"
           href="<?= e(url('/destinasi/kategori/' . $k['slug'])) ?>">
          <span class="ikon" aria-hidden="true"><?= e($k['ikon']) ?></span>
          <span class="nama"><?= e(Lang::inggris() && $k['nama_en'] !== '' ? $k['nama_en'] : $k['nama']) ?></span>
          <span class="jumlah"><?= (int) $k['jumlah'] ?></span>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($artikel !== []): ?>
<section class="container py-5">
  <div class="d-flex justify-content-between align-items-end mb-3">
    <h2 class="h4 mb-0"><?= e(Lang::inggris() ? 'Guides & News' : 'Panduan & Berita') ?></h2>
    <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('/artikel')) ?>"><?= e(Lang::teks('lihat_semua')) ?></a>
  </div>
  <div class="row row-cols-1 row-cols-md-3 g-3">
    <?php foreach ($artikel as $a): ?>
    <div class="col">
      <article class="card h-100">
        <div class="rasio-foto">
          <img src="<?= e(unggahan((string) $a['gambar_sampul'], 'artikel')) ?>"
               alt="<?= e($a['judul']) ?>" loading="lazy" width="400" height="240">
        </div>
        <div class="card-body">
          <h3 class="h6"><a class="text-decoration-none text-body stretched-link"
             href="<?= e(url('/artikel/' . $a['slug'])) ?>"><?= e($a['judul']) ?></a></h3>
          <p class="small text-body-secondary mb-0"><?= e(ringkas((string) $a['ringkasan'], 110)) ?></p>
        </div>
      </article>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php
// Skrip peta ringkas beranda. Leaflet dimuat defer agar tidak memblokir render.
// Leaflet TIDAK dimuat di sini. muat-peta-malas.js menariknya hanya ketika
// peta ringkas hampir masuk layar - di ponsel posisinya di bawah lipatan,
// sehingga konten utama beranda tampil lebih dulu pada koneksi 3G.
$isiSkrip = '
<script>
window.SIKKA_PETA = ' . json_skrip([
    'pin'        => $pinAwal,
    'lat'        => (float) Pengaturan::ambil('peta_lat_awal', (string) $peta['lat_awal']),
    'lng'        => (float) Pengaturan::ambil('peta_lng_awal', (string) $peta['lng_awal']),
    'zoom'       => (int) Pengaturan::ambil('peta_zoom_awal', (string) $peta['zoom_awal']),
    'zoomMaks'   => (int) $peta['zoom_maks'],
    'tile'       => $peta['tile_url'],
    'atribusi'   => $peta['tile_atribusi'],
    'urlPeta'    => url('/peta'),
    'urlLeaflet' => aset('assets/vendor/leaflet/leaflet.js'),
    'urlSkrip'   => aset('assets/js/peta-ringkas.js'),
]) . ';
</script>
<script src="' . e(aset('assets/js/muat-peta-malas.js')) . '" defer></script>';
?>
