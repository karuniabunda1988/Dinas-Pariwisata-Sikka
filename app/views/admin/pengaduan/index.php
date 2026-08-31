<form class="mb-3" method="get">
  <label class="form-label small" for="pg-status">Status tindak lanjut</label>
  <select class="form-select form-select-sm" id="pg-status" name="status" style="max-width:220px"
          onchange="this.form.submit()">
    <?php foreach (['semua' => 'Semua', 'baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $k => $v): ?>
      <option value="<?= e($k) ?>" <?= $statusAktif === $k ? 'selected' : '' ?>><?= e($v) ?></option>
    <?php endforeach; ?>
  </select>
  <noscript><button class="btn btn-sm btn-outline-secondary mt-1" type="submit">Tampilkan</button></noscript>
</form>

<div class="card">
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th scope="col">#</th>
          <th scope="col">Isi</th>
          <th scope="col">Destinasi</th>
          <th scope="col">Masuk</th>
          <th scope="col">Status</th>
          <th scope="col"></th>
        </tr>
      </thead>
      <tbody>
      <?php if ($daftar === []): ?>
        <tr><td colspan="6" class="text-secondary small p-3">Tidak ada pengaduan pada status ini.</td></tr>
      <?php else: ?>
        <?php foreach ($daftar as $p): ?>
        <tr>
          <th scope="row" class="fw-normal"><?= (int) $p['id'] ?></th>
          <td class="small"><?= e(ringkas((string) $p['isi'], 90)) ?></td>
          <td class="small"><?= e($p['destinasi_nama'] ?? '-') ?></td>
          <td class="small text-secondary"><?= e(date('d/m/y H:i', strtotime((string) $p['created_at']))) ?></td>
          <td>
            <span class="badge text-bg-<?= $p['status_tindak_lanjut'] === 'selesai' ? 'success'
                  : ($p['status_tindak_lanjut'] === 'diproses' ? 'warning' : 'danger') ?>">
              <?= e($p['status_tindak_lanjut']) ?>
            </span>
          </td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-secondary"
               href="<?= e(url('/admin/pengaduan/' . (int) $p['id'])) ?>">Buka</a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
