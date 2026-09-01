<?php
/**
 * Dasbor admin (FR-ADM-04): begitu login, admin langsung tahu apa yang
 * perlu dikerjakan hari ini - bukan sekadar angka hiasan.
 */
$persenDestinasi = $target['destinasi'] > 0
    ? min(100, (int) round($ringkasan['aktif'] / $target['destinasi'] * 100)) : 0;
$persenUmkm = $target['umkm'] > 0
    ? min(100, (int) round($umkm['terverifikasi'] / $target['umkm'] * 100)) : 0;
?>

<?php if (!empty($peringatanKeamanan)): ?>
<div class="alert alert-danger" role="alert">
  <h2 class="h6 mb-2">Perlu perhatian keamanan</h2>
  <ul class="mb-0 small">
    <?php foreach ($peringatanKeamanan as $pk): ?>
      <li><?= e($pk) ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<!-- Yang perlu dikerjakan -->
<section class="mb-4">
  <h2 class="h6 text-uppercase text-secondary mb-2">Perlu ditindaklanjuti</h2>
  <div class="row g-3">
    <div class="col-6 col-lg-3">
      <a class="kartu-angka d-block text-decoration-none text-body" href="<?= e(url('/admin/pengaduan')) ?>">
        <div class="nilai <?= $pengaduanBaru > 0 ? 'text-danger' : '' ?>"><?= (int) $pengaduanBaru ?></div>
        <div class="label">Pengaduan belum ditindak</div>
      </a>
    </div>
    <div class="col-6 col-lg-3">
      <a class="kartu-angka d-block text-decoration-none text-body" href="<?= e(url('/admin/ulasan')) ?>">
        <div class="nilai <?= $ulasanMenunggu > 0 ? 'text-warning-emphasis' : '' ?>"><?= (int) $ulasanMenunggu ?></div>
        <div class="label">Ulasan menunggu moderasi<?= $ulasanAktif ? '' : ' (fitur nonaktif)' ?></div>
      </a>
    </div>
    <div class="col-6 col-lg-3">
      <a class="kartu-angka d-block text-decoration-none text-body" href="<?= e(url('/admin/umkm', ['status' => 'menunggu'])) ?>">
        <div class="nilai"><?= (int) $umkm['menunggu'] ?></div>
        <div class="label">UMKM menunggu verifikasi</div>
      </a>
    </div>
    <div class="col-6 col-lg-3">
      <a class="kartu-angka d-block text-decoration-none text-body" href="<?= e(url('/admin/destinasi', ['status' => 'draft'])) ?>">
        <div class="nilai"><?= (int) $ringkasan['draft'] ?></div>
        <div class="label">Destinasi masih draft</div>
      </a>
    </div>
  </div>
</section>

<!-- Progres terhadap target PRD §16 -->
<section class="mb-4">
  <h2 class="h6 text-uppercase text-secondary mb-2">Progres terhadap target tahun pertama</h2>
  <div class="row g-3">
    <div class="col-md-4">
      <div class="kartu-angka">
        <div class="d-flex justify-content-between align-items-baseline">
          <span class="nilai"><?= e(angka($ringkasan['aktif'])) ?></span>
          <span class="small text-secondary">target <?= (int) $target['destinasi'] ?></span>
        </div>
        <div class="label mb-2">Destinasi terpublikasi</div>
        <div class="progress" style="height:8px">
          <div class="progress-bar bg-success" style="width: <?= $persenDestinasi ?>%"
               role="img" aria-label="<?= $persenDestinasi ?> persen dari target"></div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="kartu-angka">
        <div class="d-flex justify-content-between align-items-baseline">
          <span class="nilai"><?= e(angka($umkm['terverifikasi'])) ?></span>
          <span class="small text-secondary">target <?= (int) $target['umkm'] ?></span>
        </div>
        <div class="label mb-2">UMKM terverifikasi</div>
        <div class="progress" style="height:8px">
          <div class="progress-bar bg-success" style="width: <?= $persenUmkm ?>%"
               role="img" aria-label="<?= $persenUmkm ?> persen dari target"></div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="kartu-angka">
        <div class="d-flex justify-content-between align-items-baseline">
          <span class="nilai"><?= (int) $pembaruanBulanIni ?></span>
          <span class="small text-secondary">target <?= (int) $target['pembaruan_bulanan'] ?>/bulan</span>
        </div>
        <div class="label mb-2">Pembaruan konten bulan ini</div>
        <?php if ($pembaruanBulanIni < $target['pembaruan_bulanan']): ?>
          <p class="small text-warning-emphasis mb-0">
            Belum mencapai target pembaruan bulanan.
          </p>
        <?php else: ?>
          <p class="small text-success mb-0">Target bulan ini tercapai.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<div class="row g-3">
  <!-- FR-ADM-03: pengingat konten basi -->
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header bg-white">
        <h2 class="h6 mb-0">Data perlu diverifikasi ulang</h2>
        <p class="small text-secondary mb-0">
          Destinasi yang tidak diperbarui lebih dari 6 bulan. Jam operasional
          dan tarif berubah - konfirmasi ulang lalu tandai terverifikasi.
        </p>
      </div>
      <div class="card-body p-0">
        <?php if ($kontenBasi === []): ?>
          <p class="small text-secondary p-3 mb-0">
            Semua data destinasi masih dalam siklus verifikasi 6 bulan.
          </p>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th scope="col">Destinasi</th>
                <th scope="col">Terakhir diverifikasi</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($kontenBasi as $kb): ?>
              <tr>
                <th scope="row" class="fw-normal">
                  <?= e($kb['nama']) ?>
                  <span class="badge text-bg-light border"><?= e($kb['status']) ?></span>
                </th>
                <td class="small text-secondary">
                  <?= e($kb['terakhir_diverifikasi'] !== null
                        ? tanggal_lokal((string) $kb['terakhir_diverifikasi'])
                        : 'Belum pernah') ?>
                </td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-secondary"
                     href="<?= e(url('/admin/destinasi/' . (int) $kb['id'] . '/ubah')) ?>">Periksa</a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card mb-3">
      <div class="card-header bg-white">
        <h2 class="h6 mb-0">Cakupan wilayah</h2>
      </div>
      <div class="card-body">
        <p class="mb-2">
          <strong><?= (int) $kecamatanTercakup ?></strong> dari
          <strong><?= (int) $kecamatanTotal ?></strong> kecamatan sudah punya
          minimal satu destinasi terpublikasi.
        </p>
        <div class="progress mb-2" style="height:8px">
          <div class="progress-bar" style="width: <?= $kecamatanTotal > 0 ? (int) round($kecamatanTercakup / $kecamatanTotal * 100) : 0 ?>%"></div>
        </div>
        <p class="small text-secondary mb-0">
          <?= (int) $ringkasan['ter_pin'] ?> destinasi aktif sudah punya titik peta;
          <?= (int) $ringkasan['perlu_verifikasi'] ?> entri menunggu verifikasi lapangan.
        </p>
      </div>
    </div>

    <div class="card">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h2 class="h6 mb-0">Aktivitas terbaru</h2>
        <?php if (Auth::adalah('super_admin')): ?>
          <a class="small" href="<?= e(url('/admin/log')) ?>">Semua log</a>
        <?php endif; ?>
      </div>
      <div class="card-body p-0">
        <ul class="list-group list-group-flush small">
          <?php if ($logTerbaru === []): ?>
            <li class="list-group-item text-secondary">Belum ada aktivitas tercatat.</li>
          <?php else: ?>
            <?php foreach ($logTerbaru as $l): ?>
            <li class="list-group-item">
              <strong><?= e($l['nama_pengguna']) ?></strong>
              <span class="text-secondary"><?= e($l['keterangan'] !== '' ? $l['keterangan'] : $l['aksi']) ?></span>
              <span class="d-block text-secondary" style="font-size:.75rem">
                <?= e(date('d/m/Y H:i', strtotime((string) $l['created_at']))) ?>
              </span>
            </li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="d-flex flex-wrap gap-2 mt-4">
  <a class="btn btn-teal" href="<?= e(url('/admin/destinasi/baru')) ?>">+ Tambah Destinasi</a>
  <a class="btn btn-outline-secondary" href="<?= e(url('/admin/umkm/baru')) ?>">+ Tambah UMKM</a>
  <a class="btn btn-outline-secondary" href="<?= e(url('/admin/event/baru')) ?>">+ Tambah Event</a>
  <a class="btn btn-outline-secondary" href="<?= e(url('/admin/artikel/baru')) ?>">+ Tulis Artikel</a>
</div>
