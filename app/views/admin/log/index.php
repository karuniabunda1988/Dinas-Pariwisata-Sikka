<p class="small text-secondary">
  Jejak audit seluruh perubahan data. Total <?= e(angka($total)) ?> catatan.
</p>

<div class="card">
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th scope="col">Waktu</th>
          <th scope="col">Pengguna</th>
          <th scope="col">Aksi</th>
          <th scope="col">Entitas</th>
          <th scope="col">Keterangan</th>
          <th scope="col">IP</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($daftar === []): ?>
        <tr><td colspan="6" class="text-secondary small p-3">Belum ada catatan.</td></tr>
      <?php else: ?>
        <?php foreach ($daftar as $l): ?>
        <tr>
          <th scope="row" class="fw-normal small text-nowrap">
            <?= e(date('d/m/y H:i', strtotime((string) $l['created_at']))) ?>
          </th>
          <td class="small"><?= e($l['nama_pengguna']) ?></td>
          <td class="small"><span class="badge text-bg-light border"><?= e($l['aksi']) ?></span></td>
          <td class="small"><?= e($l['entitas']) ?><?= $l['entitas_id'] !== null ? ' #' . (int) $l['entitas_id'] : '' ?></td>
          <td class="small"><?= e($l['keterangan']) ?></td>
          <td class="small text-secondary"><?= e($l['ip']) ?></td>
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
    <?php for ($i = max(1, $halaman - 4); $i <= min($totalHalaman, $halaman + 4); $i++): ?>
      <li class="page-item <?= $i === $halaman ? 'active' : '' ?>">
        <a class="page-link" href="<?= e(url('/admin/log', ['hal' => $i])) ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>
