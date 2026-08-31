<header class="bg-brand-soft py-4">
  <div class="container">
    <h1 class="h3 mb-1"><?= e($judul) ?></h1>
    <p class="text-body-secondary mb-0"><?= e($meta['deskripsi']) ?></p>
  </div>
</header>

<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-8">
      <h2 class="h5"><?= e(Lang::inggris() ? 'About this platform' : 'Tentang Platform Ini') ?></h2>
      <p>
        <?= e(Lang::inggris()
              ? 'This platform is the official tourism information system of Sikka Regency, built around an interactive pin map so that every destination has a real location, practical details and a way to reach it.'
              : 'Platform ini adalah sistem informasi pariwisata resmi Kabupaten Sikka, dibangun dengan peta interaktif berbasis pin agar setiap destinasi punya lokasi nyata, informasi praktis, dan cara mencapainya.') ?>
      </p>
      <p>
        <?= e(Lang::inggris()
              ? 'Destination data is compiled from the Puspar UGM mapping study, verified entries on the national Sisparnas registry, and field knowledge of Tourism Office staff and district contacts. Entries marked as awaiting field verification have not yet been confirmed on the ground.'
              : 'Data destinasi disusun dari studi pemetaan Puspar UGM, entri terverifikasi Sisparnas Kemenparekraf, serta pengetahuan lapangan staf Dinas dan kontak kecamatan. Entri yang ditandai menunggu verifikasi lapangan belum dikonfirmasi langsung di lokasi.') ?>
      </p>

      <h2 class="h5 mt-4"><?= e(Lang::inggris() ? 'Data accuracy' : 'Akurasi Data') ?></h2>
      <p class="mb-0">
        <?= e(Lang::inggris()
              ? 'Opening hours, fees and contacts can change. Every destination shows when it was last updated and, where applicable, when it was last verified. If you find outdated information, please report it through the public services page - it will reach the staff responsible.'
              : 'Jam operasional, tarif, dan kontak dapat berubah. Setiap destinasi menampilkan kapan terakhir diperbarui dan, bila ada, kapan terakhir diverifikasi. Bila menemukan informasi yang sudah tidak sesuai, laporkan melalui halaman layanan publik - laporan akan sampai ke staf yang bertanggung jawab.') ?>
      </p>
    </div>

    <div class="col-lg-4">
      <div class="card">
        <div class="card-body">
          <h2 class="h6 text-uppercase text-secondary mb-3"><?= e(Lang::teks('kontak')) ?></h2>
          <p class="mb-1"><strong><?= e(Pengaturan::ambil('instansi', 'Dinas Pariwisata Kabupaten Sikka')) ?></strong></p>
          <p class="small mb-2"><?= e(Pengaturan::ambil('alamat_instansi', 'Maumere, Kabupaten Sikka, NTT')) ?></p>
          <?php if (($tel = Pengaturan::ambil('telepon_instansi', '')) !== ''): ?>
            <p class="small mb-1">Telepon: <?= e($tel) ?></p>
          <?php endif; ?>
          <?php if (($mail = Pengaturan::ambil('email_instansi', '')) !== ''): ?>
            <p class="small mb-1">Email: <a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a></p>
          <?php endif; ?>
          <?php if (($ig = Pengaturan::ambil('instagram', '')) !== ''): ?>
            <a class="btn btn-sm btn-outline-secondary mt-2" rel="noopener" target="_blank"
               href="https://instagram.com/<?= e(ltrim($ig, '@')) ?>">Instagram @<?= e(ltrim($ig, '@')) ?></a>
          <?php endif; ?>
        </div>
      </div>

      <div class="card mt-3">
        <div class="card-body small">
          <h2 class="h6 text-uppercase text-secondary mb-2"><?= e(Lang::inggris() ? 'System' : 'Sistem') ?></h2>
          <p class="mb-1">
            <?= e(Lang::inggris() ? 'Developed by' : 'Dikembangkan oleh') ?>:
            <strong><?= e(Pengaturan::ambil('hak_cipta', 'Karunia Bunda IT Training Center Maumere')) ?></strong>
          </p>
          <p class="mb-0 text-secondary">
            <?= e(Lang::inggris() ? 'Map data' : 'Data peta') ?>: OpenStreetMap &middot; Leaflet.js
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
