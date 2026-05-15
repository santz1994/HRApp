#!/bin/bash

# HR App Development Environment Setup Script
# Run this script to set up the HR application from scratch

echo "================================"
echo "HR App - Development Setup"
echo "================================"
echo ""

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Step 1: Check PHP version
echo -e "${BLUE}[1/8]${NC} Checking PHP version..."
php -v | head -n 1
echo ""

# Step 2: Install Composer dependencies
echo -e "${BLUE}[2/8]${NC} Installing Composer dependencies..."
composer install
echo ""

# Step 3: Copy environment file
echo -e "${BLUE}[3/8]${NC} Setting up .env file..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✓ .env file created${NC}"
else
    echo -e "${GREEN}✓ .env file already exists${NC}"
fi
echo ""

# Step 4: Generate application key
echo -e "${BLUE}[4/8]${NC} Generating application key..."
php artisan key:generate
echo ""

# Step 5: Create database (assumes MySQL is running)
echo -e "${BLUE}[5/8]${NC} Creating database..."
echo "Note: Update DB credentials in .env before running migrations"
echo "Database: hrapp"
echo "Host: localhost"
echo ""

# Step 6: Run migrations
echo -e "${BLUE}[6/8]${NC} Running database migrations..."
php artisan migrate
echo ""

# Step 7: Seed database
echo -e "${BLUE}[7/8]${NC} Seeding database with test data..."
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=EmployeeSeeder
echo ""

# Step 8: Display next steps
echo -e "${BLUE}[8/8]${NC} Setup complete!"
echo ""
echo -e "${GREEN}✓ Application is ready to run${NC}"
echo ""
echo "Next steps:"
echo "1. Update database credentials in .env if needed"
echo "2. Run: php artisan serve"
echo "3. Login with:"
echo "   - Email: hr@quty.co.id"
echo "   - Password: password123"
echo ""
echo "API will be available at: http://localhost:8000/api"
echo ""
