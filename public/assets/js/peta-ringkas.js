/**
 * Peta ringkas beranda (FR-HOME-01).
 * Sengaja dibuat sesederhana mungkin: tanpa clustering plugin, tanpa filter -
 * tugasnya hanya membuat pengunjung sadar bahwa peta ini bisa disentuh,
 * lalu mengantar mereka ke /peta.
 */
(function () {
  'use strict';

  var K = window.SIKKA_PETA;
  var wadah = document.getElementById('peta-ringkas');
  if (!K || !wadah) { return; }

  if (typeof L === 'undefined') {
    window.addEventListener('load', mulai);
  } else {
    mulai();
  }

  function mulai() {
    if (typeof L === 'undefined') {
      wadah.innerHTML = '<div class="p-3 small text-center">' +
        'Peta tidak dapat dimuat. <a href="' + K.urlPeta + '">Buka halaman peta</a>.</div>';
      return;
    }

    var peta = L.map(wadah, {
      center: [K.lat, K.lng],
      zoom: K.zoom,
      scrollWheelZoom: false,   // jangan membajak scroll halaman beranda
      zoomControl: true
    });

    L.tileLayer(K.tile, {
      maxZoom: K.zoomMaks || 18,
      attribution: K.atribusi
    }).addTo(peta);

    var skeleton = wadah.querySelector('.peta-skeleton');
    if (skeleton) { skeleton.remove(); }

    var grup = L.layerGroup().addTo(peta);

    (K.pin || []).forEach(function (p) {
      L.marker([p.lat, p.lng], { icon: ikon(p.kategori.warna), title: p.nama, alt: p.nama })
        .addTo(grup)
        .on('click', function () {
          // Klik pin di beranda langsung membuka peta lengkap pada pin itu.
          window.location.href = K.urlPeta + '?destinasi=' + encodeURIComponent(p.slug);
        });
    });

    // Sesuaikan viewport agar seluruh pin terlihat.
    if ((K.pin || []).length > 1) {
      peta.fitBounds(L.latLngBounds(K.pin.map(function (p) { return [p.lat, p.lng]; })),
        { padding: [30, 30], maxZoom: 11 });
    }
  }

  function ikon(warna) {
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="32" viewBox="0 0 28 40">' +
      '<path d="M14 0C6.3 0 0 6.3 0 14c0 10 14 26 14 26s14-16 14-26C28 6.3 21.7 0 14 0z" ' +
      'fill="' + warna + '" stroke="#fff" stroke-width="2"/>' +
      '<circle cx="14" cy="14" r="5" fill="#fff"/></svg>';
    return L.icon({
      iconUrl: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
      iconSize: [22, 32],
      iconAnchor: [11, 32]
    });
  }
})();
