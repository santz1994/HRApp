# HR Application - Setup & Usage Guide

## ✅ What's Been Accomplished

### 1. **Full-Stack Laravel HR Application** ✓
- **Backend**: Laravel 10.x with PHP 8.2.12
- **Authentication**: Laravel Sanctum token-based API
- **Architecture**: Controller-Service-Repository pattern (scalable design)
- **Database**: MySQL with 4 core tables

### 2. **Frontend Pages Created** ✓
- **Login Page** (`/`): Clean UI with test credentials
- **Dashboard** (`/dashboard`): Employee management interface
- Token-based authentication with localStorage

### 3. **API Endpoints Implemented** ✓

#### Authentication
```
POST   /api/auth/login         - Login & get token
POST   /api/auth/logout        - Logout
GET    /api/auth/me            - Get current user
```

#### Employee Management (Protected - auth:sanctum)
```
GET    /api/employees                        - List all employees (HR + Director)
GET    /api/employees/{id}                   - Get employee details (HR + Director)
POST   /api/employees                        - Create employee (HR only)
PUT    /api/employees/{id}                   - Update employee (HR only)
DELETE /api/employees/{id}                   - Delete employee (HR only)
```

#### Import/Export (HR only)
```
POST   /api/employees/import-export/import   - Import from Excel
GET    /api/employees/import-export/export   - Export to Excel
GET    /api/employees/import-export/template - Download template
```

### 4. **Database Schema** ✓
```sql
-- Employees Table (15+ columns)
- nik (unique)
- no_ktp (unique)
- nama
- department, jabatan
- tempat_lahir, tanggal_lahir, tanggal_masuk
- jenis_kelamin
- dept_on_line, dept_on_line_awal
- status_pkwtt, status_keluarga
- pendidikan, alamat

-- Calculated Fields (on-the-fly, not stored)
- age (current age)
- age_on_joining
- tenure_years
- tenure_formatted
```

### 5. **Role-Based Access Control (RBAC)** ✓
- **HR Role**: Full CRUD + Import/Export
- **Director Role**: Read-only access

### 6. **Test Credentials** ✓
```
Email: hr@quty.co.id
Password: password123
Role: HR (full access)

Email: director@quty.co.id
Password: password123
Role: Director (read-only)
```

---

## 🔄 How to Import Your Excel File

### Via API (cURL / Postman)

```bash
# 1. Get authentication token
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"hr@quty.co.id","password":"password123"}'

# Response: { "token": "1|xxxxx" }

# 2. Import Excel file
curl -X POST http://127.0.0.1:8000/api/employees/import-export/import \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -F "file=@/path/to/DATKAR APRIL 2026.xlsx"
```

### Expected Excel Column Headers

Your file should have these columns (case-insensitive):
- **Required**: NIK, NAMA, TANGGAL_MASUK, TANGGAL_LAHIR
- **Optional**: NO_KTP, DEPT, JABATAN, TEMPAT_LAHIR, JENIS_KELAMIN, DEPT_ON_LINE, DEPT_ON_LINE_AWAL, STATUS_PKWTT, STATUS_KELUARGA, PENDIDIKAN, ALAMAT

### Import Features
- ✓ **Upsert logic**: If NIK exists → update; else → create new
- ✓ **Date parsing**: Supports multiple formats (d-M-Y, d/m/Y, etc.)
- ✓ **Validation**: Checks required fields, returns detailed errors
- ✓ **Bulk processing**: Handles large files efficiently

---

## 📊 Application Features Following Project.md

### ✓ Data Separation
- **Stored Data**: All fields in DB
- **Calculated Data**: Age, Tenure computed on-the-fly (no cron jobs needed)

### ✓ Architecture Pattern
- **Controller**: Handles requests, validation, authorization
- **Service**: Business logic, data processing, calculations
- **Repository**: Database queries with dynamic filters

### ✓ Filter & Sorting
- Search by: NIK, name, email, department
- Filter by: department, status_pkwtt, gender
- Dynamic sorting (ascending/descending)

### ✓ RBAC Implementation
- Middleware-based access control
- Role-specific endpoints
- HR ↔ Director permission separation

---

## 🚀 Next Steps / Future Enhancements

As outlined in Project.md, the foundation supports these future updates:

1. **Advanced Dashboard Analytics**
   - Age distribution charts
   - Tenure analytics
   - Department statistics

2. **Department Management**
   - Create/manage departments (relation instead of string)
   - Department-wise analytics

3. **Position/Job Title Management**
   - Centralized position management
   - Linked to salary grades

4. **Bulk Excel Import with Progress**
   - Queue-based imports for large files
   - Real-time progress tracking

5. **Advanced Search & Filters**
   - Multi-field advanced filters
   - Saved filter presets

6. **Employee History & Audit Trail**
   - Track changes to employee records
   - Audit logs for compliance

---

## 📝 Development Notes

- **Server**: Running on http://127.0.0.1:8000
- **Database**: MySQL @ 127.0.0.1:3306 (hrapp database)
- **Framework**: Laravel 10.x, PHP 8.2.12
- **API Documentation**: See routes via `php artisan route:list`
- **Cache**: Use `php artisan cache:clear` if issues arise

All code follows the **Controller-Service-Repository** architecture pattern for scalability and maintainability.
