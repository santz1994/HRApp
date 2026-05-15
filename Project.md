MASTER BLUEPRINT: HRIS & AI INTEGRATION PROJECT
1. Visi Sistem & Tech Stack
Sistem HRIS ini dirancang untuk memproses ribuan data relasional dengan antarmuka yang cepat (high-performance) dan siap untuk analitik Machine Learning.

Core Backend: PHP (Laravel 11+) untuk stabilitas relasional, antrean (queue), dan RBAC.

AI Microservice: Python (FastAPI) terintegrasi dengan model AI lokal (Ollama/LLM) untuk memproses kepribadian dan analitik prediktif.

Frontend Web: React.js (dengan TanStack Query & TanStack Table) atau ekosistem Blade/Livewire jika menggunakan pendekatan monolitik modern.

Frontend Mobile (Opsional): Dart (Flutter) dengan arsitektur state management Riverpod untuk akses karyawan (Employee Self-Service).

Database: PostgreSQL / MySQL 8.0+.

2. Struktur Data & Normalisasi (Skema 26 Poin)
Menerapkan prinsip pemisahan antara Stored Data, Calculated Data, dan Relational Data untuk mencegah anomali database dan resource timeout.

A. Tabel Utama: employees (Stored Data)
Tabel ini tidak menyimpan data yang berubah karena waktu.

- id (Primary Key, BigInt)
- nik_karyawan (String/Varchar, Unique, Index)
- no_ktp (String/Varchar, Unique, Index)
- nama_lengkap (String)
- alamat_ktp (Text)
- tempat_lahir (String)
- tanggal_lahir (Date)
- tanggal_masuk_kerja (Date)
- department_id (Foreign Key -> departments)
- position_id (Foreign Key -> positions)
- jenis_kelamin (Enum: 'L', 'P')
- initial_department_id (Foreign Key -> departments)
- current_department_id (Foreign Key -> departments)
- status_pkwtt (Enum: 'TETAP', 'KONTRAK', 'HARIAN', 'MAGANG')
- status_keluarga (Enum: 'Lajang', 'Kawin', 'Cerai Hidup', 'Cerai Mati')
- jumlah_anak (Integer, Default 0)
- status_pajak (String, misal: 'TK/0', 'K/1') (tergantung pada status keluarga dan jumlah anak)
- pendidikan (String)
- alamat_domisili (Text)
- dokumen_pendukung (JSON - menyimpan array of file paths)
    - foto_ktp (String - path file)
    - foto_kk (String - path file)
    - foto_ijazah (String - path file)
    - foto_selfie (String - path file)

26a. data_kepribadian (JSON - hasil assessment MBTI/DISC) (opsional, bisa diisi manual oleh admin per departemen atau hasil parsing dari AI)
26b. ai_metrics (JSON - metrik prediksi turnover, rekomendasi AI)

B. Dynamic/Calculated Data (Virtual Attributes)
Kalkulasi dilakukan on-the-fly di level Model menggunakan Accessors (Laravel) agar data selalu akurat detik itu juga.
11. usia_masuk_bekerja (Selisih tanggal_masuk_kerja dan tanggal_lahir)
12. masa_kerja (Selisih tanggal_masuk_kerja dan Current Date)
13. usia_saat_ini (Selisih tanggal_lahir dan Current Date)

C. Tabel Relasional (One-to-Many)
Tabel attendances: id, employee_id, tanggal, jam_masuk, jam_pulang, status_kehadiran.

Tabel medical_records: id, employee_id, tanggal_mulai, tanggal_selesai, keterangan_sakit, path_file_skd.

3. Arsitektur Backend (Design Pattern)
Gunakan Controller-Service-Repository Pattern agar kode tidak menjadi spaghetti.

Controller: Hanya bertugas menerima Request HTTP, memvalidasi otorisasi (Middleware RBAC), dan mengembalikan Response JSON. Dilarang menempatkan logika perhitungan di sini.

Service Layer (EmployeeService): Berisi Business Logic. Di sinilah proses format data, kalkulasi tambahan, persiapan antrean Job, dan logika parsing data Excel diletakkan.

Repository Layer (EmployeeRepository): Memisahkan logika query database. Dynamic Query Builder untuk Filter (Departemen, Status) dan Sorting diletakkan di sini untuk reusability.

4. UI/UX & Frontend State Management
Antarmuka ditargetkan untuk managerial level (Premium, Cepat, Minimalis).

Data Table (Headless UI): Gunakan TanStack Table. Implementasikan Sticky Columns (kunci NIK dan Nama di kiri, Aksi di kanan). Berikan opsi pengaturan Density (kerapatan baris).

State Management: Gunakan Zustand/Riverpod untuk UI State (tema, navigasi), dan TanStack Query (React Query) untuk Server State (caching data karyawan, auto-refetch setelah sorting tanpa reload halaman).

Route Protection: Bungkus rute dengan komponen keamanan frontend yang mengecek Role pengguna secara sinkron dengan token autentikasi.

Gunakan modal untuk konfirmasi aksi destruktif (hapus) dan formulir CRUD. Pastikan UX responsif dengan feedback loading dan error handling yang jelas.

5. Implementasi Fitur Krusial
A. RBAC (Role-Based Access Control)

- Direksi: Read-Only global, akses ke Dashboard Analitik AI.
- HR: Full CRUD, akses modul Import/Export, Manajemen Absensi.
- Admin Department: Read-Only untuk departemen sendiri, akses ke laporan kehadiran, edit data karyawan di departemen sendiri dengan persetujuan HR (Workflow Approval).
- IT Developer & Administrator: Akses penuh untuk pengelolaan sistem, termasuk manajemen pengguna dan konfigurasi AI.
- Karyawan: Akses terbatas untuk melihat data diri sendiri, mengajukan cuti, dan melihat riwayat absensi.

B. Import & Export Skala Besar (Anti-RTO)
Export: Jangan menggunakan Employee::all(). Gunakan teknik Chunking (tarik per 1000 baris, sisipkan ke memori Excel, lalu bersihkan RAM). Kalkulasi atribut dinamis harus di-inject selama proses ini.

Import (Upsert & Queue): File Excel di-upload dan diproses di background menggunakan Laravel Queue (ShouldQueue). Terapkan logika Upsert (Update or Insert) berdasarkan NIK/KTP untuk menghindari duplikasi. Sediakan tabel log_imports agar HR tahu baris mana yang gagal dimasukkan.

C. Integrasi AI (Python/FastAPI Microservice) (Update Akhir)
Sistem Laravel akan mengirimkan JSON (Data Karyawan, Absensi, Usia) melalui REST API ke server FastAPI.

Model AI lokal (Ollama) memproses sentimen, anomali absensi, dan data kepribadian.

FastAPI mengirimkan kembali hasil analitik (misal: "probabilitas_resign": 85%) untuk disimpan di kolom ai_metrics pada database HRIS.

D. Cetak Laporan & Dashboard Analitik
Gunakan Chart.js atau D3.js untuk visualisasi data di dashboard. Tampilkan metrik seperti distribusi usia, masa kerja, tingkat kehadiran, dan hasil analitik AI dalam bentuk grafik yang mudah dipahami.

E. Cetak Kartu Karyawan
Gunakan library seperti Dompdf untuk menghasilkan PDF kartu karyawan dengan desain yang profesional, termasuk foto KTP dan informasi penting lainnya.

F. Log Aktivitas & Audit Trail
Setiap aksi CRUD, import, export, dan login/logout harus dicatat di tabel logs dengan informasi pengguna, timestamp, dan deskripsi aksi untuk keperluan audit dan keamanan.

G. Semua pengguna login menggunakan NIK (Karyawan)atau email dan password(wajib). Implementasikan fitur reset password dengan email untuk keamanan. Email tidak wajib untuk login, tapi sangat disarankan untuk fitur reset password dan notifikasi. email menggunakan format standar (misal:example@quty.co.id).

6. Deployment Strategy (Langkah Akhir)
Sistem harus di-deploy menggunakan praktik CI/CD dan manajemen server yang modern.

Environment Preparation:

Setel server Linux (Ubuntu 22.04/24.04).

Instalasi Nginx, PHP-FPM, PostgreSQL/MySQL, dan Redis (sangat penting untuk Queue dan Caching).

Queue Management (Penting untuk HRIS):

Fungsi Import Excel dan AI processing wajib berjalan di background. Konfigurasi Supervisor di server Linux untuk memastikan perintah php artisan queue:work berjalan otomatis dan restart sendiri jika crash.

Storage Link & Permissions:

Jalankan php artisan storage:link untuk mengekspos folder dokumen pendukung dan foto KTP ke publik/akses privat. Pastikan permission folder storage dan bootstrap/cache adalah 775.

Reverse Proxy & SSL:

Gunakan Nginx sebagai reverse proxy. Konfigurasikan SSL Certificate gratis via Let's Encrypt (Certbot).

Jika FastAPI (AI) berjalan di port berbeda (misal: 8000), setel rute internal di Nginx (contoh: /api/ai) yang mengarah ke localhost:8000 demi keamanan firewall.

Database Migration & Seeding:

Jalankan php artisan migrate --force di mode production.

Jalankan Seeder khusus untuk akun Administrator utama (HR/Direksi) dan struktur Departemen awal.

Version Control & CI/CD:

Hubungkan repositori Git (GitHub/GitLab) dengan fitur Actions atau Webhooks. Setiap push ke branch main akan otomatis menjalankan script penarikan (git pull), instalasi dependency (composer install --no-dev), dan membersihkan cache (php artisan optimize:clear).