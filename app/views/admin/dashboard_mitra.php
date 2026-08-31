<?php /** Dasbor terbatas untuk akun mitra (FR-ADM-01). */ ?>

<div class="alert alert-light border">
  <h2 class="h6">Selamat datang, <?= e(Auth::pengguna()['nama'] ?? '') ?>.</h2>
  <p class="small mb-0">
    Akun mitra dapat menambah dan menyunting entri UMKM miliknya sendiri.
    Entri baru berstatus <strong>menunggu</strong> sampai diverifikasi staf
    Dinas, lalu tampil di direktori publik dan di halaman destinasi terdekat.
  </p>
</div>

<div class="card">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h2 class="h6 mb-0">Entri UMKM Anda</h2>
    <a class="btn btn-sm btn-teal" href="<?= e(url('/admin/umkm/baru')) ?>">+ Tambah</a>
  </div>
  <div class="card-body p-0">
    <?php if ($umkm === []): ?>
      <p class="small text-secondary p-3 mb-0">
        Anda belum memiliki entri. Klik "Tambah" untuk mendaftarkan usaha Anda.
      </p>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-sm mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th scope="col">Nama</th>
            <th scope="col">Jenis</th>
            <th scope="col">Status</th>
            <th scope="col"></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($umkm as $u): ?>
          <tr>
            <th scope="row" class="fw-normal"><?= e($u['nama']) ?></th>
            <td class="small"><?= e(Umkm::labelJenis((string) $u['jenis'])) ?></td>
            <td>
              <span class="badge text-bg-<?= $u['status_verifikasi'] === 'terverifikasi' ? 'success'
                    : ($u['status_verifikasi'] === 'ditolak' ? 'secondary' : 'warning') ?>">
                <?= e($u['status_verifikasi']) ?>
              </span>
            </td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-secondary"
                 href="<?= e(url('/admin/umkm/' . (int) $u['id'] . '/ubah')) ?>">Ubah</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
