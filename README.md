# Sistem Pelacakan Alumni
Sistem Pelacakan Alumni merupakan aplikasi web yang membantu admin kampus melakukan pelacakan alumni melalui berbagai sumber publik.

## Identitas
Nama: Muh Wahyudi Akil Somar  
NIM: 202010370311114  
Kelas: Rekayasa Kebutuhan B  

## Deskripsi
Sistem digunakan untuk mengelola data alumni, membuat query pencarian, mencatat kandidat hasil pelacakan, menghitung confidence score, menetapkan status alumni, dan menyimpan jejak bukti.

Sumber pencarian yang tersedia meliputi:
- Google Web
- LinkedIn
- GitHub
- Google Scholar
- ResearchGate
- ORCID
- Instagram
- Facebook
Sistem tidak mengambil data dari media sosial secara otomatis. Admin membuka tautan pencarian, memeriksa informasi yang ditemukan, lalu mencatat hasilnya ke dalam aplikasi.

## Fitur
Fitur yang tersedia pada aplikasi:
- Login dan logout admin
- Dashboard statistik alumni
- Tambah data alumni
- Lihat detail alumni
- Edit data alumni
- Hapus data alumni
- Pencarian dan filter alumni
- Import data CSV atau Excel
- Pemilihan sumber pelacakan
- Pengaturan prioritas sumber
- Pembuatan query pencarian
- Penyimpanan riwayat query
- Pencatatan hasil pelacakan
- Perhitungan confidence score
- Penetapan status alumni
- Penyimpanan jejak bukti
- Scheduler pembuatan query
- Pengaturan profil admin
- Pengubahan kata sandi

## Alur Penggunaan
1. Admin memasukkan data alumni.
2. Sistem menyimpan data sebagai profil target pencarian.
3. Admin memilih sumber pencarian.
4. Sistem membuat dan menyimpan query.
5. Admin membuka tautan pencarian.
6. Admin mencatat kandidat yang ditemukan.
7. Admin menilai kecocokan identitas kandidat.
8. Sistem menghitung confidence score.
9. Admin menetapkan status alumni.
10. Sistem menyimpan hasil sebagai jejak bukti.

## Confidence Score
Confidence score dihitung berdasarkan empat sinyal identitas.
| Sinyal identitas | Bobot |
|---|---:|
| Nama sesuai | 40 persen |
| Afiliasi sesuai | 25 persen |
| Timeline sesuai | 20 persen |
| Bidang sesuai | 15 persen |
| Total | 100 persen |

Kategori hasil:
| Skor | Kategori |
|---:|---|
| 80 sampai 100 | Kemungkinan Kuat |
| 50 sampai 79 | Perlu Verifikasi |
| 0 sampai 49 | Tidak Cocok |
Confidence score digunakan sebagai alat bantu. Keputusan akhir tetap dilakukan oleh admin setelah memeriksa bukti.

## Status Alumni
Status yang digunakan dalam aplikasi:
1. Belum Dilacak
2. Teridentifikasi dari Sumber Publik
3. Perlu Verifikasi Manual
4. Belum Ditemukan di Sumber Publik

## Teknologi
| Bagian | Teknologi |
|---|---|
| Bahasa pemrograman | PHP 8.2 |
| Framework | Laravel 12 |
| Tampilan | Blade dan Livewire |
| Styling | Tailwind CSS |
| Database | SQLite |
| Build frontend | Vite |
| Dependency backend | Composer |
| Dependency frontend | Node.js dan npm |
| Pengujian | Pest dan PHPUnit |

## Cara Menjalankan Project
Pastikan PHP, Composer, Node.js, dan npm sudah terpasang.

Clone repository:

```bash
git clone LINK_REPOSITORY_GITHUB
```

Masuk ke folder project:

```bash
cd tracer-alumni
```

Install dependency PHP:

```bash
composer install
```

Salin file konfigurasi pada Windows:

```bash
copy .env.example .env
```

Buat application key:

```bash
php artisan key:generate
```

Buat file database SQLite pada folder `database` dengan nama:

```text
database.sqlite
```

Jalankan migration dan seeder:

```bash
php artisan migrate --seed
```

Install dependency frontend:

```bash
npm install
```

Build tampilan aplikasi:

```bash
npm run build
```

Apabila PowerShell memblokir perintah npm, gunakan:

```bash
npm.cmd install
npm.cmd run build
```

Jalankan aplikasi:

```bash
php artisan serve
```

Aplikasi lokal dapat dibuka melalui:

```text
http://127.0.0.1:8000
```

Menjalankan queue:

```bash
php artisan queue:work
```

Menjalankan scheduler:

```bash
php artisan schedule:work
```

## Akun 
Email: admin@traceralumni.test  
Password: TracerAlumni#Aman2026

## Link Project
Repository GitHub: https://github.com/Agill171/tracer-alumni-project3-final-fixed

Link publikasi web: https://tracer-alumni-project3-final-fixed-production.up.railway.app

## Hasil Pengujian Manual
Pengujian dilakukan berdasarkan proses yang dirancang pada Daily Project 2.

| Kode | Bagian yang diuji | Skenario pengujian | Hasil aktual | Status |
|---|---|---|---|---|
| RK-01 | Profil target pencarian | Menambahkan data Ahmad Fikri Pratama | Data alumni berhasil disimpan dan ditampilkan dengan status awal Belum Dilacak | Lulus |
| RK-02 | Sumber dan prioritas | Memilih Google Web, LinkedIn, dan GitHub | Ketiga sumber berhasil disimpan dengan prioritas 1, 2, dan 3 | Lulus |
| RK-03 | Pembentukan query | Membuat query berdasarkan data alumni | Query berhasil dibuat dari nama, kampus, program studi, tahun lulus, dan tempat bekerja. Sumber, prioritas, waktu pembuatan, dan tombol pencarian juga ditampilkan | Lulus |
| RK-04 | Scheduler pelacakan | Menjalankan command untuk alumni yang belum memiliki query | Scheduler berhasil membuat delapan query untuk Google Web, LinkedIn, GitHub, Google Scholar, ResearchGate, ORCID, Instagram, dan Facebook | Lulus |
| RK-05 | Kandidat dengan kecocokan rendah | Mencatat kandidat dengan kecocokan nama saja | Sistem menghitung skor 40 persen, memberi kategori Tidak Cocok, dan mengubah status menjadi Belum Ditemukan di Sumber Publik | Lulus |
| RK-06 | Kandidat yang perlu diverifikasi | Mencatat kandidat dengan nama dan afiliasi sesuai | Sistem menghitung skor 65 persen, memberi kategori Perlu Verifikasi, dan mengubah status menjadi Perlu Verifikasi Manual | Lulus |
| RK-07 | Kandidat dengan kecocokan kuat | Mencatat kandidat dengan semua sinyal identitas sesuai | Sistem menghitung skor 100 persen, memberi kategori Kemungkinan Kuat, dan mengubah status menjadi Teridentifikasi dari Sumber Publik | Lulus |
| RK-08 | Verifikasi silang | Menyimpan bukti LinkedIn dan Website Perusahaan untuk alumni yang sama | Kedua sumber berhasil disimpan dan dapat dibandingkan oleh admin | Lulus |
| RK-09 | Penetapan status alumni | Memeriksa status setelah kandidat kuat disimpan | Status profil Ahmad Fikri Pratama berhasil berubah menjadi Teridentifikasi dari Sumber Publik | Lulus |
| RK-10 | Jejak bukti | Memeriksa hasil setelah halaman dimuat ulang dan membuka link bukti | Seluruh hasil tetap tersimpan setelah halaman di-refresh. Sumber, skor, kategori, tanggal, query, ringkasan, link bukti, dan admin pencatat tetap ditampilkan | Lulus |

## Hasil Pengujian Teknis
| Pengujian | Perintah | Hasil | Status |
|---|---|---|---|
| Pemeriksaan route | `php artisan route:list` | Sistem berhasil menampilkan 45 route | Lulus |
| Pemeriksaan scheduler | `php artisan schedule:list` | Command `pelacakan:siapkan-query --days=30` terdaftar setiap hari pukul 01.00 | Lulus |
| Automated testing | `php artisan test` | 33 test dengan 82 assertions berhasil dijalankan tanpa kegagalan | Lulus |

## Data Pengujian
Data alumni utama yang digunakan:

```text
Nama Lengkap       : Ahmad Fikri Pratama
NIM                : 202010370311901
Program Studi      : Informatika
Angkatan           : 2020
Tahun Lulus        : 2024
Email              : ahmad.fikri@example.com
Nomor HP           : 081234560901
Tempat Bekerja     : PT Solusi Digital Nusantara
Posisi             : Software Engineer
Kategori Pekerjaan : Swasta
Alamat Bekerja     : Kota Malang
Catatan            : Data dummy pengujian Project 3
```

Data untuk pengujian scheduler:

```text
Nama Lengkap       : Siti Nur Aisyah
NIM                : 202010370311902
Program Studi      : Informatika
Angkatan           : 2019
Tahun Lulus        : 2023
Email              : siti.aisyah@example.com
Nomor HP           : 081234560902
Tempat Bekerja     : Universitas Teknologi Nusantara
Posisi             : Asisten Peneliti
Alamat Bekerja     : Kota Surabaya
Catatan            : Data dummy pengujian scheduler
```

## Batasan Sistem
1. Sistem belum mengambil data secara otomatis dari media sosial.
2. Sistem hanya membuat query dan tautan pencarian.
3. Kandidat hasil pencarian dicatat oleh admin.
4. Penilaian kecocokan identitas dilakukan oleh admin.
5. Bukti dari beberapa sumber dapat disimpan, tetapi penyesuaian skor gabungan masih dilakukan secara manual.
6. Confidence score hanya digunakan sebagai alat bantu.
7. Keputusan akhir mengenai identitas alumni tetap dilakukan oleh admin.
8. Link bukti pada pengujian menggunakan alamat dummy dari example.com.

## Keamanan dan Privasi
1. File `.env` tidak diunggah ke GitHub.
2. Password dan API key tidak disimpan dalam repository.
3. Database yang berisi data alumni sebenarnya tidak dipublikasikan.
4. Data dummy digunakan selama pengujian.
5. Penggunaan sumber publik harus mengikuti ketentuan masing-masing platform.

## File yang Tidak Diunggah
Pastikan file dan folder berikut tidak masuk repository:

```text
.env
vendor/
node_modules/
database/database.sqlite
storage/logs/
```
## Kesimpulan
Sistem Pelacakan Alumni telah berhasil dijalankan sebagai aplikasi web. Sistem dapat mengelola data alumni, memilih sumber pencarian, membuat query, mencatat kandidat, menghitung confidence score, menetapkan status alumni, menyimpan beberapa bukti, dan menjalankan scheduler.
Seluruh pengujian manual RK-01 sampai RK-10 memperoleh status Lulus. Pengujian teknis juga berhasil menampilkan 45 route, mendaftarkan scheduler, serta menjalankan 33 test dengan 82 assertions tanpa kegagalan.