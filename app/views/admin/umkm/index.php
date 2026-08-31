<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div class="small text-secondary">
    <?= (int) $ringkasan['terverifikasi'] ?> terverifikasi &middot;
    <?= (int) $ringkasan['menunggu'] ?> menunggu &middot;
    total <?= (int) $ringkasan['total'] ?>
  </div>
  <a class="btn btn-teal" href="<?= e(url('/admin/umkm/baru')) ?>">+ Tambah UMKM</a>
</div>

<form class="row g-2 align-items-end mb-3" method="get">
  <div class="col-md-4">
    <label class="form-label small" for="u-q">Cari</label>
    <input class="form-control form-control-sm" type="search" id="u-q" name="q" value="<?= e(get_param('q')) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label small" for="u-status">Status verifikasi</label>
    <select class="form-select form-select-sm" id="u-status" name="status">
      <?php foreach (['semua' => 'Semua', 'menunggu' => 'Menunggu', 'terverifikasi' => 'Terverifikasi', 'ditolak' => 'Ditolak'] as $k => $v): ?>
        <option value="<?= e($k) ?>" <?= get_param('status', 'semua') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label small" for="u-jenis">Jenis</label>
    <select class="form-select form-select-sm" id="u-jenis" name="jenis">
      <option value="">Semua</option>
      <?php foreach (Umkm::JENIS as $k => $v): ?>
        <option value="<?= e($k) ?>" <?= get_param('jenis') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2 d-grid">
    <button class="btn btn-sm btn-outline-secondary" type="submit">Filter</button>
  </div>
</form>

<div class="card">
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th scope="col">Nama</th>
          <th scope="col">Jenis</th>
          <th scope="col">Destinasi terdekat</th>
          <th scope="col">Status</th>
          <th scope="col"></th>
        </tr>
      </thead>
      <tbody>
      <?php if ($daftar === []): ?>
        <tr><td colspan="5" class="text-secondary small p-3">Belum ada data UMKM.</td></tr>
      <?php else: ?>
        <?php foreach ($daftar as $u): ?>
        <tr>
          <th scope="row" class="fw-normal"><?= e($u['nama']) ?></th>
          <td class="small"><?= e(Umkm::labelJenis((string) $u['jenis'])) ?></td>
          <td class="small"><?= e($u['destinasi_nama'] ?? '-') ?></td>
          <td>
            <?php if (Auth::adalah('super_admin', 'admin_konten')): ?>
            <form method="post" action="<?= e(url('/admin/umkm/' . (int) $u['id'] . '/verifikasi')) ?>"
                  class="d-flex gap-1">
              <?= Csrf::field() ?>
              <select class="form-select form-select-sm" name="status_verifikasi"
                      aria-label="Status verifikasi <?= e($u['nama']) ?>" style="width:auto">
                <?php foreach (['menunggu' => 'Menunggu', 'terverifikasi' => 'Terverifikasi', 'ditolak' => 'Ditolak'] as $k => $v): ?>
                  <option value="<?= e($k) ?>" <?= $u['status_verifikasi'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-sm btn-outline-secondary" type="submit">Simpan</button>
            </form>
            <?php else: ?>
              <span class="badge text-bg-light border"><?= e($u['status_verifikasi']) ?></span>
            <?php endif; ?>
          </td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-secondary"
               href="<?= e(url('/admin/umkm/' . (int) $u['id'] . '/ubah')) ?>">Ubah</a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
