# Plan B (multi-tenant) — uji di staging

Semua perubahan ini HANYA untuk `staging.vortexsign.web.id`. Jangan unggah ke produksi sebelum lolos checklist di bagian 4.

## 1. Urutan pasang (tanpa terminal)
1. **Backup** database staging (phpMyAdmin > Export) dan folder `staging.vortexsign.web.id/signage`.
2. phpMyAdmin > database `novasgn_staging_database` > tab SQL: jalankan `sql/01_multitenant_staging.sql`.
   - Cek `SHOW COLUMNS FROM users LIKE 'role';` lalu jalankan baris ENUM bila perlu, dan jadikan satu akun `superadmin` (langkah 4 di file SQL).
3. `.env` staging, tambahkan baris:
   `MEDIA_PATH=/home/novasgn/domains/staging.vortexsign.web.id/public_html/media`
4. Upload file kode (struktur folder sama dengan proyek) ke `staging.vortexsign.web.id/signage/`:
   `app/Support/Tenant.php`, `app/Models/Concerns/BelongsToClient.php`, `app/Models/Client.php`, `app/Models/{Toko,DigitalSignage,FileIklan,JadwalTayang,DistribusiJadwal,User}.php`, `app/Http/Middleware/EnsureSuperAdmin.php`, `app/Http/Kernel.php`, `app/Http/Controllers/{ClientController,AuthController,DashboardController,FileIklanController,TokoController,DigitalSignageController,DigitalSignageDeviceController,JadwalTayangController,DistribusiJadwalController}.php`, `app/Http/Controllers/Api/VideoController.php`, `config/media.php`, `routes/web.php`, `resources/views/clients/*`, `resources/views/layouts/app.blade.php`.
5. Hapus isi `storage/framework/views` dan `bootstrap/cache/config.php` (bila ada) agar perubahan terbaca.
6. Buat folder `staging.vortexsign.web.id/public_html/media/iklan` (izin 775) bila belum ada.

## 2. Cara kerja singkat
- Tiap user punya `client_id`. Data (toko, signage, file iklan, jadwal, distribusi) otomatis disaring ke client user yang login dan `client_id` terisi otomatis saat membuat data. User tanpa `client_id` tidak melihat data apa pun.
- `superadmin` melihat menu **Client** (buat client + akun admin pertama, nonaktifkan client). Superadmin sendiri ada di client 1, jadi dashboard-nya tetap data client default.
- Perangkat TV tetap satu alamat API. Perangkat baru mendaftar tanpa client (`client_id` kosong, "MENUNGGU AKTIVASI"). Admin client mengaktifkannya lewat **Digital Signage > Tambah** dengan mengetik Device ID yang tampil di layar. Saat itu perangkat "diklaim" ke client tersebut dengan token yang sama.
- API `/video/{id}` hanya melayani video milik client si perangkat.
- Video client selain id 1 disimpan di `media/{client_id}/iklan/`; client 1 tetap `media/iklan/`.
- Perangkat yang belum diklaim tidak muncul di daftar mana pun (bukan bug): klaim hanya lewat input manual Device ID.

## 3. Yang sengaja BELUM dikerjakan
- Halaman edit/hapus user dan reset password client (sementara lewat SQL).
- Penegakan role `operator`/`viewer` (masih akses penuh dalam client-nya).
- `model_signage` tetap data bersama semua client.
- `/link-storage` publik dan path `API_BASE` TV (lihat rancangan bagian 9).

## 4. Checklist uji (staging)
**A. Data lama utuh (client 1)**
- [ ] Login akun superadmin: dashboard, toko, signage, file iklan, jadwal, distribusi tampil seperti sebelum migrasi (jumlahnya sama).
- [ ] Bandingkan JSON `GET /api/device/{id}/jadwal` sebelum/sesudah: bentuknya identik.
- [ ] Menu **Client** muncul hanya untuk superadmin.

**B. Client baru kosong**
- [ ] Menu Client > Tambah Client (kode `uji2`) + akun admin. Logout, login sebagai admin `uji2`.
- [ ] Dashboard semua 0; daftar toko/signage/file/jadwal/distribusi kosong; menu Client tidak ada; buka `/clients` langsung = 403.

**C. Isolasi data**
- [ ] Sebagai admin `uji2`: buat toko, upload video, buat jadwal, tambah signage dengan Device ID baru, buat distribusi.
- [ ] Login ulang sebagai client 1: tidak ada satu pun data `uji2`.
- [ ] Sebagai `uji2`, buka URL edit milik client 1 (mis. `/toko/1/edit`, `/jadwal/1/edit`, `/distribusi/1/edit`) = 404.
- [ ] Buat toko dengan `kode_toko` yang sama di dua client = diizinkan (setelah langkah 5 di SQL); Device ID sama di dua client = ditolak.
- [ ] Distribusi: pilih lokasi hanya menampilkan signage client sendiri.

**D. Perangkat**
- [ ] Layar/simulasi baru mendaftar (`POST /api/device/register`): `client_id` kosong di tabel.
- [ ] Admin `uji2` Tambah Signage dengan Device ID itu: aktif, `client_id` = `uji2`, token tidak berubah, TV mengambil jadwal.
- [ ] TV `uji2` memanggil `/api/video/{id}/download` untuk video client 1 = ditolak/404; video client sendiri = berhasil.
- [ ] Video yang diunggah `uji2` ada di `public_html/media/2/iklan/` (id client sesuai).

**E. Nonaktif**
- [ ] Nonaktifkan client `uji2`: adminnya tidak bisa login. Aktifkan lagi: bisa.

Jika semua centang, lanjut: rencana deploy produksi dengan checklist 12.1 di rancangan (backup, maintenance mode, SQL, bersihkan cache).
