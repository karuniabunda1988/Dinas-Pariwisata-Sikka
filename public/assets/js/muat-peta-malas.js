/**
 * Pemuat malas untuk peta ringkas beranda (FR-HOME-01).
 *
 * Leaflet berukuran ~145 KB dan harus diurai peramban sebelum peta bisa
 * dipakai. Di ponsel, peta ringkas beranda berada di bawah lipatan layar,
 * sehingga memuatnya di awal hanya memperlambat tampilnya konten utama pada
 * koneksi 3G tanpa memberi manfaat apa pun.
 *
 * Berkas ini menunda pemuatan Leaflet sampai peta hampir masuk layar. Peta
 * tetap interaktif sebagaimana diminta PRD - bukan gambar statis - hanya
 * saja biayanya dibayar ketika pengguna benar-benar akan melihatnya.
 */
(function () {
  'use strict';

  var K = window.SIKKA_PETA;
  var wadah = document.getElementById('peta-ringkas');
  if (!K || !wadah || !K.urlLeaflet || !K.urlSkrip) { return; }

  var sudahDimuat = false;

  function muat() {
    if (sudahDimuat) { return; }
    sudahDimuat = true;

    sisipkan(K.urlLeaflet, function () { sisipkan(K.urlSkrip, null); });
  }

  function sisipkan(src, selesai) {
    var s = document.createElement('script');
    s.src = src;
    s.async = false;               // jaga urutan: Leaflet dulu, baru skrip peta
    s.onload = function () { if (selesai) { selesai(); } };
    s.onerror = function () {
      // Gagal memuat peta bukan alasan halaman jadi rusak - tampilkan
      // ajakan menuju halaman peta lengkap saja.
      wadah.innerHTML = '<div class="p-3 small text-center">'
        + 'Peta tidak dapat dimuat. <a href="' + K.urlPeta + '">Buka halaman peta</a>.</div>';
    };
    document.head.appendChild(s);
  }

  if ('IntersectionObserver' in window) {
    var pengamat = new IntersectionObserver(function (entri) {
      for (var i = 0; i < entri.length; i++) {
        if (entri[i].isIntersecting) {
          pengamat.disconnect();
          muat();
          return;
        }
      }
    }, { rootMargin: '300px' });   // mulai memuat sebelum benar-benar terlihat
    pengamat.observe(wadah);
  } else {
    // Peramban lama: muat setelah halaman selesai, jangan bersaing dengan
    // konten utama.
    window.addEventListener('load', muat);
  }
})();
