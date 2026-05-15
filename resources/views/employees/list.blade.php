<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management - HR App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            font-size: 24px;
            color: #333;
            margin: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-badge {
            background: #667eea;
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .logout-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        .container-main {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h2 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
        }

        .btn-primary {
            background: #667eea;
            border: none;
            padding: 10px 20px;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .filter-group input,
        .filter-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-reset {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-reset:hover {
            background: #7f8c8d;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        thead {
            background: #f8f9fa;
            border-bottom: 2px solid #e0e0e0;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        th:first-child {
            position: sticky;
            left: 0;
            background: #f8f9fa;
            z-index: 10;
        }

        th:nth-child(2) {
            position: sticky;
            left: 100px;
            background: #f8f9fa;
            z-index: 10;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
            color: #555;
        }

        td:first-child {
            position: sticky;
            left: 0;
            background: white;
            font-weight: 600;
            color: #667eea;
            z-index: 9;
        }

        td:nth-child(2) {
            position: sticky;
            left: 100px;
            background: white;
            z-index: 9;
        }

        tbody tr:hover {
            background: #f9f9f9;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-tetap {
            background: #d5f4e6;
            color: #27ae60;
        }

        .badge-kontrak {
            background: #fde9da;
            color: #e67e22;
        }

        .badge-harian {
            background: #e8f4f8;
            color: #3498db;
        }

        .badge-male {
            background: #e3f2fd;
            color: #1976d2;
        }

        .badge-female {
            background: #fce4ec;
            color: #c2185b;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            padding: 20px;
            background: white;
            border-top: 1px solid #e0e0e0;
        }

        .pagination button,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            background: white;
            color: #333;
            font-size: 14px;
            transition: all 0.3s;
        }

        .pagination button:hover:not(:disabled) {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination .active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
            color: #666;
        }

        @media (max-width: 768px) {
            .filter-row {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                text-align: center;
            }

            th:nth-child(2) {
                left: 80px;
            }

            td:nth-child(2) {
                left: 80px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <h1>HR Management System</h1>
        <div class="user-info">
            <span id="userName">Loading...</span>
            <span class="user-badge" id="userRole">USER</span>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-main">
        <div class="header">
            <h2>👥 Employee Directory</h2>
            <button class="btn-primary" onclick="refreshTable()"><i class="fas fa-sync"></i> Refresh</button>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <div class="filter-row">
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" id="searchInput" placeholder="NIK, Name, or Email...">
                </div>
                <div class="filter-group">
                    <label>Department</label>
                    <select id="departmentFilter">
                        <option value="">All Departments</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Employment Status</label>
                    <select id="statusFilter">
                        <option value="">All Status</option>
                        <option value="TETAP">TETAP (Permanent)</option>
                        <option value="KONTRAK">KONTRAK (Contract)</option>
                        <option value="HARIAN">HARIAN (Daily)</option>
                        <option value="MAGANG">MAGANG (Internship)</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Gender</label>
                    <select id="genderFilter">
                        <option value="">All Gender</option>
                        <option value="L">Male</option>
                        <option value="P">Female</option>
                    </select>
                </div>
            </div>
            <div class="filter-row">
                <div class="filter-group">
                    <label>Sort By</label>
                    <select id="sortBy">
                        <option value="nama">Name (A-Z)</option>
                        <option value="nik">NIK</option>
                        <option value="department">Department</option>
                        <option value="tanggal_masuk">Join Date</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Direction</label>
                    <select id="sortDir">
                        <option value="asc">Ascending</option>
                        <option value="desc">Descending</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Per Page</label>
                    <select id="perPage">
                        <option value="10">10 rows</option>
                        <option value="25" selected>25 rows</option>
                        <option value="50">50 rows</option>
                        <option value="100">100 rows</option>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn-primary" onclick="applyFilters()"><i class="fas fa-search"></i> Apply Filters</button>
                <button class="btn-reset" onclick="resetFilters()"><i class="fas fa-redo"></i> Reset</button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="info-row">
                <span>Total Employees: <strong id="totalCount">0</strong></span>
                <span>Showing <strong id="fromCount">0</strong> to <strong id="toCount">0</strong></span>
            </div>
            <div class="table-wrapper">
                <table id="employeeTable">
                    <thead>
                        <tr>
                            <th style="width: 100px;">NIK</th>
                            <th style="width: 150px;">Name</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Gender</th>
                            <th>Family Status</th>
                            <th>Children</th>
                            <th>Education</th>
                            <th>Join Date</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr><td colspan="10" style="text-align: center; padding: 40px;"><div class="loading"></div></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="pagination" id="pagination"></div>
        </div>
    </div>

    <script>
        let currentFilters = {
            search: '',
            department: '',
            status: '',
            gender: '',
            sortBy: 'nama',
            sortDir: 'asc',
            perPage: 25,
            page: 1
        };

        // Check authentication
        function checkAuth() {
            const token = localStorage.getItem('auth_token');
            const user = localStorage.getItem('user');
            
            if (!token || !user) {
                window.location.href = '/login';
                return;
            }

            const userData = JSON.parse(user);
            document.getElementById('userName').textContent = userData.name;
            document.getElementById('userRole').textContent = userData.role.toUpperCase();
        }

        // Logout
        function logout() {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/login';
        }

        // Load employees
        async function loadEmployees() {
            try {
                const token = localStorage.getItem('auth_token');
                const params = new URLSearchParams({
                    search: currentFilters.search,
                    department: currentFilters.department,
                    status_pkwtt: currentFilters.status,
                    jenis_kelamin: currentFilters.gender,
                    sort_by: currentFilters.sortBy,
                    sort_dir: currentFilters.sortDir,
                    per_page: currentFilters.perPage,
                    page: currentFilters.page
                });

                const response = await fetch(`/api/employees?${params}`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });

                if (!response.ok) throw new Error('Failed to load employees');

                const data = await response.json();
                renderEmployees(data);
                renderPagination(data);
            } catch (error) {
                console.error(error);
                document.getElementById('tableBody').innerHTML = 
                    '<tr><td colspan="10" class="empty-state"><i class="fas fa-exclamation-circle"></i><br>Error loading employees</td></tr>';
            }
        }

        // Render employees
        function renderEmployees(data) {
            const tableBody = document.getElementById('tableBody');
            const employees = data.data || [];

            if (employees.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="10" class="empty-state"><i class="fas fa-inbox"></i><br>No employees found</td></tr>';
                document.getElementById('totalCount').textContent = '0';
                document.getElementById('fromCount').textContent = '0';
                document.getElementById('toCount').textContent = '0';
                return;
            }

            tableBody.innerHTML = employees.map(emp => `
                <tr>
                    <td>${emp.nik}</td>
                    <td><strong>${emp.nama}</strong></td>
                    <td>${emp.jabatan}</td>
                    <td>${emp.department}</td>
                    <td><span class="badge badge-${emp.status_pkwtt.toLowerCase()}">${emp.status_pkwtt}</span></td>
                    <td><span class="badge badge-${emp.jenis_kelamin === 'L' ? 'male' : 'female'}">${emp.jenis_kelamin === 'L' ? '♂ Male' : '♀ Female'}</span></td>
                    <td>${emp.status_keluarga}</td>
                    <td>${emp.jumlah_anak}</td>
                    <td>${emp.pendidikan}</td>
                    <td>${new Date(emp.tanggal_masuk).toLocaleDateString('id-ID')}</td>
                </tr>
            `).join('');

            // Update info
            const from = (data.current_page - 1) * data.per_page + 1;
            const to = Math.min(from + data.per_page - 1, data.total);
            document.getElementById('totalCount').textContent = data.total;
            document.getElementById('fromCount').textContent = from;
            document.getElementById('toCount').textContent = to;
        }

        // Render pagination
        function renderPagination(data) {
            const pagination = document.getElementById('pagination');
            const maxPages = Math.ceil(data.total / data.per_page);
            const currentPage = data.current_page;
            let html = '';

            if (currentPage > 1) {
                html += `<button onclick="goToPage(${currentPage - 1})"><i class="fas fa-chevron-left"></i></button>`;
            }

            for (let i = Math.max(1, currentPage - 2); i <= Math.min(maxPages, currentPage + 2); i++) {
                if (i === currentPage) {
                    html += `<span class="active">${i}</span>`;
                } else {
                    html += `<button onclick="goToPage(${i})">${i}</button>`;
                }
            }

            if (currentPage < maxPages) {
                html += `<button onclick="goToPage(${currentPage + 1})"><i class="fas fa-chevron-right"></i></button>`;
            }

            pagination.innerHTML = html;
        }

        // Load departments
        async function loadDepartments() {
            try {
                const token = localStorage.getItem('auth_token');
                const response = await fetch('/api/employees?per_page=1', {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                
                if (response.ok) {
                    // For now, manually populate departments from seeder data
                    const departments = ['Finance', 'IT', 'Human Resources', 'Marketing', 'Operations', 'Sales', 'Quality Assurance', 'Customer Service', 'Logistics', 'Admin'];
                    const select = document.getElementById('departmentFilter');
                    departments.forEach(dept => {
                        const option = document.createElement('option');
                        option.value = dept;
                        option.textContent = dept;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading departments:', error);
            }
        }

        // Apply filters
        function applyFilters() {
            currentFilters.search = document.getElementById('searchInput').value;
            currentFilters.department = document.getElementById('departmentFilter').value;
            currentFilters.status = document.getElementById('statusFilter').value;
            currentFilters.gender = document.getElementById('genderFilter').value;
            currentFilters.sortBy = document.getElementById('sortBy').value;
            currentFilters.sortDir = document.getElementById('sortDir').value;
            currentFilters.perPage = document.getElementById('perPage').value;
            currentFilters.page = 1;
            loadEmployees();
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('departmentFilter').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('genderFilter').value = '';
            document.getElementById('sortBy').value = 'nama';
            document.getElementById('sortDir').value = 'asc';
            document.getElementById('perPage').value = '25';
            currentFilters = {
                search: '',
                department: '',
                status: '',
                gender: '',
                sortBy: 'nama',
                sortDir: 'asc',
                perPage: 25,
                page: 1
            };
            loadEmployees();
        }

        // Go to page
        function goToPage(page) {
            currentFilters.page = page;
            loadEmployees();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Refresh table
        function refreshTable() {
            currentFilters.page = 1;
            loadEmployees();
        }

        // Initialize
        checkAuth();
        loadDepartments();
        loadEmployees();
    </script>
</body>
</html>
