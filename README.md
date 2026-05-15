# HR Application - Laravel Implementation

## 📋 Overview

A scalable, production-ready HR application built with Laravel using the **Controller-Service-Repository Pattern**. This architecture ensures clean code separation, easy testing, and seamless scalability for future updates.

## ✨ Key Features

### 1. **Architecture**
- **Controller Layer**: Handles HTTP requests and responses
- **Service Layer**: Contains all business logic
- **Repository Layer**: Manages database queries and data access
- **Middleware**: RBAC (Role-Based Access Control) enforcement

### 2. **Employee Management**
- Complete CRUD operations for employee records
- Advanced filtering and sorting capabilities
- Full-text search across multiple fields
- Dashboard statistics and analytics

### 3. **Data Processing**
- **Calculated Fields** (NOT stored in DB):
  - Current Age (Umur Sekarang)
  - Age on Joining (Umur Saat Masuk)
  - Tenure in Years (Masa Kerja)
  - Tenure Formatted (e.g., "5 years 3 months")
  
- These are calculated on-the-fly using Laravel Accessors to ensure data accuracy

### 4. **Import/Export**
- Excel import with upsert logic (update if exists, create if new)
- Chunk-based export for memory efficiency
- Validation and error reporting
- Import template generation

### 5. **Role-Based Access Control (RBAC)**
- **Director**: View-only access to employee data and dashboards
- **HR Manager**: Full CRUD, import/export, and management capabilities

### 6. **Authentication**
- Laravel Sanctum token-based authentication
- Secure login/logout endpoints
- User information retrieval

## 🏗️ Project Structure

```
HRApp/
├── app/
│   ├── Models/
│   │   ├── User.php                 # User model with role methods
│   │   ├── Role.php                 # Role model
│   │   └── Employee.php             # Employee model with calculated accessors
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── EmployeeController.php
│   │   │   └── EmployeeImportExportController.php
│   │   └── Middleware/
│   │       ├── CheckRole.php        # Single role verification
│   │       └── CheckAnyRole.php     # Multiple role verification
│   ├── Repositories/
│   │   ├── EmployeeRepository.php   # Employee data access layer
│   │   └── UserRepository.php       # User data access layer
│   ├── Services/
│   │   ├── EmployeeService.php      # Employee business logic
│   │   └── AuthService.php          # Authentication logic
│   ├── Exports/
│   │   └── EmployeeExport.php       # Excel export handler
│   ├── Imports/
│   │   └── EmployeeImport.php       # Excel import handler
│   ├── Providers/
│   │   └── AppServiceProvider.php   # Dependency injection bindings
│   └── Traits/
│       └── ApiResponse.php          # JSON response helper
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_roles_table.php
│   │   ├── 2024_01_01_000002_create_users_table.php
│   │   ├── 2024_01_01_000003_create_employees_table.php
│   │   └── ...
│   └── seeders/
│       ├── RoleSeeder.php           # Create default roles
│       ├── UserSeeder.php           # Create test users
│       └── EmployeeSeeder.php       # Create sample employees
├── routes/
│   └── api.php                      # API routes with middleware protection
├── config/
│   └── hrapp.php                    # Application configuration
├── composer.json                    # Dependencies
├── .env.example                     # Environment template
└── README.md
```

## 🗄️ Database Schema

### Employees Table
Optimized schema with proper data types and indexes:

| Column | Type | Notes |
|--------|------|-------|
| id | BigInt PK | Primary key |
| nik | String (Unique, Indexed) | Employee ID |
| no_ktp | String (Unique, Indexed) | ID Card number |
| nama | String (Indexed) | Employee name |
| department | String (Indexed) | Department |
| jabatan | String | Position |
| tempat_lahir | String | Birth place |
| **tanggal_lahir** | Date | Used for age calculation |
| **tanggal_masuk** | Date | Used for tenure calculation |
| jenis_kelamin | Enum(L, P) | Gender |
| dept_on_line | String | Current online department |
| dept_on_line_awal | String | Initial online department |
| status_pkwtt | Enum(TETAP, KONTRAK) | Employment status |
| status_keluarga | String | Family status (e.g., K/1, TK/0) |
| pendidikan | String | Education level |
| alamat | Text | Address |
| timestamps | - | created_at, updated_at |
| soft_deletes | - | deleted_at |

**Indexes**: composite index on (department, status_pkwtt) for fast queries

## 🔐 RBAC Implementation

### API Routes Structure
```
POST   /api/auth/login                 # Public - Login
POST   /api/auth/logout                # Protected - Logout (All authenticated)
GET    /api/auth/me                    # Protected - Get current user

GET    /api/employees                  # HR & Director - List employees
GET    /api/employees/{id}             # HR & Director - Employee details
GET    /api/employees/statistics       # HR & Director - Dashboard stats
POST   /api/employees                  # HR Only - Create employee
PUT    /api/employees/{id}             # HR Only - Update employee
DELETE /api/employees/{id}             # HR Only - Delete employee

GET    /api/employees/import-export/export      # HR Only - Export Excel
POST   /api/employees/import-export/import      # HR Only - Import Excel
GET    /api/employees/import-export/template    # HR Only - Get template
```

### Middleware Usage
- `auth:sanctum` - Requires valid token
- `checkRole:hr` - Requires HR role
- `checkAnyRole:hr,director` - Accepts either HR or Director

## 🚀 Getting Started

### Prerequisites
- PHP 8.1+
- Composer
- MySQL/MariaDB
- Node.js (optional, for frontend)

### Installation

1. **Clone or extract the project**
   ```bash
   cd d:\Project\HRApp
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Setup environment**
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

4. **Configure database** in `.env`
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hrapp
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Start development server**
   ```bash
   php artisan serve
   ```

The application will be available at `http://localhost:8000`

## 📝 Testing the API

### 1. Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "hr@quty.co.id",
    "password": "password123"
  }'
```

### 2. Get Employees List
```bash
curl -X GET "http://localhost:8000/api/employees?department=Finance&sort_by=nama" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. Create Employee
```bash
curl -X POST http://localhost:8000/api/employees \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nik": "12345678901234567894",
    "no_ktp": "3171234567890127",
    "nama": "New Employee",
    "department": "IT",
    "jabatan": "Developer",
    "tanggal_masuk": "2024-01-15",
    "status_pkwtt": "KONTRAK"
  }'
```

### 4. Export Employees
```bash
curl -X GET "http://localhost:8000/api/employees/import-export/export?department=IT" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o employees.xlsx
```

### 5. Get Dashboard Statistics
```bash
curl -X GET http://localhost:8000/api/employees/statistics \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 📊 Key Design Decisions

### 1. **Calculated Fields in Model Accessors**
✅ Age and tenure are NOT stored but calculated on-the-fly using Carbon
✅ No need for daily cron jobs to update these values
✅ Always accurate and current

### 2. **Repository Pattern**
✅ Centralizes all database queries
✅ Enables easy filtering and sorting reuse
✅ Makes testing easier with mock repositories

### 3. **Service Layer for Business Logic**
✅ Keeps controllers thin and focused on HTTP handling
✅ Business logic is testable independently
✅ Easy to add new features without controller bloat

### 4. **RBAC Middleware**
✅ Fine-grained permission control at route level
✅ Director gets read-only access
✅ HR gets full management capabilities

### 5. **Chunk-Based Export**
✅ Handles large datasets without memory overflow
✅ Processes data in 500-row chunks
✅ Suitable for thousands of employees

## 🔄 Prepared for Scalability

The architecture is designed for easy expansion:

- **Add new modules**: Create Model → Repository → Service → Controller
- **Internationalization**: Add translation files for multi-language support
- **Department/Position tables**: Migrate string fields to foreign keys
- **Audit logging**: Add activity log tracking for compliance
- **Advanced reporting**: Extend Service Layer with analytics methods
- **Queue jobs**: Move import/export to background queue processing
- **API versioning**: Easy to add new API versions alongside existing ones

## 🛠️ Maintenance

### Running Migrations
```bash
php artisan migrate              # Run all pending migrations
php artisan migrate:rollback     # Rollback last batch
php artisan migrate:fresh --seed # Fresh database with seeds
```

### Database Backup
```bash
mysqldump -u root hrapp > backup.sql
```

### Cache Clearing
```bash
php artisan cache:clear
php artisan config:cache
```

## 📦 Dependencies

- **laravel/framework** - Core framework
- **laravel/sanctum** - Token authentication
- **maatwebsite/excel** - Excel import/export
- **doctrine/dbal** - Database abstraction
- **spatie/laravel-activitylog** - Activity logging (optional, for future audit trail)

## 📄 License

This project is open-source.

## 🤝 Support

For issues or questions, refer to the Project.md architecture documentation.

---

**Built with ❤️ using Laravel | Ready for Enterprise Growth**
