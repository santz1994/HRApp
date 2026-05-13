<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'HR App - Dashboard' }}</title>
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
        
        .empty {
            text-align: center;
            padding: 40px;
            color: #999;
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
    <div class="container">
        <div class="header">
            <h2>Employees</h2>
            <div class="actions">
                <button class="btn btn-primary" onclick="loadEmployees()">Refresh</button>
            </div>
        </div>
        
        <div class="table-container">
            <div id="employees-table-wrapper">
                <div class="empty">Loading employees...</div>
            </div>
        </div>
    </div>

    <script>
        // Check authentication
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

        // Load and display employees
        async function loadEmployees() {
            const auth = checkAuth();
            if (!auth) return;

            try {
                const response = await fetch('/api/employees', {
                    headers: {
                        'Authorization': `Bearer ${auth.token}`
                    }
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
                    html += '<th>Status</th>';
                    html += '</tr></thead><tbody>';
                    
                    employees.forEach(emp => {
                        html += '<tr>';
                        html += `<td>${emp.id}</td>`;
                        html += `<td>${emp.nama || emp.name || 'N/A'}</td>`;
                        html += `<td>${emp.email || 'N/A'}</td>`;
                        html += `<td>${emp.jabatan || emp.position || 'N/A'}</td>`;
                        html += `<td><span class="badge badge-active">${emp.status || 'active'}</span></td>`;
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table>';
                } else {
                    html = '<div class="empty">No employees found</div>';
                }

                document.getElementById('employees-table-wrapper').innerHTML = html;
            } catch (err) {
                console.error('Error loading employees:', err);
                document.getElementById('employees-table-wrapper').innerHTML = `<div class="empty">Error loading employees</div>`;
            }
        }

        // Logout function
        function logout() {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/';
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', () => {
            const auth = checkAuth();
            if (auth) {
                document.getElementById('userName').textContent = auth.user.name + ' (' + auth.user.email + ')';
                document.getElementById('userRole').textContent = (auth.user.role || 'user').toUpperCase();
                loadEmployees();
            }
        });
    </script>
</body>
</html>
