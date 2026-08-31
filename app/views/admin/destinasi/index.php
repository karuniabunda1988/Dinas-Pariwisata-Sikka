<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div class="small text-secondary">
    Total <?= e(angka($ringkasan['total'])) ?> destinasi &middot;
    <?= (int) $ringkasan['aktif'] ?> aktif &middot;
    <?= (int) $ringkasan['draft'] ?> draft &middot;
    <?= (int) $ringkasan['ter_pin'] ?> punya titik peta
  </div>
  <a class="btn btn-teal" href="<?= e(url('/admin/destinasi/baru')) ?>">+ Tambah Destinasi</a>
</div>

<form class="row g-2 align-items-end mb-3" method="get">
  <div class="col-md-4">
    <label class="form-label small" for="a-q">Cari</label>
    <input class="form-control form-control-sm" type="search" id="a-q" name="q" value="<?= e(get_param('q')) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label small" for="a-status">Status</label>
    <select class="form-select form-select-sm" id="a-status" name="status">
      <?php foreach (['semua' => 'Semua', 'aktif' => 'Aktif', 'draft' => 'Draft', 'nonaktif' => 'Nonaktif'] as $k => $v): ?>
        <option value="<?= e($k) ?>" <?= get_param('status', 'semua') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label small" for="a-kategori">Kategori</label>
    <select class="form-select form-select-sm" id="a-kategori" name="kategori">
      <option value="">Semua</option>
      <?php foreach (Kategori::semua() as $k): ?>
        <option value="<?= e($k['slug']) ?>" <?= get_param('kategori') === $k['slug'] ? 'selected' : '' ?>><?= e($k['nama']) ?></option>
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
          <th scope="col">Kategori</th>
          <th scope="col">Kecamatan</th>
          <th scope="col">Titik peta</th>
          <th scope="col">Status</th>
          <th scope="col">Diperbarui</th>
          <th scope="col"></th>
        </tr>
      </thead>
      <tbody>
      <?php if ($daftar === []): ?>
        <tr><td colspan="7" class="text-secondary small p-3">Tidak ada destinasi yang cocok dengan filter.</td></tr>
      <?php else: ?>
        <?php foreach ($daftar as $d): ?>
        <tr>
          <th scope="row" class="fw-normal">
            <?= e($d['nama']) ?>
            <?php if ((int) $d['unggulan'] === 1): ?>
              <span class="badge text-bg-warning">Unggulan</span>
            <?php endif; ?>
            <?php if ((int) $d['perlu_verifikasi_lapangan'] === 1): ?>
              <span class="badge text-bg-light border">perlu verifikasi</span>
            <?php endif; ?>
          </th>
          <td>
            <span class="chip-kategori" style="--warna: <?= e($d['kategori_warna']) ?>"><?= e($d['kategori_nama']) ?></span>
          </td>
          <td class="small"><?= e($d['kecamatan_nama'] ?? '-') ?></td>
          <td class="small">
            <?php if ($d['latitude'] !== null): ?>
              <span class="text-success">✓ ada</span>
            <?php else: ?>
              <span class="text-danger">✗ belum</span>
            <?php endif; ?>
          </td>
          <td>
            <span class="badge text-bg-<?= $d['status'] === 'aktif' ? 'success' : ($d['status'] === 'draft' ? 'secondary' : 'dark') ?>">
              <?= e($d['status']) ?>
            </span>
          </td>
          <td class="small text-secondary"><?= e(date('d/m/y', strtotime((string) $d['updated_at']))) ?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-secondary"
               href="<?= e(url('/admin/destinasi/' . (int) $d['id'] . '/ubah')) ?>">Ubah</a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($totalHalaman > 1): ?>
<nav class="mt-3" aria-label="Navigasi halaman">
  <ul class="pagination pagination-sm">
    <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
      <?php $qs = $_GET; $qs['hal'] = $i; ?>
      <li class="page-item <?= $i === $halaman ? 'active' : '' ?>">
        <a class="page-link" href="<?= e(url('/admin/destinasi', $qs)) ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>
