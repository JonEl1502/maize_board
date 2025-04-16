<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card { 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .summary-card {
            border-left: 4px solid #198754;
        }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
    </style>
</head>

<body class="bg-light">
      
    <nav class="navbar navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand me-4" href="#" id="welcomeMessage">Loading...</a>

            <div class="d-flex align-items-end">
                <!-- <a class="btn btn-outline-light me-4" href="home.php">Home</a> -->
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle me-4" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        Menu
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                    <!-- Add more dropdown items here if needed -->
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4" id="reportTitle">Loading...</h2>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card summary-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Transactions</h5>
                        <h3 class="card-text" id="totalTransactions">Loading...</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card summary-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Amount</h5>
                        <h3 class="card-text" id="totalAmount">Loading...</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card summary-card">
                    <div class="card-body">
                        <h5 class="card-title">Success Rate</h5>
                        <h3 class="card-text" id="successRate">Loading...</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Transaction History</h5>
                        <div class="chart-container">
                            <canvas id="transactionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Status Distribution</h5>
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions Table -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Recent Transactions</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="recentTransactions">
                            <tr>
                                <td colspan="5" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        let transactionChart = null;
        let statusChart = null;
        let userId = null;
        let roleId = null;

        // Get user data from session storage
            // const user = localStorage.getItem("user");
        const userDataStr = localStorage.getItem('user');
        console.log("userDataStr: ",JSON.stringify(userDataStr));
        if (userDataStr) {
            const userData = JSON.parse(userDataStr);
            userId = userData.id;
            roleId = userData.role_id;
            console.log("roleId: ",JSON.stringify(roleId));

            // Set report title based on role
            const reportTitle = document.getElementById('reportTitle');
            reportTitle.textContent = roleId === 1 ? 'Sales Reports' : 'Purchase Reports';

            // Load reports data
            loadReports(userId, roleId);
        } else {
            // window.location.href = 'login.php';
        }

        function loadReports(userId, roleId) {
            fetch(`${window.location.origin}/maizemarket/backend/reports_backend.php?user_id=${userId}&role_id=${roleId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200) {
                        updateSummaryCards(data.summary);
                        updateTransactionChart(data.transaction_history);
                        updateStatusChart(data.status_distribution);
                        updateRecentTransactions(data.recent_transactions);
                    } else {
                        throw new Error(data.message || 'Failed to load reports');
                    }
                })
                .catch(error => {
                    console.error('Error loading reports:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load reports data'
                    });
                });
        }

        function updateSummaryCards(summary) {
            document.getElementById('totalTransactions').textContent = summary.total_transactions;
            document.getElementById('totalAmount').textContent = `Ksh ${summary.total_amount.toLocaleString()}`;
            document.getElementById('successRate').textContent = `${summary.success_rate}%`;
        }

        function updateTransactionChart(history) {
            const ctx = document.getElementById('transactionChart').getContext('2d');
            
            if (transactionChart) {
                transactionChart.destroy();
            }

            transactionChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: history.map(item => item.date),
                    datasets: [{
                        label: roleId === 1 ? 'Sales Amount' : 'Purchase Amount',
                        data: history.map(item => item.amount),
                        borderColor: '#198754',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => `Ksh ${value.toLocaleString()}`
                            }
                        }
                    }
                }
            });
        }

        function updateStatusChart(distribution) {
            const ctx = document.getElementById('statusChart').getContext('2d');
            
            if (statusChart) {
                statusChart.destroy();
            }

            const statusColors = {
                'Pending': '#ffc107',
                'Completed': '#198754',
                'Cancelled': '#dc3545'
            };

            statusChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(distribution),
                    datasets: [{
                        data: Object.values(distribution),
                        backgroundColor: Object.keys(distribution).map(status => statusColors[status] || '#6c757d')
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => `Ksh ${value.toLocaleString()}`
                            }
                        }
                    }
                }
            });
        }

        function updateRecentTransactions(transactions) {
            const tbody = document.getElementById('recentTransactions');
            tbody.innerHTML = transactions.map(t => `
                <tr>
                    <td>${new Date(t.date).toLocaleDateString()}</td>
                    <td>${t.product_name}</td>
                    <td>${t.quantity} ${t.unit_name}</td>
                    <td>Ksh ${parseFloat(t.amount).toLocaleString()}</td>
                    <td><span class="badge ${getStatusBadgeClass(t.status)}">${t.status}</span></td>
                </tr>
            `).join('');
        }

        function getStatusBadgeClass(status) {
            switch(status.toLowerCase()) {
                case 'completed': return 'bg-success';
                case 'pending': return 'bg-warning text-dark';
                case 'cancelled': return 'bg-danger';
                default: return 'bg-secondary';
            }
        }

        function loadUser() {
            const user = localStorage.getItem('user');
            if (user) {
                const userData = JSON.parse(user);
                const welcomeMessageElement = document.getElementById('welcomeMessage');
                if (welcomeMessageElement) {
                    welcomeMessageElement.innerText = `Welcome, ${userData.name}`;
                }
                loadReports(userData.id, userData.role);
            } else {
                // window.location.href = 'login.php';
            }
        }

        function logout() {
            // localStorage.removeItem('user');
            // window.location.href = 'login.php';
        }

        window.onload = loadUser;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>