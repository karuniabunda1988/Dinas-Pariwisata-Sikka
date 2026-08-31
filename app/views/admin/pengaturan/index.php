<?php
/** Pengaturan situs - hanya kunci yang diizinkan controller yang tampil. */
$label = [
    'nama_situs'       => ['Nama situs', 'text', 'Tampil di header dan judul halaman.'],
    'tagline'          => ['Tagline (Indonesia)', 'text', 'Kalimat utama di beranda.'],
    'tagline_en'       => ['Tagline (Inggris)', 'text', ''],
    'instansi'         => ['Nama instansi', 'text', ''],
    'alamat_instansi'  => ['Alamat kantor', 'text', ''],
    'email_instansi'   => ['Email resmi', 'text', ''],
    'telepon_instansi' => ['Telepon kantor', 'text', ''],
    'wa_notifikasi'    => ['Nomor WhatsApp notifikasi', 'text',
        'Tujuan notifikasi pengaduan. Format bebas (08xx / 62xx), disimpan sebagai 62xx. Kosongkan untuk menonaktifkan - pengaduan tetap tersimpan di sistem.'],
    'wa_gateway_url'   => ['URL gateway WhatsApp (opsional)', 'text',
        'Bila diisi, notifikasi dicoba lewat gateway. Bila kosong atau gagal, sistem mundur ke tautan wa.me lalu ke log - alur pengaduan tidak pernah terhenti.'],
    'wa_gateway_token' => ['Token gateway WhatsApp', 'password', ''],
    'running_text'     => ['Teks berjalan beranda', 'text', 'Tampil bila belum ada event mendatang.'],
    'instagram'        => ['Username Instagram', 'text', ''],
    'peta_lat_awal'    => ['Titik tengah peta - latitude', 'text', ''],
    'peta_lng_awal'    => ['Titik tengah peta - longitude', 'text', ''],
    'peta_zoom_awal'   => ['Zoom awal peta', 'number', '10 mencakup seluruh kabupaten; 12 lebih dekat ke Maumere.'],
    'link_ppid'        => ['URL PPID Kabupaten Sikka', 'url', ''],
    'link_oss'         => ['URL OSS', 'url', ''],
    'link_dpmptsp'     => ['URL DPMPTSP', 'url', ''],
    'hak_cipta'        => ['Pemegang hak cipta sistem', 'text', 'Tampil di footer situs dan panel admin.'],
];
?>

<form method="post" action="<?= e(url('/admin/pengaturan')) ?>">
  <?= Csrf::field() ?>

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-body">
          <?php foreach ($label as $kunci => [$judulKolom, $tipe, $bantuan]): ?>
          <div class="mb-3">
            <label class="form-label" for="set-<?= e($kunci) ?>"><?= e($judulKolom) ?></label>
            <input class="form-control" type="<?= e($tipe === 'url' ? 'url' : $tipe) ?>"
                   id="set-<?= e($kunci) ?>" name="<?= e($kunci) ?>"
                   value="<?= e($data[$kunci] ?? '') ?>"
                   <?= $tipe === 'password' ? 'autocomplete="off"' : '' ?>>
            <?php if ($bantuan !== ''): ?>
              <div class="form-text"><?= e($bantuan) ?></div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card mb-3">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Fitur Fase 2</h2></div>
        <div class="card-body">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="ulasan_aktif" name="ulasan_aktif" value="1"
                   <?= ($data['ulasan_aktif'] ?? '0') === '1' ? 'checked' : '' ?>>
            <label class="form-check-label" for="ulasan_aktif">Aktifkan ulasan &amp; rating publik</label>
          </div>
          <div class="form-text">
            Ulasan tetap wajib melalui moderasi admin sebelum tayang.
            Jangan aktifkan sebelum ada staf yang rutin memoderasi -
            ulasan spam yang dibiarkan justru merusak kredibilitas.
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <button class="btn btn-teal w-100" type="submit">Simpan Pengaturan</button>
        </div>
      </div>
    </div>
  </div>
</form>
