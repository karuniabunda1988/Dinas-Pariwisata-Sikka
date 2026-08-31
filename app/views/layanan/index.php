<?php
$oss     = Pengaturan::ambil('link_oss', '');
$dpmptsp = Pengaturan::ambil('link_dpmptsp', '');
$ppid    = Pengaturan::ambil('link_ppid', '');
?>
<header class="bg-brand-soft py-4">
  <div class="container">
    <h1 class="h3 mb-1"><?= e($judul) ?></h1>
    <p class="text-body-secondary mb-0"><?= e($meta['deskripsi']) ?></p>
  </div>
</header>

<div class="container py-4">
  <div class="row g-4">
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <h2 class="h5"><?= e(Lang::teks('pengaduan')) ?></h2>
          <p class="small text-body-secondary">
            <?= e(Lang::inggris()
                  ? 'Report damaged facilities, cleanliness problems, unsafe conditions or inaccurate information on this site. Reports are recorded and forwarded to the Tourism Office.'
                  : 'Laporkan fasilitas rusak, masalah kebersihan, kondisi tidak aman, atau informasi yang tidak akurat di situs ini. Laporan tercatat dan diteruskan ke Dinas Pariwisata.') ?>
          </p>
          <a class="btn btn-teal" href="<?= e(url('/layanan/pengaduan')) ?>">
            <?= e(Lang::inggris() ? 'Submit a report' : 'Kirim pengaduan') ?>
          </a>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <h2 class="h5"><?= e(Lang::inggris() ? 'Tourism Business Licensing' : 'Perizinan Usaha Pariwisata') ?></h2>
          <p class="small text-body-secondary">
            <?= e(Lang::inggris()
                  ? 'Business licensing is handled by the national OSS system and the regency investment office - not by this platform. Use the official links below.'
                  : 'Perizinan usaha ditangani sistem OSS nasional dan DPMPTSP kabupaten, bukan oleh platform ini. Gunakan tautan resmi di bawah.') ?>
          </p>
          <div class="d-grid gap-2">
            <?php if ($oss !== ''): ?>
              <a class="btn btn-outline-secondary btn-sm" rel="noopener" target="_blank" href="<?= e($oss) ?>">
                OSS - Perizinan Berusaha
              </a>
            <?php endif; ?>
            <?php if ($dpmptsp !== ''): ?>
              <a class="btn btn-outline-secondary btn-sm" rel="noopener" target="_blank" href="<?= e($dpmptsp) ?>">
                DPMPTSP Kabupaten Sikka
              </a>
            <?php endif; ?>
            <?php if ($ppid !== ''): ?>
              <a class="btn btn-outline-secondary btn-sm" rel="noopener" target="_blank" href="<?= e($ppid) ?>">
                PPID Kabupaten Sikka
              </a>
            <?php endif; ?>
            <?php if ($oss === '' && $dpmptsp === '' && $ppid === ''): ?>
              <p class="small text-secondary mb-0">
                Tautan layanan belum diatur oleh admin.
              </p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h2 class="h5"><?= e(Lang::inggris() ? 'Register your business or tourism village' : 'Daftarkan UMKM atau Desa Wisata Anda') ?></h2>
          <p class="small text-body-secondary mb-0">
            <?= e(Lang::inggris()
                  ? 'Listing in the business directory is free. Contact the Tourism Office to have your business, homestay, weaving group or dive operator verified and added - it will then appear alongside the nearest destination on the map.'
                  : 'Pendaftaran di direktori UMKM tidak dipungut biaya. Hubungi Dinas Pariwisata untuk memverifikasi dan menambahkan usaha, homestay, kelompok tenun, atau operator dive Anda - entri akan tampil bersama destinasi terdekat di peta.') ?>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
