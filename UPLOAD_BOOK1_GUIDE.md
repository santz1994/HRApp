# 📊 PANDUAN UPLOAD BOOK1.XLSX KE DATABASE KARYAWAN

**Status:** ✅ Siap digunakan
**Bahasa:** Bahasa Indonesia (sesuai database)
**Format:** Excel (.xlsx, .xls) atau CSV
**Max Size:** 10 MB

---

## 📋 STRUKTUR KOLOM EXCEL (BOOK1.XLSX)

Kolom-kolom yang harus ada di file Excel:

| # | Kolom Database | Kolom Excel (Bisa Salah Satu) | Tipe | Wajib | Contoh |
|---|---|---|---|---|---|
| 1 | nik_karyawan | NIK Karyawan, nik_karyawan, nik | String | ✅ | 1001 |
| 2 | no_ktp | No. KTP, no_ktp, NO KTP | String (16 digit) | ✅ | 3171234567890123 |
| 3 | nama_lengkap | Nama Lengkap, nama, Nama | String | ✅ | Budi Santoso |
| 4 | department_id | Departemen, nama_departemen, department | String | ✅ | IT |
| 5 | position_id | Jabatan, nama_jabatan, position | String | ✅ | Developer |
| 6 | tempat_lahir | Tempat Lahir, tempat_lahir | String | ⚠️ | Jakarta |
| 7 | tanggal_lahir | Tanggal Lahir, tanggal_lahir | Date (YYYY-MM-DD) | ⚠️ | 1990-05-15 |
| 8 | tanggal_masuk_kerja | Tanggal Masuk Kerja, tanggal_masuk | Date (YYYY-MM-DD) | ✅ | 2020-01-10 |
| 9 | jenis_kelamin | Jenis Kelamin, jenis_kelamin | L atau P | ⚠️ | L |
| 10 | status_pkwtt | Status PKWTT, status_pkwtt | TETAP, KONTRAK, HARIAN, MAGANG | ⚠️ | TETAP |
| 11 | status_keluarga | Status Keluarga, status_keluarga | Lajang, Kawin, Cerai Hidup, Cerai Mati | ⚠️ | Kawin |
| 12 | jumlah_anak | Jumlah Anak, jumlah_anak | Angka | ⚠️ | 2 |
| 13 | pendidikan | Pendidikan, pendidikan | S1, S2, D3, SMA, dll | ⚠️ | S1 |
| 14 | alamat_ktp | Alamat KTP, alamat_ktp | Text | ⚠️ | Jl. Sudirman No. 123 |
| 15 | alamat_domisili | Alamat Domisili, alamat_domisili | Text | ⚠️ | Jl. Gatot Subroto No. 456 |

**Catatan:**
- ✅ = Kolom WAJIB diisi
- ⚠️ = Kolom opsional (bisa dikosongkan)
- Nama kolom fleksibel (bisa English atau Indonesian)
- Urutan kolom tidak harus sama dengan tabel di atas

---

## 🎯 LANGKAH-LANGKAH UPLOAD

### **Step 1: Validasi File Excel**

```bash
curl -X POST http://127.0.0.1:8000/api/import/validate \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@Book1.xlsx"
```

**Response:**
```json
{
  "sukses": true,
  "pesan": "Data valid, siap diimpor",
  "data": {
    "total_baris": 50,
    "error_count": 0,
    "errors": []
  }
}
```

Jika ada error, akan ditampilkan di array `errors`.

---

### **Step 2: Preview Data (Opsional)**

Lihat preview 5 baris pertama sebelum import:

```bash
curl -X POST http://127.0.0.1:8000/api/import/preview \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@Book1.xlsx"
```

**Response:**
```json
{
  "sukses": true,
  "pesan": "File berhasil dibaca",
  "data": {
    "nama_file": "Book1.xlsx",
    "ukuran_file": "45KB",
    "total_baris_data": 50,
    "header_kolom": [
      "nik_karyawan", "no_ktp", "nama_lengkap", "departemen", ...
    ],
    "preview_data": [
      {
        "nik_karyawan": "1001",
        "no_ktp": "3171234567890123",
        "nama_lengkap": "Budi Santoso",
        "departemen": "IT",
        "jabatan": "Developer",
        ...
      },
      ...
    ]
  }
}
```

---

### **Step 3: Import Data ke Database**

Setelah validasi sukses, lakukan import:

```bash
curl -X POST http://127.0.0.1:8000/api/import/process \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@Book1.xlsx"
```

**Response Sukses:**
```json
{
  "sukses": true,
  "pesan": "Import berhasil selesai",
  "data": {
    "nama_file": "Book1.xlsx",
    "total_baris": 50,
    "berhasil": 48,
    "gagal": 2,
    "id_karyawan_diimpor": [1, 2, 3, 4, ...],
    "error_detail": [
      {
        "baris": 5,
        "error": "NIK Karyawan sudah terdaftar"
      }
    ]
  }
}
```

---

## 🔑 LOGIN & GET TOKEN

### **Dapatkan Token API:**

```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "hr@quty.co.id",
    "password": "password"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "token": "1|abcdef123456...",
  "user": {
    "id": 2,
    "name": "HR Manager",
    "email": "hr@quty.co.id",
    "role": "hr"
  }
}
```

**Gunakan token di header Authorization untuk semua request:**
```
-H "Authorization: Bearer 1|abcdef123456..."
```

---

## 📥 UPLOAD VIA FORM (untuk frontend/UI)

### HTML Form Example:

```html
<form id="uploadForm" enctype="multipart/form-data">
  <input type="file" id="file" name="file" accept=".xlsx,.xls,.csv" required />
  <button type="submit">Upload & Import</button>
</form>

<script>
document.getElementById('uploadForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const file = document.getElementById('file').files[0];
  const formData = new FormData();
  formData.append('file', file);
  
  const token = localStorage.getItem('auth_token');
  
  // Validate first
  const validateRes = await fetch('/api/import/validate', {
    method: 'POST',
    headers: { 'Authorization': `Bearer ${token}` },
    body: formData
  });
  
  const validateData = await validateRes.json();
  
  if (!validateData.sukses) {
    alert('Validasi gagal: ' + JSON.stringify(validateData.data.errors));
    return;
  }
  
  // Then process import
  const processRes = await fetch('/api/import/process', {
    method: 'POST',
    headers: { 'Authorization': `Bearer ${token}` },
    body: formData
  });
  
  const processData = await processRes.json();
  
  if (processData.sukses) {
    alert(`Sukses! ${processData.data.berhasil} karyawan diimpor`);
  } else {
    alert('Import gagal: ' + processData.pesan);
  }
});
</script>
```

---

## 📋 CONTOH FILE EXCEL (BOOK1.XLSX)

### Baris Header (Baris 1):
```
NIK Karyawan | No. KTP | Nama Lengkap | Departemen | Jabatan | Tempat Lahir | Tanggal Lahir | Tanggal Masuk Kerja | Jenis Kelamin | Status PKWTT | Status Keluarga | Jumlah Anak | Pendidikan | Alamat KTP | Alamat Domisili
```

### Data Contoh (Baris 2-3):
```
1001 | 3171234567890123 | Budi Santoso | IT | Developer | Jakarta | 1990-05-15 | 2020-01-10 | L | TETAP | Kawin | 2 | S1 | Jl. Sudirman No. 123 | Jl. Gatot Subroto No. 456

1002 | 3171234567890124 | Siti Nurhaliza | HR | Manager | Bandung | 1988-08-20 | 2019-06-15 | P | TETAP | Lajang | 0 | S1 | Jl. Ahmad Yani No. 456 | Jl. Cipaganti No. 789
```

---

## ✅ TEMPLATE DOWNLOAD

### Download template kosong dari API:

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://127.0.0.1:8000/api/import/template
```

**Response:**
```json
{
  "sukses": true,
  "pesan": "Template siap diunduh",
  "data": {
    "template": [...],
    "daftar_departemen": ["IT", "HR", "Finance", ...],
    "daftar_jabatan": ["Developer", "Manager", "Staff", ...],
    "catatan": [...]
  }
}
```

---

## 🚨 ERROR & SOLUSI

### Error 1: "File tidak mengandung data"
```json
{
  "sukses": false,
  "pesan": "File kosong atau tidak mengandung data"
}
```
**Solusi:** Pastikan Excel memiliki header di baris 1 dan data mulai dari baris 2.

---

### Error 2: "NIK Karyawan wajib diisi"
```json
{
  "sukses": false,
  "pesan": "Data tidak valid",
  "data": {
    "errors": [
      {
        "baris": 5,
        "error": "NIK Karyawan wajib diisi"
      }
    ]
  }
}
```
**Solusi:** Isi kolom NIK Karyawan di semua baris data.

---

### Error 3: "NIK Karyawan sudah terdaftar"
```json
{
  "sukses": true,
  "pesan": "Import berhasil selesai",
  "data": {
    "berhasil": 48,
    "gagal": 2,
    "error_detail": [
      {
        "baris": 5,
        "error": "NIK Karyawan sudah terdaftar"
      }
    ]
  }
}
```
**Solusi:** Data akan di-update (upsert) berdasarkan NIK. Jika NIK sudah ada, datanya akan diperbarui.

---

### Error 4: "Unauthorized" (401)
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```
**Solusi:**
1. Login terlebih dahulu dengan `/api/auth/login`
2. Gunakan token di header Authorization
3. Pastikan user memiliki role HR atau IT

---

### Error 5: "Format tanggal tidak valid"
```json
{
  "sukses": false,
  "pesan": "...",
  "error": "Format tanggal tidak valid"
}
```
**Solusi:** Gunakan format tanggal YYYY-MM-DD (contoh: 1990-05-15)

---

## 🔍 CEK STATUS IMPORT TERAKHIR

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://127.0.0.1:8000/api/import/status
```

**Response:**
```json
{
  "sukses": true,
  "pesan": "Status import",
  "data": {
    "total_karyawan": 150,
    "karyawan_aktif": 145,
    "karyawan_terhapus": 5,
    "import_terakhir": {
      "id": 1,
      "user_id": 2,
      "action": "IMPORT_KARYAWAN",
      "table_name": "employees",
      "old_values": null,
      "new_values": {...},
      "created_at": "2026-05-15 10:30:00"
    }
  }
}
```

---

## 📱 AKSES ROLE

**Siapa yang bisa import?**
- ✅ HR Manager (hr@quty.co.id)
- ✅ IT Administrator (it@quty.co.id)
- ❌ Director (read-only)
- ❌ Admin Department (read-only dept)
- ❌ Employee (self-service)

---

## 🎯 WORKFLOW LENGKAP

```
1. Login → Dapatkan Token
          ↓
2. Validasi File → Pastikan data valid
          ↓
3. Preview Data → (Opsional) Lihat contoh data
          ↓
4. Import Process → Upload ke database
          ↓
5. Cek Status → Lihat hasil import
          ↓
6. Success! → Karyawan sudah terdaftar di sistem
```

---

## 🛠️ TROUBLESHOOTING

### Kolom tidak terbaca
- Pastikan header di baris 1
- Nama kolom bisa English atau Indonesia
- Sistem akan otomatis mengenali (case-insensitive)

### Data tidak lengkap
- Cek kolom wajib: NIK Karyawan, Nama, Tanggal Masuk
- Gunakan format yang benar untuk tanggal (YYYY-MM-DD)

### Import lambat
- Jika banyak data (>1000 baris), gunakan queue worker
- Jalankan: `php artisan queue:work`

---

## 📞 QUICK COMMANDS

**Login:**
```bash
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"hr@quty.co.id","password":"password"}' | jq -r '.token')
```

**Validasi & Import:**
```bash
curl -X POST http://127.0.0.1:8000/api/import/process \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@Book1.xlsx"
```

---

**Ready to upload Book1.xlsx? Gunakan curl commands di atas atau buka UI di browser!** 🚀

**Last Updated:** May 15, 2026
**Language:** Bahasa Indonesia
**Status:** ✅ Production Ready
