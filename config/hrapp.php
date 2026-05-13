<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Role Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file defines the roles and permissions for the HR
    | Application using Role-Based Access Control (RBAC).
    |
    */

    'roles' => [
        'director' => [
            'name' => 'Director',
            'permissions' => [
                'employees.view',
                'employees.dashboard',
            ],
        ],
        'hr' => [
            'name' => 'HR Manager',
            'permissions' => [
                'employees.view',
                'employees.create',
                'employees.update',
                'employees.delete',
                'employees.import',
                'employees.export',
                'employees.dashboard',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Settings
    |--------------------------------------------------------------------------
    */

    'pagination' => [
        'per_page_default' => 50,
        'per_page_max' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Export/Import Settings
    |--------------------------------------------------------------------------
    */

    'import_export' => [
        'chunk_size' => 500,
        'max_file_size_mb' => 10,
        'allowed_mime_types' => ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'],
    ],
];
