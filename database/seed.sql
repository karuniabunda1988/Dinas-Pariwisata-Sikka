-- =====================================================================
-- Data awal (seeding) - Pariwisata Kabupaten Sikka
-- Jalankan SETELAH schema.sql
--
-- PERINGATAN KOORDINAT (PRD Lampiran A):
--   Koordinat di bawah adalah PERKIRAAN dari sumber sekunder, BUKAN hasil
--   survei GPS lapangan. Baris dengan perlu_verifikasi_lapangan = 1 wajib
--   diverifikasi ulang oleh admin/kontak kecamatan sebelum status diubah
--   menjadi 'aktif'. Jangan pakai data ini sebagai dasar navigasi resmi
--   sebelum diverifikasi.
-- =====================================================================

USE `sikka_pariwisata`;
SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- Kategori (§10.5 - warna pin konsisten dengan chip kategori situs)
-- ---------------------------------------------------------------------
INSERT INTO `kategori` (`id`,`nama`,`nama_en`,`slug`,`warna`,`ikon`,`urutan`) VALUES
(1,'Pantai & Bahari','Beach & Marine','pantai-bahari','#0d9488','🏖',1),
(2,'Alam & Trekking','Nature & Trekking','alam-trekking','#4d7c0f','⛰',2),
(3,'Budaya & Religi','Culture & Religion','budaya-religi','#ca8a04','🛕',3),
(4,'Buatan','Man-made','buatan','#c2410c','🏛',4),
(5,'Kuliner','Culinary','kuliner','#be123c','🍽',5),
(6,'Akomodasi','Accommodation','akomodasi','#4338ca','🛏',6);

-- ---------------------------------------------------------------------
-- 21 Kecamatan Kabupaten Sikka (§2.1)
-- Koordinat pusat = perkiraan kasar untuk fallback geolokasi (FR-MAP-06)
-- ---------------------------------------------------------------------
INSERT INTO `kecamatan` (`id`,`nama`,`slug`,`latitude`,`longitude`) VALUES
(1,'Alok','alok',-8.6199000,122.2111000),
(2,'Alok Barat','alok-barat',-8.6150000,122.1800000),
(3,'Alok Timur','alok-timur',-8.6250000,122.2400000),
(4,'Bola','bola',-8.7200000,122.3300000),
(5,'Doreng','doreng',-8.7000000,122.4000000),
(6,'Hewokloang','hewokloang',-8.6400000,122.3100000),
(7,'Kangae','kangae',-8.6500000,122.2800000),
(8,'Kewapante','kewapante',-8.6700000,122.3200000),
(9,'Koting','koting',-8.6800000,122.2200000),
(10,'Lela','lela',-8.7400000,122.2100000),
(11,'Magepanda','magepanda',-8.5800000,122.0700000),
(12,'Mapitara','mapitara',-8.7100000,122.4200000),
(13,'Mego','mego',-8.7000000,122.1300000),
(14,'Nelle','nelle',-8.6900000,122.1800000),
(15,'Nita','nita',-8.6600000,122.1600000),
(16,'Paga','paga',-8.7300000,122.0500000),
(17,'Palue','palue',-8.3200000,121.7100000),
(18,'Talibura','talibura',-8.5800000,122.5200000),
(19,'Tanawawo','tanawawo',-8.7600000,122.0800000),
(20,'Waiblama','waiblama',-8.6600000,122.4700000),
(21,'Waigete','waigete',-8.6100000,122.4300000);

-- ---------------------------------------------------------------------
-- Akun admin awal
-- Username: superadmin  |  Password: SikkaAdmin2026!
-- WAJIB DIGANTI SEGERA setelah instalasi (lihat docs/panduan-admin.md).
-- ---------------------------------------------------------------------
INSERT INTO `pengguna_admin` (`id`,`nama`,`username`,`email`,`password_hash`,`peran`,`aktif`) VALUES
(1,'Super Admin Dinas','superadmin','admin@pariwisatasikka.go.id','$2y$12$yo426PaydDHU6Qd8fWWan.//zsilsgVDkjM0LZoqglNVwvHlQe5OS','super_admin',1);

-- ---------------------------------------------------------------------
-- Destinasi awal - PRD Lampiran A
-- ---------------------------------------------------------------------
INSERT INTO `destinasi`
(`id`,`nama`,`nama_en`,`slug`,`kategori_id`,`kecamatan_id`,`latitude`,`longitude`,
 `deskripsi_singkat`,`deskripsi_singkat_en`,`deskripsi_lengkap`,
 `jam_operasional`,`kisaran_tarif`,`fasilitas`,`cara_mencapai`,
 `kontak_nama`,`kontak_telepon`,`jarak_dari_maumere_km`,`waktu_tempuh_menit`,
 `unggulan`,`status`,`sumber_data`,`perlu_verifikasi_lapangan`) VALUES

(1,'Pantai Koka','Koka Beach','pantai-koka',1,16,-8.7139000,122.1103000,
 'Pantai berpasir putih dengan dua teluk kembar yang dipisahkan tebing karang, salah satu destinasi unggulan Kabupaten Sikka.',
 'A white-sand beach with twin bays separated by a rocky headland, one of Sikka Regency''s flagship destinations.',
 'Pantai Koka terletak di Kecamatan Paga, di sisi selatan Kabupaten Sikka. Bentang pantainya terbagi dua oleh tanjung karang sehingga terlihat seperti sepasang teluk kembar. Ombak di sisi timur relatif tenang dan lebih aman untuk berenang keluarga, sementara sisi barat berombak lebih besar.\n\nDestinasi ini masuk dalam 10 objek kelas unggulan hasil studi Pusat Studi Pariwisata UGM. Fasilitas dasar sudah tersedia namun masih terbatas; disarankan membawa air minum sendiri.',
 '07.00 - 18.00 WITA','Rp5.000 - Rp10.000 per orang (retribusi lokal)',
 'Parkir, Warung, Toilet sederhana, Gazebo',
 'Dari Maumere ambil jalur selatan menuju Paga. Akses masuk terakhir berupa jalan tanah/berbatu - kendaraan roda empat rendah perlu berhati-hati terutama saat musim hujan.',
 'Pengelola Pantai Koka','',52.0,90,1,'aktif','Studi Puspar UGM 2025 - 10 destinasi unggulan',0),

(2,'Pulau Koja Doi','Koja Doi Island','pulau-koja-doi',1,3,-8.4569000,122.4386000,
 'Pulau kecil di gugusan Teluk Maumere dengan desa wisata berbasis masyarakat dan jembatan batu alami yang menjadi ikonnya.',
 'A small island in the Maumere Bay cluster, home to a community-based tourism village and its iconic natural stone bridge.',
 'Pulau Koja Doi termasuk gugusan pulau di kawasan Teluk Maumere. Pulau ini dikenal dengan desa wisata berbasis masyarakat dan formasi batu yang membentuk jembatan alami. Perjalanan ditempuh dengan perahu motor dari pelabuhan/dermaga di sekitar Maumere.\n\nMasuk dalam 10 destinasi kelas unggulan studi Puspar UGM. Waktu tempuh perahu bergantung cuaca dan gelombang - konfirmasikan ke pengelola sebelum berangkat.',
 'Menyesuaikan jadwal perahu','Biaya sewa perahu ditanggung bersama rombongan',
 'Homestay warga, Pemandu lokal, Perahu wisata',
 'Naik perahu motor dari dermaga di kawasan Maumere/Alok Timur. Sangat disarankan menghubungi pengelola desa wisata terlebih dahulu untuk mengatur perahu dan penginapan.',
 'Kelompok Sadar Wisata Koja Doi','',0.0,NULL,1,'aktif','Studi Puspar UGM 2025 - 10 destinasi unggulan',1),

(3,'Taman Wisata Perairan Teluk Maumere','Maumere Bay Marine Tourism Park','twp-teluk-maumere',1,3,-8.5333000,122.3000000,
 'Kawasan konservasi perairan dengan titik selam dan snorkeling yang menjadi daya tarik utama wisatawan mancanegara.',
 'A marine conservation area with dive and snorkelling sites that are the main draw for international visitors.',
 'Taman Wisata Perairan (TWP) Teluk Maumere adalah kawasan konservasi perairan yang mencakup gugusan pulau dan terumbu karang di Teluk Maumere. Kawasan ini menjadi tujuan utama wisatawan penyelam - salah satu persona pengguna utama platform ini.\n\nPenyelaman wajib melalui operator dive terverifikasi. Sebagai kawasan konservasi, berlaku aturan tidak merusak/mengambil biota laut.',
 'Sepanjang tahun, tergantung cuaca','Bervariasi menurut paket operator selam',
 'Operator selam, Perahu, Penyewaan alat',
 'Titik keberangkatan umumnya dari kawasan pantai utara Maumere. Hubungi operator dive terdaftar di direktori UMKM.',
 'Operator selam terdaftar','',10.0,25,1,'aktif','Studi Puspar UGM 2025 - kawasan konservasi perairan',1),

(4,'Gereja Tua Sikka','Sikka Old Church','gereja-tua-sikka',3,10,-8.7397000,122.2028000,
 'Gereja bergaya Eropa dengan ornamen motif tenun lokal, berdiri sejak 1899 dan menjadi penanda sejarah Katolik di Flores.',
 'A European-style church decorated with local weaving motifs, standing since 1899 as a landmark of Catholic history in Flores.',
 'Gereja Tua Sikka (Gereja St. Ignatius Loyola) di Desa Sikka, Kecamatan Lela, berdiri sejak 1899. Bangunannya memadukan arsitektur Eropa dengan ornamen bermotif tenun ikat khas Sikka - perpaduan yang jarang ditemukan di gereja tua lain di Flores.\n\nLokasi ini terdaftar dan terverifikasi pada Sisparnas Kemenparekraf. Berdekatan dengan Kampung Adat Sikka, sehingga keduanya lazim dikunjungi dalam satu perjalanan.',
 'Setiap hari, di luar jadwal ibadah','Gratis / sumbangan sukarela',
 'Parkir, Area ibadah, Pemandu setempat',
 'Sekitar 25 km dari Maumere menuju arah selatan/Lela. Jalan beraspal, dapat dilalui kendaraan roda empat.',
 'Pengurus Paroki Sikka','',25.0,45,1,'aktif','Sisparnas Kemenparekraf - terverifikasi',0),

(5,'Kampung Adat Sikka','Sikka Traditional Village','kampung-adat-sikka',3,10,-8.7412000,122.2045000,
 'Kampung adat pesisir yang menjadi asal-usul nama Kabupaten Sikka, dengan tradisi tenun ikat yang masih hidup.',
 'A coastal traditional village that gave Sikka Regency its name, where ikat weaving traditions remain alive.',
 'Kampung Adat Sikka adalah kampung pesisir yang menjadi asal-usul nama Kabupaten Sikka. Di kampung ini pengunjung dapat menyaksikan proses tenun ikat tradisional yang masih dikerjakan warga, serta rumah adat dan situs sejarah kerajaan Sikka.\n\nBerjarak sangat dekat dengan Gereja Tua Sikka.',
 '08.00 - 17.00 WITA','Sumbangan sukarela',
 'Pemandu lokal, Penjualan tenun ikat, Parkir',
 'Satu jalur dengan Gereja Tua Sikka di Kecamatan Lela, sekitar 25 km dari Maumere.',
 'Tetua Adat Kampung Sikka','',25.0,45,1,'aktif','Studi Puspar UGM 2025; Sisparnas',0),

(6,'Sanctuarium Wisata Maria Wisung Fatima','Maria Wisung Fatima Sanctuary','sanctuarium-maria-wisung-fatima',3,10,-8.7305000,122.2192000,
 'Situs ziarah Maria di perbukitan Lela, ramai dikunjungi peziarah terutama pada bulan Mei dan Oktober.',
 'A Marian pilgrimage site in the hills of Lela, busiest with pilgrims in May and October.',
 'Sanctuarium Maria Wisung Fatima merupakan lokasi ziarah umat Katolik di Kecamatan Lela. Kawasan ini memiliki jalur salib dan area doa terbuka dengan pemandangan ke arah laut selatan.\n\nTerdaftar pada Sisparnas Kemenparekraf dan berdekatan dengan Gereja Tua Sikka - keduanya sering dijadikan satu paket ziarah.',
 'Setiap hari 06.00 - 18.00 WITA','Gratis / sumbangan sukarela',
 'Parkir, Toilet, Area doa, Warung',
 'Searah dengan Gereja Tua Sikka, Kecamatan Lela.',
 'Pengelola Sanctuarium','',24.0,45,0,'aktif','Sisparnas Kemenparekraf',1),

(7,'Bliran Sina Watublapi','Bliran Sina Watublapi Weaving Centre','bliran-sina-watublapi',3,8,-8.6742000,122.3186000,
 'Sanggar tenun ikat Watublapi: demonstrasi proses tenun dari pemintalan, pewarnaan alami, hingga tarian penyambutan.',
 'The Watublapi ikat weaving studio: live demonstrations from spinning and natural dyeing to a welcome dance.',
 'Sanggar Bliran Sina di Watublapi, Kecamatan Kewapante, adalah salah satu sentra tenun ikat paling dikenal di Kabupaten Sikka. Pengunjung dapat menyaksikan seluruh rangkaian proses: pemintalan kapas, pewarnaan dengan bahan alami, hingga penenunan.\n\nEntri ini juga berfungsi ganda sebagai mitra UMKM kerajinan (§9.5 PRD) - kain hasil tenun dijual langsung oleh kelompok penenun.',
 '08.00 - 16.00 WITA','Sumbangan kelompok; disarankan konfirmasi untuk rombongan',
 'Demonstrasi tenun, Penjualan kain, Parkir, Toilet',
 'Sekitar 20 km arah timur Maumere melalui jalur Kewapante. Jalan beraspal.',
 'Kelompok Tenun Bliran Sina','',20.0,40,0,'aktif','Sisparnas Kemenparekraf',1),

(8,'Gunung Egon','Mount Egon','gunung-egon',2,21,-8.6758000,122.4508000,
 'Gunung berapi aktif dengan jalur pendakian pendek menuju bibir kawah dan panorama laut Flores di kedua sisi.',
 'An active volcano with a short trekking route to the crater rim and views of the Flores Sea on both sides.',
 'Gunung Egon adalah gunung berapi aktif di bagian timur Kabupaten Sikka. Jalur pendakiannya relatif pendek dibanding gunung lain di Flores, namun tetap menuntut kesiapan fisik dan pemandu lokal.\n\nPENTING: status aktivitas vulkanik dapat berubah. Selalu cek status terkini dari PVMBG/Pos Pengamatan Gunung Api sebelum mendaki. Jalur GPX akan disediakan pada Fase 2 (FR-MAP-11).',
 'Pendakian disarankan dini hari','Retribusi lokal + jasa pemandu',
 'Pemandu lokal, Titik awal pendakian',
 'Titik awal pendakian dapat dicapai dari jalur Waigete. Wajib menggunakan pemandu lokal dan memeriksa status gunung api terlebih dahulu.',
 'Pemandu lokal Waigete','',35.0,60,0,'aktif','Studi Puspar UGM 2025; liputan media lokal',1),

(9,'Pantai Kajuwulu','Kajuwulu Beach','pantai-kajuwulu',1,2,-8.5836000,122.1236000,
 'Pantai dengan bukit sabana di tepi jalan lintas utara - titik favorit menonton matahari terbenam dekat Maumere.',
 'A beach backed by savannah hills along the northern coastal road - a favourite sunset spot near Maumere.',
 'Pantai Kajuwulu (Tanjung Kajuwulu) berada di jalur lintas utara sebelah barat Maumere. Kombinasi bukit sabana, tebing, dan garis pantai membuatnya menjadi salah satu titik matahari terbenam yang paling mudah dijangkau dari pusat kota.\n\nAkses sangat mudah karena berada tepat di tepi jalan raya.',
 '24 jam (disarankan siang - sore)','Gratis (parkir sukarela)',
 'Parkir tepi jalan, Warung musiman',
 'Sekitar 12 km arah barat Maumere di jalur lintas utara menuju Magepanda. Jalan beraspal baik.',
 '','',12.0,25,0,'aktif','Sisparnas; liputan media lokal',1),

(10,'Mini Beach','Mini Beach','mini-beach',1,NULL,NULL,NULL,
 'Destinasi pantai yang masuk daftar 10 objek unggulan studi Puspar UGM. Koordinat dan detail praktis menunggu verifikasi lapangan.',
 'A beach destination listed among Puspar UGM''s ten priority sites. Coordinates and practical details await field verification.',
 'Destinasi ini tercatat sebagai salah satu dari 10 objek kelas unggulan dalam studi Pusat Studi Pariwisata UGM (2025), namun koordinat pasti, jam operasional, tarif, dan kontak pengelola belum tersedia dari sumber sekunder.\n\nTugas admin/kontak kecamatan (§14 PRD) adalah melengkapi data ini, bukan mencarinya dari nol.',
 '','','','','','',NULL,NULL,1,'draft','Studi Puspar UGM 2025 - 10 destinasi unggulan',1),

(11,'Jembatan Batu','Stone Bridge','jembatan-batu',4,NULL,NULL,NULL,
 'Formasi/struktur jembatan batu yang masuk daftar 10 objek unggulan studi Puspar UGM. Menunggu verifikasi lapangan.',
 'A stone bridge structure listed among Puspar UGM''s ten priority sites. Awaiting field verification.',
 'Tercatat sebagai destinasi unggulan dalam studi Puspar UGM (2025). Koordinat pasti dan detail praktis belum tersedia dari sumber sekunder dan menunggu verifikasi lapangan oleh Dinas.',
 '','','','','','',NULL,NULL,1,'draft','Studi Puspar UGM 2025 - 10 destinasi unggulan',1),

(12,'Air Terjun Wairhoret','Wairhoret Waterfall','air-terjun-wairhoret',2,NULL,NULL,NULL,
 'Air terjun yang terdaftar pada Sisparnas Kemenparekraf. Koordinat dan akses menunggu verifikasi lapangan.',
 'A waterfall listed on Sisparnas. Coordinates and access details await field verification.',
 'Terdaftar pada Sisparnas Kemenparekraf. Koordinat pasti, jalur akses, dan kondisi terkini perlu diverifikasi langsung oleh kontak kecamatan sebelum dipublikasikan sebagai destinasi aktif.',
 '','','','','','',NULL,NULL,0,'draft','Sisparnas Kemenparekraf',1),

(13,'Pintar Asia Beach','Pintar Asia Beach','pintar-asia-beach',1,NULL,NULL,NULL,
 'Atraksi pantai yang terdaftar pada Sisparnas. Menunggu verifikasi koordinat dan detail praktis.',
 'A beach attraction listed on Sisparnas. Awaiting verification of coordinates and practical details.',
 'Terdaftar pada Sisparnas Kemenparekraf sebagai atraksi berdekatan. Data koordinat dan informasi praktis menunggu pelengkapan oleh admin Dinas.',
 '','','','','','',NULL,NULL,0,'draft','Sisparnas Kemenparekraf',1),

(14,'Jong Dobo','Jong Dobo','jong-dobo',3,NULL,NULL,NULL,
 'Situs budaya yang terdaftar pada Sisparnas. Menunggu verifikasi koordinat dan detail praktis.',
 'A cultural site listed on Sisparnas. Awaiting verification of coordinates and practical details.',
 'Terdaftar pada Sisparnas Kemenparekraf. Kategori sementara diklasifikasikan sebagai budaya; klasifikasi final, koordinat, dan detail praktis menunggu verifikasi Dinas Kebudayaan dan kontak kecamatan.',
 '','','','','','',NULL,NULL,0,'draft','Sisparnas Kemenparekraf',1);

-- ---------------------------------------------------------------------
-- UMKM contoh (FR-UMKM-02: terhubung ke destinasi terdekat)
-- ---------------------------------------------------------------------
INSERT INTO `umkm` (`nama`,`slug`,`jenis`,`deskripsi`,`alamat`,`kontak_telepon`,`kontak_wa`,`kecamatan_id`,`destinasi_terdekat_id`,`status_verifikasi`) VALUES
('Kelompok Tenun Bliran Sina','tenun-bliran-sina','kerajinan','Kelompok penenun ikat Watublapi. Menjual kain tenun ikat pewarna alami langsung dari penenun.','Watublapi, Kecamatan Kewapante','','',8,7,'terverifikasi'),
('Warung Pantai Koka','warung-pantai-koka','kuliner','Warung sederhana di area Pantai Koka. Menyediakan kelapa muda, mi instan, dan makanan ringan.','Area Pantai Koka, Kecamatan Paga','','',16,1,'menunggu'),
('Homestay Desa Wisata Koja Doi','homestay-koja-doi','penginapan','Penginapan rumah warga di Pulau Koja Doi, dikelola kelompok sadar wisata setempat.','Pulau Koja Doi','','',3,2,'menunggu');

-- ---------------------------------------------------------------------
-- Event contoh (§9.4) - tanggal wajib diverifikasi Dinas sebelum publish
-- ---------------------------------------------------------------------
INSERT INTO `event` (`nama`,`slug`,`tanggal_mulai`,`tanggal_selesai`,`lokasi_teks`,`destinasi_terkait_id`,`deskripsi`,`status`) VALUES
('Semana Santa Larantuka - Rangkaian Pekan Suci Flores','semana-santa-pekan-suci','2027-03-25','2027-03-27','Wilayah Flores Timur (rujukan lintas kabupaten)',NULL,'Rangkaian prosesi Pekan Suci yang menjadi magnet wisata religi di Flores. Dicantumkan sebagai rujukan narasi Flores bersama (§2.3 poin 4 PRD). Tanggal mengikuti kalender liturgi dan WAJIB dikonfirmasi Dinas setiap tahun.','draft'),
('Prosesi Jumat Agung Gereja Tua Sikka','prosesi-jumat-agung-sikka','2027-03-26',NULL,'Gereja Tua Sikka, Kecamatan Lela',4,'Prosesi Jumat Agung di Gereja Tua Sikka. Tanggal mengikuti kalender liturgi - konfirmasi ke Paroki Sikka sebelum dipublikasikan.','draft');

-- ---------------------------------------------------------------------
-- Pengaturan situs (pola key-value §11)
-- ---------------------------------------------------------------------
INSERT INTO `pengaturan` (`kunci`,`nilai`,`keterangan`) VALUES
('nama_situs','Pariwisata Kabupaten Sikka','Nama situs di header & meta title'),
('tagline','Peta & Informasi Resmi Wisata Kabupaten Sikka','Tagline beranda'),
('tagline_en','Official Tourism Map & Information of Sikka Regency','Tagline bahasa Inggris'),
('instansi','Dinas Pariwisata Kabupaten Sikka','Nama instansi pengelola'),
('alamat_instansi','Maumere, Kabupaten Sikka, Nusa Tenggara Timur','Alamat kantor'),
('email_instansi','','Email resmi Dinas'),
('telepon_instansi','','Telepon kantor'),
('wa_notifikasi','','Nomor WhatsApp tujuan notifikasi pengaduan (format 62xxx). Kosongkan untuk menonaktifkan.'),
('wa_gateway_url','','Endpoint gateway WA opsional (§13.1). Kosong = fallback ke wa.me/log.'),
('wa_gateway_token','','Token gateway WA opsional'),
('running_text','Selamat datang di portal resmi pariwisata Kabupaten Sikka','Teks berjalan beranda'),
('instagram','disparbudkabsikka','Username Instagram resmi'),
('peta_lat_awal','-8.6199','Titik tengah peta saat dibuka'),
('peta_lng_awal','122.2111','Titik tengah peta saat dibuka'),
('peta_zoom_awal','10','Level zoom awal peta'),
('link_ppid','','URL PPID Kabupaten Sikka (FR-SVC-02)'),
('link_oss','https://oss.go.id','URL layanan perizinan OSS'),
('link_dpmptsp','','URL DPMPTSP Kabupaten Sikka'),
('hak_cipta','Karunia Bunda IT Training Center Maumere','Pemegang hak cipta sistem');
