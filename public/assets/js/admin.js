/**
 * Interaksi kecil panel admin: pratinjau foto sebelum unggah,
 * konfirmasi hapus, dan slug otomatis dari nama.
 */
(function () {
  'use strict';

  // --- Konfirmasi untuk aksi yang menghapus data ----------------------
  document.addEventListener('submit', function (ev) {
    var form = ev.target;
    var pesan = form.getAttribute('data-konfirmasi');
    if (pesan && !window.confirm(pesan)) {
      ev.preventDefault();
    }
  });

  // --- Pratinjau foto sebelum publish (FR-ADM-02) ---------------------
  Array.prototype.forEach.call(document.querySelectorAll('[data-pratinjau]'), function (input) {
    var target = document.getElementById(input.getAttribute('data-pratinjau'));
    if (!target) { return; }

    input.addEventListener('change', function () {
      target.innerHTML = '';
      Array.prototype.forEach.call(input.files || [], function (berkas) {
        if (!berkas.type.startsWith('image/')) { return; }
        var img = document.createElement('img');
        img.className = 'pratinjau-foto';
        img.alt = 'Pratinjau ' + berkas.name;
        img.src = URL.createObjectURL(berkas);
        img.onload = function () { URL.revokeObjectURL(img.src); };
        target.appendChild(img);
      });
    });
  });

  // --- Slug otomatis dari nama/judul ----------------------------------
  var sumberSlug = document.querySelector('[data-sumber-slug]');
  var kolomSlug = document.getElementById('input-slug');
  if (sumberSlug && kolomSlug) {
    var pernahDisunting = kolomSlug.value !== '';
    kolomSlug.addEventListener('input', function () { pernahDisunting = true; });

    sumberSlug.addEventListener('input', function () {
      if (pernahDisunting) { return; }
      kolomSlug.value = sumberSlug.value
        .toLowerCase()
        .replace(/&/g, ' dan ')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');
    });
  }

  // --- Hitung sisa karakter pada kolom berbatas -----------------------
  Array.prototype.forEach.call(document.querySelectorAll('[data-hitung]'), function (el) {
    var info = document.getElementById(el.getAttribute('data-hitung'));
    if (!info) { return; }
    var maks = parseInt(el.getAttribute('maxlength') || '400', 10);

    function perbarui() {
      info.textContent = el.value.length + ' / ' + maks + ' karakter';
    }
    el.addEventListener('input', perbarui);
    perbarui();
  });
})();
