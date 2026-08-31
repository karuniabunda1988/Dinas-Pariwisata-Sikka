<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="small text-secondary mb-0"><?= count($daftar) ?> artikel</p>
  <a class="btn btn-teal" href="<?= e(url('/admin/artikel/baru')) ?>">+ Tulis Artikel</a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th scope="col">Judul</th>
          <th scope="col">Kategori</th>
          <th scope="col">Status</th>
          <th scope="col">Terbit</th>
          <th scope="col"></th>
        </tr>
      </thead>
      <tbody>
      <?php if ($daftar === []): ?>
        <tr><td colspan="5" class="text-secondary small p-3">Belum ada artikel.</td></tr>
      <?php else: ?>
        <?php foreach ($daftar as $a): ?>
        <tr>
          <th scope="row" class="fw-normal"><?= e($a['judul']) ?></th>
          <td class="small"><?= e($a['kategori']) ?></td>
          <td>
            <span class="badge text-bg-<?= $a['status'] === 'publish' ? 'success' : 'secondary' ?>">
              <?= e($a['status']) ?>
            </span>
          </td>
          <td class="small text-secondary">
            <?= e($a['published_at'] !== null ? tanggal_lokal((string) $a['published_at']) : '-') ?>
          </td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-secondary"
               href="<?= e(url('/admin/artikel/' . (int) $a['id'] . '/ubah')) ?>">Ubah</a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
