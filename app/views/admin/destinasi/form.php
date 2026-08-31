<?php
/**
 * Form destinasi dengan PEMILIH TITIK DI PETA (§10.6, FR-ADM-02).
 * Staf Dinas tidak pernah diminta mengetik lintang/bujur.
 */
$peta = App::config('peta');
$isiKepala = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">';

$nilai = static function (string $kunci, $bawaan = '') use ($d) {
    $lama = Session::inputLama($kunci, null);
    if ($lama !== null) {
        return $lama;
    }
    return $d[$kunci] ?? $bawaan;
};
$adaKoordinat = ($d['latitude'] ?? null) !== null;
?>

<form method="post" action="<?= e($aksi) ?>" enctype="multipart/form-data">
  <?= Csrf::field() ?>

  <div class="row g-4">
    <div class="col-lg-8">

      <!-- ---------- Identitas ---------- -->
      <div class="card mb-3">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Identitas Destinasi</h2></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label" for="nama">Nama destinasi <span class="text-danger">*</span></label>
            <input class="form-control" type="text" id="nama" name="nama" required maxlength="160"
                   data-sumber-slug value="<?= e($nilai('nama')) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label" for="nama_en">Nama dalam Bahasa Inggris</label>
            <input class="form-control" type="text" id="nama_en" name="nama_en" maxlength="160"
                   value="<?= e($nilai('nama_en')) ?>">
            <div class="form-text">Kosongkan bila sama dengan nama Indonesia.</div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="input-slug">Slug URL</label>
            <input class="form-control" type="text" id="input-slug" name="slug" maxlength="180"
                   value="<?= e($nilai('slug')) ?>">
            <div class="form-text">
              Terisi otomatis dari nama. Alamat halaman:
              <code><?= e(url('/destinasi/')) ?>slug-anda</code>.
              Hindari mengubahnya setelah dipublikasikan agar tautan lama tidak rusak.
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="kategori_id">Kategori <span class="text-danger">*</span></label>
              <select class="form-select" id="kategori_id" name="kategori_id" required>
                <option value="">— Pilih kategori —</option>
                <?php foreach (Kategori::semua() as $k): ?>
                <option value="<?= (int) $k['id'] ?>" <?= (int) $nilai('kategori_id') === (int) $k['id'] ? 'selected' : '' ?>>
                  <?= e($k['ikon']) ?> <?= e($k['nama']) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Menentukan warna pin di peta.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label" for="kecamatan_id">Kecamatan</label>
              <select class="form-select" id="kecamatan_id" name="kecamatan_id">
                <option value="">— Belum diketahui —</option>
                <?php foreach (Kecamatan::semua() as $kc): ?>
                <option value="<?= (int) $kc['id'] ?>" <?= (int) $nilai('kecamatan_id') === (int) $kc['id'] ? 'selected' : '' ?>>
                  <?= e($kc['nama']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- ---------- PEMILIH TITIK PETA (§10.6) ---------- -->
      <div class="card mb-3">
        <div class="card-header bg-white">
          <h2 class="h6 mb-0">Lokasi di Peta</h2>
          <p class="small text-secondary mb-0">
            Klik titik lokasi pada peta untuk mengisi koordinat otomatis.
            Anda tidak perlu mengetik angka lintang/bujur.
          </p>
        </div>
        <div class="card-body">
          <div class="mb-2">
            <label class="form-label small" for="cari-titik-awal">Loncat ke area (opsional)</label>
            <select class="form-select form-select-sm" id="cari-titik-awal">
              <option value="">— Pilih kecamatan sebagai titik awal —</option>
              <?php foreach (Kecamatan::semua() as $kc): ?>
                <?php if ($kc['latitude'] !== null): ?>
                <option value="<?= e($kc['latitude']) ?>,<?= e($kc['longitude']) ?>"><?= e($kc['nama']) ?></option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
            <div class="form-text">
              Memindahkan tampilan peta saja - tidak mengubah koordinat destinasi.
            </div>
          </div>

          <div id="peta-pemilih" class="peta-pemilih mb-2"><div class="peta-skeleton"></div></div>

          <p id="teks-koordinat" class="form-text">
            <?= $adaKoordinat
                  ? 'Titik terpilih: ' . e($d['latitude']) . ', ' . e($d['longitude'])
                  : 'Belum ada titik dipilih. Klik pada peta untuk menentukan lokasi.' ?>
          </p>

          <input type="hidden" id="input-latitude"  name="latitude"  value="<?= e($nilai('latitude')) ?>">
          <input type="hidden" id="input-longitude" name="longitude" value="<?= e($nilai('longitude')) ?>">

          <button class="btn btn-sm btn-outline-secondary" type="button" id="hapus-koordinat">
            Hapus titik
          </button>

          <!-- Jalan keluar terakhir bila peta gagal dimuat -->
          <div id="koordinat-manual" hidden class="row g-2 mt-2">
            <div class="col-6">
              <label class="form-label small" for="manual-lat">Latitude</label>
              <input class="form-control form-control-sm" type="text" id="manual-lat"
                     oninput="document.getElementById('input-latitude').value = this.value">
            </div>
            <div class="col-6">
              <label class="form-label small" for="manual-lng">Longitude</label>
              <input class="form-control form-control-sm" type="text" id="manual-lng"
                     oninput="document.getElementById('input-longitude').value = this.value">
            </div>
          </div>
        </div>
      </div>

      <!-- ---------- Deskripsi ---------- -->
      <div class="card mb-3">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Deskripsi</h2></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label" for="deskripsi_singkat">
              Deskripsi singkat <span class="text-danger">*</span>
            </label>
            <textarea class="form-control" id="deskripsi_singkat" name="deskripsi_singkat" rows="2"
                      maxlength="400" data-hitung="hitung-singkat"><?= e($nilai('deskripsi_singkat')) ?></textarea>
            <div class="form-text" id="hitung-singkat"></div>
            <div class="form-text">Tampil di popup peta, kartu daftar, dan hasil pencarian Google.</div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="deskripsi_singkat_en">Deskripsi singkat (Inggris)</label>
            <textarea class="form-control" id="deskripsi_singkat_en" name="deskripsi_singkat_en"
                      rows="2" maxlength="400"><?= e($nilai('deskripsi_singkat_en')) ?></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label" for="deskripsi_lengkap">Deskripsi lengkap</label>
            <textarea class="form-control" id="deskripsi_lengkap" name="deskripsi_lengkap"
                      rows="7"><?= e($nilai('deskripsi_lengkap')) ?></textarea>
            <div class="form-text">Pisahkan paragraf dengan baris kosong.</div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="deskripsi_lengkap_en">Deskripsi lengkap (Inggris)</label>
            <textarea class="form-control" id="deskripsi_lengkap_en" name="deskripsi_lengkap_en"
                      rows="5"><?= e($nilai('deskripsi_lengkap_en')) ?></textarea>
          </div>

          <div class="mb-0">
            <label class="form-label" for="cara_mencapai">Cara mencapai</label>
            <textarea class="form-control" id="cara_mencapai" name="cara_mencapai"
                      rows="3"><?= e($nilai('cara_mencapai')) ?></textarea>
            <div class="form-text">
              Sebutkan kondisi jalan yang sebenarnya - wisatawan lebih kecewa
              karena kaget daripada karena tahu sejak awal.
            </div>
          </div>
        </div>
      </div>

      <!-- ---------- Informasi praktis ---------- -->
      <div class="card mb-3">
        <div class="card-header bg-white">
          <h2 class="h6 mb-0">Informasi Praktis</h2>
          <p class="small text-secondary mb-0">
            Bagian ini yang mencegah kunjungan gagal (§4 PRD).
          </p>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="jam_operasional">Jam operasional</label>
              <input class="form-control" type="text" id="jam_operasional" name="jam_operasional"
                     maxlength="120" placeholder="07.00 - 18.00 WITA"
                     value="<?= e($nilai('jam_operasional')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="kisaran_tarif">Kisaran tarif</label>
              <input class="form-control" type="text" id="kisaran_tarif" name="kisaran_tarif"
                     maxlength="120" placeholder="Rp5.000 - Rp10.000 per orang"
                     value="<?= e($nilai('kisaran_tarif')) ?>">
            </div>
            <div class="col-12">
              <label class="form-label" for="fasilitas">Fasilitas</label>
              <input class="form-control" type="text" id="fasilitas" name="fasilitas" maxlength="500"
                     placeholder="Parkir, Toilet, Warung, Gazebo"
                     value="<?= e($nilai('fasilitas')) ?>">
              <div class="form-text">Pisahkan dengan koma.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="kontak_nama">Nama kontak pengelola</label>
              <input class="form-control" type="text" id="kontak_nama" name="kontak_nama"
                     maxlength="120" value="<?= e($nilai('kontak_nama')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="kontak_telepon">Telepon / WhatsApp pengelola</label>
              <input class="form-control" type="text" id="kontak_telepon" name="kontak_telepon"
                     maxlength="40" value="<?= e($nilai('kontak_telepon')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="jarak_dari_maumere_km">Jarak dari Maumere (km)</label>
              <input class="form-control" type="number" step="0.1" min="0" max="500"
                     id="jarak_dari_maumere_km" name="jarak_dari_maumere_km"
                     value="<?= e($nilai('jarak_dari_maumere_km')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="waktu_tempuh_menit">Waktu tempuh dari Maumere (menit)</label>
              <input class="form-control" type="number" min="0" max="1440"
                     id="waktu_tempuh_menit" name="waktu_tempuh_menit"
                     value="<?= e($nilai('waktu_tempuh_menit')) ?>">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ---------- Kolom samping ---------- -->
    <div class="col-lg-4">
      <div class="card mb-3">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Publikasi</h2></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label" for="status">Status</label>
            <select class="form-select" id="status" name="status">
              <?php foreach (['draft' => 'Draft (belum tampil)',
                              'aktif' => 'Aktif (tampil di peta & situs)',
                              'nonaktif' => 'Nonaktif (disembunyikan)'] as $kode => $label): ?>
              <option value="<?= e($kode) ?>" <?= $nilai('status', 'draft') === $kode ? 'selected' : '' ?>>
                <?= e($label) ?>
              </option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">
              Destinasi berstatus <strong>aktif</strong> wajib punya titik peta
              dan deskripsi singkat.
            </div>
          </div>

          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="unggulan" name="unggulan" value="1"
                   <?= (int) $nilai('unggulan', 0) === 1 ? 'checked' : '' ?>>
            <label class="form-check-label" for="unggulan">Tampilkan sebagai destinasi unggulan</label>
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="perlu_verifikasi_lapangan"
                   name="perlu_verifikasi_lapangan" value="1"
                   <?= (int) $nilai('perlu_verifikasi_lapangan', 0) === 1 ? 'checked' : '' ?>>
            <label class="form-check-label" for="perlu_verifikasi_lapangan">
              Data belum diverifikasi lapangan
            </label>
            <div class="form-text">
              Menampilkan catatan jujur di halaman publik bahwa koordinat/detail
              masih menunggu konfirmasi lapangan.
            </div>
          </div>

          <div class="mb-0">
            <label class="form-label" for="sumber_data">Sumber data</label>
            <input class="form-control form-control-sm" type="text" id="sumber_data" name="sumber_data"
                   maxlength="200" placeholder="mis. Survei lapangan Maret 2026 / Sisparnas"
                   value="<?= e($nilai('sumber_data')) ?>">
          </div>
        </div>
        <div class="card-footer bg-white d-grid gap-2">
          <button class="btn btn-teal" type="submit">Simpan</button>
          <?php if ($d !== null): ?>
            <a class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener"
               href="<?= e(url('/destinasi/' . $d['slug'])) ?>">Pratinjau halaman ↗</a>
          <?php endif; ?>
          <a class="btn btn-link btn-sm" href="<?= e(url('/admin/destinasi')) ?>">Kembali ke daftar</a>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Foto Utama</h2></div>
        <div class="card-body">
          <?php if ($d !== null && (string) $d['foto_utama'] !== ''): ?>
            <img class="img-fluid rounded mb-2" src="<?= e(unggahan((string) $d['foto_utama'])) ?>"
                 alt="Foto utama saat ini">
          <?php endif; ?>
          <input class="form-control form-control-sm" type="file" name="foto_utama"
                 accept="image/jpeg,image/png,image/webp" data-pratinjau="pratinjau-utama">
          <div id="pratinjau-utama"></div>
          <div class="form-text">
            JPG/PNG/WebP, maksimal 3 MB. Orientasi lanskap. Gunakan foto hasil
            kunjungan lapangan atau kontribusi desa wisata dengan izin -
            jangan mengambil foto dari internet.
          </div>
        </div>
      </div>

      <?php if ($d !== null): ?>
      <div class="card mb-3">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Verifikasi Berkala</h2></div>
        <div class="card-body">
          <p class="small mb-2">
            Terakhir diverifikasi:
            <strong><?= e($d['terakhir_diverifikasi'] !== null
                  ? tanggal_lokal((string) $d['terakhir_diverifikasi']) : 'Belum pernah') ?></strong>
          </p>
          <p class="small text-secondary">
            Siklus verifikasi minimal 6 bulan sekali. Tandai setelah Anda
            mengonfirmasi jam, tarif, dan kontak masih benar.
          </p>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</form>

<?php if ($d !== null): ?>
<!-- Aksi terpisah agar tidak bersarang di dalam form utama -->
<div class="row g-4 mt-1">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header bg-white"><h2 class="h6 mb-0">Galeri Foto</h2></div>
      <div class="card-body">
        <?php if ($galeri !== []): ?>
        <div class="row row-cols-3 row-cols-md-4 g-2 mb-3">
          <?php foreach ($galeri as $g): ?>
          <div class="col">
            <img class="img-fluid rounded" src="<?= e(unggahan((string) $g['file'])) ?>"
                 alt="<?= e($g['alt_text']) ?>">
            <form method="post" action="<?= e(url('/admin/galeri/' . (int) $g['id'] . '/hapus')) ?>"
                  data-konfirmasi="Hapus foto ini dari galeri?">
              <?= Csrf::field() ?>
              <button class="btn btn-sm btn-link text-danger p-0 mt-1" type="submit">Hapus</button>
            </form>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
          <p class="small text-secondary">
            Belum ada foto galeri. Standar minimum PRD: 3 foto per destinasi.
          </p>
        <?php endif; ?>

        <form method="post" action="<?= e(url('/admin/destinasi/' . (int) $d['id'] . '/galeri')) ?>"
              enctype="multipart/form-data" class="row g-2 align-items-end">
          <?= Csrf::field() ?>
          <div class="col-md-6">
            <label class="form-label small" for="galeri">Tambah foto (bisa pilih banyak)</label>
            <input class="form-control form-control-sm" type="file" id="galeri" name="galeri[]"
                   multiple accept="image/jpeg,image/png,image/webp" data-pratinjau="pratinjau-galeri">
          </div>
          <div class="col-md-4">
            <label class="form-label small" for="alt_text">Alt text (deskripsi foto)</label>
            <input class="form-control form-control-sm" type="text" id="alt_text" name="alt_text"
                   maxlength="255" placeholder="mis. Pantai Koka dilihat dari tebing">
          </div>
          <div class="col-md-2 d-grid">
            <button class="btn btn-sm btn-outline-teal" type="submit">Unggah</button>
          </div>
          <div class="col-12"><div id="pratinjau-galeri"></div></div>
          <div class="col-12">
            <div class="form-text">
              Alt text wajib demi aksesibilitas (FR-A11Y-01) - jelaskan apa
              yang terlihat pada foto, bukan sekadar nama destinasi.
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="d-grid gap-2">
      <form method="post" action="<?= e(url('/admin/destinasi/' . (int) $d['id'] . '/verifikasi')) ?>">
        <?= Csrf::field() ?>
        <button class="btn btn-outline-success w-100" type="submit">
          Tandai sudah diverifikasi hari ini
        </button>
      </form>

      <?php if (Auth::adalah('super_admin')): ?>
      <form method="post" action="<?= e(url('/admin/destinasi/' . (int) $d['id'] . '/hapus')) ?>"
            data-konfirmasi="Hapus destinasi ini beserta seluruh fotonya? Tindakan ini tidak dapat dibatalkan.">
        <?= Csrf::field() ?>
        <button class="btn btn-outline-danger w-100" type="submit">Hapus destinasi</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php
$isiSkrip = '
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
<script>
window.SIKKA_ADMIN_PETA = ' . json_skrip([
    'lat'      => (float) Pengaturan::ambil('peta_lat_awal', (string) $peta['lat_awal']),
    'lng'      => (float) Pengaturan::ambil('peta_lng_awal', (string) $peta['lng_awal']),
    'zoom'     => (int) Pengaturan::ambil('peta_zoom_awal', (string) $peta['zoom_awal']),
    'zoomMaks' => (int) $peta['zoom_maks'],
    'tile'     => $peta['tile_url'],
    'atribusi' => $peta['tile_atribusi'],
]) . ';
</script>
<script src="' . e(aset('assets/js/admin-peta.js')) . '" defer></script>';
?>
