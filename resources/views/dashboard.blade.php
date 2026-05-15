<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'HR App - Dashboard' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }
        
        .layout-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            padding-top: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 100;
        }

        .sidebar-header {
            padding: 0 20px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 12px;
            opacity: 0.8;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: white;
        }

        .sidebar-menu a.active {
            background: rgba(0, 0, 0, 0.2);
            color: white;
            border-left-color: #fff;
        }

        .sidebar-menu i {
            width: 20px;
            text-align: center;
        }

        .sidebar-menu .menu-label {
            font-size: 11px;
            text-transform: uppercase;
            opacity: 0.6;
            padding: 10px 20px 5px;
            margin-top: 10px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 250px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 60px;
        }
        
        .navbar h1 {
            font-size: 22px;
            color: #333;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-badge {
            background: #667eea;
            color: white;
            padding: 4px 12px;
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
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
            flex: 1;
            width: 100%;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .header h2 {
            color: #333;
            font-size: 24px;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background: #1e8449;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-active {
            background: #d5f4e6;
            color: #27ae60;
        }

        .badge-inactive {
            background: #fadbd8;
            color: #c0392b;
        }
        
        .empty {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #667eea;
        }

        .stat-card h3 {
            color: #999;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .stat-card .value {
            color: #333;
            font-size: 28px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }

            .container {
                padding: 0 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-menu a {
                padding: 10px 15px;
            }

            .navbar {
                flex-wrap: wrap;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="layout-wrapper">
        <!-- SIDEBAR MENU -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>HR App</h2>
                <p id="userDept">Loading...</p>
            </div>
            
            <ul class="sidebar-menu">
                <div class="menu-label">Main</div>
                <li><a href="#" onclick="switchSection('dashboard')" class="menu-link active" data-section="dashboard">
                    <i class="fas fa-home"></i> Dashboard
                </a></li>
                <li><a href="#" onclick="switchSection('employees')" class="menu-link" data-section="employees">
                    <i class="fas fa-users"></i> Employees
                </a></li>

                <div class="menu-label">Management</div>
                <li><a href="#" onclick="switchSection('attendance')" class="menu-link" data-section="attendance">
                    <i class="fas fa-calendar-check"></i> Attendance
                </a></li>
                <li><a href="#" onclick="switchSection('leave')" class="menu-link" data-section="leave">
                    <i class="fas fa-calendar-times"></i> Leave
                </a></li>

                <div class="menu-label">HR Functions</div>
                <li><a href="#" onclick="switchSection('users')" class="menu-link" data-section="users">
                    <i class="fas fa-user-tie"></i> Users
                </a></li>
                <li><a href="#" onclick="switchSection('settings')" class="menu-link" data-section="settings">
                    <i class="fas fa-cog"></i> Settings
                </a></li>

                <li id="admin-menu" style="display: none;">
                    <a href="#" onclick="switchSection('system')" class="menu-link" data-section="system">
                        <i class="fas fa-server"></i> System
                    </a>
                </li>

                <div class="menu-label">Other</div>
                <li><a href="#" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a></li>
            </ul>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- Navbar -->
            <div class="navbar">
                <h1 id="page-title">Dashboard</h1>
                <div class="user-info">
                    <span id="userName">Loading...</span>
                    <span class="user-badge" id="userRole">USER</span>
                    <button class="logout-btn" onclick="logout()">Logout</button>
                </div>
            </div>

            <!-- Content Container -->
            <div class="container">
                <!-- DASHBOARD SECTION -->
                <section id="dashboard" class="section active">
                    <div class="header">
                        <h2>Dashboard Overview</h2>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3>Total Employees</h3>
                            <div class="value" id="stat-total">0</div>
                        </div>
                        <div class="stat-card">
                            <h3>On Leave Today</h3>
                            <div class="value" id="stat-leave">0</div>
                        </div>
                        <div class="stat-card">
                            <h3>Departments</h3>
                            <div class="value" id="stat-depts">0</div>
                        </div>
                        <div class="stat-card">
                            <h3>Active Users</h3>
                            <div class="value" id="stat-users">0</div>
                        </div>
                    </div>
                </section>

                <!-- EMPLOYEES SECTION -->
                <section id="employees" class="section">
                    <div class="header">
                        <h2>Employees</h2>
                        <div class="actions">
                            <button class="btn btn-primary" onclick="loadEmployees()">Refresh</button>
                            <button class="btn btn-success" onclick="triggerImportFile()">Import</button>
                            <button class="btn btn-secondary" onclick="downloadImportTemplate()">Template</button>
                            <button class="btn btn-secondary" onclick="exportEmployees()">Export</button>
                        </div>
                    </div>
                    <input type="file" id="import-file" accept=".xlsx,.xls,.csv" style="display:none" onchange="handleImportFile(event)">
                    <div class="table-container">
                        <div id="employees-table-wrapper">
                            <div class="empty">Loading employees...</div>
                        </div>
                    </div>
                </section>

                <!-- ATTENDANCE SECTION -->
                <section id="attendance" class="section">
                    <div class="header">
                        <h2>Attendance</h2>
                        <div class="actions">
                            <button class="btn btn-primary" onclick="loadAttendance()">Refresh</button>
                        </div>
                    </div>
                    <div class="table-container">
                        <div id="attendance-table-wrapper">
                            <div class="empty">Attendance module coming soon</div>
                        </div>
                    </div>
                </section>

                <!-- LEAVE SECTION -->
                <section id="leave" class="section">
                    <div class="header">
                        <h2>Leave Requests</h2>
                        <div class="actions">
                            <button class="btn btn-primary" onclick="loadLeave()">Refresh</button>
                        </div>
                    </div>
                    <div class="table-container">
                        <div id="leave-table-wrapper">
                            <div class="empty">Leave module coming soon</div>
                        </div>
                    </div>
                </section>

                <!-- USERS SECTION -->
                <section id="users" class="section">
                    <div class="header">
                        <h2>User Management</h2>
                        <div class="actions">
                            <button class="btn btn-primary" onclick="loadUsers()">Refresh</button>
                            <button class="btn btn-success" onclick="alert('Add user feature coming soon')">Add User</button>
                        </div>
                    </div>
                    <div class="table-container">
                        <div id="users-table-wrapper">
                            <div class="empty">Loading users...</div>
                        </div>
                    </div>
                </section>

                <!-- SETTINGS SECTION -->
                <section id="settings" class="section">
                    <div class="header">
                        <h2>Settings & Profile</h2>
                    </div>
                    <div class="table-container" style="padding: 20px;">
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" id="profile-email" readonly>
                        </div>
                        <div class="form-group">
                            <label>Name:</label>
                            <input type="text" id="profile-name">
                        </div>
                        <div class="form-group">
                            <label>Current Password:</label>
                            <input type="password" id="current-password">
                        </div>
                        <div class="form-group">
                            <label>New Password:</label>
                            <input type="password" id="new-password">
                        </div>
                        <button class="btn btn-primary" onclick="updateProfile()">Update Profile</button>
                    </div>
                </section>

                <!-- SYSTEM SECTION (Admin only) -->
                <section id="system" class="section">
                    <div class="header">
                        <h2>System Configuration</h2>
                    </div>
                    <div class="table-container" style="padding: 20px;">
                        <div id="system-info">
                            <div class="empty">Loading system info...</div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        const BASE_URL = window.location.origin;

        function checkAuth() {
            const token = localStorage.getItem('auth_token');
            const user = localStorage.getItem('user');

            if (!token || !user) {
                window.location.href = '/';
                return null;
            }

            return {
                token,
                user: JSON.parse(user)
            };
        }

        function switchSection(sectionName) {
            const auth = checkAuth();
            if (!auth) return;

            document.querySelectorAll('.section').forEach(section => section.classList.remove('active'));
            document.querySelectorAll('.menu-link').forEach(link => link.classList.remove('active'));

            const targetSection = document.getElementById(sectionName);
            const activeMenu = document.querySelector(`[data-section="${sectionName}"]`);

            if (targetSection) targetSection.classList.add('active');
            if (activeMenu) activeMenu.classList.add('active');

            const titles = {
                dashboard: 'Dashboard Overview',
                employees: 'Employees',
                attendance: 'Attendance',
                leave: 'Leave Requests',
                users: 'User Management',
                settings: 'Settings & Profile',
                system: 'System Configuration'
            };

            document.getElementById('page-title').textContent = titles[sectionName] || 'Dashboard';

            if (sectionName === 'dashboard') loadDashboardStats();
            if (sectionName === 'employees') loadEmployees();
            if (sectionName === 'users') loadUsers();
            if (sectionName === 'system') loadSystemInfo();
        }

        async function loadDashboardStats() {
            const auth = checkAuth();
            if (!auth) return;

            try {
                const response = await fetch(`${BASE_URL}/api/employees/statistics`, {
                    headers: { 'Authorization': `Bearer ${auth.token}` }
                });

                if (!response.ok) throw new Error('Failed to fetch dashboard stats');

                const data = await response.json();
                document.getElementById('stat-total').textContent = data.total_employees ?? 0;
                document.getElementById('stat-leave').textContent = data.on_leave_today ?? 0;
                document.getElementById('stat-depts').textContent = data.total_departments ?? 0;
                document.getElementById('stat-users').textContent = data.active_users ?? 0;
            } catch (error) {
                console.error('Error loading dashboard stats:', error);
            }
        }

        async function loadEmployees() {
            const auth = checkAuth();
            if (!auth) return;

            try {
                const response = await fetch(`${BASE_URL}/api/employees`, {
                    headers: { 'Authorization': `Bearer ${auth.token}` }
                });

                if (response.status === 401) {
                    logout();
                    return;
                }

                if (!response.ok) throw new Error('Failed to fetch employees');

                const data = await response.json();
                const employees = data.data || data;

                let html = '';
                if (Array.isArray(employees) && employees.length > 0) {
                    html = '<table><thead><tr>';
                    html += '<th>Employee ID</th>';
                    html += '<th>Name</th>';
                    html += '<th>Email</th>';
                    html += '<th>Position</th>';
                    html += '<th>Department</th>';
                    html += '<th>Status</th>';
                    html += '</tr></thead><tbody>';

                    employees.forEach(emp => {
                        const status = emp.status || 'active';
                        const statusClass = String(status).toLowerCase() === 'active' ? 'badge-active' : 'badge-inactive';

                        html += '<tr>';
                        html += `<td>${emp.id ?? '-'}</td>`;
                        html += `<td>${emp.nama || emp.name || 'N/A'}</td>`;
                        html += `<td>${emp.email_karyawan || emp.email || 'N/A'}</td>`;
                        html += `<td>${emp.jabatan || emp.position || 'N/A'}</td>`;
                        html += `<td>${emp.department?.nama || emp.department || 'N/A'}</td>`;
                        html += `<td><span class="badge ${statusClass}">${status}</span></td>`;
                        html += '</tr>';
                    });

                    html += '</tbody></table>';
                } else {
                    html = '<div class="empty">No employees found</div>';
                }

                document.getElementById('employees-table-wrapper').innerHTML = html;
            } catch (error) {
                console.error('Error loading employees:', error);
                document.getElementById('employees-table-wrapper').innerHTML = '<div class="empty">Error loading employees</div>';
            }
        }

        function triggerImportFile() {
            const auth = checkAuth();
            if (!auth) return;

            document.getElementById('import-file').click();
        }

        async function handleImportFile(event) {
            const auth = checkAuth();
            if (!auth) return;

            const file = event.target.files && event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);

            try {
                const response = await fetch(`${BASE_URL}/api/employees/import-export/import`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${auth.token}`
                    },
                    body: formData
                });

                const result = await response.json().catch(() => ({}));

                if (!response.ok && response.status !== 202) {
                    alert(result.message || 'Import gagal diproses');
                    return;
                }

                alert(result.message || 'Import sedang diproses');
                event.target.value = '';
                loadEmployees();
            } catch (error) {
                console.error('Error importing employees:', error);
                alert('Gagal mengirim file import');
            }
        }

        function downloadImportTemplate() {
            const auth = checkAuth();
            if (!auth) return;

            downloadFile(`${BASE_URL}/api/employees/import-export/template`, 'template_import_karyawan.xlsx', auth.token);
        }

        function exportEmployees() {
            const auth = checkAuth();
            if (!auth) return;

            downloadFile(`${BASE_URL}/api/employees/import-export/export`, 'data_karyawan.xlsx', auth.token);
        }

        async function downloadFile(url, fileName, token) {
            try {
                const response = await fetch(url, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                if (!response.ok) {
                    const message = await response.text();
                    throw new Error(message || 'Download failed');
                }

                const blob = await response.blob();
                const objectUrl = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = objectUrl;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(objectUrl);
            } catch (error) {
                console.error('Error downloading file:', error);
                alert('Gagal mengunduh file');
            }
        }

        async function loadUsers() {
            document.getElementById('users-table-wrapper').innerHTML = '<div class="empty">User management API not wired yet</div>';
        }

        async function loadSystemInfo() {
            document.getElementById('system-info').innerHTML = '<div class="empty">System dashboard not wired yet</div>';
        }

        async function loadAttendance() {
            document.getElementById('attendance-table-wrapper').innerHTML = '<div class="empty">Attendance module coming soon</div>';
        }

        async function loadLeave() {
            document.getElementById('leave-table-wrapper').innerHTML = '<div class="empty">Leave module coming soon</div>';
        }

        async function updateProfile() {
            alert('Profile update not wired yet');
        }

        function logout() {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const auth = checkAuth();
            if (!auth) return;

            document.getElementById('userName').textContent = `${auth.user.name} (${auth.user.email})`;
            document.getElementById('userRole').textContent = (auth.user.role || 'user').toUpperCase();
            document.getElementById('userDept').textContent = auth.user.department || 'HR';
            document.getElementById('profile-email').value = auth.user.email || '';
            document.getElementById('profile-name').value = auth.user.name || '';

            const role = String(auth.user.role || '').toLowerCase();
            if (role === 'it' || role === 'admin') {
                document.getElementById('admin-menu').style.display = 'list-item';
            }

            loadDashboardStats();
            loadEmployees();
        });
    </script>
</body>
</html>
