<?php
/**
 * Halaman detail destinasi (FR-DEST-02).
 * Menjawab pertanyaan wisatawan: apa, di mana, jam berapa, berapa biaya,
 * bagaimana ke sana, dan apa yang ada di sekitarnya.
 */
$peta = App::config('peta');
$adaKoordinat = $d['latitude'] !== null && $d['longitude'] !== null;
$fasilitas = array_values(array_filter(array_map('trim', explode(',', (string) $d['fasilitas']))));

if ($adaKoordinat) {
    $isiKepala = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">';
}
?>

<header class="bg-brand-soft py-4">
  <div class="container">
    <nav aria-label="Remah roti">
      <ol class="breadcrumb small mb-2">
        <li class="breadcrumb-item"><a href="<?= e(url('/')) ?>"><?= e(Lang::teks('beranda')) ?></a></li>
        <li class="breadcrumb-item"><a href="<?= e(url('/destinasi')) ?>"><?= e(Lang::teks('destinasi')) ?></a></li>
        <li class="breadcrumb-item">
          <a href="<?= e(url('/destinasi/kategori/' . $d['kategori_slug'])) ?>"><?= e($d['kategori_nama']) ?></a>
        </li>
        <li class="breadcrumb-item active" aria-current="page"><?= e(Lang::kolom($d, 'nama')) ?></li>
      </ol>
    </nav>

    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
      <span class="chip-kategori" style="--warna: <?= e($d['kategori_warna']) ?>">
        <?= e($d['kategori_ikon']) ?> <?= e($d['kategori_nama']) ?>
      </span>
      <?php if ((int) $d['unggulan'] === 1): ?>
        <span class="badge text-bg-warning">Destinasi Unggulan</span>
      <?php endif; ?>
      <?php if ((int) $d['perlu_verifikasi_lapangan'] === 1): ?>
        <span class="badge text-bg-light border"><?= e(Lang::teks('perlu_verifikasi')) ?></span>
      <?php endif; ?>
    </div>

    <h1 class="h2 mb-1"><?= e(Lang::kolom($d, 'nama')) ?></h1>
    <p class="text-body-secondary mb-0">
      <?php if ($d['kecamatan_nama'] !== null): ?>
        Kecamatan <?= e($d['kecamatan_nama']) ?>, Kabupaten Sikka
      <?php else: ?>
        Kabupaten Sikka
      <?php endif; ?>
      <?php if ($d['waktu_tempuh_menit'] !== null): ?>
        &middot; ~<?= (int) $d['waktu_tempuh_menit'] ?> <?= e(Lang::teks('menit')) ?> <?= e(Lang::teks('dari_maumere')) ?>
      <?php endif; ?>
    </p>
  </div>
</header>

<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-8">
      <!-- Galeri foto -->
      <div class="galeri-destinasi mb-4">
        <img src="<?= e(unggahan((string) $d['foto_utama'])) ?>"
             alt="<?= e(Lang::kolom($d, 'nama')) ?>" class="mb-2" width="800" height="600">
        <?php if ($galeri !== []): ?>
          <div class="row row-cols-3 row-cols-md-4 g-2">
            <?php foreach ($galeri as $g): ?>
            <div class="col">
              <img src="<?= e(unggahan((string) $g['file'])) ?>"
                   alt="<?= e($g['alt_text'] !== '' ? $g['alt_text'] : Lang::kolom($d, 'nama')) ?>"
                   loading="lazy" width="200" height="150">
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <?php if ((int) $d['perlu_verifikasi_lapangan'] === 1): ?>
      <div class="catatan-verifikasi mb-4">
        <strong><?= e(Lang::inggris() ? 'Data not yet field-verified.' : 'Data belum diverifikasi lapangan.') ?></strong>
        <?= e(Lang::inggris()
              ? 'Coordinates and practical details for this destination come from secondary sources and are awaiting confirmation by the Tourism Office. Please confirm before travelling.'
              : 'Koordinat dan detail praktis destinasi ini berasal dari sumber sekunder dan menunggu konfirmasi Dinas Pariwisata. Mohon konfirmasi ulang sebelum berkunjung.') ?>
      </div>
      <?php endif; ?>

      <?php $lengkap = Lang::kolom($d, 'deskripsi_lengkap'); ?>
      <?php if (trim($lengkap) !== ''): ?>
        <section class="mb-4">
          <h2 class="h5"><?= e(Lang::inggris() ? 'About this destination' : 'Tentang Destinasi Ini') ?></h2>
          <?= paragraf($lengkap) ?>
        </section>
      <?php elseif (Lang::kolom($d, 'deskripsi_singkat') !== ''): ?>
        <p class="lead"><?= e(Lang::kolom($d, 'deskripsi_singkat')) ?></p>
      <?php endif; ?>

      <?php if (trim((string) $d['cara_mencapai']) !== ''): ?>
      <section class="mb-4">
        <h2 class="h5"><?= e(Lang::teks('cara_mencapai')) ?></h2>
        <?= paragraf((string) $d['cara_mencapai']) ?>
      </section>
      <?php endif; ?>

      <?php if ($fasilitas !== []): ?>
      <section class="mb-4">
        <h2 class="h5"><?= e(Lang::teks('fasilitas')) ?></h2>
        <ul class="list-inline">
          <?php foreach ($fasilitas as $f): ?>
            <li class="list-inline-item badge text-bg-light border fw-normal mb-1"><?= e($f) ?></li>
          <?php endforeach; ?>
        </ul>
      </section>
      <?php endif; ?>

      <!-- FR-UMKM-02: UMKM terdekat tampil di halaman destinasi -->
      <?php if ($umkm !== []): ?>
      <section class="mb-4">
        <h2 class="h5"><?= e(Lang::teks('umkm_terdekat')) ?></h2>
        <div class="row row-cols-1 row-cols-md-2 g-3">
          <?php foreach ($umkm as $u): ?>
          <div class="col">
            <div class="card h-100">
              <div class="card-body">
                <span class="badge text-bg-light border mb-1"><?= e(Umkm::labelJenis((string) $u['jenis'])) ?></span>
                <h3 class="h6 mb-1">
                  <a class="text-decoration-none text-body" href="<?= e(url('/umkm/' . $u['slug'])) ?>"><?= e($u['nama']) ?></a>
                </h3>
                <?php if ((string) $u['alamat'] !== ''): ?>
                  <p class="small text-secondary mb-1"><?= e($u['alamat']) ?></p>
                <?php endif; ?>
                <?php if ((string) $u['kontak_wa'] !== ''): ?>
                  <a class="btn btn-sm btn-outline-success" rel="noopener" target="_blank"
                     href="https://wa.me/<?= e(nomor_wa((string) $u['kontak_wa'])) ?>">WhatsApp</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <!-- Ulasan (Fase 2 - hanya tampil bila diaktifkan admin) -->
      <?php if ($ulasanAktif): ?>
      <section class="mb-4">
        <h2 class="h5">
          <?= e(Lang::inggris() ? 'Visitor Reviews' : 'Ulasan Pengunjung') ?>
          <?php if ($rating['jumlah'] > 0): ?>
            <small class="text-secondary">&middot; <?= e($rating['rata']) ?>/5 (<?= (int) $rating['jumlah'] ?>)</small>
          <?php endif; ?>
        </h2>

        <?php if ($ulasan === []): ?>
          <p class="small text-secondary"><?= e(Lang::teks('belum_ada_data')) ?></p>
        <?php else: ?>
          <?php foreach ($ulasan as $ul): ?>
          <div class="border-bottom py-2">
            <strong class="small"><?= e($ul['nama_penulis']) ?></strong>
            <span class="text-warning small"><?= str_repeat('★', (int) $ul['rating']) ?></span>
            <p class="small mb-0"><?= e($ul['komentar']) ?></p>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <form method="post" action="<?= e(url('/destinasi/' . $d['slug'] . '/ulasan')) ?>" class="mt-3">
          <?= Csrf::field() ?>
          <div class="visually-hidden" aria-hidden="true">
            <label for="website-ul">Website</label>
            <input type="text" id="website-ul" name="website" tabindex="-1" autocomplete="off">
          </div>
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label small" for="ul-nama">Nama</label>
              <input class="form-control form-control-sm" id="ul-nama" name="nama" required maxlength="120">
            </div>
            <div class="col-md-6">
              <label class="form-label small" for="ul-rating">Rating</label>
              <select class="form-select form-select-sm" id="ul-rating" name="rating">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                  <option value="<?= $i ?>"><?= str_repeat('★', $i) ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label small" for="ul-komentar">Ulasan</label>
              <textarea class="form-control form-control-sm" id="ul-komentar" name="komentar"
                        rows="3" required minlength="10" maxlength="2000"></textarea>
              <div class="form-text">Ulasan tayang setelah diperiksa admin.</div>
            </div>
            <div class="col-12">
              <button class="btn btn-sm btn-teal" type="submit"><?= e(Lang::teks('kirim')) ?></button>
            </div>
          </div>
        </form>
      </section>
      <?php endif; ?>
    </div>

    <!-- Kolom samping: fakta praktis + peta mini + rute -->
    <div class="col-lg-4">
      <div class="card mb-3">
        <div class="card-body">
          <h2 class="h6 text-uppercase text-secondary mb-3">
            <?= e(Lang::inggris() ? 'Practical Information' : 'Informasi Praktis') ?>
          </h2>
          <dl class="daftar-fakta mb-0">
            <dt><?= e(Lang::teks('jam_operasional')) ?></dt>
            <dd><?= e((string) $d['jam_operasional'] !== '' ? $d['jam_operasional'] : '-') ?></dd>

            <dt><?= e(Lang::teks('kisaran_tarif')) ?></dt>
            <dd><?= e((string) $d['kisaran_tarif'] !== '' ? $d['kisaran_tarif'] : '-') ?></dd>

            <dt><?= e(Lang::teks('kecamatan')) ?></dt>
            <dd><?= e($d['kecamatan_nama'] ?? '-') ?></dd>

            <?php if ($d['jarak_dari_maumere_km'] !== null): ?>
            <dt><?= e(Lang::inggris() ? 'Distance from Maumere' : 'Jarak dari Maumere') ?></dt>
            <dd>
              &plusmn;<?= e((float) $d['jarak_dari_maumere_km']) ?> <?= e(Lang::teks('km')) ?>
              <?php if ($d['waktu_tempuh_menit'] !== null): ?>
                (~<?= (int) $d['waktu_tempuh_menit'] ?> <?= e(Lang::teks('menit')) ?>)
              <?php endif; ?>
            </dd>
            <?php endif; ?>

            <?php if ((string) $d['kontak_nama'] !== '' || (string) $d['kontak_telepon'] !== ''): ?>
            <dt><?= e(Lang::teks('kontak')) ?></dt>
            <dd>
              <?= e($d['kontak_nama']) ?>
              <?php if ((string) $d['kontak_telepon'] !== ''): ?>
                <br>
                <a rel="noopener" target="_blank"
                   href="https://wa.me/<?= e(nomor_wa((string) $d['kontak_telepon'])) ?>">
                  <?= e($d['kontak_telepon']) ?>
                </a>
              <?php endif; ?>
            </dd>
            <?php endif; ?>

            <dt><?= e(Lang::inggris() ? 'Last updated' : 'Terakhir diperbarui') ?></dt>
            <dd class="small">
              <?= e(tanggal_lokal((string) $d['updated_at'])) ?>
              <?php if ($d['terakhir_diverifikasi'] !== null): ?>
                <br><span class="text-success">
                  <?= e(Lang::inggris() ? 'Verified' : 'Diverifikasi') ?>:
                  <?= e(tanggal_lokal((string) $d['terakhir_diverifikasi'])) ?>
                </span>
              <?php endif; ?>
            </dd>
          </dl>
        </div>
      </div>

      <?php if ($adaKoordinat): ?>
      <div class="card mb-3">
        <div class="card-body">
          <div id="peta-mini" class="peta-mini mb-2"><div class="peta-skeleton"></div></div>
          <div class="d-grid gap-2">
            <!-- FR-MAP-08: deep link navigasi eksternal, koordinat sudah terisi -->
            <a class="btn btn-teal" rel="noopener" target="_blank"
               href="https://www.google.com/maps/dir/?api=1&destination=<?= e($d['latitude']) ?>,<?= e($d['longitude']) ?>">
              <?= e(Lang::teks('rute_ke_sini')) ?> (Google Maps)
            </a>
            <a class="btn btn-outline-secondary btn-sm" rel="noopener" target="_blank"
               href="https://waze.com/ul?ll=<?= e($d['latitude']) ?>,<?= e($d['longitude']) ?>&navigate=yes">Waze</a>
            <a class="btn btn-outline-secondary btn-sm"
               href="<?= e(url('/peta', ['destinasi' => $d['slug']])) ?>">
              <?= e(Lang::inggris() ? 'Open on the full map' : 'Buka di peta lengkap') ?>
            </a>
          </div>
          <p class="form-text mb-0 mt-2">
            <?= e($d['latitude']) ?>, <?= e($d['longitude']) ?>
          </p>
        </div>
      </div>
      <?php else: ?>
      <div class="alert alert-warning small">
        <?= e(Lang::teks('data_belum_lengkap')) ?>
      </div>
      <?php endif; ?>

      <?php if ($event !== []): ?>
      <div class="card mb-3">
        <div class="card-body">
          <h2 class="h6 text-uppercase text-secondary mb-2"><?= e(Lang::teks('event_terdekat')) ?></h2>
          <ul class="list-unstyled small mb-0">
            <?php foreach ($event as $ev): ?>
            <li class="mb-2">
              <a class="text-decoration-none" href="<?= e(url('/event/' . $ev['slug'])) ?>">
                <strong><?= e(tanggal_lokal((string) $ev['tanggal_mulai'])) ?></strong><br>
                <?= e($ev['nama']) ?>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
      <?php endif; ?>

      <a class="btn btn-outline-secondary btn-sm w-100"
         href="<?= e(url('/layanan/pengaduan', ['destinasi' => (int) $d['id']])) ?>">
        <?= e(Lang::inggris() ? 'Report an issue at this site' : 'Laporkan masalah di destinasi ini') ?>
      </a>
    </div>
  </div>

  <?php if ($terdekat !== []): ?>
  <section class="mt-5">
    <h2 class="h5 mb-3"><?= e(Lang::teks('destinasi_terdekat')) ?></h2>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
      <?php
      // $dUtama menjaga data destinasi ini tetap utuh: partial kartu memakai
      // variabel $d, sehingga loop di bawah akan menimpanya.
      $dUtama = $d;
      foreach ($terdekat as $d): ?>
        <div class="col"><?php require dirname(__DIR__) . '/partials/kartu-destinasi.php'; ?></div>
      <?php endforeach;
      $d = $dUtama;
      ?>
    </div>
  </section>
  <?php endif; ?>
</div>

<?php
if ($adaKoordinat) {
    // Peta mini terpusat pada lokasi destinasi (FR-DEST-02).
    $isiSkrip = '
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
<script>
window.addEventListener("load", function () {
  var el = document.getElementById("peta-mini");
  if (!el || typeof L === "undefined") { return; }
  var lat = ' . json_skrip((float) $d['latitude']) . ';
  var lng = ' . json_skrip((float) $d['longitude']) . ';
  var warna = ' . json_skrip((string) $d['kategori_warna']) . ';
  var nama = ' . json_skrip(Lang::kolom($d, 'nama')) . ';

  el.innerHTML = "";
  var peta = L.map(el, { center: [lat, lng], zoom: 14, scrollWheelZoom: false });
  L.tileLayer(' . json_skrip($peta['tile_url']) . ', {
    maxZoom: ' . (int) $peta['zoom_maks'] . ',
    attribution: ' . json_skrip($peta['tile_atribusi']) . '
  }).addTo(peta);

  var svg = \'<svg xmlns="http://www.w3.org/2000/svg" width="28" height="40" viewBox="0 0 28 40">\' +
    \'<path d="M14 0C6.3 0 0 6.3 0 14c0 10 14 26 14 26s14-16 14-26C28 6.3 21.7 0 14 0z" fill="\' + warna +
    \'" stroke="#fff" stroke-width="2"/><circle cx="14" cy="14" r="5" fill="#fff"/></svg>\';

  L.marker([lat, lng], {
    icon: L.icon({
      iconUrl: "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(svg),
      iconSize: [28, 40], iconAnchor: [14, 40]
    }),
    title: nama, alt: nama
  }).addTo(peta);
});
</script>';
}
?>
