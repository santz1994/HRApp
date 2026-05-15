# 🚀 Quick Reference Guide

## ⚡ Super Quick Start (5 minutes)

```bash
# 1. Open terminal and navigate
cd d:\Project\HRApp

# 2. Install dependencies
composer install

# 3. Setup environment
copy .env.example .env
php artisan key:generate

# 4. Create database
mysql -u root -p
CREATE DATABASE hrapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# 5. Run migrations and seeders
php artisan migrate --seed

# 6. Start server
php artisan serve

# Now visit: http://localhost:8000/api/auth/login
```

## 🔐 Login Credentials

```
Email: hr@quty.co.id
Password: password123

OR

Email: director@quty.co.id
Password: password123
```

## 📡 Test API Quickly

### 1. Login and get token
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"hr@quty.co.id","password":"password123"}'
```

### 2. Copy the token, then list employees
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/employees
```

### 3. Create employee
```bash
curl -X POST http://localhost:8000/api/employees \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nik":"12345",
    "no_ktp":"67890",
    "nama":"John Doe",
    "department":"IT",
    "jabatan":"Developer",
    "tanggal_masuk":"2024-01-01",
    "status_pkwtt":"TETAP"
  }'
```

## 📚 Key Files & What They Do

| File | Purpose | What to Know |
|------|---------|--------------|
| `app/Models/Employee.php` | Data model | Has calculated accessors for age, tenure |
| `app/Services/EmployeeService.php` | Business logic | Where all validation & operations happen |
| `app/Repositories/EmployeeRepository.php` | Database queries | Where filtering & pagination happen |
| `app/Http/Controllers/EmployeeController.php` | API endpoints | REST CRUD operations |
| `routes/api.php` | API routes | Where RBAC middleware is applied |
| `database/migrations/*.php` | Database schema | Table definitions |
| `database/seeders/*.php` | Test data | Creates roles, users, employees |

## 🎯 Common Tasks

### Add a new employee
1. POST to `/api/employees` with HR role
2. Service validates and checks for duplicates
3. Repository creates in database

### Export employees
1. GET `/api/employees/import-export/export` with HR role
2. Excel file downloads with calculated fields
3. Each employee's age, tenure auto-calculated

### Import employees
1. POST Excel file to `/api/employees/import-export/import` with HR role
2. Service processes each row with upsert logic
3. If NIK exists, updates; else creates new

### Check permissions
1. Director can only VIEW data
2. HR can CREATE, READ, UPDATE, DELETE, IMPORT, EXPORT
3. Non-authenticated users get 401 Unauthorized
4. Insufficient role gets 403 Forbidden

## 🗄️ Database Quick View

### Main Table: employees
```
nik (unique)           → 12345678901234567890
no_ktp (unique)        → 3171234567890123
nama                   → John Doe
department             → Finance
jabatan                → Manager
tanggal_lahir          → 1985-05-15
tanggal_masuk          → 2015-03-01
jenis_kelamin          → L or P
status_pkwtt           → TETAP or KONTRAK
status_keluarga        → K/2, TK/0, etc.
pendidikan             → S1, S2, etc.
alamat                 → Street address
```

## 🔑 Important API Responses

### Success Response (200)
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response (400/401/403/500)
```json
{
  "success": false,
  "message": "Error description",
  "errors": { "field": ["error message"] }
}
```

## 📊 Filter Examples

```
GET /employees?department=Finance&status_pkwtt=TETAP
GET /employees?search=John
GET /employees?jenis_kelamin=L&sort_by=nama&sort_dir=asc
GET /employees?per_page=20&page=2
```

## ⚙️ Common Commands

```bash
# Check migrations status
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback

# Fresh database with seeds
php artisan migrate:fresh --seed

# List all routes
php artisan route:list

# Clear cache
php artisan cache:clear

# Check app key
php artisan key:generate

# Database optimization
php artisan db:seed
```

## 🐛 Quick Troubleshooting

| Problem | Solution |
|---------|----------|
| "Class not found" | Run `composer dump-autoload` |
| "Database connection failed" | Check .env DB credentials |
| "Token invalid" | Re-login to get new token |
| "Permission denied" | Check user role: HR or Director |
| "Duplicate entry" | NIK or KTP already exists |
| Port 8000 in use | Use `php artisan serve --port=8001` |

## 🎓 Architecture at a Glance

```
HTTP Request
    ↓
Route + Middleware (RBAC check)
    ↓
Controller (Parse request)
    ↓
Service (Apply business logic)
    ↓
Repository (Execute database query)
    ↓
Database
    ↓
Response JSON
```

## 💡 What Makes This Scalable

1. **Separation of Concerns** - Each layer has one job
2. **Repository Pattern** - Queries in one place, reusable
3. **Service Layer** - Business logic testable independently
4. **RBAC Middleware** - Easy to add new roles/permissions
5. **Calculated Fields** - Age/tenure never need updating
6. **Chunk Processing** - Can handle 100,000+ employees
7. **Migrations** - Easy schema changes
8. **Models** - Clean data relationships

## 🔐 Security Checkpoints

- ✅ Unauthenticated → 401 Unauthorized
- ✅ Wrong role → 403 Forbidden
- ✅ Invalid input → 422 Validation error
- ✅ Duplicate NIK → Error with message
- ✅ Soft deletes → No permanent data loss
- ✅ Encrypted passwords → Bcrypt hash
- ✅ API tokens → Sanctum expiring tokens

## 📈 Next Steps

1. **Install & Test** - Follow quick start above
2. **Explore API** - Use cURL or Postman
3. **Read Docs** - See README.md for features
4. **Build Frontend** - React, Vue, Flutter
5. **Add Features** - Follow same pattern
6. **Deploy** - Container or traditional server

## 🆘 Need Help?

| Resource | Location |
|----------|----------|
| Full features | README.md |
| Setup steps | SETUP.md |
| API reference | API_DOCUMENTATION.md |
| Code structure | DIRECTORY_STRUCTURE.md |
| Complete summary | IMPLEMENTATION_SUMMARY.md |
| Architecture | Project.md (original) |

## ✅ Verification Checklist

- [ ] PHP 8.1+ installed
- [ ] Composer installed
- [ ] MySQL running
- [ ] .env configured
- [ ] Database created
- [ ] Migrations ran
- [ ] Seeders ran
- [ ] Server started
- [ ] Can login at `/api/auth/login`
- [ ] Can list employees at `/api/employees`

---

**You're all set! Start building! 🚀**
