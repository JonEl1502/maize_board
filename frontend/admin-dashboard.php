<?php
include '../backend/config.php';

// Redirect if not admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

// Log admin dashboard access
logActivity(getUserId(), "Admin Dashboard Access", "Admin accessed the dashboard");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | E-commerce System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 5px;
        }
        .sidebar .nav-link:hover {
            color: white;
        }
        .sidebar .nav-link.active {
            background-color: #198754;
            color: white;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .stat-card {
            border-left: 5px solid #198754;
        }
        .stat-icon {
            font-size: 2rem;
            color: #198754;
        }
        .report-card {
            transition: transform 0.3s;
        }
        .report-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5>E-commerce System</h5>
                        <p>Admin Panel</p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="admin-dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-users.php">
                                <i class="fas fa-users me-2"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-products.php">
                                <i class="fas fa-box me-2"></i> Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-inventory.php">
                                <i class="fas fa-warehouse me-2"></i> Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-orders.php">
                                <i class="fas fa-shopping-cart me-2"></i> Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-reports.php">
                                <i class="fas fa-chart-bar me-2"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-settings.php">
                                <i class="fas fa-cog me-2"></i> Settings
                            </a>
                        </li>
                        <li class="nav-item mt-5">
                            <a class="nav-link" href="home.php">
                                <i class="fas fa-home me-2"></i> Main Site
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-arrow-left me-2"></i> Back to Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="logout()">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Admin Dashboard</h2>
                    <div>
                        <span class="me-3">Welcome, <?php echo getUserName(); ?></span>
                        <button class="btn btn-outline-secondary" onclick="logout()">Logout</button>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-muted">Total Users</h6>
                                    <h3 class="card-text" id="totalUsers">Loading...</h3>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-muted">Total Products</h6>
                                    <h3 class="card-text" id="totalProducts">Loading...</h3>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-muted">Total Sales</h6>
                                    <h3 class="card-text" id="totalSales">Loading...</h3>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-muted">Revenue</h6>
                                    <h3 class="card-text" id="totalRevenue">Loading...</h3>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Sales Overview</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="salesChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">User Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="userChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity and Reports -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Recent Activity</h5>
                                <a href="admin-activity.php" class="btn btn-sm btn-outline-success">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Action</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody id="recentActivity">
                                            <tr>
                                                <td colspan="3" class="text-center">Loading activity data...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Available Reports</h5>
                                <a href="admin-reports.php" class="btn btn-sm btn-outline-success">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="row" id="reportsList">
                                    <div class="col-md-6 mb-3">
                                        <div class="card report-card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                                                <h6>Sales Summary</h6>
                                                <a href="admin-reports.php?report=sales" class="btn btn-sm btn-outline-success mt-2">View</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card report-card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-warehouse fa-2x text-success mb-2"></i>
                                                <h6>Inventory Status</h6>
                                                <a href="admin-reports.php?report=inventory" class="btn btn-sm btn-outline-success mt-2">View</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card report-card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-users fa-2x text-success mb-2"></i>
                                                <h6>User Activity</h6>
                                                <a href="admin-reports.php?report=users" class="btn btn-sm btn-outline-success mt-2">View</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card report-card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                                                <h6>Financial Overview</h6>
                                                <a href="admin-reports.php?report=financial" class="btn btn-sm btn-outline-success mt-2">View</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Check if user is logged in
        document.addEventListener('DOMContentLoaded', function() {
            const user = localStorage.getItem('user');
            if (!user) {
                window.location.href = 'login.php';
                return;
            }

            const userData = JSON.parse(user);
            if (userData.role_id != 1) {
                window.location.href = 'home.php';
                return;
            }

            // Load dashboard data
            loadDashboardData();
            loadRecentActivity();
            initCharts();
        });

        function logout() {
            localStorage.removeItem('user');
            window.location.href = 'login.php';
        }

        function loadDashboardData() {
            // Fetch dashboard statistics
            fetch('../backend/admin/dashboard_stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200) {
                        document.getElementById('totalUsers').textContent = data.stats.total_users;
                        document.getElementById('totalProducts').textContent = data.stats.total_products;
                        document.getElementById('totalSales').textContent = data.stats.total_sales;
                        document.getElementById('totalRevenue').textContent = data.stats.total_revenue;
                    } else {
                        console.error('Error loading dashboard data:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function loadRecentActivity() {
            // Fetch recent activity
            fetch('../backend/admin/recent_activity.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200) {
                        const activityTable = document.getElementById('recentActivity');
                        activityTable.innerHTML = '';

                        data.activities.forEach(activity => {
                            activityTable.innerHTML += `
                                <tr>
                                    <td>${activity.user_name}</td>
                                    <td>${activity.action}</td>
                                    <td>${new Date(activity.created_at).toLocaleString()}</td>
                                </tr>
                            `;
                        });
                    } else {
                        document.getElementById('recentActivity').innerHTML = `
                            <tr>
                                <td colspan="3" class="text-center">No recent activity found</td>
                            </tr>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('recentActivity').innerHTML = `
                        <tr>
                            <td colspan="3" class="text-center">Error loading activity data</td>
                        </tr>
                    `;
                });
        }

        function initCharts() {
            // Sales Chart
            fetch('../backend/admin/sales_chart_data.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200) {
                        const ctx = document.getElementById('salesChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: data.labels,
                                datasets: [{
                                    label: 'Sales',
                                    data: data.sales,
                                    backgroundColor: 'rgba(25, 135, 84, 0.2)',
                                    borderColor: 'rgba(25, 135, 84, 1)',
                                    borderWidth: 2,
                                    tension: 0.3
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    }
                })
                .catch(error => console.error('Error:', error));

            // User Distribution Chart
            fetch('../backend/admin/user_chart_data.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200) {
                        const ctx = document.getElementById('userChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: data.labels,
                                datasets: [{
                                    data: data.counts,
                                    backgroundColor: [
                                        'rgba(25, 135, 84, 0.7)',
                                        'rgba(13, 110, 253, 0.7)',
                                        'rgba(255, 193, 7, 0.7)',
                                        'rgba(220, 53, 69, 0.7)'
                                    ],
                                    borderColor: [
                                        'rgba(25, 135, 84, 1)',
                                        'rgba(13, 110, 253, 1)',
                                        'rgba(255, 193, 7, 1)',
                                        'rgba(220, 53, 69, 1)'
                                    ],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>
