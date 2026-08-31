<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="small text-secondary mb-0"><?= count($daftar) ?> akun</p>
  <a class="btn btn-teal" href="<?= e(url('/admin/pengguna/baru')) ?>">+ Tambah Pengguna</a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th scope="col">Nama</th>
          <th scope="col">Nama pengguna</th>
          <th scope="col">Peran</th>
          <th scope="col">Status</th>
          <th scope="col">Login terakhir</th>
          <th scope="col"></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($daftar as $u): ?>
      <tr>
        <th scope="row" class="fw-normal"><?= e($u['nama']) ?></th>
        <td class="small"><code><?= e($u['username']) ?></code></td>
        <td class="small"><?= e(Pengguna::LABEL_PERAN[$u['peran']] ?? $u['peran']) ?></td>
        <td>
          <span class="badge text-bg-<?= (int) $u['aktif'] === 1 ? 'success' : 'secondary' ?>">
            <?= (int) $u['aktif'] === 1 ? 'aktif' : 'nonaktif' ?>
          </span>
        </td>
        <td class="small text-secondary">
          <?= e($u['login_terakhir'] !== null ? date('d/m/y H:i', strtotime((string) $u['login_terakhir'])) : 'Belum pernah') ?>
        </td>
        <td class="text-end">
          <a class="btn btn-sm btn-outline-secondary"
             href="<?= e(url('/admin/pengguna/' . (int) $u['id'] . '/ubah')) ?>">Ubah</a>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
