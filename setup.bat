@echo off
REM HR App Development Environment Setup Script for Windows
REM Run this script to set up the HR application from scratch

echo.
echo ================================
echo HR App - Development Setup
echo ================================
echo.

REM Step 1: Check PHP version
echo [1/8] Checking PHP version...
php -v | findstr /R "^PHP"
echo.

REM Step 2: Install Composer dependencies
echo [2/8] Installing Composer dependencies...
call composer install
echo.

REM Step 3: Copy environment file
echo [3/8] Setting up .env file...
if not exist .env (
    copy .env.example .env
    echo. Created .env file
) else (
    echo. .env file already exists
)
echo.

REM Step 4: Generate application key
echo [4/8] Generating application key...
php artisan key:generate
echo.

REM Step 5: Database creation notice
echo [5/8] Database creation...
echo. Note: Ensure MySQL is running and hrapp database exists
echo. You can create it with: CREATE DATABASE hrapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
echo.

REM Step 6: Run migrations
echo [6/8] Running database migrations...
php artisan migrate
echo.

REM Step 7: Seed database
echo [7/8] Seeding database with test data...
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=EmployeeSeeder
echo.

REM Step 8: Display next steps
echo [8/8] Setup complete!
echo.
echo. ✓ Application is ready to run
echo.
echo. Next steps:
echo. 1. Update database credentials in .env if needed
echo. 2. Run: php artisan serve
echo. 3. Login with:
echo.    - Email: hr@hrapp.com
echo.    - Password: password123
echo.
echo. API will be available at: http://localhost:8000/api
echo.
pause
