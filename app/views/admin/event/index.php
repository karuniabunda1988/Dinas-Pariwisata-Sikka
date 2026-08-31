<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="small text-secondary mb-0"><?= count($daftar) ?> event tercatat</p>
  <a class="btn btn-teal" href="<?= e(url('/admin/event/baru')) ?>">+ Tambah Event</a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th scope="col">Nama</th>
          <th scope="col">Tanggal</th>
          <th scope="col">Lokasi</th>
          <th scope="col">Status</th>
          <th scope="col"></th>
        </tr>
      </thead>
      <tbody>
      <?php if ($daftar === []): ?>
        <tr><td colspan="5" class="text-secondary small p-3">Belum ada event.</td></tr>
      <?php else: ?>
        <?php foreach ($daftar as $ev): ?>
        <tr>
          <th scope="row" class="fw-normal"><?= e($ev['nama']) ?></th>
          <td class="small">
            <?= e(tanggal_lokal((string) $ev['tanggal_mulai'])) ?>
            <?php if ($ev['tanggal_selesai'] !== null): ?>
              &ndash; <?= e(tanggal_lokal((string) $ev['tanggal_selesai'])) ?>
            <?php endif; ?>
          </td>
          <td class="small"><?= e($ev['destinasi_nama'] ?? $ev['lokasi_teks']) ?></td>
          <td>
            <span class="badge text-bg-<?= $ev['status'] === 'aktif' ? 'success' : 'secondary' ?>">
              <?= e($ev['status']) ?>
            </span>
          </td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-secondary"
               href="<?= e(url('/admin/event/' . (int) $ev['id'] . '/ubah')) ?>">Ubah</a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
