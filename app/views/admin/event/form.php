<?php
$nilai = static function (string $kunci, $bawaan = '') use ($e) {
    $lama = Session::inputLama($kunci, null);
    return $lama !== null ? $lama : ($e[$kunci] ?? $bawaan);
};
?>

<form method="post" action="<?= e($aksi) ?>" enctype="multipart/form-data">
  <?= Csrf::field() ?>
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label" for="nama">Nama event <span class="text-danger">*</span></label>
            <input class="form-control" type="text" id="nama" name="nama" required maxlength="180"
                   data-sumber-slug value="<?= e($nilai('nama')) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label" for="input-slug">Slug URL</label>
            <input class="form-control" type="text" id="input-slug" name="slug" maxlength="200"
                   value="<?= e($nilai('slug')) ?>">
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label" for="tanggal_mulai">Tanggal mulai <span class="text-danger">*</span></label>
              <input class="form-control" type="date" id="tanggal_mulai" name="tanggal_mulai" required
                     value="<?= e($nilai('tanggal_mulai')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="tanggal_selesai">Tanggal selesai</label>
              <input class="form-control" type="date" id="tanggal_selesai" name="tanggal_selesai"
                     value="<?= e($nilai('tanggal_selesai')) ?>">
              <div class="form-text">Kosongkan untuk event satu hari.</div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="lokasi_teks">Lokasi (teks)</label>
            <input class="form-control" type="text" id="lokasi_teks" name="lokasi_teks" maxlength="200"
                   value="<?= e($nilai('lokasi_teks')) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label" for="destinasi_terkait_id">Destinasi terkait</label>
            <select class="form-select" id="destinasi_terkait_id" name="destinasi_terkait_id">
              <option value="">— Tidak terkait destinasi —</option>
              <?php foreach (Destinasi::daftar(['status' => 'semua', 'urut' => 'nama']) as $dd): ?>
              <option value="<?= (int) $dd['id'] ?>" <?= (int) $nilai('destinasi_terkait_id') === (int) $dd['id'] ? 'selected' : '' ?>>
                <?= e($dd['nama']) ?>
              </option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Bila diisi, event tertaut ke pin peta destinasi tersebut.</div>
          </div>

          <div class="mb-0">
            <label class="form-label" for="deskripsi">Deskripsi</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="6"><?= e($nilai('deskripsi')) ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card">
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label" for="status">Status</label>
            <select class="form-select" id="status" name="status">
              <option value="draft" <?= $nilai('status', 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
              <option value="aktif" <?= $nilai('status', 'draft') === 'aktif' ? 'selected' : '' ?>>Aktif (tampil publik)</option>
            </select>
            <div class="form-text">
              Event dengan tanggal mengikuti kalender adat/liturgi sebaiknya
              tetap draft sampai tanggalnya dikonfirmasi.
            </div>
          </div>

          <?php if ($e !== null && (string) $e['foto'] !== ''): ?>
            <img class="img-fluid rounded mb-2" src="<?= e(unggahan((string) $e['foto'], 'event')) ?>" alt="Foto saat ini">
          <?php endif; ?>
          <label class="form-label" for="foto">Foto</label>
          <input class="form-control form-control-sm" type="file" id="foto" name="foto"
                 accept="image/jpeg,image/png,image/webp" data-pratinjau="pratinjau-event">
          <div id="pratinjau-event"></div>
        </div>
        <div class="card-footer bg-white d-grid gap-2">
          <button class="btn btn-teal" type="submit">Simpan</button>
          <a class="btn btn-link btn-sm" href="<?= e(url('/admin/event')) ?>">Kembali ke daftar</a>
        </div>
      </div>
    </div>
  </div>
</form>

<?php if ($e !== null): ?>
<form method="post" action="<?= e(url('/admin/event/' . (int) $e['id'] . '/hapus')) ?>"
      data-konfirmasi="Hapus event ini?" class="mt-3">
  <?= Csrf::field() ?>
  <button class="btn btn-sm btn-outline-danger" type="submit">Hapus event</button>
</form>
<?php endif; ?>
