# Panduan Pengguna (User Guide) - CWA (Credit Worthiness Assessment)

Selamat datang di Panduan Penggunaan Aplikasi **CWA (Credit Worthiness Assessment)**. Aplikasi ini dapat diakses melalui tautan [https://cwa.pipk.my.id/](https://cwa.pipk.my.id/) dan dirancang untuk membantu dalam proses pencatatan, pengelolaan, dan penilaian kapasitas atau kelayakan keuangan entitas Pemerintah Daerah (Pemda).

---

## Daftar Isi
1. [Memulai Penggunaan](#1-memulai-penggunaan)
2. [Dashboard](#2-dashboard)
3. [Manajemen Akses (Administrator)](#3-manajemen-akses-administrator)
4. [Data Referensi](#4-data-referensi)
5. [Proses Penilaian (Assessments)](#5-proses-penilaian-assessments)
   - [Detail Aspek Penilaian](#detail-aspek-penilaian)
6. [Upload Data Masal](#6-upload-data-masal)
7. [Manual Aplikasi](#7-manual-aplikasi)

---

## 1. Memulai Penggunaan

Untuk mulai menggunakan aplikasi:
1. Buka browser web Anda dan arahkan ke URL: [https://cwa.pipk.my.id/](https://cwa.pipk.my.id/).
2. Anda akan dihadapkan pada halaman login (atau *Welcome Page*).
3. Masukkan kredensial (Email/Username dan Password) yang telah diberikan oleh Administrator sistem Anda.
4. Klik tombol **Login**. 

*(Catatan: Fitur pendaftaran/register mandiri mungkin dinonaktifkan oleh administrator untuk alasan keamanan. Hubungi Admin jika Anda tidak memiliki akun.)*

---

## 2. Dashboard

Setelah berhasil login, halaman pertama yang Anda lihat adalah **Dashboard**.
- Dashboard berfungsi sebagai pusat informasi (ringkasan) dari seluruh aktivitas Anda di dalam aplikasi.
- Bergantung pada hak akses pengguna (*roles*), Anda mungkin melihat statistik Pemda, progres penilaian, atau ringkasan metrik kelayakan keuangan secara visual.

---

## 3. Manajemen Akses (Administrator)

Menu ini khusus bagi pengguna dengan peran (role) **Administrator** atau yang memiliki hak akses (*permissions*) yang sesuai untuk mengelola sistem.

- **Manajemen Pengguna (Users):**
  - **Melihat Data:** Melihat daftar seluruh pengguna aplikasi.
  - **Tambah/Edit/Hapus:** Mengelola akun staf dan pengguna aplikasi.
  - **Import CSV:** Anda dapat menambahkan banyak pengguna sekaligus dengan mengunduh template (`Download Template`) lalu mengunggahnya (Import CSV).
- **Manajemen Peran (Roles):** Membuat profil otorisasi tertentu (contoh: Admin, Assessor, Viewer) agar fungsi aplikasi lebih terstruktur.
- **Hak Akses (Permissions):** Melihat daftar aturan akses secara spesifik dan menautkannya ke Peran (*Roles*) yang telah dibuat.

---

## 4. Data Referensi

Sebelum melakukan penilaian kelayakan kredit/keuangan, Anda perlu menyiapkan data entitas.

### a. Pemerintah Daerah (Govs)
Menu **Govs** (Pemerintah Daerah) merupakan data referensi tingkat teratas.
- **Daftar Pemda:** Melihat daftar provinsi, kabupaten, atau kota yang akan dinilai.
- **Aksi:** Anda dapat menambah, mengedit profil, serta menghapus data Pemda.

### b. Entitas Penilaian (Assessees)
Menu **Assessees** mengatur unit-unit spesifik yang akan dinilai dari suatu Pemda (tergantung dari struktur penilaian yang diterapkan). 
- Anda dapat menautkan entitas-entitas ini ke Pemda tertentu pada tahap awal sebelum beralih ke pembuatan modul Penilaian (Assessment).

---

## 5. Proses Penilaian (Assessments)

Modul ini adalah fitur inti dari aplikasi **CWA**. Di sinilah Anda melakukan analisis dan pengukuran terhadap kapasitas kelayakan Pemda.

1. Buka menu **Assessments**.
2. Anda dapat membuat sesi penilaian baru atau melanjutkan penilaian yang sedang berjalan.
3. Klik pada baris/kartu assessment tertentu untuk masuk ke mode edit/detail penilaian.

### Detail Aspek Penilaian
Di dalam satu Assessment, terdapat pengelompokkan sub-modul (indikator) untuk mempermudah penilaian secara tematik:

- 🏗️ **Infrastructure (Infrastruktur):** Menilai aspek fasilitas dan kesiapan infrastruktur dari entitas.
- 💰 **Budget Real (Realisasi Anggaran):** Menginput dan melihat pencapaian dan laporan realisasi anggaran.
- 📈 **Economy Condition (Kondisi Ekonomi):** Mengisi data PDRB sektoral dan indikator makroekonomi daerah.
- 🏦 **Financial Condition (Kondisi Keuangan):** Input rasio keuangan serta kapasitas fiskal daerah.
- 💳 **Debt Service (Layanan Utang):** Menginput atau meninjau daftar dan status layanan pinjaman/utang saat ini.
- ⚖️ **DSCR (Debt Service Coverage Ratio):** Area spesifik untuk menghitung rasio kecukupan arus kas dalam menutupi kewajiban pinjaman/utang.
- ⭐ **Indicative Rating (Rating Indikatif):** Fitur ini akan menarik nilai/ringkasan kalkulasi dan menyajikan proyeksi rating kelayakan entitas.
- 📝 **Action Plan (Rencana Aksi):** Mengelola catatan, rencana tindak lanjut (Action Plan), serta penyelesaian *challenge* (tantangan) dari hasil assessment.

Setiap formulir di atas dirancang berurutan. Pastikan Anda mengklik tombol **Simpan (Save/Update)** di setiap tab agar perubahan tidak hilang.

---

## 6. Upload Data Masal

Jika Anda memiliki data finansial (seperti Realisasi Anggaran, DSCR, atau Kondisi Ekonomi) dalam format Excel/CSV yang ekstensif, Anda tidak perlu menginputnya satu persatu.

1. Buka menu **Upload Data**.
2. Anda akan disajikan opsi kategori data yang ingin diunggah (misal: Data Pemda, PDRB Sektoral, Indikator Ekonomi, dll.).
3. **Penting:** Selalu mulai dengan mengklik **Download Template** pada kategori yang sesuai agar format tabel yang Anda unggah cocok dengan sistem.
4. Isi template tersebut menggunakan aplikasi *spreadsheet* (Excel, dsb.).
5. Unggah kembali file Anda melalui halaman ini. Sistem akan otomatis memvalidasi dan memproses datanya (*Limit: 10 upload per menit*).

---

## 7. Manual Aplikasi

Jika Anda lupa atau bingung mengenai suatu fitur, aplikasi ini juga menyediakan dokumen manual resmi yang disematkan (*embedded*):
- Cukup akses menu **Manual** di panel samping (Sidebar).
- Sebuah dokumen panduan interaktif (terhubung ke modul Google Drive) akan langsung dimuat di dalam aplikasi. Anda bisa membaca panduan teknis yang lebih detail di sana.

---
> **Bantuan Lebih Lanjut**
> *Jika terjadi kendala teknis (error/bug) atau Anda lupa kata sandi yang tidak bisa diakses mandiri, silakan hubungi tim Administrator CWA.*
