# API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication

All endpoints (except login) require Bearer token authentication.

### Login
**Endpoint:** `POST /auth/login`

**Request:**
```json
{
  "email": "hr@hrapp.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "token": "1|abc123...",
  "user": {
    "id": 2,
    "name": "HR User",
    "email": "hr@hrapp.com",
    "role": "hr"
  }
}
```

**Usage in requests:**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8000/api/employees
```

### Logout
**Endpoint:** `POST /auth/logout`

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "message": "Logout successful"
}
```

### Get Current User
**Endpoint:** `GET /auth/me`

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "user": {
    "id": 2,
    "name": "HR User",
    "email": "hr@hrapp.com",
    "role": "hr"
  }
}
```

---

## Employee Management Endpoints

### List Employees
**Endpoint:** `GET /employees`

**Access:** HR, Director

**Query Parameters:**
- `search` (string): Search by NIK, KTP, name, or department
- `department` (string): Filter by department
- `status_pkwtt` (string): Filter by status (TETAP, KONTRAK)
- `jenis_kelamin` (string): Filter by gender (L, P)
- `sort_by` (string): Sort field (default: nama)
- `sort_dir` (string): Sort direction (asc, desc)
- `per_page` (integer): Items per page (default: 50, max: 500)

**Example:**
```bash
GET /employees?department=Finance&status_pkwtt=TETAP&sort_by=nama&sort_dir=asc&per_page=20
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nik": "12345678901234567890",
      "no_ktp": "3171234567890123",
      "nama": "John Doe",
      "department": "Finance",
      "jabatan": "Manager",
      "tempat_lahir": "Jakarta",
      "tanggal_lahir": "1985-05-15",
      "tanggal_masuk": "2015-03-01",
      "jenis_kelamin": "L",
      "status_pkwtt": "TETAP",
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-01T00:00:00Z"
    }
  ],
  "meta": {
    "total": 100,
    "per_page": 20,
    "current_page": 1,
    "last_page": 5
  }
}
```

### Get Employee Details
**Endpoint:** `GET /employees/{id}`

**Access:** HR, Director

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nik": "12345678901234567890",
    "no_ktp": "3171234567890123",
    "nama": "John Doe",
    "department": "Finance",
    "jabatan": "Manager",
    "tempat_lahir": "Jakarta",
    "tanggal_lahir": "1985-05-15",
    "tanggal_masuk": "2015-03-01",
    "jenis_kelamin": "L",
    "status_pkwtt": "TETAP",
    "age": 38,
    "age_on_joining": 30,
    "tenure_years": 8.84,
    "tenure_formatted": "8 years 10 months 30 days",
    "created_at": "2024-01-01T00:00:00Z",
    "updated_at": "2024-01-01T00:00:00Z"
  }
}
```

### Create Employee
**Endpoint:** `POST /employees`

**Access:** HR only

**Request:**
```json
{
  "nik": "12345678901234567895",
  "no_ktp": "3171234567890127",
  "nama": "New Employee",
  "department": "IT",
  "jabatan": "Developer",
  "tempat_lahir": "Jakarta",
  "tanggal_lahir": "1995-06-20",
  "tanggal_masuk": "2024-01-15",
  "jenis_kelamin": "L",
  "status_pkwtt": "KONTRAK",
  "status_keluarga": "TK/0",
  "pendidikan": "S1",
  "alamat": "Jalan Example No. 123"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Employee created successfully",
  "data": {
    "id": 5,
    "nik": "12345678901234567895",
    "no_ktp": "3171234567890127",
    "nama": "New Employee",
    ...
  }
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "Employee with this NIK already exists."
}
```

### Update Employee
**Endpoint:** `PUT /employees/{id}`

**Access:** HR only

**Request:** (All fields optional)
```json
{
  "jabatan": "Senior Developer",
  "status_pkwtt": "TETAP"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Employee updated successfully",
  "data": { ... }
}
```

### Delete Employee
**Endpoint:** `DELETE /employees/{id}`

**Access:** HR only

**Response (200):**
```json
{
  "success": true,
  "message": "Employee deleted successfully"
}
```

### Get Dashboard Statistics
**Endpoint:** `GET /employees/statistics`

**Access:** HR, Director

**Response (200):**
```json
{
  "success": true,
  "data": {
    "total_employees": 4,
    "by_department": {
      "Finance": 1,
      "Human Resources": 1,
      "IT": 1,
      "Marketing": 1
    },
    "by_status": {
      "TETAP": 3,
      "KONTRAK": 1
    },
    "departments": ["Finance", "Human Resources", "IT", "Marketing"]
  }
}
```

---

## Import/Export Endpoints

### Get Import Template
**Endpoint:** `GET /employees/import-export/template`

**Access:** HR only

**Response:** Excel file download

**Description:** Downloads template with proper column headers for importing employees

### Export Employees
**Endpoint:** `GET /employees/import-export/export`

**Access:** HR only

**Query Parameters:**
- `department` (string): Filter by department before export
- `status_pkwtt` (string): Filter by status before export

**Response:** Excel file download

**File includes columns:**
- NIK, No. KTP, Nama, Department, Jabatan
- Tempat Lahir, Tanggal Lahir, Tanggal Masuk
- Jenis Kelamin, Umur Sekarang, Umur Saat Masuk, Masa Kerja
- Status PKWTT, Status Keluarga, Pendidikan, Alamat

### Import Employees
**Endpoint:** `POST /employees/import-export/import`

**Access:** HR only

**Request:** Multipart form data with file upload

```bash
curl -X POST http://localhost:8000/api/employees/import-export/import \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@employees.xlsx"
```

**Response (200):**
```json
{
  "success": true,
  "message": "Import completed",
  "data": {
    "total": 50,
    "success": 48,
    "failed": 2,
    "errors": [
      {
        "row": 10,
        "data": { ... },
        "error": "Employee with this NIK already exists."
      }
    ]
  }
}
```

**Features:**
- Upsert logic: Updates existing employees (by NIK) or creates new ones
- Validates all required fields
- Reports errors row-by-row
- Returns summary of successful and failed imports

---

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Forbidden - Insufficient permissions"
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Employee not found"
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "message": "Error message here"
}
```

---

## Rate Limiting & Pagination

- **Default per_page:** 50 items
- **Maximum per_page:** 500 items
- **Pagination:** Uses Laravel's standard pagination with Eloquent

## Testing with cURL

### Login and save token
```bash
TOKEN=$(curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"hr@hrapp.com","password":"password123"}' | jq -r '.token')

echo $TOKEN
```

### Use token in requests
```bash
curl -X GET http://localhost:8000/api/employees \
  -H "Authorization: Bearer $TOKEN"
```

---

**Note:** All timestamps are in UTC and follow ISO 8601 format.
