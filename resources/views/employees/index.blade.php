@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="mb-4">📊 HR Data Management</h1>
        </div>
    </div>

    <!-- Import/Export Section -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📤 Import Employee Data</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Upload an Excel file to import employee data. Existing employees (by NIK) will be updated.</p>
                    <div class="mb-3">
                        <input type="file" id="importFile" class="form-control" accept=".xlsx,.xls" />
                    </div>
                    <button id="importBtn" class="btn btn-primary w-100">Import Excel File</button>
                    <div id="importStatus" class="mt-3"></div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">📥 Export Employee Data</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Download all employee data as an Excel file.</p>
                    <div class="mb-3">
                        <label class="form-label">Export Format</label>
                        <select id="exportFormat" class="form-select">
                            <option value="all">All Employees (with calculations)</option>
                            <option value="template">Template (for import)</option>
                        </select>
                    </div>
                    <button id="exportBtn" class="btn btn-success w-100">Download Excel</button>
                    <div id="exportStatus" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Data Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">👥 Employee Data</h5>
            <div>
                <button id="refreshBtn" class="btn btn-sm btn-light">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter & Search -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by NIK, Name, Email..." />
                </div>
                <div class="col-md-2">
                    <select id="departmentFilter" class="form-select">
                        <option value="">All Departments</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="TETAP">TETAP</option>
                        <option value="KONTRAK">KONTRAK</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="sortBy" class="form-select">
                        <option value="nama">Sort by Name</option>
                        <option value="nik">Sort by NIK</option>
                        <option value="created_at">Sort by Date</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="sortDir" class="form-select">
                        <option value="asc">Ascending</option>
                        <option value="desc">Descending</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="employeeTable">
                    <thead class="table-dark">
                        <tr>
                            <th>NIK</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Tenure</th>
                            <th>Age</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="employeeTableBody">
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <div class="spinner-border spinner-border-sm me-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                Loading employees...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-center" id="pagination"></ul>
            </nav>

            <!-- Summary Stats -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Employees</h6>
                            <h3 id="totalCount">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="card-title">Permanent</h6>
                            <h3 id="permanentCount">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="card-title">Contract</h6>
                            <h3 id="contractCount">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="card-title">Departments</h6>
                            <h3 id="deptCount">-</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 8px;
}

.card-header {
    border-radius: 8px 8px 0 0 !important;
    padding: 1rem;
}

.table-responsive {
    border-radius: 0 0 8px 8px;
    overflow: hidden;
}

.btn {
    border-radius: 4px;
    font-weight: 500;
}

.form-control, .form-select {
    border-radius: 4px;
    border: 1px solid #ddd;
}

.alert {
    border-radius: 4px;
    animation: slideIn 0.3s ease-in-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}
</style>

<script>
// Global state
let currentPage = 1;
let currentFilters = {};
let authToken = localStorage.getItem('authToken');
let currentUser = JSON.parse(localStorage.getItem('user') || '{}');

const API_BASE = '/api';

// Check authentication
if (!authToken) {
    window.location.href = '/login';
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadEmployees();
    setupEventListeners();
});

function setupEventListeners() {
    // Search & Filter
    document.getElementById('searchInput').addEventListener('input', debounce(loadEmployees, 300));
    document.getElementById('departmentFilter').addEventListener('change', loadEmployees);
    document.getElementById('statusFilter').addEventListener('change', loadEmployees);
    document.getElementById('sortBy').addEventListener('change', loadEmployees);
    document.getElementById('sortDir').addEventListener('change', loadEmployees);
    document.getElementById('refreshBtn').addEventListener('click', loadEmployees);

    // Import
    document.getElementById('importBtn').addEventListener('click', handleImport);
    document.getElementById('importFile').addEventListener('change', function(e) {
        document.getElementById('importStatus').innerHTML = '';
    });

    // Export
    document.getElementById('exportBtn').addEventListener('click', handleExport);
}

function debounce(func, delay) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), delay);
    };
}

async function loadEmployees() {
    currentPage = 1;
    
    const filters = {
        search: document.getElementById('searchInput').value,
        department: document.getElementById('departmentFilter').value,
        status_pkwtt: document.getElementById('statusFilter').value,
        sort_by: document.getElementById('sortBy').value,
        sort_dir: document.getElementById('sortDir').value,
        page: currentPage,
        per_page: 20
    };

    try {
        const response = await fetch(`${API_BASE}/employees?${new URLSearchParams(filters)}`, {
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Accept': 'application/json'
            }
        });

        if (response.status === 401) {
            window.location.href = '/login';
            return;
        }

        const data = await response.json();
        displayEmployees(data);
        loadDepartments();
    } catch (error) {
        showAlert('error', 'Failed to load employees: ' + error.message);
        console.error(error);
    }
}

function displayEmployees(data) {
    const tbody = document.getElementById('employeeTableBody');
    tbody.innerHTML = ''; // Safe: only clearing

    if (!data.data || data.data.length === 0) {
        const tr = document.createElement('tr');
        const td = document.createElement('td');
        td.colSpan = 9;
        td.className = 'text-center text-muted py-4';
        td.textContent = 'No employees found';
        tr.appendChild(td);
        tbody.appendChild(tr);
        return;
    }

    data.data.forEach(emp => {
        const tr = document.createElement('tr');

        // SECURITY FIX: Create cells safely using textContent (prevents XSS)
        const createCell = (content, isHTML = false) => {
            const td = document.createElement('td');
            if (isHTML) {
                // Only use innerHTML for trusted system-generated content
                td.innerHTML = content;
            } else {
                // Use textContent for user data (automatically escapes HTML)
                td.textContent = content || '-';
            }
            return td;
        };

        tr.appendChild(createCell(emp.nik));
        tr.appendChild(createCell(emp.nama));
        tr.appendChild(createCell(emp.email));
        tr.appendChild(createCell(emp.jabatan));
        tr.appendChild(createCell(emp.department));

        // Badge (trusted content - only values from enum)
        const badgeClass = emp.status_pkwtt === 'TETAP' ? 'bg-success' : 'bg-warning';
        tr.appendChild(createCell(`<span class="badge ${badgeClass}">${escapeHtml(emp.status_pkwtt || '-')}</span>`, true));

        tr.appendChild(createCell(emp.tenure_formatted));
        tr.appendChild(createCell(emp.age));

        // Action button
        const actionTd = document.createElement('td');
        const editBtn = document.createElement('button');
        editBtn.className = 'btn btn-sm btn-outline-primary';
        editBtn.textContent = 'Edit';
        editBtn.onclick = () => editEmployee(emp.id);
        actionTd.appendChild(editBtn);
        tr.appendChild(actionTd);

        tbody.appendChild(tr);
    });

    // Update stats
    document.getElementById('totalCount').textContent = data.total || 0;

    // Update pagination
    updatePagination(data);
}

// SECURITY FIX: Helper function to escape HTML characters
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
function updatePagination(data) {
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';

    if (!data.last_page || data.last_page === 1) return;

    // Previous button
    if (data.current_page > 1) {
        pagination.innerHTML += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="goToPage(${data.current_page - 1}); return false;">Previous</a>
            </li>
        `;
    }

    // Page numbers
    for (let i = 1; i <= data.last_page; i++) {
        const active = i === data.current_page ? 'active' : '';
        pagination.innerHTML += `
            <li class="page-item ${active}">
                <a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a>
            </li>
        `;
    }

    // Next button
    if (data.current_page < data.last_page) {
        pagination.innerHTML += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="goToPage(${data.current_page + 1}); return false;">Next</a>
            </li>
        `;
    }
}

function goToPage(page) {
    currentPage = page;
    loadEmployees();
    window.scrollTo(0, 0);
}

async function loadDepartments() {
    try {
        const response = await fetch(`${API_BASE}/employees?per_page=1000`, {
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        
        const departments = [...new Set(data.data.map(e => e.department).filter(d => d))];
        const select = document.getElementById('departmentFilter');
        const current = select.value;
        
        select.innerHTML = '<option value="">All Departments</option>';
        departments.forEach(dept => {
            select.innerHTML += `<option value="${dept}">${dept}</option>`;
        });
        select.value = current;
    } catch (error) {
        console.error('Failed to load departments:', error);
    }
}

async function handleImport() {
    const fileInput = document.getElementById('importFile');
    const file = fileInput.files[0];

    if (!file) {
        showAlert('warning', 'Please select a file to import');
        return;
    }

    const formData = new FormData();
    formData.append('file', file);

    const statusDiv = document.getElementById('importStatus');
    statusDiv.innerHTML = '<div class="alert alert-info"><div class="spinner-border spinner-border-sm me-2"></div>Importing...</div>';

    try {
        const response = await fetch(`${API_BASE}/employees/import-export/import`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${authToken}`
            },
            body: formData
        });

        const result = await response.json();

        if (response.ok) {
            statusDiv.innerHTML = `
                <div class="alert alert-success">
                    <strong>✓ Import Successful!</strong><br>
                    Imported: ${result.imported_count || 0} employees<br>
                    ${result.failed_count > 0 ? `Failed: ${result.failed_count}` : ''}
                </div>
            `;
            fileInput.value = '';
            loadEmployees();
        } else {
            statusDiv.innerHTML = `
                <div class="alert alert-danger">
                    <strong>✗ Import Failed</strong><br>
                    ${result.message || 'Unknown error'}
                </div>
            `;
        }
    } catch (error) {
        statusDiv.innerHTML = `<div class="alert alert-danger"><strong>✗ Error:</strong> ${error.message}</div>`;
    }
}

async function handleExport() {
    const format = document.getElementById('exportFormat').value;
    const statusDiv = document.getElementById('exportStatus');
    
    statusDiv.innerHTML = '<div class="alert alert-info"><div class="spinner-border spinner-border-sm me-2"></div>Preparing download...</div>';

    try {
        const endpoint = format === 'template' 
            ? `${API_BASE}/employees/import-export/template`
            : `${API_BASE}/employees/import-export/export`;

        const response = await fetch(endpoint, {
            headers: {
                'Authorization': `Bearer ${authToken}`
            }
        });

        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `employees_${new Date().toISOString().split('T')[0]}.xlsx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

            statusDiv.innerHTML = '<div class="alert alert-success">✓ File downloaded successfully</div>';
        } else {
            statusDiv.innerHTML = '<div class="alert alert-danger">✗ Download failed</div>';
        }
    } catch (error) {
        statusDiv.innerHTML = `<div class="alert alert-danger">✗ Error: ${error.message}</div>`;
    }
}

function showAlert(type, message) {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';

    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.insertBefore(alert, document.body.firstChild);
    
    setTimeout(() => alert.remove(), 5000);
}

function editEmployee(id) {
    alert('Edit functionality coming soon for employee #' + id);
}
</script>
@endsection
