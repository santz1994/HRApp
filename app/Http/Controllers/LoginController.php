<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login', [
            'title' => 'HR App - Login',
            'appName' => 'HR Management System'
        ]);
    }

    public function showDashboard()
    {
        // Dashboard is publicly accessible - JS will handle auth check via localStorage
        return view('dashboard', [
            'title' => 'HR App - Dashboard'
        ]);
    }

    public function showEmployees()
    {
        // Employees page is publicly accessible - JS will handle auth check via localStorage
        return view('employees.index', [
            'title' => 'HR App - Employee Management'
        ]);
    }

    public function showEmployeesList()
    {
        // Employees list page with advanced table features
        return view('employees.list', [
            'title' => 'HR App - Employee Directory'
        ]);
    }
}
