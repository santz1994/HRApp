# 🏗️ Architecture & Design Patterns

## System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT (Frontend)                         │
│                 (React, Vue, Flutter, Web)                       │
└────────────────────────────┬────────────────────────────────────┘
                             │ HTTP/JSON
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                    LARAVEL API SERVER                            │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │              ROUTE LAYER (routes/api.php)               │   │
│  │  - Define endpoints                                     │   │
│  │  - Mount middleware                                     │   │
│  │  - Link to controllers                                  │   │
│  └─────────────────┬──────────────────────────────────────┘   │
│                    │                                             │
│  ┌─────────────────▼──────────────────────────────────────┐   │
│  │          MIDDLEWARE LAYER                              │   │
│  │  - auth:sanctum (authentication)                       │   │
│  │  - checkRole (single role)                             │   │
│  │  - checkAnyRole (multiple roles)                       │   │
│  └─────────────────┬──────────────────────────────────────┘   │
│                    │                                             │
│  ┌─────────────────▼──────────────────────────────────────┐   │
│  │          CONTROLLER LAYER                              │   │
│  │  - EmployeeController (CRUD)                           │   │
│  │  - AuthController (Login)                              │   │
│  │  - ImportExportController (Excel)                      │   │
│  │                                                         │   │
│  │  Responsibilities:                                     │   │
│  │  ✓ Parse HTTP request                                 │   │
│  │  ✓ Validate input (basic)                             │   │
│  │  ✓ Call service methods                               │   │
│  │  ✓ Return JSON response                               │   │
│  └─────────────────┬──────────────────────────────────────┘   │
│                    │                                             │
│  ┌─────────────────▼──────────────────────────────────────┐   │
│  │          SERVICE LAYER                                 │   │
│  │  - EmployeeService (business logic)                    │   │
│  │  - AuthService (authentication)                        │   │
│  │                                                         │   │
│  │  Responsibilities:                                     │   │
│  │  ✓ Validate all data                                  │   │
│  │  ✓ Apply business rules                               │   │
│  │  ✓ Handle complex operations                          │   │
│  │  ✓ Call repository methods                            │   │
│  │  ✓ Process import/export                              │   │
│  └─────────────────┬──────────────────────────────────────┘   │
│                    │                                             │
│  ┌─────────────────▼──────────────────────────────────────┐   │
│  │          REPOSITORY LAYER                              │   │
│  │  - EmployeeRepository (queries)                        │   │
│  │  - UserRepository (queries)                            │   │
│  │                                                         │   │
│  │  Responsibilities:                                     │   │
│  │  ✓ Build database queries                             │   │
│  │  ✓ Apply filters & sorting                            │   │
│  │  ✓ Execute CRUD operations                            │   │
│  │  ✓ Return data objects                                │   │
│  └─────────────────┬──────────────────────────────────────┘   │
│                    │                                             │
│  ┌─────────────────▼──────────────────────────────────────┐   │
│  │          MODEL LAYER (Eloquent)                        │   │
│  │  - Employee (with accessors)                           │   │
│  │  - User (with relationships)                           │   │
│  │  - Role                                                │   │
│  │                                                         │   │
│  │  Responsibilities:                                     │   │
│  │  ✓ Define data structure                              │   │
│  │  ✓ Calculate values (age, tenure)                     │   │
│  │  ✓ Define relationships                               │   │
│  │  ✓ Provide query scopes                               │   │
│  └─────────────────┬──────────────────────────────────────┘   │
└────────────────────┼───────────────────────────────────────────┘
                     │ SQL
                     ▼
        ┌─────────────────────────────┐
        │     MySQL Database          │
        │  ┌─────────────────────┐   │
        │  │ roles table         │   │
        │  │ users table         │   │
        │  │ employees table     │   │
        │  │ tokens table        │   │
        │  └─────────────────────┘   │
        └─────────────────────────────┘
```

---

## Request Flow Diagram

```
USER REQUEST
     │
     ▼
POST /api/employees
     │
     ▼
api.php Routes
     │ Matches route pattern
     ▼
Middleware Chain:
 • auth:sanctum ──────► Verify token valid?
                        No  ──► 401 Unauthorized
 • checkRole:hr ───────► User has HR role?
                        No  ──► 403 Forbidden
     │
     ▼ Yes
EmployeeController@store
     │ Parse JSON request
     ▼ Validate input (FormRequest)
EmployeeService@createEmployee
     │ Apply business logic
     │ • Check duplicate NIK
     │ • Check duplicate KTP
     │ • Validate dates
     ▼
EmployeeRepository@create
     │ Build INSERT query
     ▼
Database
     │ Execute SQL
     │ • Check unique constraints
     │ • Insert record
     ▼
Success?
     │ Yes
     ▼
Return Employee Model
     │
     ▼ Controller
Format as JSON
     │ Add metadata
     ▼
HTTP 201 Response
     │
     ▼
CLIENT receives:
{
  "success": true,
  "message": "Employee created",
  "data": { id, nik, nama, ... }
}
```

---

## Data Models & Relationships

```
┌────────────┐                    ┌─────────────┐
│   Role     │────────────────────│    User     │
├────────────┤   one-to-many     ├─────────────┤
│ id         │                    │ id          │
│ name       │                    │ name        │
│ slug       │                    │ email       │
└────────────┘                    │ password    │
                                  │ role_id  ───┼──► References Role.id
                                  └─────────────┘
                                         │
                                         │ Authenticated via
                                         │ personal_access_tokens
                                         ▼
                                  ┌──────────────────┐
                                  │ Employee         │
                                  ├──────────────────┤
                                  │ id               │
                                  │ nik (unique)     │
                                  │ no_ktp (unique)  │
                                  │ nama             │
                                  │ department       │
                                  │ jabatan          │
                                  │ tanggal_lahir    │
                                  │ tanggal_masuk    │
                                  │ status_pkwtt     │
                                  │ (+ 7 more fields)│
                                  │                  │
                                  │ CALCULATED:      │
                                  │ • age            │
                                  │ • age_on_joining │
                                  │ • tenure_years   │
                                  │ • tenure_format  │
                                  └──────────────────┘
```

---

## Design Patterns Used

### 1. **Repository Pattern**
```php
// BEFORE (Spaghetti Code)
$employees = Employee::where('department', 'IT')
                      ->where('status_pkwtt', 'TETAP')
                      ->orderBy('nama')
                      ->paginate(50);
// Repeated in multiple controllers & services

// AFTER (Repository Pattern)
class EmployeeRepository {
    public function getByDepartmentAndStatus($dept, $status) {
        return $this->model->where('department', $dept)
                          ->where('status_pkwtt', $status)
                          ->orderBy('nama')
                          ->paginate(50);
    }
}

// Usage everywhere
$employees = $this->employeeRepository
    ->getByDepartmentAndStatus('IT', 'TETAP');
```

**Benefits:**
- ✅ Queries defined in one place
- ✅ Easy to change database later
- ✅ Easier to test with mock data
- ✅ Better code organization

---

### 2. **Service Layer Pattern**
```php
// BEFORE (Fat Controller)
public function store(Request $request) {
    $this->validate($request, [...]);
    
    if (Employee::where('nik', $request->nik)->exists()) {
        // handle duplicate
    }
    
    $employee = Employee::create([...]);
    
    Log::info('Employee created', [...]);
    
    return response()->json([...]);
}

// AFTER (Service Layer)
class EmployeeService {
    public function createEmployee($data) {
        $this->validateData($data);
        
        if ($this->isDuplicateNIK($data['nik'])) {
            throw new Exception('NIK exists');
        }
        
        return $this->repository->create($data);
    }
}

// Controller stays clean
public function store(Request $request) {
    $employee = $this->service->createEmployee(
        $request->validated()
    );
    
    return response()->json($employee, 201);
}
```

**Benefits:**
- ✅ Controllers focus on HTTP only
- ✅ Business logic testable separately
- ✅ Reusable between controllers
- ✅ Easier to understand each file

---

### 3. **Middleware Pattern for RBAC**
```php
// BEFORE (Check in every controller)
public function store(Request $request) {
    if ($request->user()->role->slug !== 'hr') {
        abort(403, 'Forbidden');
    }
    // ... rest of code
}

public function update(Request $request) {
    if ($request->user()->role->slug !== 'hr') {
        abort(403, 'Forbidden');
    }
    // ... rest of code
}

// AFTER (Middleware)
Route::post('/employees', [EmployeeController::class, 'store'])
    ->middleware('checkRole:hr');
    
Route::put('/employees/{id}', [EmployeeController::class, 'update'])
    ->middleware('checkRole:hr');

// Middleware checks before controller even runs
class CheckRole {
    public function handle($request, $next, $role) {
        if (!$request->user()->hasRole($role)) {
            abort(403);
        }
        return $next($request);
    }
}
```

**Benefits:**
- ✅ DRY - Don't Repeat Yourself
- ✅ Centralized permission logic
- ✅ Easy to audit security
- ✅ Automatic 403 before controller

---

### 4. **Accessor Pattern for Calculated Fields**
```php
// Database stores only necessary fields
$employee = Employee::find(1);
$employee->tanggal_lahir  // 1985-05-15
$employee->tanggal_masuk  // 2015-03-01

// Accessors calculate on-the-fly
$employee->age              // 39 (calculated)
$employee->age_on_joining   // 30 (calculated)
$employee->tenure_years     // 8.84 (calculated)
$employee->tenure_formatted // "8 years 10 months" (calculated)

// Never stored in database
// No cron jobs needed
// Always accurate

// In Model:
class Employee extends Model {
    public function getAgeAttribute() {
        return $this->tanggal_lahir->diffInYears(Carbon::now());
    }
}

// Usage:
$employee->age  // Calls getAgeAttribute() automatically
```

**Benefits:**
- ✅ Always accurate (not stored)
- ✅ No background jobs needed
- ✅ Transparent to caller
- ✅ Easy to maintain

---

### 5. **Dependency Injection Pattern**
```php
// BEFORE (Hard Dependencies)
class EmployeeController {
    public function index() {
        $repository = new EmployeeRepository();
        $service = new EmployeeService($repository);
        return $service->getList();
    }
}

// AFTER (Injected Dependencies)
class EmployeeController {
    protected $service;
    
    public function __construct(EmployeeService $service) {
        $this->service = $service; // Automatically provided
    }
    
    public function index() {
        return $this->service->getList();
    }
}

// Configured in AppServiceProvider
$this->app->bind('App\Services\EmployeeService', function ($app) {
    return new EmployeeService(
        $app->make('App\Repositories\EmployeeRepository')
    );
});
```

**Benefits:**
- ✅ Easy to test with mocks
- ✅ Loose coupling
- ✅ Automatic wiring
- ✅ Configurable behavior

---

### 6. **Query Scope Pattern**
```php
// BEFORE (Repeated conditions)
$employees = Employee::where('department', 'IT')
                      ->where('status_pkwtt', 'TETAP')
                      ->get();

// Later...
$employees = Employee::where('department', 'IT')
                      ->where('status_pkwtt', 'TETAP')
                      ->where('jenis_kelamin', 'L')
                      ->get();

// AFTER (Scopes)
class Employee extends Model {
    public function scopeByDepartment($query, $dept) {
        return $query->where('department', $dept);
    }
    
    public function scopeByStatusPKWTT($query, $status) {
        return $query->where('status_pkwtt', $status);
    }
    
    public function scopeByGender($query, $gender) {
        return $query->where('jenis_kelamin', $gender);
    }
}

// Usage - clean and readable
$employees = Employee::byDepartment('IT')
                      ->byStatusPKWTT('TETAP')
                      ->get();

$employees = Employee::byDepartment('IT')
                      ->byStatusPKWTT('TETAP')
                      ->byGender('L')
                      ->get();
```

**Benefits:**
- ✅ Reusable filter conditions
- ✅ Readable chaining
- ✅ DRY principle
- ✅ Easier to test

---

## Data Flow Examples

### Create Employee Flow
```
1. Client POST /api/employees
   { nik: "123", no_ktp: "456", nama: "John", ... }

2. Route middleware verifies:
   - Token valid? ✓
   - User has HR role? ✓

3. Controller validates:
   - Required fields present? ✓
   - Data types correct? ✓

4. Service applies logic:
   - Repository.findByNIK("123") → null? ✓
   - Repository.findByKTP("456") → null? ✓
   - Validate date formats ✓

5. Repository executes:
   Employee::create([...]) 
   → SQL INSERT
   → Database constraint check (unique NIK, KTP)
   → Returns new Employee model

6. Service returns:
   Employee object with ID set

7. Controller formats:
   {
     "success": true,
     "data": { id: 1, nik: "123", ... },
     "status": 201
   }

8. Client receives response
```

### Export Flow
```
1. Client GET /employees/import-export/export?department=IT

2. Middleware checks:
   - Auth? ✓
   - HR role? ✓

3. Controller calls:
   Service.getEmployeesForExport({ department: 'IT' })

4. Service calls:
   Repository.toArray({ department: 'IT' })

5. Repository:
   a) Queries: Employee::byDepartment('IT')->get()
   b) For each employee, builds array:
      {
        nik: emp.nik,
        nama: emp.nama,
        age: emp.age,              ← Accessor calculation
        tenure_formatted: emp.tenure_formatted,  ← Accessor
        ...
      }

6. Excel export handler:
   - Takes array of employees
   - Formats headers (bold, blue background)
   - Writes to Excel file
   - Returns download stream

7. Browser receives:
   File download: employees_2024-01-15_120530.xlsx

8. User opens in Excel with:
   - All calculated fields included
   - Professional formatting
   - Correct column headers
```

---

## Security Architecture

```
┌─────────────────────────────────────┐
│     AUTHENTICATION & TOKENS         │
│                                     │
│  1. User posts /auth/login          │
│     { email, password }             │
│                                     │
│  2. Service checks:                 │
│     - User exists? ✓                │
│     - Password matches? ✓           │
│                                     │
│  3. Create Sanctum token            │
│     - Token stored in DB            │
│     - Token expires                 │
│     - Returned to client            │
│                                     │
│  4. Client includes in all requests │
│     Authorization: Bearer token     │
└─────────────────────────────────────┘
                │
                ▼
┌─────────────────────────────────────┐
│     AUTHORIZATION & RBAC            │
│                                     │
│  1. Middleware checks token:        │
│     auth:sanctum                    │
│     - Valid? ✓                      │
│     - Not expired? ✓                │
│                                     │
│  2. Middleware checks role:         │
│     checkRole:hr                    │
│     - User.role.slug == 'hr'? ✓     │
│                                     │
│  3. If checks fail → 403 Forbidden  │
│     Request never reaches controller│
│                                     │
│  4. If all pass → Continue          │
└─────────────────────────────────────┘
                │
                ▼
┌─────────────────────────────────────┐
│    INPUT VALIDATION                 │
│                                     │
│  1. Controller validates:           │
│     - NIK required & string? ✓      │
│     - Date format valid? ✓          │
│     - Enum values correct? ✓        │
│                                     │
│  2. Service validates:              │
│     - Duplicate NIK/KTP? ✓          │
│     - Date logic valid? ✓           │
│     - Business rules? ✓             │
│                                     │
│  3. Database validates:             │
│     - Unique constraints ✓          │
│     - Foreign keys ✓                │
│     - Data types ✓                  │
│                                     │
│  4. If validation fails:            │
│     → 422 Unprocessable Entity      │
│     → Error details returned        │
└─────────────────────────────────────┘
```

---

## Scalability Patterns

### 1. **Chunk-Based Export** (Memory Safe)
```php
// Without chunks - loads all 100,000 in RAM
$employees = Employee::all();  // ❌ Memory overflow

// With chunks - processes 500 at a time
$employees = Employee::all()->chunk(500); // ✓ Safe
foreach ($chunk as $employee) {
    // Process each chunk
    // Calculate calculated fields
    // Write to Excel
    // Garbage collected
}
```

### 2. **Query Optimization** (Performance)
```php
// Without indexes - slow on large tables
SELECT * FROM employees WHERE department = 'IT';

// With indexes - fast
INDEX (department)  ← Added in migration

// Composite index for common filters
INDEX (department, status_pkwtt)
```

### 3. **Pagination** (Manageable Responses)
```php
// Without pagination - returns all
$employees = Employee::get();  // Could be 100,000 items

// With pagination - returns 50 at a time
$employees = Employee::paginate(50);
// Returns: items, total, per_page, current_page, last_page
```

### 4. **Ready for Background Jobs**
```php
// Current - synchronous
Job::dispatch($data);  // Process immediately

// Future - queue-based
Queue::push(ImportEmployeesJob::class, $data);
// Process in background worker
// UI stays responsive
```

---

## What's Ready for Growth

✅ **Add new modules** - Follow same patterns
✅ **Add new roles** - Just add to roles table
✅ **Add new permissions** - Extend middleware
✅ **Switch database** - Repository abstraction
✅ **Add caching** - Service layer ready
✅ **Add queues** - Infrastructure in place
✅ **Add GraphQL** - Alongside REST
✅ **Multi-tenancy** - Can add scopes
✅ **Internationalization** - Service layer ready
✅ **Auditing** - Migration ready

---

**Clean architecture that grows with you! 🚀**
