# 🚀 QUICK START - UPLOAD BOOK1.XLSX

**Format File:** Excel (.xlsx, .xls) atau CSV
**Database:** hrapp  
**Tabel:** employees (25 kolom)
**Max Upload:** 10 MB

---

## 📊 STRUKTUR KOLOM DATABASE vs EXCEL

Dari database `employees`, mapping kolom Excel yang bisa digunakan:

```
Excel Column           → Database Column      → Type    | Required
─────────────────────────────────────────────────────────────────
NIK Karyawan           → nik_karyawan        → String  | ✅
No. KTP                → no_ktp              → String  | ✅  
Nama Lengkap           → nama_lengkap        → String  | ✅
Departemen             → department_id       → FK      | ✅
Jabatan                → position_id         → FK      | ✅
Tanggal Masuk Kerja    → tanggal_masuk_kerja → Date    | ✅
───────────────────────────────────────────────────────────────
Tempat Lahir           → tempat_lahir        → String  | ⚠️
Tanggal Lahir          → tanggal_lahir       → Date    | ⚠️
Jenis Kelamin (L/P)    → jenis_kelamin       → Enum    | ⚠️
Status PKWTT           → status_pkwtt        → Enum    | ⚠️
Status Keluarga        → status_keluarga     → Enum    | ⚠️
Jumlah Anak            → jumlah_anak         → Int     | ⚠️
Pendidikan             → pendidikan          → String  | ⚠️
Alamat KTP             → alamat_ktp          → Text    | ⚠️
Alamat Domisili        → alamat_domisili     → Text    | ⚠️
Status Pajak           → status_pajak        → String  | ⚠️
```

---

## 🔐 STEP 1: LOGIN & DAPATKAN TOKEN

```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "hr@quty.co.id",
    "password": "password"
  }' | jq
```

**Simpan token dari response:**
```bash
TOKEN="1|abc123..."
```

---

## ✅ STEP 2: VALIDASI FILE

```bash
curl -X POST http://127.0.0.1:8000/api/import/validate \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@Book1.xlsx" | jq
```

**Expected Response (Sukses):**
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

---

## 🔍 STEP 3: PREVIEW DATA (Opsional)

```bash
curl -X POST http://127.0.0.1:8000/api/import/preview \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@Book1.xlsx" | jq
```

Akan menampilkan:
- Header kolom
- 5 baris data pertama untuk preview

---

## 📥 STEP 4: IMPORT KE DATABASE

```bash
curl -X POST http://127.0.0.1:8000/api/import/process \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@Book1.xlsx" | jq
```

**Expected Response (Sukses):**
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
        "baris": 10,
        "error": "NIK Karyawan sudah terdaftar"
      }
    ]
  }
}
```

---

## 📋 CEK STATUS IMPORT

```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://127.0.0.1:8000/api/import/status | jq
```

**Response:**
```json
{
  "sukses": true,
  "pesan": "Status import",
  "data": {
    "total_karyawan": 150,
    "karyawan_aktif": 145,
    "karyawan_terhapus": 5
  }
}
```

---

## 📥 CONTOH FILE BOOK1.XLSX

**Baris 1 (Header):**
```
NIK Karyawan | No. KTP | Nama Lengkap | Departemen | Jabatan | Tanggal Masuk Kerja | Tempat Lahir | Tanggal Lahir | Jenis Kelamin | Status PKWTT | Status Keluarga | Jumlah Anak | Pendidikan | Alamat KTP | Alamat Domisili
```

**Baris 2 (Data Contoh):**
```
1001 | 3171234567890123 | Budi Santoso | IT | Developer | 2020-01-10 | Jakarta | 1990-05-15 | L | TETAP | Kawin | 2 | S1 | Jl. Sudirman 123 | Jl. Gatot Subroto 456
```

---

## 🎯 ALL IN ONE SCRIPT

Jalankan semuanya di satu command:

```bash
#!/bin/bash

# Login
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"hr@quty.co.id","password":"password"}' | jq -r '.token')

echo "✅ Token: $TOKEN"

# Validasi
echo "🔍 Validasi..."
curl -s -X POST http://127.0.0.1:8000/api/import/validate \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@Book1.xlsx" | jq

# Import
echo "📥 Import..."
curl -s -X POST http://127.0.0.1:8000/api/import/process \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@Book1.xlsx" | jq

# Status
echo "📊 Status..."
curl -s -H "Authorization: Bearer $TOKEN" \
  http://127.0.0.1:8000/api/import/status | jq
```

**Simpan sebagai `upload.sh`, kemudian jalankan:**
```bash
chmod +x upload.sh
./upload.sh
```

---

## 🚨 ERROR FIXES

| Error | Solusi |
|-------|--------|
| "Unauthorized" (401) | Login dulu, dapatkan token, gunakan di Authorization header |
| "File tidak mengandung data" | Pastikan header di baris 1, data mulai baris 2 |
| "NIK Karyawan wajib diisi" | Isi kolom NIK di semua baris data |
| "NIK Karyawan sudah terdaftar" | Data akan di-update (upsert), bukan error |
| "Format tanggal tidak valid" | Gunakan format YYYY-MM-DD |
| "Departemen tidak ditemukan" | Pastikan nama departemen sesuai di database |

---

## 🔗 API ENDPOINTS

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | /api/import/preview | Preview 5 baris pertama |
| POST | /api/import/validate | Validasi file tanpa import |
| POST | /api/import/process | Import ke database |
| GET | /api/import/template | Download template |
| GET | /api/import/status | Cek status import terakhir |

---

## ✨ NOTES

- ✅ Semua response menggunakan **Bahasa Indonesia**
- ✅ Nama kolom fleksibel (English atau Indonesian)
- ✅ Sistem akan **upsert** berdasarkan NIK (update jika ada, insert jika baru)
- ✅ File bisa sampai **10 MB**
- ✅ Format support: **XLSX, XLS, CSV**

---

**Ready?** Copy paste curl command di atas dan jalankan! 🚀

