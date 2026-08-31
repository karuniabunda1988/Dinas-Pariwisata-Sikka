/**
 * Pemilih titik lokasi di panel admin (§10.6, FR-MAP-12, FR-ADM-02).
 *
 * KEPUTUSAN PRODUK PALING PENTING untuk keberlanjutan data: staf Dinas
 * TIDAK diminta mengetik lintang/bujur. Admin cukup mengklik titik di peta -
 * koordinat terisi otomatis. Mengetik koordinat manual adalah sumber
 * kesalahan input terbesar di proyek sejenis.
 */
(function () {
  'use strict';

  var K = window.SIKKA_ADMIN_PETA;
  var wadah = document.getElementById('peta-pemilih');
  if (!K || !wadah) { return; }

  if (typeof L === 'undefined') {
    window.addEventListener('load', mulai);
  } else {
    mulai();
  }

  function mulai() {
    var inLat = document.getElementById('input-latitude');
    var inLng = document.getElementById('input-longitude');
    var teksKoordinat = document.getElementById('teks-koordinat');
    var tombolHapus = document.getElementById('hapus-koordinat');

    if (typeof L === 'undefined') {
      wadah.innerHTML = '<div class="alert alert-warning small mb-0">' +
        'Peta pemilih titik gagal dimuat (periksa koneksi internet). ' +
        'Koordinat masih bisa diisi manual pada kolom di bawah, tetapi ' +
        'sangat disarankan menggunakan peta agar tidak salah input.</div>';
      // Tampilkan kolom manual sebagai jalan keluar terakhir.
      var manual = document.getElementById('koordinat-manual');
      if (manual) { manual.hidden = false; }
      return;
    }

    var adaTitik = inLat.value !== '' && inLng.value !== '';
    var lat = adaTitik ? parseFloat(inLat.value) : K.lat;
    var lng = adaTitik ? parseFloat(inLng.value) : K.lng;

    var peta = L.map(wadah, {
      center: [lat, lng],
      zoom: adaTitik ? 14 : K.zoom
    });
    L.tileLayer(K.tile, { maxZoom: K.zoomMaks || 18, attribution: K.atribusi }).addTo(peta);

    var penanda = null;
    if (adaTitik) { pasangPenanda(lat, lng); }

    // Klik di mana pun pada peta = pilih titik.
    peta.on('click', function (ev) {
      pasangPenanda(ev.latlng.lat, ev.latlng.lng);
    });

    if (tombolHapus) {
      tombolHapus.addEventListener('click', function () {
        if (penanda) { peta.removeLayer(penanda); penanda = null; }
        inLat.value = '';
        inLng.value = '';
        perbaruiTeks(null, null);
      });
    }

    // Pencarian nama tempat sebagai titik awal - memakai destinasi yang
    // sudah terdata, bukan layanan geocoding berbayar.
    var cari = document.getElementById('cari-titik-awal');
    if (cari) {
      cari.addEventListener('change', function () {
        if (!cari.value) { return; }
        var bagian = cari.value.split(',');
        peta.setView([parseFloat(bagian[0]), parseFloat(bagian[1])], 13);
      });
    }

    function pasangPenanda(la, ln) {
      la = Math.round(la * 1e7) / 1e7;
      ln = Math.round(ln * 1e7) / 1e7;

      if (penanda) {
        penanda.setLatLng([la, ln]);
      } else {
        penanda = L.marker([la, ln], { draggable: true }).addTo(peta);
        penanda.on('dragend', function () {
          var p = penanda.getLatLng();
          pasangPenanda(p.lat, p.lng);
        });
      }
      inLat.value = la;
      inLng.value = ln;
      perbaruiTeks(la, ln);
    }

    function perbaruiTeks(la, ln) {
      if (!teksKoordinat) { return; }

      if (la === null) {
        teksKoordinat.textContent = 'Belum ada titik dipilih. Klik pada peta untuk menentukan lokasi.';
        teksKoordinat.className = 'form-text text-warning-emphasis';
        return;
      }

      var diLuar = la < -9.2 || la > -8.0 || ln < 121.4 || ln > 122.9;
      teksKoordinat.textContent = diLuar
        ? '⚠ Titik ini berada di luar wilayah Kabupaten Sikka (' + la + ', ' + ln + '). Periksa kembali.'
        : 'Titik terpilih: ' + la + ', ' + ln;
      teksKoordinat.className = diLuar ? 'form-text text-danger' : 'form-text text-success';
    }
  }
})();
