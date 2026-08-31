/**
 * Peta interaktif wisata Kabupaten Sikka.
 *
 * Mengimplementasikan FR-MAP-01 s/d FR-MAP-09:
 *  - pin seluruh destinasi aktif (01)
 *  - popup info lengkap + tombol rute (02, 08)
 *  - filter multi-kategori (03)
 *  - pencarian autocomplete (04)
 *  - clustering otomatis (05)
 *  - geolokasi + fallback pilih kecamatan (06)
 *  - warna pin per kategori + legenda (07)
 *  - URL unik per pin yang bisa dibagikan (09)
 *
 * Vanilla JS - tanpa React/Vue agar bundel tetap ringan (§13).
 * (c) Karunia Bunda IT Training Center Maumere
 */
(function () {
  'use strict';

  var K = window.SIKKA_PETA;
  if (!K) { return; }

  var wadah = document.getElementById('peta-utama');
  if (!wadah) { return; }

  // Keadaan modul HARUS disiapkan sebelum mulai() dipanggil. Ketika Leaflet
  // dimuat dari berkas lokal, skrip ini berjalan setelah Leaflet siap
  // sehingga mulai() dijalankan langsung - bila deklarasi di bawahnya,
  // semuaPin masih undefined dan peta gagal menggambar pin.
  var peta, lapisanPin, penandaLokasi = null;
  var semuaPin = K.pin || [];
  var penandaPerSlug = {};
  var filter = { kategori: '', kecamatan: '', cari: '' };

  if (typeof L === 'undefined') {
    // Leaflet belum siap (mis. dimuat dari CDN atau koneksi lambat).
    window.addEventListener('load', mulai);
  } else {
    mulai();
  }

  function mulai() {
    if (typeof L === 'undefined') {
      gagalMuatPeta();
      return;
    }

    peta = L.map(wadah, {
      center: [K.lat, K.lng],
      zoom: K.zoom,
      scrollWheelZoom: true,
      // Kontrol zoom di kanan agar tidak tertutup panel filter di layar kecil
      zoomControl: false
    });
    L.control.zoom({ position: 'topright' }).addTo(peta);

    L.tileLayer(K.tile, {
      maxZoom: K.zoomMaks || 18,
      attribution: K.atribusi
    }).addTo(peta);

    // FR-MAP-05: clustering - krusial karena Waigete saja punya 12 titik
    // berdekatan. Bila plugin gagal dimuat, mundur ke LayerGroup biasa.
    if (typeof L.markerClusterGroup === 'function') {
      lapisanPin = L.markerClusterGroup({
        showCoverageOnHover: false,
        maxClusterRadius: 45,
        disableClusteringAtZoom: 15
      });
    } else {
      lapisanPin = L.layerGroup();
    }
    peta.addLayer(lapisanPin);

    var skeleton = wadah.querySelector('.peta-skeleton');
    if (skeleton) { skeleton.remove(); }

    gambarPin();
    pasangKendali();

    // FR-MAP-09: buka popup otomatis bila URL memuat ?destinasi=slug.
    // Tunggu peta selesai bergerak lebih dulu - membuka popup saat peta masih
    // beranimasi membuat popup tidak jadi muncul.
    if (K.terpilih && penandaPerSlug[K.terpilih]) {
      var penandaAwal = penandaPerSlug[K.terpilih];
      // Zoom 16 melewati disableClusteringAtZoom (15) sehingga pin yang
      // dituju pasti tidak tergabung dalam cluster.
      peta.once('moveend', function () { bukaPopupAman(penandaAwal); });
      peta.setView(penandaAwal.getLatLng(), 16);
    }
  }

  /**
   * Membuka popup sebuah penanda dengan aman.
   *
   * zoomToShowLayer() memecah cluster lebih dulu, tetapi callback-nya hanya
   * terpanggil bila peta benar-benar bergerak. Ketika penanda sudah terlihat
   * (tidak dalam cluster), callback itu tidak pernah jalan dan popup tidak
   * terbuka - persis kasus tautan berbagi /peta?destinasi=slug. Karena itu
   * selalu ada jaring pengaman yang membuka popup secara langsung.
   */
  function bukaPopupAman(penanda) {
    if (!penanda) { return; }

    var sisa = 10;
    var timer = window.setInterval(function () {
      if (penanda.isPopupOpen && penanda.isPopupOpen()) {
        window.clearInterval(timer);
        return;
      }
      if (sisa-- <= 0) {
        window.clearInterval(timer);
        return;
      }

      // Penanda yang sedang tergabung dalam cluster tidak punya _icon dan
      // tidak terpasang di peta, sehingga openPopup() tidak berefek apa pun.
      // Pecah dulu cluster-nya, baru buka popup.
      if (penanda._icon) {
        penanda.openPopup();
      } else if (lapisanPin.zoomToShowLayer) {
        try {
          lapisanPin.zoomToShowLayer(penanda, function () { penanda.openPopup(); });
        } catch (e) { /* diabaikan - percobaan berikutnya menangani */ }
      }
    }, 250);
  }

  function gagalMuatPeta() {
    // §10.7: bila peta gagal dimuat, arahkan ke fallback daftar teks yang
    // sudah ada di HTML - wisatawan tetap mendapat informasi inti.
    wadah.innerHTML =
      '<div class="p-4 text-center">' +
      '<p class="mb-2">Peta tidak dapat dimuat. Periksa koneksi internet Anda.</p>' +
      '<a class="btn btn-sm btn-teal" href="#daftar-teks-destinasi">Lihat daftar destinasi</a>' +
      '</div>';
  }

  /** Ikon pin berwarna per kategori (FR-MAP-07) - SVG inline, tanpa file. */
  function ikonKategori(warna) {
    var svg =
      '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="40" viewBox="0 0 28 40">' +
      '<path d="M14 0C6.3 0 0 6.3 0 14c0 10 14 26 14 26s14-16 14-26C28 6.3 21.7 0 14 0z" ' +
      'fill="' + warna + '" stroke="#ffffff" stroke-width="2"/>' +
      '<circle cx="14" cy="14" r="5" fill="#ffffff"/></svg>';

    return L.icon({
      iconUrl: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
      iconSize: [28, 40],
      iconAnchor: [14, 40],
      popupAnchor: [0, -38]
    });
  }

  function amanHtml(teks) {
    var d = document.createElement('div');
    d.textContent = teks == null ? '' : String(teks);
    return d.innerHTML;
  }

  /** FR-MAP-02 + FR-MAP-08: isi popup dan tombol rute deep-link. */
  function isiPopup(p) {
    var t = K.teks;
    var html = '<div class="popup-destinasi">';

    if (p.foto) {
      html += '<img class="popup-foto" src="' + amanHtml(p.foto) + '" alt="' + amanHtml(p.nama) + '" loading="lazy">';
    }
    html += '<span class="chip-kategori" style="--warna:' + amanHtml(p.kategori.warna) + '">' +
            amanHtml(p.kategori.ikon) + ' ' + amanHtml(p.kategori.nama) + '</span>';
    html += '<h3 class="popup-judul">' + amanHtml(p.nama) + '</h3>';

    if (p.kecamatan) {
      html += '<p class="popup-meta">' + amanHtml(t.kecamatan) + ': ' + amanHtml(p.kecamatan) + '</p>';
    }
    if (p.ringkas) {
      html += '<p class="popup-ringkas">' + amanHtml(p.ringkas) + '</p>';
    }
    if (p.jam) {
      html += '<p class="popup-meta"><span aria-hidden="true">🕒</span> ' + amanHtml(p.jam) + '</p>';
    }
    if (p.tarif) {
      html += '<p class="popup-meta"><span aria-hidden="true">🎟</span> ' + amanHtml(p.tarif) + '</p>';
    }
    if (p.menit) {
      html += '<p class="popup-meta"><span aria-hidden="true">🚗</span> ~' + amanHtml(p.menit) +
              ' menit dari Maumere</p>';
    }

    html += '<div class="popup-aksi">';
    html += '<a class="btn btn-sm btn-teal" href="' + amanHtml(p.url) + '">' + amanHtml(t.lihatDetail) + '</a>';
    // Deep link ke aplikasi navigasi eksternal - kita tidak membangun
    // navigasi sendiri (FR-MAP-08).
    html += '<a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" ' +
            'href="https://www.google.com/maps/dir/?api=1&destination=' + p.lat + ',' + p.lng + '">' +
            amanHtml(t.ruteKeSini) + '</a>';
    html += '<button class="btn btn-sm btn-outline-secondary" type="button" data-bagikan="' +
            amanHtml(p.slug) + '">' + amanHtml(t.bagikan) + '</button>';
    html += '</div></div>';

    return html;
  }

  function pinTerlihat() {
    return semuaPin.filter(function (p) {
      if (filter.kategori && p.kategori.slug !== filter.kategori) { return false; }
      if (filter.kecamatan && slugify(p.kecamatan) !== filter.kecamatan) { return false; }
      if (filter.cari) {
        var teks = (p.nama + ' ' + (p.kecamatan || '')).toLowerCase();
        if (teks.indexOf(filter.cari.toLowerCase()) === -1) { return false; }
      }
      return true;
    });
  }

  function slugify(teks) {
    return (teks || '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
  }

  function gambarPin() {
    lapisanPin.clearLayers();
    penandaPerSlug = {};

    var terlihat = pinTerlihat();

    terlihat.forEach(function (p) {
      var penanda = L.marker([p.lat, p.lng], {
        icon: ikonKategori(p.kategori.warna),
        title: p.nama,
        alt: p.nama
      });
      penanda.bindPopup(isiPopup(p), { maxWidth: 300, minWidth: 240 });
      penanda.on('popupopen', function () {
        // FR-MAP-09: URL ikut berubah agar bisa disalin/dibagikan.
        gantiUrlPin(p.slug);
      });
      lapisanPin.addLayer(penanda);
      penandaPerSlug[p.slug] = penanda;
    });

    perbaruiHasil(terlihat);
    var jml = document.getElementById('peta-jumlah');
    if (jml) { jml.textContent = String(terlihat.length); }
  }

  function gantiUrlPin(slug) {
    if (!window.history || !window.history.replaceState) { return; }
    window.history.replaceState({}, '', K.urlPeta + '?destinasi=' + encodeURIComponent(slug));
  }

  /** Daftar hasil di panel - juga membantu pengguna keyboard & pembaca layar. */
  function perbaruiHasil(daftar) {
    var wadahHasil = document.getElementById('peta-hasil');
    if (!wadahHasil) { return; }

    if (daftar.length === 0) {
      wadahHasil.innerHTML = '<p class="small text-secondary mb-0">' + amanHtml(K.teks.tidakAda) + '</p>';
      return;
    }

    var html = '<p class="small text-secondary mb-2">' + daftar.length + ' ' + amanHtml(K.teks.hasil) + '</p><ul class="list-unstyled mb-0">';
    daftar.forEach(function (p) {
      html += '<li class="hasil-item">' +
              '<button type="button" class="hasil-tombol" data-slug="' + amanHtml(p.slug) + '">' +
              '<span class="titik-kategori" style="background:' + amanHtml(p.kategori.warna) + '"></span>' +
              '<span class="hasil-nama">' + amanHtml(p.nama) + '</span>' +
              '<small class="text-secondary d-block">' + amanHtml(p.kecamatan || '') + '</small>' +
              '</button></li>';
    });
    wadahHasil.innerHTML = html + '</ul>';
  }

  function bukaPin(slug) {
    var penanda = penandaPerSlug[slug];
    if (!penanda) { return; }
    peta.setView(penanda.getLatLng(), Math.max(peta.getZoom(), 14));
    bukaPopupAman(penanda);
  }

  function pasangKendali() {
    // --- Filter kategori (FR-MAP-03) ---------------------------------
    var chip = document.querySelectorAll('.chip-filter');
    Array.prototype.forEach.call(chip, function (b) {
      b.addEventListener('click', function () {
        Array.prototype.forEach.call(chip, function (x) { x.classList.remove('aktif'); });
        b.classList.add('aktif');
        filter.kategori = b.getAttribute('data-kategori') || '';
        gambarPin();
      });
    });

    // --- Filter kecamatan --------------------------------------------
    var selKec = document.getElementById('filter-kecamatan');
    if (selKec) {
      selKec.addEventListener('change', function () {
        filter.kecamatan = selKec.value;
        gambarPin();
        if (selKec.value) { pasKeHasil(); }
      });
    }

    // --- Reset --------------------------------------------------------
    var reset = document.getElementById('peta-reset');
    if (reset) {
      reset.addEventListener('click', function () {
        filter = { kategori: '', kecamatan: '', cari: '' };
        if (selKec) { selKec.value = ''; }
        var cari = document.getElementById('peta-cari');
        if (cari) { cari.value = ''; }
        Array.prototype.forEach.call(chip, function (x) { x.classList.remove('aktif'); });
        if (chip[0]) { chip[0].classList.add('aktif'); }
        gambarPin();
        peta.setView([K.lat, K.lng], K.zoom);
      });
    }

    // --- Pencarian + autocomplete (FR-MAP-04) -------------------------
    pasangPencarian();

    // --- Geolokasi (FR-MAP-06) ----------------------------------------
    var tombolLokasi = document.getElementById('peta-lokasi-saya');
    if (tombolLokasi) {
      tombolLokasi.addEventListener('click', cariTerdekat);
    }
    var fallbackSel = document.getElementById('pilih-kecamatan');
    if (fallbackSel) {
      fallbackSel.addEventListener('change', function () {
        if (!fallbackSel.value) { return; }
        var bagian = fallbackSel.value.split(',');
        peta.setView([parseFloat(bagian[0]), parseFloat(bagian[1])], 12);
      });
    }

    // --- Klik hasil & tombol bagikan (delegasi event) ------------------
    document.addEventListener('click', function (ev) {
      var tombolHasil = ev.target.closest ? ev.target.closest('.hasil-tombol') : null;
      if (tombolHasil) {
        bukaPin(tombolHasil.getAttribute('data-slug'));
        return;
      }
      var bagikan = ev.target.closest ? ev.target.closest('[data-bagikan]') : null;
      if (bagikan) {
        bagikanPin(bagikan.getAttribute('data-bagikan'));
      }
    });

    // --- Panel filter di layar kecil ----------------------------------
    var toggle = document.getElementById('peta-toggle-panel');
    if (toggle) {
      toggle.addEventListener('click', function () {
        document.querySelector('.peta-halaman').classList.toggle('panel-terbuka');
        window.setTimeout(function () { peta.invalidateSize(); }, 250);
      });
    }
  }

  function pasKeHasil() {
    var terlihat = pinTerlihat();
    if (terlihat.length === 0) { return; }
    var batas = L.latLngBounds(terlihat.map(function (p) { return [p.lat, p.lng]; }));
    peta.fitBounds(batas, { padding: [40, 40], maxZoom: 13 });
  }

  function pasangPencarian() {
    var input = document.getElementById('peta-cari');
    var daftar = document.getElementById('peta-saran');
    if (!input || !daftar) { return; }

    var timer = null;

    input.addEventListener('input', function () {
      var q = input.value.trim();
      filter.cari = q;
      window.clearTimeout(timer);

      // Debounce: hemat permintaan di koneksi lambat (§12).
      timer = window.setTimeout(function () {
        gambarPin();
        if (q.length < 2) {
          tutupSaran();
          return;
        }
        ambilSaran(q);
      }, 250);
    });

    input.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape') { tutupSaran(); }
      if (ev.key === 'Enter') {
        ev.preventDefault();
        var pertama = daftar.querySelector('[data-slug]');
        if (pertama) {
          bukaPin(pertama.getAttribute('data-slug'));
          tutupSaran();
        }
      }
    });

    document.addEventListener('click', function (ev) {
      if (!daftar.contains(ev.target) && ev.target !== input) { tutupSaran(); }
    });

    function ambilSaran(q) {
      fetch(K.urlCari + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.ok ? r.json() : { data: [] }; })
        .then(function (j) { tampilkanSaran(j.data || []); })
        .catch(function () { tutupSaran(); });
    }

    function tampilkanSaran(hasil) {
      if (hasil.length === 0) { tutupSaran(); return; }

      daftar.innerHTML = hasil.map(function (h) {
        return '<li role="option"><button type="button" class="saran-item" data-slug="' +
               amanHtml(h.slug) + '">' +
               '<span class="titik-kategori" style="background:' + amanHtml(h.warna) + '"></span>' +
               amanHtml(h.nama) +
               '<small class="text-secondary d-block">' + amanHtml(h.kecamatan || h.kategori) + '</small>' +
               '</button></li>';
      }).join('');

      daftar.hidden = false;
      input.setAttribute('aria-expanded', 'true');

      Array.prototype.forEach.call(daftar.querySelectorAll('[data-slug]'), function (b) {
        b.addEventListener('click', function () {
          var slug = b.getAttribute('data-slug');
          input.value = b.textContent.trim().split('\n')[0];
          filter.cari = '';
          gambarPin();
          bukaPin(slug);
          tutupSaran();
        });
      });
    }

    function tutupSaran() {
      daftar.hidden = true;
      daftar.innerHTML = '';
      input.setAttribute('aria-expanded', 'false');
    }
  }

  /** FR-MAP-06: cari terdekat dari lokasi pengguna, dengan fallback manual. */
  function cariTerdekat() {
    var fallback = document.getElementById('peta-fallback-kecamatan');

    if (!navigator.geolocation) {
      if (fallback) { fallback.hidden = false; }
      return;
    }

    navigator.geolocation.getCurrentPosition(
      function (pos) {
        var lat = pos.coords.latitude;
        var lng = pos.coords.longitude;

        if (penandaLokasi) { peta.removeLayer(penandaLokasi); }
        penandaLokasi = L.circleMarker([lat, lng], {
          radius: 8, color: '#0d6efd', fillColor: '#0d6efd', fillOpacity: 0.6
        }).addTo(peta).bindPopup(K.teks.lokasiAnda);

        // Urutkan panel hasil berdasarkan jarak dari pengguna.
        var terlihat = pinTerlihat().map(function (p) {
          p._jarak = jarakKm(lat, lng, p.lat, p.lng);
          return p;
        }).sort(function (a, b) { return a._jarak - b._jarak; });

        perbaruiHasil(terlihat);
        if (terlihat.length > 0) {
          peta.setView([lat, lng], 11);
        }
      },
      function () {
        // Izin ditolak - tampilkan pemilih kecamatan manual, jangan buntu.
        if (fallback) { fallback.hidden = false; }
        var hasil = document.getElementById('peta-hasil');
        if (hasil) {
          hasil.insertAdjacentHTML('afterbegin',
            '<div class="alert alert-warning small py-2">' + amanHtml(K.teks.lokasiDitolak) + '</div>');
        }
      },
      { timeout: 8000, maximumAge: 60000 }
    );
  }

  function jarakKm(lat1, lng1, lat2, lng2) {
    var R = 6371;
    var dLat = (lat2 - lat1) * Math.PI / 180;
    var dLng = (lng2 - lng1) * Math.PI / 180;
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLng / 2) * Math.sin(dLng / 2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  }

  /** FR-MAP-09: bagikan tautan pin (mis. ke WhatsApp teman perjalanan). */
  function bagikanPin(slug) {
    var tautan = window.location.origin + K.urlPeta + '?destinasi=' + encodeURIComponent(slug);

    if (navigator.share) {
      navigator.share({ url: tautan }).catch(function () { /* dibatalkan pengguna */ });
      return;
    }
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(tautan).then(function () {
        window.alert(K.teks.tautanDisalin + '\n' + tautan);
      }).catch(function () { window.prompt(K.teks.bagikan, tautan); });
      return;
    }
    window.prompt(K.teks.bagikan, tautan);
  }
})();
