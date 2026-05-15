# HR Application - Complete User Guide

## 📋 Overview

This is a **production-ready HR Management System** built with Laravel 10.x following the **Controller-Service-Repository** architecture pattern as specified in Project.md.

### Key Features
✅ Role-based access control (HR vs Director)  
✅ Employee data import/export (Excel)  
✅ Dynamic filtering and sorting  
✅ Calculated fields (age, tenure)  
✅ Real-time employee management  

---

## 🔐 Login & Authentication

### Test Accounts

```
HR User (Full Access):
  Email: hr@quty.co.id
  Password: password123
  
Director User (Read-only):
  Email: director@quty.co.id
  Password: password123
```

### How Login Works
1. Go to `http://127.0.0.1:8000/`
2. Enter email and password
3. Click "Sign In"
4. Token is stored in browser's `localStorage` for API calls
5. Redirects to `/employees` page

### Logout
- Click your name in top-right corner
- Click "Logout"
- Token is cleared from localStorage

---

## 👥 Employee Management Page

Access at: **`http://127.0.0.1:8000/employees`**

### Page Features

#### 1️⃣ **Search & Filter Section** (Top)

| Feature | Description |
|---------|-------------|
| **Search Box** | Search by NIK, Name, or Email (real-time) |
| **Department Filter** | Filter employees by department |
| **Status Filter** | Filter by employment type (TETAP/KONTRAK) |
| **Sort By** | Choose column to sort (Name, NIK, Date) |
| **Sort Direction** | Ascending or Descending order |

**Example:**
- Search: "John" → Shows all employees with "John" in name
- Department: "SEWING" → Shows only SEWING department employees
- Status: "TETAP" → Shows only permanent employees
- Sort: "Name" + "Ascending" → A-Z by name

#### 2️⃣ **Data Table** (Main)

| Column | Description |
|--------|-------------|
| **NIK** | Employee ID number (unique identifier) |
| **Name** | Full employee name |
| **Email** | Work email address |
| **Position** | Job title/position |
| **Department** | Department assignment |
| **Status** | Employment type badge (TETAP=Green, KONTRAK=Yellow) |
| **Tenure** | How long employed (e.g., "5 years 3 months 12 days") |
| **Age** | Current age in years |
| **Actions** | Edit button (future feature) |

#### 3️⃣ **Summary Statistics** (Bottom)

- **Total Employees** - Count of all employees
- **Permanent** - Count of TETAP status employees
- **Contract** - Count of KONTRAK status employees
- **Departments** - Number of unique departments

#### 4️⃣ **Pagination**

- Shows page numbers at bottom
- 20 employees per page
- Click page number or Previous/Next buttons

---

## 📤 How to IMPORT Data

### 🎯 Scenario: You have DATKAR APRIL 2026.xlsx with real employee data

#### Via Web UI (Easiest)

1. Go to `/employees` page
2. Look for **"📤 Import Employee Data"** card (top-left)
3. Click **"Choose File"** and select your Excel file
4. Click **"Import Excel File"** button
5. Wait for success message ✓

#### What Happens During Import

| Process | Details |
|---------|---------|
| **Validation** | Checks required fields (NIK, Name, Join Date, Birth Date) |
| **Duplicate Detection** | If NIK exists → **UPDATE** existing record |
| **New Employees** | If NIK not exists → **CREATE** new record |
| **Date Parsing** | Handles formats: "11-Jan-91", "2024-05-13", "11/01/91" |
| **Status** | Shows count of successful imports and any errors |

#### Excel File Requirements

Your file should have these columns (case-insensitive):

| Required | Column | Example |
|----------|--------|---------|
| ✅ | NIK | 12345678901234 |
| ✅ | NAMA | John Doe |
| ✅ | TANGGAL_MASUK | 11-Jan-91 |
| ✅ | TANGGAL_LAHIR | 11-Jan-75 |
| ⭐ | NO_KTP | 1234567890123456 |
| ⭐ | DEPT | SEWING |
| ⭐ | JABATAN | Supervisor |
| ⭐ | TEMPAT_LAHIR | Jakarta |
| ⭐ | JENIS_KELA | L/P |
| ⭐ | DEPT_ON_LINE | SEWING-01 |
| ⭐ | DEPT_ON_LINE_awal | SEWING-01 |
| ⭐ | STATUS_PKWTT | TETAP/KONTRAK |
| ⭐ | STATUS_KELUARGA | K/1 |
| ⭐ | PENDIDIKAN | SMA |
| ⭐ | ALAMAT | Jl. Merdeka No. 123 |

✅ = Required  
⭐ = Optional (but recommended)

#### Example: Import Your DATKAR File

```powershell
# PowerShell on Windows
$headers = @{
    "Authorization" = "Bearer 1|YOUR_TOKEN_HERE"
}
$form = @{
    file = Get-Item -Path "D:\DATKAR APRIL 2026.xlsx"
}
$response = Invoke-WebRequest `
    -Uri "http://127.0.0.1:8000/api/employees/import-export/import" `
    -Method Post `
    -Headers $headers `
    -Form $form

$response.Content | ConvertFrom-Json
```

#### Response Example

✅ **Success:**
```json
{
    "success": true,
    "message": "Import completed successfully",
    "imported_count": 245,
    "failed_count": 0,
    "imported_ids": [1, 2, 3, ..., 245]
}
```

❌ **With Errors:**
```json
{
    "success": false,
    "message": "Import completed with errors",
    "imported_count": 240,
    "failed_count": 5,
    "errors": [
        { "row": 2, "error": "Invalid date format in column TANGGAL_LAHIR" },
        { "row": 5, "error": "NIK cannot be empty" }
    ]
}
```

---

## 📥 How to EXPORT Data

### 🎯 Scenario: You want to download current employee database as Excel

#### Via Web UI

1. Go to `/employees` page
2. Look for **"📥 Export Employee Data"** card (top-right)
3. Select export format:
   - **"All Employees (with calculations)"** - Full data + calculated fields
   - **"Template (for import)"** - Empty template for new imports
4. Click **"Download Excel"** button
5. File saves as `employees_YYYY-MM-DD.xlsx`

#### What Gets Exported

**All Employees Export includes:**
- All stored data (NIK, Name, Position, Department, etc.)
- Calculated fields:
  - **Age** - Current age computed from birth date
  - **Tenure** - Time employed formatted as "X years Y months Z days"
  - Plus original Excel columns

**Template Export includes:**
- Empty template structure
- Column headers only
- Ready for you to fill and re-import

#### Example: Download All Employees

```powershell
# Get token first
$loginResponse = Invoke-WebRequest `
    -Uri "http://127.0.0.1:8000/api/auth/login" `
    -Method Post `
    -Headers @{"Content-Type" = "application/json"} `
    -Body '{"email":"hr@quty.co.id","password":"password123"}''

$token = ($loginResponse.Content | ConvertFrom-Json).token

# Download Excel
$headers = @{"Authorization" = "Bearer $token"}
$response = Invoke-WebRequest `
    -Uri "http://127.0.0.1:8000/api/employees/import-export/export" `
    -Method Get `
    -Headers $headers `
    -OutFile "employees_export.xlsx"

Write-Host "✓ File saved as employees_export.xlsx"
```

---

## 🔍 How to SORT & FILTER Data

### Dynamic Filtering

| Filter | Behavior |
|--------|----------|
| **Search Box** | Real-time search (300ms debounce) |
| **Department** | Exact match filter |
| **Status** | TETAP or KONTRAK only |
| **Sort Column** | Choose: Name, NIK, or Date |
| **Sort Order** | Ascending (A-Z) or Descending (Z-A) |

### Combined Filter Example

**Goal:** Find all TETAP employees in SEWING department, sorted by name

1. Set **Department Filter** → "SEWING"
2. Set **Status Filter** → "TETAP"
3. Set **Sort By** → "Nama" (Name)
4. Set **Sort Direction** → "Ascending"
5. Table updates automatically ✓

### Database Query Behind Filters

```php
// From Repository Layer
$query = Employee::query();

// Department filter
if ($department) {
    $query->where('department', $department);
}

// Status filter
if ($status) {
    $query->where('status_pkwtt', $status);
}

// Search filter
if ($search) {
    $query->where(function($q) use ($search) {
        $q->where('nik', 'like', "%$search%")
          ->orWhere('nama', 'like', "%$search%")
          ->orWhere('email', 'like', "%$search%");
    });
}

// Sort
$query->orderBy($sortBy, $sortDir);

// Paginate
return $query->paginate($perPage);
```

---

## ✏️ How to UPDATE Employee Data

### 🎯 Scenario: Modify existing employee information

#### Via API

```bash
# Update specific employee
curl -X PUT http://127.0.0.1:8000/api/employees/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nama": "John Smith",
    "jabatan": "Senior Supervisor",
    "department": "QUALITY",
    "email": "john.smith@company.com"
  }'
```

#### Response

```json
{
    "id": 1,
    "nik": "12345678901234",
    "nama": "John Smith",
    "jabatan": "Senior Supervisor",
    "department": "QUALITY",
    "email": "john.smith@company.com",
    "age": 48,
    "tenure_formatted": "5 years 2 months 15 days",
    "updated_at": "2026-05-13T15:30:00.000000Z"
}
```

#### Update via Import/Export

The **easiest way** to update multiple employees:

1. **Export** current data → `employees_export.xlsx`
2. **Edit** the Excel file (change positions, departments, etc.)
3. **Re-import** the file
4. System detects NIK matches and **updates** instead of creating duplicates ✓

---

## 🏗️ Architecture Explanation

### Controller-Service-Repository Pattern

```
Request Flow:
  HTTP Request
      ↓
  LoginController.php (HTTP handling)
      ↓
  AuthService.php (Business logic - login, token generation)
      ↓
  UserRepository.php (Database queries)
      ↓
  Database (MySQL)
      
  Response Flow (reverse):
  Database → Repository → Service → Controller → JSON Response
```

### Data Separation

As per Project.md:

**Stored in Database:**
- nik, no_ktp, nama, department, jabatan, tempat_lahir, tanggal_masuk, tanggal_lahir, jenis_kelamin, dept_on_line, dept_on_line_awal, status_pkwtt, status_keluarga, pendidikan, alamat

**Calculated On-the-Fly (not stored):**
- age - Current age from `tanggal_lahir`
- age_on_joining - Age when joined from `tanggal_masuk - tanggal_lahir`
- tenure_years - Years of service as decimal
- tenure_formatted - "X years Y months Z days" format

This means:
✅ No cron job needed to update age daily  
✅ Always accurate calculated fields  
✅ Reduced database storage  

### Role-Based Access Control (RBAC)

```php
// HR User - Full Access
GET    /api/employees              → ✓ Can list
POST   /api/employees              → ✓ Can create
PUT    /api/employees/{id}         → ✓ Can update
DELETE /api/employees/{id}         → ✓ Can delete
POST   /api/employees/import-export/import  → ✓ Can import
GET    /api/employees/import-export/export  → ✓ Can export

// Director User - Read-Only
GET    /api/employees              → ✓ Can list
POST   /api/employees              → ✗ 403 Forbidden
PUT    /api/employees/{id}         → ✗ 403 Forbidden
DELETE /api/employees/{id}         → ✗ 403 Forbidden
POST   /api/employees/import-export/import  → ✗ 403 Forbidden
GET    /api/employees/import-export/export  → ✗ 403 Forbidden
```

---

## 📊 Database Schema

### employees table

```sql
CREATE TABLE employees (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nik VARCHAR(255) UNIQUE,
    no_ktp VARCHAR(255) UNIQUE,
    nama VARCHAR(255),
    department VARCHAR(255),
    jabatan VARCHAR(255),
    tempat_lahir VARCHAR(255),
    tanggal_masuk DATE,
    tanggal_lahir DATE,
    jenis_kelamin ENUM('L', 'P'),
    dept_on_line VARCHAR(255),
    dept_on_line_awal VARCHAR(255),
    status_pkwtt ENUM('TETAP', 'KONTRAK'),
    status_keluarga VARCHAR(50),
    pendidikan VARCHAR(50),
    alamat TEXT,
    email VARCHAR(255),
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🔗 API Endpoints Reference

### Authentication

```
POST /api/auth/login
  Input:  { email, password }
  Output: { token, user }
  
POST /api/auth/logout
  Auth: Required
  Output: { message }
  
GET /api/auth/me
  Auth: Required
  Output: { user data }
```

### Employee Management

```
GET /api/employees
  Auth: Required
  Params: search, department, status_pkwtt, sort_by, sort_dir, page, per_page
  Output: { data, total, current_page, last_page }
  
POST /api/employees
  Auth: Required (HR only)
  Input: { nik, nama, tanggal_lahir, ... }
  Output: { id, nik, nama, ... }
  
PUT /api/employees/{id}
  Auth: Required (HR only)
  Input: { nik, nama, ... }
  Output: { id, nik, nama, ... }
  
DELETE /api/employees/{id}
  Auth: Required (HR only)
  Output: { message }
```

### Import/Export

```
POST /api/employees/import-export/import
  Auth: Required (HR only)
  Form: file (xlsx/xls)
  Output: { success, imported_count, failed_count, errors }
  
GET /api/employees/import-export/export
  Auth: Required (HR only)
  Output: Excel file (binary)
  
GET /api/employees/import-export/template
  Auth: Required (HR only)
  Output: Excel template (binary)
```

---

## ⚙️ Configuration

### .env File

```
APP_NAME="HR System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hrapp
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=127.0.0.1:8000
SESSION_DOMAIN=127.0.0.1
```

### Important Artisan Commands

```bash
# Run migrations
php artisan migrate

# Seed test data
php artisan db:seed

# Clear cache if needed
php artisan cache:clear
php artisan config:clear

# View all routes
php artisan route:list

# Run development server
php artisan serve

# Interactive shell
php artisan tinker
```

---

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| **404 Not Found on /employees** | Clear cache: `php artisan cache:clear` |
| **Unauthorized (401) on API calls** | Check token in localStorage, re-login |
| **Excel import fails** | Verify column names match spec, dates are valid |
| **Calculated fields show old values** | Hard refresh browser (Ctrl+F5) |
| **Database not connecting** | Check DB credentials in `.env` and MySQL running |

---

## 🚀 Next Features (Planned)

Based on Project.md recommendations:

- [ ] Department management module
- [ ] Position/job title management
- [ ] Salary grades & payroll integration
- [ ] Employee history & audit trail
- [ ] Advanced dashboard analytics
- [ ] Bulk operations (delete, status change)
- [ ] Email notifications
- [ ] Mobile app support

---

## 📞 Support

For issues or questions:
1. Check the troubleshooting section above
2. Review API response messages
3. Check Laravel logs: `storage/logs/laravel.log`
4. Clear cache and retry

---

**Last Updated:** May 13, 2026  
**Version:** 1.0 (Production Ready)
