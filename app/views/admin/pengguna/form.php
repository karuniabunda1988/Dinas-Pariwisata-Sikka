<?php
$nilai = static function (string $kunci, $bawaan = '') use ($u) {
    $lama = Session::inputLama($kunci, null);
    return $lama !== null ? $lama : ($u[$kunci] ?? $bawaan);
};
$baru = $u === null;
?>

<div class="row">
  <div class="col-lg-6">
    <form method="post" action="<?= e($aksi) ?>">
      <?= Csrf::field() ?>
      <div class="card">
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label" for="nama">Nama lengkap <span class="text-danger">*</span></label>
            <input class="form-control" type="text" id="nama" name="nama" required maxlength="120"
                   value="<?= e($nilai('nama')) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label" for="username">Nama pengguna</label>
            <?php if ($baru): ?>
              <input class="form-control" type="text" id="username" name="username" required
                     minlength="4" maxlength="60" value="<?= e($nilai('username')) ?>">
              <div class="form-text">Huruf, angka, titik, dan garis bawah. Tidak dapat diubah nanti.</div>
            <?php else: ?>
              <input class="form-control" type="text" id="username" value="<?= e($u['username']) ?>" disabled>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control" type="email" id="email" name="email" maxlength="160"
                   value="<?= e($nilai('email')) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label" for="peran">Peran <span class="text-danger">*</span></label>
            <select class="form-select" id="peran" name="peran" required>
              <?php foreach (Pengguna::LABEL_PERAN as $k => $v): ?>
                <option value="<?= e($k) ?>" <?= $nilai('peran', 'admin_konten') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">
              <strong>Super Admin</strong>: akses penuh termasuk pengguna &amp; pengaturan.<br>
              <strong>Admin Konten</strong>: kelola seluruh konten, tanpa akses sistem.<br>
              <strong>Mitra</strong>: hanya entri UMKM miliknya sendiri.
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="password">
              <?= $baru ? 'Kata sandi' : 'Kata sandi baru (kosongkan bila tidak diubah)' ?>
              <?php if ($baru): ?><span class="text-danger">*</span><?php endif; ?>
            </label>
            <input class="form-control" type="password" id="password" name="password"
                   autocomplete="new-password" <?= $baru ? 'required minlength="10"' : 'minlength="10"' ?>>
            <div class="form-text">Minimal 10 karakter.</div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="password_ulang">Ulangi kata sandi</label>
            <input class="form-control" type="password" id="password_ulang" name="password_ulang"
                   autocomplete="new-password">
          </div>

          <?php if (!$baru): ?>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="aktif" name="aktif" value="1"
                   <?= (int) $nilai('aktif', 1) === 1 ? 'checked' : '' ?>>
            <label class="form-check-label" for="aktif">Akun aktif (dapat masuk)</label>
          </div>
          <?php endif; ?>
        </div>
        <div class="card-footer bg-white">
          <button class="btn btn-teal" type="submit">Simpan</button>
          <a class="btn btn-link" href="<?= e(url('/admin/pengguna')) ?>">Kembali</a>
        </div>
      </div>
    </form>

    <?php if (!$baru && (int) $u['id'] !== Auth::id()): ?>
    <form method="post" action="<?= e(url('/admin/pengguna/' . (int) $u['id'] . '/hapus')) ?>"
          data-konfirmasi="Hapus akun ini?" class="mt-3">
      <?= Csrf::field() ?>
      <button class="btn btn-sm btn-outline-danger" type="submit">Hapus akun</button>
    </form>
    <?php endif; ?>
  </div>
</div>
