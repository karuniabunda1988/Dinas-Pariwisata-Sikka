<?php
$nilai = static function (string $kunci, $bawaan = '') use ($a) {
    $lama = Session::inputLama($kunci, null);
    return $lama !== null ? $lama : ($a[$kunci] ?? $bawaan);
};
?>

<form method="post" action="<?= e($aksi) ?>" enctype="multipart/form-data">
  <?= Csrf::field() ?>
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label" for="judul">Judul <span class="text-danger">*</span></label>
            <input class="form-control" type="text" id="judul" name="judul" required maxlength="200"
                   data-sumber-slug value="<?= e($nilai('judul')) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label" for="input-slug">Slug URL</label>
            <input class="form-control" type="text" id="input-slug" name="slug" maxlength="220"
                   value="<?= e($nilai('slug')) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label" for="ringkasan">Ringkasan</label>
            <textarea class="form-control" id="ringkasan" name="ringkasan" rows="2" maxlength="400"
                      data-hitung="hitung-ringkasan"><?= e($nilai('ringkasan')) ?></textarea>
            <div class="form-text" id="hitung-ringkasan"></div>
            <div class="form-text">Dipakai sebagai deskripsi di hasil pencarian Google.</div>
          </div>

          <div class="mb-0">
            <label class="form-label" for="isi">Isi artikel</label>
            <textarea class="form-control" id="isi" name="isi" rows="16"><?= e($nilai('isi')) ?></textarea>
            <div class="form-text">
              Teks biasa. Pisahkan paragraf dengan baris kosong - HTML tidak
              diproses demi keamanan.
            </div>
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
              <option value="draft"   <?= $nilai('status', 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
              <option value="publish" <?= $nilai('status', 'draft') === 'publish' ? 'selected' : '' ?>>Publish</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label" for="kategori">Kategori</label>
            <select class="form-select" id="kategori" name="kategori">
              <?php foreach (['panduan' => 'Panduan Perjalanan', 'berita' => 'Berita Dinas', 'budaya' => 'Budaya'] as $k => $v): ?>
                <option value="<?= e($k) ?>" <?= $nilai('kategori', 'panduan') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <?php if ($a !== null && (string) $a['gambar_sampul'] !== ''): ?>
            <img class="img-fluid rounded mb-2" src="<?= e(unggahan((string) $a['gambar_sampul'], 'artikel')) ?>"
                 alt="Gambar sampul saat ini">
          <?php endif; ?>
          <label class="form-label" for="gambar_sampul">Gambar sampul</label>
          <input class="form-control form-control-sm" type="file" id="gambar_sampul" name="gambar_sampul"
                 accept="image/jpeg,image/png,image/webp" data-pratinjau="pratinjau-artikel">
          <div id="pratinjau-artikel"></div>
        </div>
        <div class="card-footer bg-white d-grid gap-2">
          <button class="btn btn-teal" type="submit">Simpan</button>
          <a class="btn btn-link btn-sm" href="<?= e(url('/admin/artikel')) ?>">Kembali ke daftar</a>
        </div>
      </div>
    </div>
  </div>
</form>

<?php if ($a !== null): ?>
<form method="post" action="<?= e(url('/admin/artikel/' . (int) $a['id'] . '/hapus')) ?>"
      data-konfirmasi="Hapus artikel ini?" class="mt-3">
  <?= Csrf::field() ?>
  <button class="btn btn-sm btn-outline-danger" type="submit">Hapus artikel</button>
</form>
<?php endif; ?>
