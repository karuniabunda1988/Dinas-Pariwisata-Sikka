-- =====================================================================
-- Sistem Informasi & Peta Interaktif Pariwisata Kabupaten Sikka
-- Skema basis data - MySQL / MariaDB (XAMPP)
-- Referensi: PRD v1.0 §11 (Model Data / Skema Basis Data)
-- (c) Karunia Bunda IT Training Center Maumere
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `sikka_pariwisata`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sikka_pariwisata`;

-- ---------------------------------------------------------------------
-- Tabel referensi tetap (§10.3: kategori & kecamatan bukan teks bebas)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `kategori`;
CREATE TABLE `kategori` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama`      VARCHAR(80)  NOT NULL,
  `nama_en`   VARCHAR(80)  NOT NULL DEFAULT '',
  `slug`      VARCHAR(80)  NOT NULL,
  `warna`     VARCHAR(9)   NOT NULL DEFAULT '#0d9488' COMMENT 'Warna pin peta, §10.5',
  `ikon`      VARCHAR(16)  NOT NULL DEFAULT '',
  `urutan`    SMALLINT     NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kategori_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `kecamatan`;
CREATE TABLE `kecamatan` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama`      VARCHAR(80)  NOT NULL,
  `slug`      VARCHAR(80)  NOT NULL,
  `latitude`  DECIMAL(10,7) DEFAULT NULL COMMENT 'Titik pusat kecamatan, untuk fallback geolokasi FR-MAP-06',
  `longitude` DECIMAL(10,7) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kecamatan_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Pengguna admin & mitra (FR-ADM-01: peran berjenjang)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `pengguna_admin`;
CREATE TABLE `pengguna_admin` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama`           VARCHAR(120) NOT NULL,
  `username`       VARCHAR(60)  NOT NULL,
  `email`          VARCHAR(160) NOT NULL DEFAULT '',
  `password_hash`  VARCHAR(255) NOT NULL,
  `peran`          ENUM('super_admin','admin_konten','mitra') NOT NULL DEFAULT 'admin_konten',
  `aktif`          TINYINT(1)   NOT NULL DEFAULT 1,
  `login_terakhir` DATETIME     DEFAULT NULL,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pengguna_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Destinasi - inti tiap pin peta (§10.3)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `destinasi`;
CREATE TABLE `destinasi` (
  `id`                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama`                  VARCHAR(160) NOT NULL,
  `nama_en`               VARCHAR(160) NOT NULL DEFAULT '',
  `slug`                  VARCHAR(180) NOT NULL,
  `kategori_id`           INT UNSIGNED NOT NULL,
  `kecamatan_id`          INT UNSIGNED DEFAULT NULL,
  `latitude`              DECIMAL(10,7) DEFAULT NULL,
  `longitude`             DECIMAL(10,7) DEFAULT NULL,
  `deskripsi_singkat`     VARCHAR(400) NOT NULL DEFAULT '',
  `deskripsi_singkat_en`  VARCHAR(400) NOT NULL DEFAULT '',
  `deskripsi_lengkap`     MEDIUMTEXT,
  `deskripsi_lengkap_en`  MEDIUMTEXT,
  `jam_operasional`       VARCHAR(120) NOT NULL DEFAULT '',
  `kisaran_tarif`         VARCHAR(120) NOT NULL DEFAULT '',
  `fasilitas`             VARCHAR(500) NOT NULL DEFAULT '' COMMENT 'Dipisah koma: parkir, toilet, warung, ...',
  `cara_mencapai`         TEXT,
  `kontak_nama`           VARCHAR(120) NOT NULL DEFAULT '',
  `kontak_telepon`        VARCHAR(40)  NOT NULL DEFAULT '',
  `jarak_dari_maumere_km` DECIMAL(6,1) DEFAULT NULL,
  `waktu_tempuh_menit`    SMALLINT UNSIGNED DEFAULT NULL,
  `foto_utama`            VARCHAR(255) NOT NULL DEFAULT '',
  `unggulan`              TINYINT(1)   NOT NULL DEFAULT 0 COMMENT 'FR-HOME-02: 10 destinasi kelas prioritas',
  `status`                ENUM('aktif','nonaktif','draft') NOT NULL DEFAULT 'draft',
  `sumber_data`           VARCHAR(200) NOT NULL DEFAULT '',
  `perlu_verifikasi_lapangan` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Lampiran A: koordinat belum tervalidasi lapangan',
  `terakhir_diverifikasi`  DATE        DEFAULT NULL COMMENT 'FR-ADM-03: siklus verifikasi 6 bulan',
  `dibuat_oleh`           INT UNSIGNED DEFAULT NULL,
  `created_at`            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_destinasi_slug` (`slug`),
  KEY `idx_destinasi_status` (`status`),
  KEY `idx_destinasi_kategori` (`kategori_id`),
  KEY `idx_destinasi_kecamatan` (`kecamatan_id`),
  KEY `idx_destinasi_bbox` (`latitude`,`longitude`),
  CONSTRAINT `fk_destinasi_kategori`  FOREIGN KEY (`kategori_id`)  REFERENCES `kategori` (`id`)  ON UPDATE CASCADE,
  CONSTRAINT `fk_destinasi_kecamatan` FOREIGN KEY (`kecamatan_id`) REFERENCES `kecamatan` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `destinasi_galeri`;
CREATE TABLE `destinasi_galeri` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `destinasi_id` INT UNSIGNED NOT NULL,
  `file`         VARCHAR(255) NOT NULL,
  `alt_text`     VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'FR-A11Y-01: alt text wajib',
  `urutan`       SMALLINT     NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_galeri_destinasi` (`destinasi_id`),
  CONSTRAINT `fk_galeri_destinasi` FOREIGN KEY (`destinasi_id`) REFERENCES `destinasi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- UMKM / akomodasi / ekonomi kreatif (§9.5)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `umkm`;
CREATE TABLE `umkm` (
  `id`                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama`                 VARCHAR(160) NOT NULL,
  `slug`                 VARCHAR(180) NOT NULL,
  `jenis`                ENUM('kuliner','kerajinan','penginapan','biro_perjalanan') NOT NULL DEFAULT 'kuliner',
  `deskripsi`            TEXT,
  `alamat`               VARCHAR(300) NOT NULL DEFAULT '',
  `kontak_telepon`       VARCHAR(40)  NOT NULL DEFAULT '',
  `kontak_wa`            VARCHAR(40)  NOT NULL DEFAULT '',
  `latitude`             DECIMAL(10,7) DEFAULT NULL,
  `longitude`            DECIMAL(10,7) DEFAULT NULL,
  `kecamatan_id`         INT UNSIGNED DEFAULT NULL,
  `destinasi_terdekat_id` INT UNSIGNED DEFAULT NULL COMMENT 'FR-UMKM-02',
  `foto`                 VARCHAR(255) NOT NULL DEFAULT '',
  `status_verifikasi`    ENUM('menunggu','terverifikasi','ditolak') NOT NULL DEFAULT 'menunggu',
  `pemilik_user_id`      INT UNSIGNED DEFAULT NULL COMMENT 'Akun mitra terbatas FR-ADM-01',
  `created_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_umkm_slug` (`slug`),
  KEY `idx_umkm_destinasi` (`destinasi_terdekat_id`),
  KEY `idx_umkm_status` (`status_verifikasi`),
  CONSTRAINT `fk_umkm_destinasi`  FOREIGN KEY (`destinasi_terdekat_id`) REFERENCES `destinasi` (`id`)  ON DELETE SET NULL,
  CONSTRAINT `fk_umkm_kecamatan`  FOREIGN KEY (`kecamatan_id`)          REFERENCES `kecamatan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Event & budaya (§9.4)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `event`;
CREATE TABLE `event` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama`                VARCHAR(180) NOT NULL,
  `slug`                VARCHAR(200) NOT NULL,
  `tanggal_mulai`       DATE NOT NULL,
  `tanggal_selesai`     DATE DEFAULT NULL,
  `lokasi_teks`         VARCHAR(200) NOT NULL DEFAULT '',
  `destinasi_terkait_id` INT UNSIGNED DEFAULT NULL,
  `deskripsi`           TEXT,
  `foto`                VARCHAR(255) NOT NULL DEFAULT '',
  `status`              ENUM('aktif','draft') NOT NULL DEFAULT 'draft',
  `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_slug` (`slug`),
  KEY `idx_event_tanggal` (`tanggal_mulai`),
  CONSTRAINT `fk_event_destinasi` FOREIGN KEY (`destinasi_terkait_id`) REFERENCES `destinasi` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Artikel / panduan (§9.6)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `artikel`;
CREATE TABLE `artikel` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `judul`         VARCHAR(200) NOT NULL,
  `slug`          VARCHAR(220) NOT NULL,
  `ringkasan`     VARCHAR(400) NOT NULL DEFAULT '',
  `isi`           MEDIUMTEXT,
  `gambar_sampul` VARCHAR(255) NOT NULL DEFAULT '',
  `kategori`      VARCHAR(80)  NOT NULL DEFAULT 'panduan',
  `penulis_id`    INT UNSIGNED DEFAULT NULL,
  `status`        ENUM('publish','draft') NOT NULL DEFAULT 'draft',
  `published_at`  DATETIME DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_artikel_slug` (`slug`),
  KEY `idx_artikel_status` (`status`,`published_at`),
  CONSTRAINT `fk_artikel_penulis` FOREIGN KEY (`penulis_id`) REFERENCES `pengguna_admin` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Ulasan (Fase 2, FR-DEST-03) - tabel disiapkan sejak awal
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `ulasan`;
CREATE TABLE `ulasan` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `destinasi_id`   INT UNSIGNED NOT NULL,
  `nama_penulis`   VARCHAR(120) NOT NULL,
  `rating`         TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `komentar`       TEXT,
  `status_moderasi` ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ulasan_destinasi` (`destinasi_id`,`status_moderasi`),
  CONSTRAINT `fk_ulasan_destinasi` FOREIGN KEY (`destinasi_id`) REFERENCES `destinasi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Pengaduan publik (FR-SVC-01)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `pengaduan`;
CREATE TABLE `pengaduan` (
  `id`                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `isi`                  TEXT NOT NULL,
  `nama_pelapor`         VARCHAR(120) NOT NULL DEFAULT '',
  `kontak_pelapor`       VARCHAR(120) NOT NULL DEFAULT '' COMMENT 'Data pribadi minimum - UU PDP 27/2022, §12',
  `destinasi_terkait_id` INT UNSIGNED DEFAULT NULL,
  `status_tindak_lanjut` ENUM('baru','diproses','selesai') NOT NULL DEFAULT 'baru',
  `catatan_admin`        TEXT,
  `status_notifikasi`    VARCHAR(40) NOT NULL DEFAULT 'belum' COMMENT 'Driver bertingkat §13.1',
  `ip_pelapor`           VARCHAR(45) NOT NULL DEFAULT '',
  `created_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pengaduan_status` (`status_tindak_lanjut`),
  CONSTRAINT `fk_pengaduan_destinasi` FOREIGN KEY (`destinasi_terkait_id`) REFERENCES `destinasi` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Statistik kunjungan (FR-STAT-01: input manual oleh admin)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `statistik_kunjungan`;
CREATE TABLE `statistik_kunjungan` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tahun`       SMALLINT UNSIGNED NOT NULL,
  `bulan`       TINYINT UNSIGNED NOT NULL,
  `kategori_id` INT UNSIGNED DEFAULT NULL COMMENT 'NULL = total seluruh kategori',
  `jumlah`      INT UNSIGNED NOT NULL DEFAULT 0,
  `sumber_data` VARCHAR(200) NOT NULL DEFAULT '',
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  -- MySQL/MariaDB memperlakukan setiap NULL sebagai nilai yang berbeda, sehingga
  -- UNIQUE(tahun,bulan,kategori_id) TIDAK mencegah duplikat pada baris "semua
  -- kategori" (kategori_id NULL). Kolom turunan ini memetakan NULL menjadi 0
  -- agar satu periode benar-benar hanya punya satu baris dan ON DUPLICATE KEY
  -- UPDATE bekerja sebagaimana mestinya.
  `kategori_kunci` INT UNSIGNED AS (COALESCE(`kategori_id`, 0)) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_statistik_periode` (`tahun`,`bulan`,`kategori_kunci`),
  CONSTRAINT `fk_statistik_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Jejak audit (FR-ADM-03) & pengaturan key-value (§11)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `log_aktivitas`;
CREATE TABLE `log_aktivitas` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pengguna_id` INT UNSIGNED DEFAULT NULL,
  `nama_pengguna` VARCHAR(120) NOT NULL DEFAULT '',
  `aksi`        VARCHAR(40)  NOT NULL,
  `entitas`     VARCHAR(60)  NOT NULL DEFAULT '',
  `entitas_id`  INT UNSIGNED DEFAULT NULL,
  `keterangan`  VARCHAR(400) NOT NULL DEFAULT '',
  `ip`          VARCHAR(45)  NOT NULL DEFAULT '',
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_log_waktu` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `pengaturan`;
CREATE TABLE `pengaturan` (
  `kunci`      VARCHAR(80)  NOT NULL,
  `nilai`      TEXT,
  `keterangan` VARCHAR(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`kunci`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
