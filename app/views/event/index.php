<?php
/** Kalender event & budaya (FR-EVT-01). */
$namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$namaBulanEn = ['', 'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'];
$labelBulan = Lang::inggris() ? $namaBulanEn[$bulan] : $namaBulan[$bulan];

$awalBulan   = mktime(0, 0, 0, $bulan, 1, $tahun);
$jumlahHari  = (int) date('t', $awalBulan);
// date('N'): 1=Senin ... 7=Minggu. Kalender dimulai hari Senin.
$offsetAwal  = (int) date('N', $awalBulan) - 1;

$bulanSebelum = $bulan === 1 ? ['bulan' => 12, 'tahun' => $tahun - 1] : ['bulan' => $bulan - 1, 'tahun' => $tahun];
$bulanSesudah = $bulan === 12 ? ['bulan' => 1, 'tahun' => $tahun + 1] : ['bulan' => $bulan + 1, 'tahun' => $tahun];
?>

<header class="bg-brand-soft py-4">
  <div class="container">
    <h1 class="h3 mb-1"><?= e($judul) ?></h1>
    <p class="text-body-secondary mb-0"><?= e($meta['deskripsi']) ?></p>
  </div>
</header>

<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('/event', $bulanSebelum)) ?>">&larr;</a>
        <h2 class="h5 mb-0"><?= e($labelBulan) ?> <?= (int) $tahun ?></h2>
        <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('/event', $bulanSesudah)) ?>">&rarr;</a>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered text-center align-top mb-0">
          <caption class="visually-hidden">Kalender event <?= e($labelBulan) ?> <?= (int) $tahun ?></caption>
          <thead class="table-light">
            <tr>
              <?php foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $h): ?>
                <th scope="col" class="small"><?= e($h) ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
          <?php
          $sel = 0;
          echo '<tr>';
          for ($i = 0; $i < $offsetAwal; $i++) {
              echo '<td class="bg-body-tertiary"></td>';
              $sel++;
          }
          for ($hari = 1; $hari <= $jumlahHari; $hari++) {
              if ($sel % 7 === 0 && $sel > 0) {
                  echo '</tr><tr>';
              }
              $tgl = sprintf('%04d-%02d-%02d', $tahun, $bulan, $hari);
              $evenHari = $kalender[$tgl] ?? [];
              $iniHariIni = $tgl === date('Y-m-d');

              echo '<td style="min-width:90px" class="' . ($iniHariIni ? 'bg-warning-subtle' : '') . '">';
              echo '<div class="small fw-semibold">' . $hari . '</div>';
              foreach ($evenHari as $ev) {
                  echo '<a class="d-block small text-decoration-none mt-1" href="'
                     . e(url('/event/' . $ev['slug'])) . '">'
                     . e(ringkas((string) $ev['nama'], 28)) . '</a>';
              }
              echo '</td>';
              $sel++;
          }
          while ($sel % 7 !== 0) {
              echo '<td class="bg-body-tertiary"></td>';
              $sel++;
          }
          echo '</tr>';
          ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="col-lg-4">
      <h2 class="h6 text-uppercase text-secondary mb-3"><?= e(Lang::teks('event_terdekat')) ?></h2>
      <?php if ($mendatang === []): ?>
        <p class="small text-secondary"><?= e(Lang::teks('belum_ada_data')) ?></p>
      <?php else: ?>
        <?php foreach ($mendatang as $ev): ?>
        <article class="card mb-2">
          <div class="card-body py-2">
            <p class="small text-secondary mb-1">
              <?= e(tanggal_lokal((string) $ev['tanggal_mulai'])) ?>
              <?php if ($ev['tanggal_selesai'] !== null && $ev['tanggal_selesai'] !== $ev['tanggal_mulai']): ?>
                &ndash; <?= e(tanggal_lokal((string) $ev['tanggal_selesai'])) ?>
              <?php endif; ?>
            </p>
            <h3 class="h6 mb-1">
              <a class="text-decoration-none text-body" href="<?= e(url('/event/' . $ev['slug'])) ?>"><?= e($ev['nama']) ?></a>
            </h3>
            <p class="small text-secondary mb-0">
              <?php if ($ev['destinasi_slug'] !== null): ?>
                <a href="<?= e(url('/destinasi/' . $ev['destinasi_slug'])) ?>"><?= e($ev['destinasi_nama']) ?></a>
              <?php else: ?>
                <?= e($ev['lokasi_teks']) ?>
              <?php endif; ?>
            </p>
          </div>
        </article>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if ($lampau !== []): ?>
        <h2 class="h6 text-uppercase text-secondary mt-4 mb-2"><?= e(Lang::inggris() ? 'Past Events' : 'Event Lampau') ?></h2>
        <ul class="list-unstyled small">
          <?php foreach ($lampau as $ev): ?>
          <li class="mb-1">
            <a class="text-decoration-none" href="<?= e(url('/event/' . $ev['slug'])) ?>">
              <?= e(tanggal_lokal((string) $ev['tanggal_mulai'])) ?> &middot; <?= e($ev['nama']) ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</div>
