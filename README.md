# Sistem Pelacakan Alumni
Sistem Pelacakan Alumni merupakan aplikasi berbasis web yang digunakan untuk membantu proses pengelolaan, pencarian, pelacakan, dan pengayaan data alumni berdasarkan informasi yang tersedia pada sumber publik.
Aplikasi ini dikembangkan menggunakan Laravel dan telah mengalami beberapa pengembangan. Pada tahap awal, sistem digunakan untuk membantu administrator membuat query pencarian dan mencatat hasil pelacakan secara manual. Pada pengembangan berikutnya, sistem ditambahkan dengan fitur batch pelacakan, Auto Enrichment, pencarian melalui provider eksternal, pengukuran Coverage dan Completeness, serta Accuracy Audit.

## Identitas
Nama: Muh Wahyudi Akil Somar
NIM: 202010370311114
Kelas: Rekayasa Kebutuhan B

## Latar Belakang
Pelacakan alumni dalam jumlah besar membutuhkan proses yang cukup panjang apabila seluruh pencarian dilakukan secara manual. Informasi alumni juga dapat tersebar pada berbagai sumber publik seperti LinkedIn, website perusahaan, media sosial, maupun hasil pencarian web.
Sistem ini dikembangkan untuk membantu proses tersebut dengan menyediakan pengelolaan data alumni, pembuatan query pencarian, penyimpanan evidence, perhitungan confidence score, serta pencarian otomatis melalui layanan pencarian eksternal.
Walaupun beberapa proses telah dilakukan secara otomatis, hasil pencarian tetap dapat diperiksa oleh administrator. Hal ini dilakukan karena informasi yang ditemukan di internet belum tentu memiliki hubungan yang benar dengan alumni yang sedang dicari.

## Tujuan
Tujuan pengembangan sistem ini adalah:
1. Mengelola data alumni dalam satu sistem.
2. Membantu administrator melakukan pencarian data alumni dari sumber publik.
3. Membentuk query pencarian secara otomatis berdasarkan identitas alumni.
4. Menyimpan evidence dan riwayat hasil pelacakan.
5. Menghitung tingkat kecocokan identitas menggunakan confidence score.
6. Memproses alumni dalam jumlah besar melalui batch pelacakan.
7. Melakukan pengayaan data alumni melalui Auto Enrichment.
8. Mengukur Coverage dan Completeness data hasil pelacakan.
9. Mengukur Accuracy melalui proses audit terhadap sampel alumni.
10. Menyediakan hasil data dalam format Excel.

## Teknologi yang Digunakan
Aplikasi menggunakan teknologi berikut:

| Komponen            | Teknologi               |
| ------------------- | ----------------------- |
| Backend             | PHP 8.2 atau lebih baru |
| Framework           | Laravel 12              |
| Antarmuka           | Blade                   |
| Komponen interaktif | Livewire dan Volt       |
| Styling             | Tailwind CSS 4          |
| Build tool          | Vite                    |
| Database lokal      | SQLite                  |
| Database production | PostgreSQL              |
| Queue               | Laravel Queue           |
| Import Excel        | Maatwebsite Excel       |
| Export Excel        | OpenSpout               |
| Object Storage      | S3 Compatible Storage   |
| Testing             | Pest dan PHPUnit        |
| Deployment          | Railway                 |
| Search Provider     | Tavily dan Grok         |

## Fitur Sistem

### Autentikasi
Sistem menyediakan autentikasi untuk administrator yang meliputi login, logout, reset password, perubahan password, serta pengaturan profil.
Registrasi pengguna secara publik dinonaktifkan karena aplikasi digunakan sebagai sistem internal.

### Dashboard
Dashboard digunakan untuk menampilkan ringkasan data alumni dan hasil pelacakan.
Informasi yang ditampilkan meliputi:
* jumlah alumni;
* jumlah data yang telah memiliki hasil Project 4;
* Coverage;
* Completeness;
* status alumni;
* progress pelacakan;
* informasi audit dataset;
* progress Accuracy Audit;
* hasil Accuracy sementara.

### Manajemen Alumni
Administrator dapat melakukan pengelolaan data alumni melalui fungsi tambah, lihat, ubah, hapus, pencarian, filter, import, dan export.
Data alumni terdiri dari data akademik dan data hasil pelacakan.
Data akademik yang disimpan antara lain:
* nama;
* NIM;
* program studi;
* angkatan;
* tahun lulus.

Data hasil pelacakan dapat meliputi:
* LinkedIn;
* Instagram;
* Facebook;
* TikTok;
* email;
* nomor HP;
* tempat bekerja;
* alamat bekerja;
* posisi atau jabatan;
* kategori pekerjaan;
* sosial media tempat bekerja.

## Kategori Data Project 4
Pada Project 4, hasil pelacakan dikelompokkan menjadi delapan kategori.

| No | Kategori                    |
| -- | --------------------------- |
| 1  | Sosial Media Alumni         |
| 2  | Email                       |
| 3  | Nomor HP                    |
| 4  | Tempat Bekerja              |
| 5  | Alamat Bekerja              |
| 6  | Posisi atau Jabatan         |
| 7  | Kategori Pekerjaan          |
| 8  | Sosial Media Tempat Bekerja |

Kategori sosial media alumni dianggap tersedia apabila minimal salah satu akun LinkedIn, Instagram, Facebook, atau TikTok ditemukan.
Kategori pekerjaan yang digunakan pada sistem antara lain PNS, Swasta, dan Wirausaha.

## Dataset Project 4
Berdasarkan hasil audit dataset yang digunakan dalam pengembangan, diperoleh informasi sebagai berikut:

| Informasi                       |  Jumlah |
| ------------------------------- | ------: |
| Total baris sumber              | 142.292 |
| Total NIM unik                  | 142.122 |
| Baris duplikat berlebih         |     170 |
| Kelompok NIM duplikat           |     169 |
| Kelompok dengan konflik atribut |     125 |

Audit dilakukan untuk mengetahui kondisi data sebelum digunakan dalam proses pelacakan.
Konflik data yang ditemukan terutama terdapat pada program studi dan informasi kelulusan.

## Coverage
Coverage menunjukkan jumlah alumni yang sudah memiliki minimal satu kategori data Project 4.
Rumus yang digunakan adalah:

```text
Coverage = jumlah alumni yang memiliki minimal satu data Project 4
```
Persentase Coverage dihitung dengan membandingkan jumlah tersebut terhadap jumlah dataset yang digunakan.
Target minimum yang digunakan pada Project 4 adalah lebih dari 106.720 alumni.
Sistem juga menggunakan target internal sebesar 115.000 alumni sebagai batas tambahan agar pencapaian Coverage memiliki margin yang lebih aman.

## Completeness
Completeness digunakan untuk melihat tingkat kelengkapan data Project 4 pada setiap alumni.
Kriteria yang digunakan adalah:

| Jumlah kategori yang terisi | Rentang       |
| --------------------------- | ------------- |
| Kurang dari 2 kategori      | 0 sampai 50   |
| 2 kategori                  | 51 sampai 70  |
| 3 kategori                  | 71 sampai 85  |
| Minimal 4 kategori          | 86 sampai 100 |

Alumni dengan empat kategori atau lebih dianggap memiliki tingkat kelengkapan yang lebih baik.

## Sumber Pelacakan
Sistem menyediakan beberapa sumber pencarian yang dapat digunakan untuk membantu proses pelacakan alumni.
Sumber tersebut meliputi:
1. Google Web
2. LinkedIn
3. Website atau Tempat Kerja
4. Instagram
5. Facebook
6. TikTok
7. GitHub
8. Google Scholar
9. ResearchGate
10. ORCID
Setiap sumber memiliki bentuk query pencarian yang berbeda sesuai dengan karakteristik platform.

## Query Pelacakan
Query pencarian dibentuk secara otomatis menggunakan data alumni.
Data yang dapat digunakan dalam pembentukan query antara lain:
* nama;
* NIM;
* program studi;
* nama universitas;
* tahun lulus;
* tempat bekerja apabila tersedia.

Nama universitas dapat diatur melalui file `.env`.

```env
TRACER_CAMPUS="Universitas Muhammadiyah Malang"
```

Contoh query:

```text
"Nama Alumni" "NIM"
```
```text
"Nama Alumni" "Program Studi" "Universitas Muhammadiyah Malang"
```
```text
site:linkedin.com/in "Nama Alumni" "Universitas Muhammadiyah Malang"
```

Setiap query yang dibuat dapat disimpan ke database bersama informasi alumni, sumber, prioritas, URL pencarian, status, dan waktu pembuatan.

## Pelacakan Manual
Pelacakan manual tetap disediakan agar administrator dapat melakukan pemeriksaan secara langsung.
Alur pelacakan manual secara umum adalah sebagai berikut:

1. Administrator memilih alumni.
2. Sistem membuat query pencarian.
3. Administrator membuka sumber pencarian.
4. Administrator memeriksa hasil yang ditemukan.
5. Hasil dicatat sebagai evidence.
6. Administrator menentukan sinyal kecocokan.
7. Sistem menghitung confidence score.
8. Data Project 4 dapat dicatat apabila ditemukan.
9. Evidence dan hasil pelacakan disimpan ke database.
Pelacakan manual tetap penting terutama pada hasil yang belum dapat dipastikan secara otomatis.

## Confidence Score
Confidence score digunakan untuk membantu menilai tingkat kecocokan antara hasil pencarian dan identitas alumni.
Bobot yang digunakan adalah:

| Sinyal   | Bobot |
| -------- | ----: |
| Nama     |    40 |
| Afiliasi |    25 |
| Timeline |    20 |
| Bidang   |    15 |
| Total    |   100 |

Kategori confidence adalah sebagai berikut:

| Score         | Keterangan       |
| ------------- | ---------------- |
| 80 sampai 100 | Kemungkinan kuat |
| 50 sampai 79  | Perlu verifikasi |
| 0 sampai 49   | Tidak cocok      |

Confidence score digunakan sebagai alat bantu. Keputusan akhir tetap dapat diperiksa oleh administrator.

## Status Alumni
Sistem menggunakan empat status utama:
1. Belum Dilacak
2. Teridentifikasi dari Sumber Publik
3. Perlu Verifikasi Manual
4. Belum Ditemukan di Sumber Publik

Status Belum Dilacak digunakan ketika alumni belum diproses.
Status Teridentifikasi dari Sumber Publik digunakan apabila ditemukan evidence yang cukup kuat.
Status Perlu Verifikasi Manual digunakan apabila ditemukan kandidat tetapi identitasnya masih perlu diperiksa.
Status Belum Ditemukan di Sumber Publik digunakan apabila pencarian telah dilakukan tetapi belum menghasilkan kandidat yang cukup relevan.
Status tersebut tidak menyatakan bahwa alumni pasti tidak memiliki informasi di internet. Status hanya menunjukkan bahwa informasi belum ditemukan dari proses pencarian yang telah dilakukan.

## Evidence
Setiap hasil pelacakan dapat disimpan sebagai evidence.
Evidence dapat mencakup:

* judul hasil;
* sumber;
* URL;
* query;
* ringkasan hasil;
* tanggal ditemukan;
* confidence score;
* sinyal identitas;
* data Project 4 yang ditemukan;
* pengguna yang mencatat;
* hasil audit;
* catatan audit.
Penyimpanan evidence dilakukan agar hasil pencarian dapat ditelusuri kembali.

## Batch Pelacakan
Batch Pelacakan digunakan untuk menyiapkan pelacakan beberapa alumni sekaligus.
Administrator dapat menentukan jumlah alumni dan sumber yang digunakan.
Jumlah alumni dalam satu batch dibatasi antara 1 sampai 1.000 alumni.

Secara umum proses batch terdiri dari:
1. Membuat batch.
2. Memilih alumni yang belum memiliki data Project 4.
3. Memasukkan item batch ke queue.
4. Membuat query pencarian.
5. Menyimpan hasil query.
6. Menjalankan Auto Enrichment apabila diperlukan.

Status batch yang digunakan meliputi:
* Disiapkan;
* Diproses;
* Query Siap;
* Enrichment;
* Perlu Review;
* Selesai;
* Gagal.

Status Query Siap hanya menunjukkan bahwa query telah dibuat. Status tersebut tidak berarti data alumni sudah berhasil ditemukan.

## Auto Enrichment
Auto Enrichment digunakan untuk membantu pencarian data alumni secara otomatis menggunakan search provider.
Fitur ini menggunakan query yang telah dibuat sebelumnya dan mengirimkannya kepada provider pencarian.

Alur umumnya adalah:
1. Memilih alumni.
2. Memilih query yang memiliki prioritas tinggi.
3. Mengirim query ke search provider.
4. Mengambil hasil pencarian.
5. Melakukan identity matching.
6. Menghitung confidence score.
7. Mengekstraksi kandidat data Project 4.
8. Menentukan hasil sebagai kandidat kuat, perlu verifikasi, atau tidak ditemukan.
9. Menyimpan evidence.
10. Mengisi data alumni apabila memenuhi syarat.

Fitur ini dapat diaktifkan atau dinonaktifkan melalui konfigurasi `.env`.

```env
AUTO_ENRICHMENT_ENABLED=false
```
Provider dapat dipilih melalui:

```env
AUTO_ENRICHMENT_PROVIDER=tavily
```
atau:

```env
AUTO_ENRICHMENT_PROVIDER=grok
```
## Tavily
Tavily digunakan sebagai salah satu provider pencarian web.
Konfigurasi dasarnya adalah:

```env
TAVILY_API_KEY=
TAVILY_SEARCH_ENDPOINT=https://api.tavily.com/search
```
Provider dapat mengembalikan informasi seperti URL, judul, ringkasan, dan isi halaman yang kemudian diproses lebih lanjut oleh sistem.

## Grok
Sistem juga menyediakan provider pencarian berbasis Grok atau xAI.
Konfigurasi dasarnya adalah:

```env
GROK_API_KEY=
GROK_SEARCH_ENDPOINT=https://api.x.ai/v1/chat/completions
GROK_MODEL=grok-4.6
```
API key harus disimpan pada environment variable dan tidak boleh disimpan langsung di repository.

## Konfigurasi Auto Enrichment
Contoh konfigurasi:

```env
AUTO_ENRICHMENT_ENABLED=false
AUTO_ENRICHMENT_PROVIDER=tavily

AUTO_ENRICHMENT_MAX_QUERIES=1
AUTO_ENRICHMENT_RESULTS_PER_QUERY=5

AUTO_ENRICHMENT_STRONG_THRESHOLD=80
AUTO_ENRICHMENT_REVIEW_THRESHOLD=50

AUTO_ENRICHMENT_TIMEOUT=60
```
Setelah mengubah konfigurasi, cache dapat dibersihkan dengan:

```bash
php artisan config:clear
```
## Perlindungan Data Alumni
Auto Enrichment tidak dirancang untuk menimpa data alumni yang sudah tersedia secara otomatis.
Apabila field alumni masih kosong dan ditemukan data dengan tingkat keyakinan yang memenuhi syarat, sistem dapat mengisi field tersebut.
Apabila field sudah memiliki nilai, nilai tersebut dipertahankan dan hasil baru dapat disimpan sebagai evidence untuk diperiksa lebih lanjut.
Pendekatan ini digunakan untuk mengurangi risiko hilangnya data yang sebelumnya sudah tersedia.

## Review Kandidat
Kandidat yang belum memenuhi syarat identifikasi kuat dapat masuk ke status Perlu Verifikasi.
Administrator dapat memeriksa evidence, confidence score, sumber, data kandidat, dan informasi alumni.
Kandidat yang terbukti tidak sesuai dapat ditolak sebagai false positive.
Evidence yang ditolak tetap dipertahankan sebagai bagian dari riwayat audit.

## Queue
Beberapa proses yang membutuhkan waktu lebih lama dijalankan melalui Laravel Queue.
Queue digunakan pada:
* import alumni;
* export data;
* batch pelacakan;
* pemrosesan item batch;
* Auto Enrichment.

Konfigurasi lokal:

```env
QUEUE_CONNECTION=database
```

Queue worker dapat dijalankan dengan:

```bash
php artisan queue:work
```
Queue worker perlu aktif ketika fitur yang menggunakan antrean sedang digunakan.

## Scheduler
Sistem memiliki command untuk menyiapkan query secara berkala.

```bash
php artisan pelacakan:siapkan-query
```
Secara default, query dapat dibuat kembali untuk alumni yang belum pernah memiliki query atau query terakhirnya sudah lebih dari 30 hari.
Jumlah hari dapat ditentukan dengan:

```bash
php artisan pelacakan:siapkan-query --days=30
```
Scheduler Laravel dapat dijalankan secara lokal dengan:

```bash
php artisan schedule:work
```
## Import Alumni
Sistem mendukung import dalam format XLSX, XLS, dan CSV.
Ukuran file maksimal yang digunakan pada aplikasi adalah 50 MB.
Contoh format header:

```csv
Nama Lulusan,NIM,Tahun Masuk,Tanggal Lulus,Fakultas,Program Studi
```
Mapping data utama adalah:

| Header        | Field       |
| ------------- | ----------- |
| Nama Lulusan  | nama        |
| NIM           | nim         |
| Tahun Masuk   | angkatan    |
| Tanggal Lulus | tahun_lulus |
| Program Studi | prodi       |

NIM digunakan sebagai identitas utama ketika proses import dilakukan kembali.
Apabila NIM sudah terdapat di database, data akademik dapat diperbarui tanpa menghapus hasil pelacakan yang sudah tersedia.

## Export Data
Data Project 4 dapat diekspor ke Excel.
Export diproses menggunakan queue karena jumlah alumni dapat cukup besar.
Data diproses secara bertahap agar penggunaan memori lebih terkendali.
File export dapat berisi data akademik, sosial media, kontak, pekerjaan, status verifikasi, evidence terbaru, confidence score, dan informasi audit.
Object storage yang digunakan dikonfigurasi melalui disk S3.
Contoh konfigurasi:

```env
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
AWS_URL=
AWS_ENDPOINT=
AWS_USE_PATH_STYLE_ENDPOINT=false
```
## Accuracy Audit
Accuracy Audit digunakan untuk memeriksa kebenaran data hasil pelacakan.
Sistem mengambil sampel alumni yang sudah memiliki minimal satu kategori Project 4.
Target sampel yang digunakan adalah 500 alumni.
Status audit terdiri dari:
* Belum Diaudit;
* Benar;
* Salah;
* Perlu Verifikasi.

Administrator dapat membuka data alumni, memeriksa evidence, menentukan hasil audit, dan memberikan catatan.
Accuracy sementara dihitung menggunakan:

```text
Accuracy = Benar / (Benar + Salah) x 100%
```
Status Perlu Verifikasi belum dihitung sebagai keputusan final.
Accuracy akhir dapat ditetapkan setelah seluruh sampel selesai diperiksa dan tidak ada lagi status Belum Diaudit atau Perlu Verifikasi.
Kriteria penilaian yang digunakan adalah:

| Jumlah data benar dari 500 sampel | Rentang nilai |
| --------------------------------- | ------------- |
| Lebih dari 475                    | 91 sampai 100 |
| 426 sampai 475                    | 76 sampai 90  |
| 350 sampai 425                    | 51 sampai 75  |
| Kurang dari 350                   | 0 sampai 50   |

## Audit Dataset
Project menyediakan beberapa Artisan command untuk membantu pemeriksaan dataset.

### Audit Alumni yang Belum Masuk Database

```bash
php artisan alumni:audit-missing
```
Command ini membandingkan data sumber dengan data database berdasarkan NIM.

### Audit Data Duplikat

```bash
php artisan alumni:audit-duplicates
```
Command digunakan untuk mendeteksi NIM yang muncul lebih dari satu kali.

### Analisis Konflik

```bash
php artisan alumni:analyze-duplicate-conflicts
```
Command digunakan untuk menganalisis perbedaan atribut pada kelompok NIM yang sama.

## Struktur Utama Project

```text
app/
├── Console/
├── Contracts/
├── Exports/
├── Http/
│   └── Controllers/
├── Imports/
├── Jobs/
├── Models/
└── Services/
    └── Search/
```
Beberapa service utama dalam aplikasi adalah:

```text
AutoEnrichmentService
IdentityMatchingService
PelacakanBatchService
PelacakanQueryService
TavilySearchProvider
GrokSearchProvider
SearchProviderManager
```
Model utama meliputi:

```text
Alumni
HasilPelacakan
PelacakanQuery
PelacakanBatch
PelacakanBatchItem
PelacakanKandidat
AccuracyAuditSample
User
```
## Persyaratan Sistem
Sebelum menjalankan aplikasi, pastikan perangkat memiliki:
* PHP 8.2 atau lebih baru;
* Composer;
* Node.js;
* npm;
* Git.

Extension PHP yang diperlukan atau direkomendasikan antara lain:
* mbstring;
* fileinfo;
* openssl;
* PDO;
* pdo_sqlite untuk SQLite;
* pdo_pgsql untuk PostgreSQL;
* DOM dan XML.

## Instalasi
Clone repository:

```bash
git clone https://github.com/Agill171/tracer-alumni-project3-final-fixed.git
```
Masuk ke direktori project:

```bash
cd tracer-alumni-project3-final-fixed
```
Install dependency PHP:

```bash
composer install
```
Install dependency frontend:

```bash
npm install
```
Salin file environment.
Windows:

```bash
copy .env.example .env
```
Linux atau macOS:

```bash
cp .env.example .env
```
Generate application key:

```bash
php artisan key:generate
```
## Database Lokal
Development lokal dapat menggunakan SQLite.
Buat file:

```text
database/database.sqlite
```
Pada `.env` gunakan:

```env
DB_CONNECTION=sqlite
```
Kemudian jalankan:

```bash
php artisan migrate --seed
```
## Build Frontend
Untuk build production:

```bash
npm run build
```
Untuk development:

```bash
npm run dev
```
## Menjalankan Aplikasi
Jalankan server Laravel:

```bash
php artisan serve
```
Pada terminal lain jalankan queue worker:

```bash
php artisan queue:work
```
Apabila scheduler diperlukan:

```bash
php artisan schedule:work
```
Aplikasi lokal dapat diakses melalui:

```text
http://127.0.0.1:8000
```
Project juga menyediakan:

```bash
composer run dev
```
untuk menjalankan beberapa proses development secara bersamaan.

## Akun Administrator Lokal
Seeder membaca konfigurasi akun administrator dari `.env`.

```env
ADMIN_EMAIL=admin@traceralumni.test
ADMIN_PASSWORD=password
```
Apabila nilai tersebut belum diubah, akun lokal yang digunakan adalah:

```text
Email: admin@traceralumni.test
Password: password
```
Password default hanya digunakan untuk development dan harus diganti pada production.

## PostgreSQL Production
Untuk production dapat digunakan PostgreSQL.
Contoh:

```env
DB_CONNECTION=pgsql
DB_URL=
```
atau menggunakan konfigurasi terpisah:

```env
DB_CONNECTION=pgsql
DB_HOST=
DB_PORT=5432
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```
Migration production dapat dijalankan menggunakan:

```bash
php artisan migrate --force
```
## Deployment
Aplikasi dikembangkan untuk dapat dijalankan pada Railway.
Website production:

```text
https://tracer-alumni-project3-final-fixed-production.up.railway.app
```
Repository:

```text
https://github.com/Agill171/tracer-alumni-project3-final-fixed
```
Beberapa environment variable yang perlu diperhatikan pada production adalah:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=
APP_KEY=

DB_CONNECTION=pgsql
DB_URL=

QUEUE_CONNECTION=database
```
Apabila Auto Enrichment digunakan, tambahkan konfigurasi provider dan API key.
Apabila fitur export digunakan, tambahkan konfigurasi object storage.
Queue worker juga perlu dijalankan pada production agar pekerjaan asynchronous dapat diproses.

## Testing
Project menggunakan Pest dan PHPUnit.
Untuk menjalankan seluruh test:

```bash
php artisan test
```
Untuk menjalankan test tertentu:

```bash
php artisan test tests/Feature/AlumniManagementTest.php
```
Pemeriksaan format kode dapat dilakukan menggunakan:

```bash
vendor/bin/pint --test
```
Automated test yang terdapat pada project mencakup beberapa fungsi seperti autentikasi, pengelolaan alumni, dashboard, validasi, filter, pelacakan, query pencarian, confidence score, dan pengaturan pengguna.

## GitHub Actions
Repository menyediakan GitHub Actions untuk membantu proses pengecekan otomatis.
Workflow digunakan untuk menjalankan pemeriksaan code style dan automated test.

Tahapan yang dilakukan antara lain:
1. Menyiapkan PHP.
2. Menyiapkan Node.js.
3. Menginstall dependency Composer.
4. Menginstall dependency npm.
5. Menyiapkan environment testing.
6. Membuat database SQLite.
7. Melakukan build frontend.
8. Menjalankan migration.
9. Menjalankan automated test.
Konfigurasi branch pada workflow perlu disesuaikan dengan branch utama repository yang digunakan.

## Keamanan dan Privasi
Aplikasi mengelola data yang dapat mencakup informasi pribadi alumni. Oleh karena itu, beberapa hal perlu diperhatikan:

1. File `.env` tidak boleh dimasukkan ke repository.
2. API key dan credential tidak boleh ditulis langsung pada source code.
3. Database production tidak boleh dibagikan secara publik.
4. Password default harus diganti pada production.
5. File export harus diperlakukan sebagai data terbatas.
6. Hasil pencarian otomatis tetap perlu dapat diverifikasi.
7. Evidence disimpan agar sumber data dapat ditelusuri.
8. Data yang sudah tersedia tidak ditimpa secara otomatis tanpa pertimbangan.
9. Informasi yang digunakan untuk pelacakan berasal dari sumber publik.
10. Penggunaan layanan eksternal harus mengikuti aturan masing-masing layanan.

## Batasan Sistem
Sistem memiliki beberapa batasan.

Pertama, hasil pelacakan sangat bergantung pada informasi yang tersedia secara publik.
Kedua, tidak semua alumni dapat ditemukan melalui internet.
Ketiga, status Belum Ditemukan tidak berarti alumni tidak memiliki informasi publik. Status tersebut hanya menunjukkan bahwa proses pencarian yang dilakukan belum menemukan informasi yang cukup.
Keempat, confidence score digunakan sebagai alat bantu dan tidak dapat menggantikan pemeriksaan manusia sepenuhnya.
Kelima, Auto Enrichment menggunakan provider eksternal sehingga proses pencarian dapat dipengaruhi oleh koneksi internet, ketersediaan layanan, kuota, dan API key.
Keenam, hasil otomatis dapat menghasilkan kandidat yang masih memerlukan verifikasi manual.
Ketujuh, proses import, export, batch, dan Auto Enrichment membutuhkan queue worker agar dapat berjalan dengan baik.
Kedelapan, Accuracy akhir baru dapat ditentukan setelah proses sampling dan audit selesai.

## Perkembangan Sistem
Versi awal sistem lebih berfokus pada pelacakan manual. Sistem membuat query pencarian dan administrator melakukan pemeriksaan sumber secara langsung.
Pada pengembangan terbaru, sistem telah mendukung proses tambahan berupa batch pelacakan, pencarian otomatis, identity matching, confidence scoring, Auto Enrichment, review kandidat, Coverage, Completeness, serta Accuracy Audit.
Pelacakan manual tetap dipertahankan untuk membantu proses verifikasi terhadap hasil yang belum dapat dipastikan secara otomatis.

## Kesimpulan
Sistem Pelacakan Alumni dikembangkan untuk membantu proses pengelolaan dan pelacakan alumni dalam jumlah besar.
Sistem tidak hanya menyediakan pengelolaan data alumni, tetapi juga mendukung pembuatan query pencarian, penyimpanan evidence, batch processing, Auto Enrichment, confidence score, Coverage, Completeness, dan Accuracy Audit.
Penggunaan proses otomatis ditujukan untuk mengurangi pekerjaan manual ketika jumlah data alumni cukup besar. Namun, sistem tetap menyediakan mekanisme review agar informasi yang ditemukan dapat diperiksa kembali sebelum digunakan sebagai data yang dianggap valid.
Dengan pendekatan tersebut, sistem dapat membantu proses tracer alumni sekaligus mempertahankan riwayat evidence dan proses verifikasi terhadap hasil pelacakan.

## Repository dan Deployment
Repository GitHub:

```text
https://github.com/Agill171/tracer-alumni-project3-final-fixed
```

Website:

```text
https://tracer-alumni-project3-final-fixed-production.up.railway.app
```

<<<<<<< HEAD
Konfigurasi database production disimpan pada Railway
```env
DB_CONNECTION=pgsql
DB_URL=${{Postgres.DATABASE_URL}}
```

Perintah tambahan untuk menjalankan queue dan scheduler
```bash
php artisan queue:work
php artisan schedule:work
```
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
=======
## Pengembang
Muh Wahyudi Akil Somar
NIM 202010370311114
Rekayasa Kebutuhan B
>>>>>>> 1463515 (Update project and documentation)
