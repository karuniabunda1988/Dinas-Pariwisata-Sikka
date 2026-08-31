<?php
$nilai = static function (string $kunci, $bawaan = '') use ($u) {
    $lama = Session::inputLama($kunci, null);
    return $lama !== null ? $lama : ($u[$kunci] ?? $bawaan);
};
?>

<form method="post" action="<?= e($aksi) ?>" enctype="multipart/form-data">
  <?= Csrf::field() ?>
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card mb-3">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Data UMKM / Mitra</h2></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label" for="nama">Nama usaha <span class="text-danger">*</span></label>
            <input class="form-control" type="text" id="nama" name="nama" required maxlength="160"
                   data-sumber-slug value="<?= e($nilai('nama')) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label" for="input-slug">Slug URL</label>
            <input class="form-control" type="text" id="input-slug" name="slug" maxlength="180"
                   value="<?= e($nilai('slug')) ?>">
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label" for="jenis">Jenis usaha</label>
              <select class="form-select" id="jenis" name="jenis">
                <?php foreach (Umkm::JENIS as $k => $v): ?>
                  <option value="<?= e($k) ?>" <?= $nilai('jenis', 'kuliner') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="destinasi_terdekat_id">Destinasi terdekat</label>
              <select class="form-select" id="destinasi_terdekat_id" name="destinasi_terdekat_id">
                <option value="">— Tidak terhubung —</option>
                <?php foreach (Destinasi::daftar(['status' => 'semua', 'urut' => 'nama']) as $dd): ?>
                <option value="<?= (int) $dd['id'] ?>" <?= (int) $nilai('destinasi_terdekat_id') === (int) $dd['id'] ? 'selected' : '' ?>>
                  <?= e($dd['nama']) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">
                Menentukan di halaman destinasi mana entri ini ikut tampil
                (FR-UMKM-02) - inilah yang mengangkat visibilitas UMKM.
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="deskripsi">Deskripsi</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"><?= e($nilai('deskripsi')) ?></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label" for="alamat">Alamat</label>
            <input class="form-control" type="text" id="alamat" name="alamat" maxlength="300"
                   value="<?= e($nilai('alamat')) ?>">
          </div>

          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label" for="kontak_telepon">Telepon</label>
              <input class="form-control" type="text" id="kontak_telepon" name="kontak_telepon"
                     maxlength="40" value="<?= e($nilai('kontak_telepon')) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" for="kontak_wa">WhatsApp</label>
              <input class="form-control" type="text" id="kontak_wa" name="kontak_wa"
                     maxlength="40" placeholder="08xx atau 62xx" value="<?= e($nilai('kontak_wa')) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label" for="kecamatan_id">Kecamatan</label>
              <select class="form-select" id="kecamatan_id" name="kecamatan_id">
                <option value="">—</option>
                <?php foreach (Kecamatan::semua() as $kc): ?>
                  <option value="<?= (int) $kc['id'] ?>" <?= (int) $nilai('kecamatan_id') === (int) $kc['id'] ? 'selected' : '' ?>>
                    <?= e($kc['nama']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="latitude">Latitude (opsional)</label>
              <input class="form-control" type="text" id="latitude" name="latitude"
                     value="<?= e($nilai('latitude')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="longitude">Longitude (opsional)</label>
              <input class="form-control" type="text" id="longitude" name="longitude"
                     value="<?= e($nilai('longitude')) ?>">
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card mb-3">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Verifikasi &amp; Foto</h2></div>
        <div class="card-body">
          <?php if (Auth::adalah('super_admin', 'admin_konten')): ?>
          <div class="mb-3">
            <label class="form-label" for="status_verifikasi">Status verifikasi</label>
            <select class="form-select" id="status_verifikasi" name="status_verifikasi">
              <?php foreach (['menunggu' => 'Menunggu', 'terverifikasi' => 'Terverifikasi', 'ditolak' => 'Ditolak'] as $k => $v): ?>
                <option value="<?= e($k) ?>" <?= $nilai('status_verifikasi', 'menunggu') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Hanya entri terverifikasi yang tampil di direktori publik.</div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="pemilik_user_id">Akun mitra pemilik</label>
            <select class="form-select" id="pemilik_user_id" name="pemilik_user_id">
              <option value="">— Tidak ada —</option>
              <?php foreach (Pengguna::semua() as $pg): ?>
                <?php if ($pg['peran'] !== 'mitra') { continue; } ?>
                <option value="<?= (int) $pg['id'] ?>" <?= (int) $nilai('pemilik_user_id') === (int) $pg['id'] ? 'selected' : '' ?>>
                  <?= e($pg['nama']) ?> (<?= e($pg['username']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Mitra yang ditunjuk dapat menyunting entri ini sendiri.</div>
          </div>
          <?php else: ?>
            <p class="small text-secondary">
              Status verifikasi ditentukan staf Dinas. Entri Anda saat ini:
              <strong><?= e($u['status_verifikasi'] ?? 'menunggu') ?></strong>.
            </p>
          <?php endif; ?>

          <?php if ($u !== null && (string) $u['foto'] !== ''): ?>
            <img class="img-fluid rounded mb-2" src="<?= e(unggahan((string) $u['foto'], 'umkm')) ?>" alt="Foto saat ini">
          <?php endif; ?>
          <label class="form-label" for="foto">Foto</label>
          <input class="form-control form-control-sm" type="file" id="foto" name="foto"
                 accept="image/jpeg,image/png,image/webp" data-pratinjau="pratinjau-umkm">
          <div id="pratinjau-umkm"></div>
        </div>
        <div class="card-footer bg-white d-grid gap-2">
          <button class="btn btn-teal" type="submit">Simpan</button>
          <a class="btn btn-link btn-sm" href="<?= e(url('/admin/umkm')) ?>">Kembali ke daftar</a>
        </div>
      </div>
    </div>
  </div>
</form>

<?php if ($u !== null && Auth::adalah('super_admin', 'admin_konten')): ?>
<form method="post" action="<?= e(url('/admin/umkm/' . (int) $u['id'] . '/hapus')) ?>"
      data-konfirmasi="Hapus entri UMKM ini?" class="mt-3">
  <?= Csrf::field() ?>
  <button class="btn btn-sm btn-outline-danger" type="submit">Hapus entri</button>
</form>
<?php endif; ?>
