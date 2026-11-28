# Rancangan: Supabase Realtime, Authentication, dan Tampilan Baru Pendaftaran Tiket Konser

Tanggal: 28 November 2026
Status: menunggu persetujuan

## 1. Tujuan

Mempraktikkan materi kelas (repo `liz7124/supabase-nodejs`, branch `practice5-authentication`, yang sudah mencakup realtime) di project tiket konser:

1. Data disimpan di database Supabase.
2. Sisa tiket berubah di browser tanpa refresh.
3. Daftar, login, dan logout memakai Supabase Auth.
4. Tamu boleh melihat website, tetapi harus login untuk memesan.
5. User bisa melihat riwayat pesanannya.
6. Tampilan semua halaman dirapikan dengan Impeccable.

Prinsipnya sederhana: tidak ada fitur di luar daftar ini tanpa persetujuanmu.

Yang dipindahkan adalah konsepnya. Project tetap Laravel.

## 2. Keputusan

**Kamu putuskan:**

- login memakai Supabase Auth, hanya dengan email dan password;
- register berisi nama lengkap, nomor telepon, email, password, dan konfirmasi password;
- tamu boleh masuk website, memesan harus login;
- ada menu riwayat pembelian;
- tampilan dibagusin dengan Impeccable, sisanya dibuat sederhana.

**Usulanku (koreksi kalau tidak setuju):**

| No | Usulan | Alasan |
|----|--------|--------|
| U1 | Supabase memeriksa akun, sesi login dipegang Laravel | Halaman Blade butuh sesi di server |
| U2 | Nama, email, dan telepon pemesan diisi dari akun | Tidak perlu mengetik ulang, dan menutup celah ganti nama akun orang lain |
| U3 | Browser berlangganan realtime langsung ke Supabase, tanpa `/events` | Di PHP, tiap koneksi SSE menahan satu proses server |
| U4 | Reset password, verifikasi email Laravel, 2FA, passkey, ubah profil, ganti password, dan hapus akun dimatikan | Semuanya hanya mengubah data lokal atau melewati Supabase |
| U5 | Form beli di halaman detail diganti tombol ke form di halaman utama | Form detail sekarang rusak; cukup satu form yang dirawat |
| U6 | Setelah login, kembali ke halaman utama; Dashboard dan Settings dihapus | Dashboard kosong, dan Settings tidak punya isi yang dipakai |
| U7 | Identitas marun-emas halaman utama dipertahankan dan dipakai di semua halaman | Sekarang ada beberapa gaya berbeda; login dan register masih berlogo Laravel |
| U8 | Kolom Notes dihapus dari form | Tidak diperlukan untuk memesan; kolom database tetap ada |
| U9 | Angka "100K+ fans" dan "rating 5/5" dihapus | Tidak ada datanya, dan total tiket hanya 100 |
| U10 | "Remember me" dihapus | Login hanya email dan password |
| U11 | Status pesanan tidak ditampilkan di My Tickets | Semua tersimpan `pending` dan belum ada fitur pembayaran |

Kalau dosen mewajibkan realtime lewat `/events`, U3 harus dirancang ulang.

## 3. Pendekatan yang dipertimbangkan

| Pendekatan | Untung | Rugi | Hasil |
|------------|--------|------|-------|
| A. Supabase Auth + sesi Laravel | Akun muncul di menu Authentication; halaman login/register tetap dipakai | Beberapa fitur Fortify dimatikan | **Dipilih** |
| B. Fortify seperti sekarang, database pindah ke Supabase | Perubahan paling sedikit | Tidak memakai Supabase Auth | Tidak dipilih |
| C. Tulis ulang dengan Node/Express seperti repo kelas | Paling mirip materi | Membuang project dan alur deploy yang sudah ada | Tidak diusulkan |

Menyimpan token di `localStorage` seperti repo kelas juga tidak dipakai. Halaman Blade tidak bisa membacanya saat dibuat di server, dan token bisa dicuri lewat XSS.

## 4. Bukan bagian pekerjaan ini

- Login Google atau media sosial, OTP.
- Halaman admin.
- Pembayaran, e-ticket atau QR code, pembatalan pesanan, nama dan NIK per tiket.
- Memindahkan data SQLite lama.
- Mengganti identitas visual marun-emas.
- Reset password (lihat bagian 8).
- Memperbaiki data konser cadangan palsu di `ConcertController::index`.

Dianggap gagal kalau: data pembeli bisa dibaca lewat API Supabase, orang bisa membeli atas nama akun lain, atau push ke `main` merusak website yang sedang jalan.

## 5. Rancangan

Urutan pengerjaan mengikuti nomor tahap. Setiap tahap punya cara cek dan harus lolos sebelum lanjut.

### Tahap 1: Database Supabase

**Kode**

- `.env.example`: tambah contoh variabel PostgreSQL (dikomentari), `SUPABASE_URL`, `SUPABASE_KEY`, `VITE_SUPABASE_URL`, `VITE_SUPABASE_KEY`. Default tetap SQLite supaya CI tidak berubah.
- Migration baru: `concerts.price` dan `ticket_purchases.total_price` menjadi `decimal(12,2)`. Batas sekarang 999.999,99, padahal harga tiket sudah di atas 1 juta. PostgreSQL akan menolak.
- Migration baru, hanya berjalan di PostgreSQL:
  - aktifkan RLS di semua tabel pada schema `public`, termasuk `users`, `sessions`, dan `ticket_purchases`;
  - beri role `anon` izin `SELECT` dan policy baca hanya untuk `concerts`.
- Laravel tersambung sebagai pemilik tabel, jadi tidak terhalang RLS.
- Tabel yang dibuat setelah ini harus mengaktifkan RLS di migration-nya sendiri.

**Kamu kerjakan:** buat project Supabase (region Singapore), lalu isi `.env` dengan data **Session pooler** (port 5432), `DB_CONNECTION=pgsql`, dan `DB_SSLMODE=require`.

**Cek:** `php artisan migrate --seed` berhasil; halaman utama terbuka; permintaan baca `ticket_purchases` dan `users` dengan anon key tidak mengembalikan data; baca `concerts` berhasil.

### Tahap 2: Realtime

**Kode**

- Tambah package `@supabase/supabase-js`.
- File baru `resources/js/realtime.js`: berlangganan `postgres_changes` event `UPDATE` pada tabel `concerts`, lalu meneruskan `available_tickets` ke halaman lewat event browser.
- Daftarkan sebagai input terpisah di `vite.config.ts` dan muat hanya di halaman utama dan detail konser.
- `welcome.blade.php`: `availableTickets` bisa berubah; label, batas jumlah, total harga, dan tombol ikut diperbarui. Saat sisa 0, tombol nonaktif.
- `show.blade.php`: angka sisa tiket dan status habis ikut diperbarui.
- Kalau variabel `VITE_SUPABASE_*` kosong atau koneksi gagal, halaman tetap jalan tanpa pembaruan otomatis.

Validasi stok di server (`lockForUpdate`) tetap jadi penentu. Realtime hanya memperbarui tampilan.

**Kamu kerjakan:** aktifkan Realtime di dashboard hanya untuk tabel `concerts`.

**Cek:** buka dua jendela, ubah `available_tickets` di Table Editor, angka di keduanya berubah tanpa refresh.

### Tahap 3: Authentication

**Kode**

- `config/services.php`: bagian `supabase` (URL dan key). Kode aplikasi membaca lewat `config()`, bukan `env()`, karena deploy menjalankan `config:cache`.
- `app/Services/SupabaseAuth.php` (baru), memakai `Http` bawaan Laravel:
  - daftar: `POST /auth/v1/signup`, mengirim `display_name` dan alamat redirect ke halaman login;
  - login: `POST /auth/v1/token?grant_type=password`;
  - setelah password terbukti benar, sesi di Supabase langsung dicabut (`/auth/v1/logout?scope=local`). Token tidak disimpan karena sesi dipegang Laravel.
- Migration baru: `users.supabase_id` (boleh kosong, unik) dan `users.phone` (boleh kosong, maksimal 30 karakter).
- Login (`Fortify::authenticateUsing`):
  1. tanya Supabase;
  2. cari user lokal berdasarkan `supabase_id`, lalu berdasarkan email; buat kalau belum ada;
  3. perbarui `supabase_id`, nama dari `display_name`, telepon dari `phone`, dan `email_verified_at`.
- Register (`CreateNewUser`):
  - field: nama lengkap, nomor telepon, email, password, konfirmasi password; semuanya wajib;
  - daftar ke Supabase, nama dan telepon dikirim sebagai data user (`display_name`, `phone`); nomor tidak diverifikasi lewat SMS;
  - buat user lokal hanya kalau email belum ada, dengan password acak; baris yang sudah ada **tidak diubah**;
  - aturan "email harus unik" tidak dipakai saat register, supaya pembeli lama tetap bisa daftar.
- Fortify otomatis membuat user login setelah register. Ganti `RegisterResponse`: logout, lalu ke halaman login dengan pesan "Cek email untuk konfirmasi". `Login.vue` sudah bisa menampilkan pesan itu. Bagian ini penting untuk keamanan: kalau email sudah ada di tabel lokal, Fortify sempat login sebagai akun tersebut. Wajib ada test bahwa register memakai email akun lama berakhir tanpa sesi login, termasuk saat request menyertakan `remember`.
- `User.php`: lepas `MustVerifyEmail`. Tanpa ini Laravel mengirim email verifikasi sendiri.
- `config/fortify.php`: sisakan fitur registrasi saja; `home` menjadi `/`.
- Frontend:
  - `Login.vue`: hanya email dan password, tanpa passkey, lupa password, dan "Remember me";
  - `Register.vue`: tambah nomor telepon; `ConfirmPassword.vue`: hapus passkey;
  - hapus halaman dan komponen milik fitur yang dimatikan;
  - hapus Dashboard dan Settings: route, halaman, layout, komponen, controller, dan request yang hanya dipakai di sana, termasuk `.well-known/passkey-endpoints`;
  - `welcome.blade.php`: tambah Login dan Daftar untuk tamu, nama user dan Logout untuk yang sudah login.
- Kolom database untuk 2FA dan passkey dibiarkan.

**Pesan error**

| Keadaan | Yang dilihat user |
|---------|-------------------|
| Password salah | pesan gagal login bawaan |
| Email belum dikonfirmasi | diminta mengonfirmasi email dulu |
| Supabase menolak pendaftaran (password lemah, terlalu sering) | pesan penolakan di form register |
| Supabase tidak bisa dihubungi atau belum dikonfigurasi | "Layanan login sedang tidak bisa dihubungi" |

Email yang sudah terdaftar bisa mendapat respons yang terlihat berhasil dari Supabase. Itu disengaja Supabase, dan karena baris lokal tidak diubah, tidak berbahaya.

**Kamu kerjakan:** aktifkan provider Email dan **Confirm email**; isi Site URL dan Redirect URL (`http://localhost:8000/login` dan `https://pendaftaranticketkonser.fsd.my.id/login`). Pasang SMTP sendiri kalau mau mendaftar dengan email di luar anggota tim project.

> **Confirm email wajib aktif.** Akun lokal disambungkan lewat email. Kalau konfirmasi mati, orang bisa mendaftar memakai email orang lain dan mengambil alih akunnya.

**Cek:** daftar, akun muncul di menu Authentication; login sebelum konfirmasi ditolak; setelah konfirmasi berhasil; logout berhasil.

### Tahap 4: Pembelian tiket

**Kode**

- Route `concerts.store` memakai middleware `auth`.
- `RegistrationController`: hapus input `name` dan `email` serta `User::firstOrNew`; pakai akun yang sedang login. Telepon dan catatan tetap dari form. Email konfirmasi booking tetap dikirim lewat Laravel.
- Form pemesanan di `welcome.blade.php`:
  - tamu tetap bisa memilih kursi dan jumlah serta melihat total; bagian data pemesan diganti ajakan login dan tombol "Log in to book";
  - user login melihat ringkasan **Buyer details**: nama dan email dari akun (tidak bisa diubah), nomor telepon terisi otomatis tapi boleh diganti untuk pesanan ini; kalau akunnya belum punya nomor, kolomnya kosong dan wajib diisi;
  - JavaScript halaman harus tetap jalan walau tombol submit tidak ada.
- Daftar harga kursi disimpan di satu tempat dan dipakai server maupun halaman. "Starting from" di daftar dan detail konser memakai harga termurah (Rp 1.000.000); sekarang tertulis Rp 1.500.000.
- `show.blade.php`: form diganti tombol ke `/#booking`.

**Cek:** tamu tidak bisa mengirim pembelian; pembelian user login tercatat atas akunnya; mengirim email lain lewat request tidak berpengaruh.

### Tahap 5: My Tickets (riwayat pembelian)

**Kode**

- Halaman Blade `/my-tickets` dengan middleware `auth`.
- Menu "My Tickets" muncul di navigasi hanya untuk user login.
- Isinya pesanan milik user yang sedang login, terbaru di atas: kode booking, nama konser, tanggal, tempat, jenis kursi, jumlah, total harga, dan waktu pesan.
- Kalau belum ada pesanan: keterangan singkat dan tombol ke form pemesanan.
- Setelah berhasil memesan, pesan sukses menyertakan link ke My Tickets.

**Cek:** pesanan baru muncul; akun lain tidak bisa melihatnya; tamu diarahkan ke login.

### Tahap 6: Tampilan (Impeccable)

Tujuannya merapikan, bukan mengganti identitas.

- Warna marun-emas dan judul tebal dari halaman utama dipakai di daftar konser, detail, My Tickets, login, dan register.
- Satu komponen navigasi untuk semua halaman Blade. Tamu: Home, Log in, Sign up. User login: Home, My Tickets, nama, Log out. Layout login dan register memakai logo dan warna yang sama.
- Form pemesanan: urutan kursi → jumlah → data pemesan → total dan tombol. Setiap input punya label yang terlihat, bukan hanya placeholder.
- Teks abu-abu yang pudar dinaikkan kontrasnya. Semua halaman dicek di lebar HP (390 px) dan desktop (1440 px).
- Teks website tetap bahasa Inggris.

Aturan craft Impeccable dibaca sebelum mengubah UI. Setelah selesai, halaman dipotret ulang dan detector Impeccable dijalankan.

### Tahap 7: Test dan deploy

**Test** (SQLite, respons Supabase dipalsukan dengan `Http::fake`, tanpa akses ke Supabase asli)

- My Tickets: tamu diarahkan ke login; user hanya melihat pesanannya sendiri.
- `DashboardTest`: dihapus bersama Dashboard.
- `ConcertPurchaseTest`: pembelian memakai user login; tambah test tamu ditolak dan email dari request diabaikan; hapus test yang mengharapkan nama akun bisa diganti.
- `AuthenticationTest` dan `RegistrationTest`: sesuaikan dengan alur baru, termasuk email belum dikonfirmasi, Supabase tidak bisa dihubungi, dan register dengan email lama.
- Hapus `ProfileUpdateTest` dan `SecurityTest` karena fiturnya dihapus. Test fitur Fortify yang dimatikan akan dilewati otomatis; yang tidak punya pengecekan itu disesuaikan.

**Cek:** `php artisan test` dan `npm run build` lolos.

**Deploy** (`.github/workflows/deploy.yaml`)

- `public/build` ikut dikirim ke server. Sekarang hanya `vendor` yang dikirim.
- `VITE_SUPABASE_URL` dan `VITE_SUPABASE_KEY` diberikan ke langkah build lewat GitHub repository variables.

**Sebelum digabung ke `main`** (push ke `main` langsung deploy), kamu pastikan:

1. `.env` server sudah berisi data database dan Supabase.
2. PHP di server punya ekstensi `pdo_pgsql`.
3. GitHub variables sudah diisi.
4. Data di database produksi sekarang boleh ditinggalkan, atau dicadangkan dulu. Data itu tidak ikut pindah.
5. Sertifikat HTTPS website sudah valid. Saat dicek 28 November 2026 sertifikatnya self-signed, sehingga browser memberi peringatan. Pasang sertifikat gratis (Let's Encrypt) dari panel hosting.

Pekerjaan dilakukan di branch terpisah, bukan langsung di `main`.

## 6. Batasan

- Anon/publishable key boleh ada di browser. **Service role key dan password database tidak boleh** masuk Git atau variabel `VITE_`.
- Isi `.env` dan pengaturan dashboard Supabase dikerjakan olehmu.
- Satu-satunya package baru: `@supabase/supabase-js`.

## 7. Diserahkan ke tahap pengerjaan

Nama method dan class, susunan test, teks pesan, serta detail SQL migration. Bentuk respons Supabase Auth (termasuk kode errornya) dicek ke dokumentasi resmi sebelum kodenya ditulis.

## 8. Menunggu keputusanmu

**Reset password.** Tanpa fitur ini, user yang lupa password tidak bisa masuk lagi dari website. Usulanku: tunda, karena tidak diminta dan materi kelas juga tidak memakainya. Selama ditunda, password diatur ulang lewat dashboard Supabase.

## 9. Bagianmu dan kapan dibutuhkan

| Kapan | Yang kamu kerjakan |
|-------|--------------------|
| Sebelum tahap 1 | Buat project Supabase (region Singapore), simpan password database, isi sendiri `.env` |
| Tahap 2 | Aktifkan Realtime untuk tabel `concerts` |
| Tahap 3 | Aktifkan Email dan Confirm email, isi URL Configuration, pasang SMTP bila perlu, klik link konfirmasi saat tes daftar |
| Tahap 6 | Lihat hasil tampilan dan beri komentar |
| Sebelum ke `main` | Siapkan server, GitHub variables, dan sertifikat (daftar di tahap 7) |

Setiap sampai di titik itu, pekerjaan berhenti dan kamu diberi langkahnya. Jangan kirim password atau key lewat chat.

