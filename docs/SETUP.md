# HR App Installation & Setup Guide

## 📋 Prerequisites

Ensure you have installed:
- PHP 8.1 or higher
- Composer (latest version)
- MySQL 5.7+ or MariaDB
- Git (optional)

## 🚀 Quick Start Guide

### Step 1: Navigate to Project Directory
```bash
cd d:\Project\HRApp
```

### Step 2: Install PHP Dependencies
```bash
composer install
```

### Step 3: Configure Environment
```bash
copy .env.example .env
```

Edit `.env` file and update database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hrapp
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 4: Generate Application Key
```bash
php artisan key:generate
```

### Step 5: Create Database
```bash
# Using MySQL command line
mysql -u root -p
CREATE DATABASE hrapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Step 6: Run Migrations & Seeders
```bash
php artisan migrate
php artisan db:seed
```

This will create:
- Roles table with Director and HR roles
- Users table with test accounts
- Employees table with sample data

### Step 7: Start Development Server
```bash
php artisan serve
```

Server will start at: `http://localhost:8000`

## 🔐 Default Test Accounts

After running seeders, use these credentials:

**Director Account:**
- Email: `director@hrapp.com`
- Password: `password123`
- Role: View-only access

**HR Account:**
- Email: `hr@hrapp.com`
- Password: `password123`
- Role: Full management access

## 📡 API Testing

### 1. Get Authentication Token
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "hr@hrapp.com",
    "password": "password123"
  }'
```

Response:
```json
{
  "success": true,
  "message": "Login successful",
  "token": "YOUR_TOKEN_HERE",
  "user": {
    "id": 2,
    "name": "HR User",
    "email": "hr@hrapp.com",
    "role": "hr"
  }
}
```

### 2. List Employees
```bash
curl -X GET "http://localhost:8000/api/employees" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. Create Employee
```bash
curl -X POST http://localhost:8000/api/employees \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nik": "12345678901234567895",
    "no_ktp": "3171234567890128",
    "nama": "Test Employee",
    "department": "Finance",
    "jabatan": "Officer",
    "tanggal_masuk": "2024-01-15",
    "status_pkwtt": "KONTRAK"
  }'
```

## 🗂️ Project Structure

```
HRApp/
├── app/
│   ├── Models/              # Eloquent models
│   ├── Http/
│   │   ├── Controllers/     # API controllers
│   │   └── Middleware/      # RBAC middleware
│   ├── Repositories/        # Data access layer
│   ├── Services/            # Business logic layer
│   ├── Exports/             # Excel export classes
│   └── Imports/             # Excel import classes
├── database/
│   ├── migrations/          # Schema migrations
│   └── seeders/             # Database seeders
├── routes/
│   └── api.php              # API routes
├── config/
│   └── hrapp.php            # App configuration
├── storage/                 # Logs and cache
├── bootstrap/               # App bootstrap files
├── public/                  # Web root
├── tests/                   # PHPUnit tests
├── .env.example             # Environment template
├── composer.json            # Dependencies
└── README.md                # Full documentation
```

## 📊 Database Schema Overview

**employees** table stores:
- Personal info: NIK, KTP, name, gender, birthplace
- Organization: Department, position, status
- Dates: Joining date, birthdate
- Calculated fields (not stored): Age, tenure

**users** table:
- Authentication credentials
- Role reference for RBAC

**roles** table:
- Director: View-only
- HR: Full CRUD + import/export

## 🔧 Common Commands

### Database Management
```bash
# Run all migrations
php artisan migrate

# Rollback last batch
php artisan migrate:rollback

# Fresh database with seeders
php artisan migrate:fresh --seed

# Reset everything
php artisan migrate:reset
php artisan migrate --seed
```

### Cache & Config
```bash
php artisan cache:clear
php artisan config:cache
php artisan view:clear
```

### Create Model/Migration
```bash
php artisan make:model ModelName -m
```

## ✅ Verification Checklist

- [ ] PHP 8.1+ installed
- [ ] Composer installed
- [ ] MySQL running
- [ ] `.env` file configured
- [ ] Database created
- [ ] Migrations run successfully
- [ ] Seeders populated test data
- [ ] Development server started
- [ ] Can login with test credentials
- [ ] API endpoints responding

## 🐛 Troubleshooting

### "Class Not Found" Error
```bash
composer dump-autoload
php artisan cache:clear
```

### Migration Errors
```bash
php artisan migrate:refresh
# or
php artisan migrate:fresh --seed
```

### Permission Issues
```bash
# Linux/Mac
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# Windows (usually automatic)
```

### Port Already in Use
```bash
php artisan serve --port=8001
```

## 📚 Next Steps

1. Read [README.md](README.md) for complete feature documentation
2. Explore API endpoints with Postman or Insomnia
3. Set up frontend (React/Vue) to consume the API
4. Configure additional features (logging, caching, queues)
5. Set up automated testing with PHPUnit

## 🆘 Need Help?

- Check [README.md](README.md) for detailed documentation
- Review migration files for database schema
- Check service classes for business logic examples
- Consult Laravel documentation: https://laravel.com/docs

---

**Ready to build! 🚀**
