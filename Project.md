Merancang aplikasi HR yang sederhana di awal namun disiapkan untuk pembaruan berskala besar (scalable) membutuhkan fondasi arsitektur yang sangat solid. Mengingat aplikasi ini nantinya akan berkembang dengan banyak pembaruan, penerapan design pattern yang memisahkan antara logika bisnis, akses data, dan routing HTTP adalah langkah yang wajib dilakukan sejak hari pertama.

Berikut adalah rancangan arsitektur dan implementasi mendalam untuk fase pertama aplikasi HR Anda.

1. Analisis Struktur Data & Normalisasi
Berdasarkan sampel data dari spreadsheet yang Anda lampirkan, ada satu prinsip database penting yang harus diterapkan: Pisahkan Stored Data dan Calculated Data.

Stored Data (Disimpan di DB): NIK, No. KTP, Nama, Dept, Jabatan, Tempat Lahir, Tanggal Masuk, Tanggal Lahir, Jenis Kelamin, Dept On Line, Dept On Line Awal, Status PKWTT, Status Keluarga, Pendidikan, Alamat.

Calculated Data (Dihitung On-the-fly): Umur Masuk, Masa Kerja, Umur Sekarang, dan On The Year.

Alasan: Menyimpan data yang terus berubah karena waktu (seperti umur dan masa kerja) di dalam database akan menyebabkan anomali dan memaksa Anda membuat cron job harian hanya untuk mengupdate umur. Kalkulasi ini harus dilakukan di level aplikasi (misalnya menggunakan Accessors di Model).

2. Arsitektur Sistem
Untuk memastikan aplikasi siap menerima banyak update ke depannya tanpa membuat kode menjadi "spaghetti", gunakan arsitektur Controller-Service-Repository Pattern. Arsitektur ini sangat ideal diterapkan menggunakan framework seperti Laravel (PHP) atau FastAPI (Python) yang dikombinasikan dengan React atau Flutter di sisi frontend.

Controller: Hanya bertugas menerima Request (Filter, Sort, File Excel), memvalidasi otorisasi (hanya HR & Direksi), dan mengembalikan Response (JSON/View).

Service Layer: Berisi Business Logic. Di sinilah proses kalkulasi umur, format data, dan logika parsing data Import/Export diletakkan.

Repository Layer: Berisi semua query database. Logika Filter (misal: cari berdasarkan departemen) dan Sorting diletakkan di sini agar bisa digunakan ulang di fitur lain nantinya.

3. Rancangan Skema Database (Fase 1)
Tabel users (Otorisasi & Autentikasi)
Sistem otorisasi sebaiknya menggunakan skema Role-Based Access Control (RBAC).

id, name, email, password

role_id (Misal: 1 = Direksi, 2 = HR)

Tabel employees (Data Master)
Gunakan tipe data yang tepat. NIK dan No. KTP harus VARCHAR (atau String), bukan Integer, karena seringkali diawali dengan angka 0 atau melebihi batas integer.

| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |lahir| VARCHAR(100) | | |tanggal_lahir| DATE | Untuk kalkulasi "Umur" | |tanggal_masuk| DATE | Untuk kalkulasi "Masa Kerja" | |jenis_kelamin` | ENUM('L', 'P') | |
| ... kolom lainnya | ... | Sesuai lampiran |

4. Strategi Implementasi Fitur
A. Filter & Sorting (Repository Layer)
Untuk mengakomodasi pencarian yang dinamis, Anda bisa membangun Dynamic Query Builder di Repository.

PHP
// Contoh-the-fly* di level Service atau Frontend agar datanya selalu presisi secara *real-time* tanpa perlu *cron job*.

**Tabel `employees`:**
*   `id` (Primary Key, UUID/BigInt)
*   `nik` (String, Unique, Index)
*    konseptual di Repository
public function getEmployees($filters = [], $sort_by = 'created_at', $sort_dir = 'desc') {
    $query = $this->model->newQuery();

    if (isset($filters['department'])) {
        $query->where('department', $filters`no_ktp` (String, Unique)
*   `nama` (String)
*   `department` (String) -> *Next update: jadikan relasi tabel `departments`*
*   `jabatan` (String) -> *Next update: jadikan relasi tabel `positions`*
*   `tempat_lahir` (String)
*   `tanggal_masuk` (Date)
*   `tanggal_lahir` (Date)
*   `jenis_kelamin` (Enum: 'L', 'P')
*   `dept_on_line` (String)
*   `dept_on_line_awal` (String)
*   `status_pkwtt` (Enum: 'TETAP', 'KONTRAK')
*   `status_keluarga` (String) -> *Contoh: K/1, TK/0*
*   `pendidikan` (String)
*   `alamat` (Text)

### 3. Implementasi Akses (RBAC)

Karena aplikasi hanya diakses oleh HR dan Direksi, implementasikan **Role-Based Access Control (RBAC)** di level Middleware.
*   **Role HR:** Memiliki akses *Create, Read, Update, Delete* (CRUD), *Import*, dan *Export*.
*   **Role Direksi:** Hanya memiliki akses *Read* (Melihat data, Dashboard analitik nanti['department']);
    }
    if (isset($filters['status_pkwtt'])) {
        $query->where('status_pkwtt', $filters['status_pkwtt']);
    }
    // Pencarian global
    if (isset($filters['search'])) {
        $query->where(function($q) use ($filters) {
            $q->where('name', 'like', '%'.$filters['search'].'%')
              ->orWhere('nik', 'like', '%'.$filters['search'].'%');
        });
    }

    return $query->orderBy($sort_by, $sort_dir)->paginate(50);
}
B. Import & Export Data (Service Layer)
Ini adalah fitur krusial yang rawan menyebabkan Resource Time Out (RTO) jika tidak ditangani dengan benar saat data mencapai ribuan baris.

Export: Jangan me-load seluruh data karyawan ke RAM sekaligus. Gunakan metode Chunking (ambil per 500 baris, tulis ke excel, ambil lagi). Saat export, panggil fungsi kalkulasi untuk memunculkan kolom Umur Sekarang dan Masa Kerja secara dinamis ke dalam sel Excel.

Import: Validasi data adalah kunci. Service Layer harus mengecek duplikasi NIK atau NO. KTP. Gunakan fitur Upsert (Update or Insert): Jika NIK sudah ada, perbarui datanya; jika belum ada, buat baris baru. Gunakan Queue (antrian) di background untuk proses import agar browser user tidak loading terus-menerus.

C. Otorisasi Otomatis (Middleware)
Pasang penghalang di level route (API/Web). Jika ada user yang bukan HR atau Direksi mencoba menembak URL /api/employees/export, sistem langsung mengembalikan status 403 Forbidden.

5. Rekomendasi Pembaruan (Next Updates)
Karena fondasi Controller-Service-Repository sudah tertanam, menambahkan fitur kompleks di masa depan akan jauh lebih aman. Beberapa arannya, atau fokus ke setup library Export/Import agar memory-safe?


Untuk sisi UI/UX dan Frontend, tantangan utamanya adalah menampilkan data tabular yang sangat lebar (mencapai 15+ kolom berdasarkan sampel data Anda) tanpa membuat antarmuka terlihat berantakan atau membingungkan. Karena aplikasi ini ditujukan untuk level manajerial (HR & Direksi), desain harus terasa premium, cepat, dan intuitif.

Berikut adalah rancangan UI/UX dan arsitektur Frontend tingkat lanjut untuk mengakomodasi kebutuhan tersebut.

1. UI/UX Design Strategy (Minimalist & High-End)
Aplikasi internal HR seringkali kaku. Kita bisa menggunakan pendekatan design language yang bersih, elegan, dan data-driven.

Tema Visual: Gunakan pendekatan black-on-white (monokromatik) yang clean dan minimalist. Gunakan garis pembatas (thin lines) yang tipis dan halus (misal: hex code #E5E7EB) untuk memisahkan baris data, serta rounded cards (radius sudut yang konsisten, misal 8px atau 12px) untuk membungkus elemen utama.

Typography: Gunakan font sans-serif yang sangat jelas dibaca pada ukuran kecil seperti Inter atau Roboto.

Data Table UX (Krusial):

Sticky Columns: Bekukan (freeze) kolom kiri (NO, NIK, NAMA) dan kolom paling kanan (Aksi: Edit/View) agar tetap terlihat saat HR melakukan scroll horizontal untuk melihat kolom alamat atau pendidikan.

Density Control: Berikan opsi toggle agar pengguna bisa mengatur kerapatan baris tabel (Compact atau Comfortable) untuk menyesuaikan dengan ukuran monitor mereka.

Visual Hierarchy: Gunakan warna abu-abu redup untuk data sekunder (seperti Tempat Lahir), dan teks tebal (hitam pekat) untuk identifier utama (seperti Nama dan NIK).

Import/Export UX: Gunakan area Drag-and-Drop yang besar untuk upload file Excel. Sediakan progress bar yang nyata saat proses import, dan jika ada validasi error (misal: NIK duplikat), tampilkan dalam modal atau drawer yang merinci baris mana yang bermasalah.

2. Frontend Architecture
Mengingat kompleksitas state dan kebutuhan performa, membangun frontend menggunakan ekosistem React atau Flutter (jika menargetkan cross-platform desktop) adalah pilihan yang sangat tangguh. Berikut adalah struktur arsitektur menggunakan pendekatan ekosistem modern (seperti React):

A. Component Pattern (Atomic Design)
Pisahkan komponen UI secara modular agar dapat digunakan kembali (reusable) saat aplikasi diperbarui dengan modul baru nantinya.

Atoms: Button, Input Field, Badge (untuk status PKWTT).

Molecules: Search Bar dengan ikon, Pagination Control.

Organisms: Employee Data Table (gabungan dari search bar, tabel, dan paginasi), Upload Excel Dropzone.

Templates/Layouts: Dashboard Layout (dengan Sidebar navigasi dan Top Bar profil).

B. State Management & Data Fetching
Jangan mencampur UI state (modal terbuka/tertutup) dengan Server state (data karyawan dari database).

Server State (Caching & Fetching): Gunakan library seperti TanStack Query (React Query). Ini sangat powerful karena otomatis menangani caching, background updates, dan loading states. Saat HR melakukan sorting atau filter departemen, Query akan otomatis memanggil ulang API (Controller) dan memperbarui tabel tanpa perlu me-refresh halaman.

Global UI State: Gunakan library ringan seperti Zustand (jika di React) atau Riverpod (jika di Flutter) untuk menyimpan data user yang sedang login (HR atau Direksi) dan status navigasi sidebar.

C. Headless Data Table (Deep Function)
Membangun tabel HTML biasa dengan tag <table> tidak akan cukup untuk kebutuhan Filter dan Sorting yang kompleks.

Gunakan konsep Headless UI seperti TanStack Table. Library ini hanya menyediakan logika sorting, filtering, dan pagination, namun membebaskan Anda sepenuhnya untuk mendesain UI (tampilan baris dan kolom) menggunakan CSS/Tailwind Anda sendiri. Ini memastikan tabel dirender dengan sangat cepat (high performance) meskipun menangani banyak kolom.

D. Route Protection (Frontend Security)
Sama seperti middleware di backend, frontend juga harus memiliki Protected Routes.

Buat komponen wrapper (misal: <ProtectedRoute role="HR,Direksi">). Jika pengguna biasa mencoba mengakses rute /employees, frontend akan otomatis melempar mereka ke halaman Unauthorized atau kembali ke halaman Login, mencegah rendering UI tabel sejak awal.

Dengan menggabungkan Headless Table di Frontend dan Dynamic Query Builder di Backend, Anda mendapatkan aplikasi yang tidak hanya cantik secara visual, tetapi juga sangat cepat saat mencari data spesifik dari ribuan baris.