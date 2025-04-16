<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales and Purchases</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .nav-tabs .nav-link {
            color: #198754;
        }
        .nav-tabs .nav-link.active {
            color: #198754;
            font-weight: bold;
            border-bottom: 2px solid #198754;
        }
        .summary-card {
            border-left: 4px solid #198754;
            margin-bottom: 20px;
        }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="#" id="welcomeMessage">Loading...</a>
            <div class="d-flex align-items-end">
                <a class="btn btn-outline-light me-4" href="index.php"><i class="fas fa-arrow-left"></i> Back</a>
                <button onclick="logout()" class="btn btn-light">Logout</button>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
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

        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales" type="button"
                    role="tab" aria-controls="sales" aria-selected="true">Sales</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="purchases-tab" data-bs-toggle="tab" data-bs-target="#purchases" type="button"
                    role="tab" aria-controls="purchases" aria-selected="false">Purchases</button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="sales" role="tabpanel" aria-labelledby="sales-tab">
                <div class="row" id="salesListings">
                    <!-- Sales content will load here -->
                </div>
            </div>
            <div class="tab-pane fade" id="purchases" role="tabpanel" aria-labelledby="purchases-tab">
                <div class="row" id="purchasesListings">
                    <!-- Purchases content will load here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        let transactionChart = null;
        let statusChart = null;

        function loadTransactions(userId, type) {
            const params = type === 'sales' ? `seller_id=${userId}` : `buyer_id=${userId}`;
            
            // Load transaction data
            fetch(`${window.location.origin}/maizemarket/backend/sales_and_purchases_backend.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById(`${type}Listings`);
                    
                    if (data.status !== 200 || !data.data) {
                        container.innerHTML = `<p class='text-center'>No ${type} found</p>`;
                        return;
                    }

                    container.innerHTML = "";
                    data.data.forEach(item => {
                        container.innerHTML += `
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">${item.product_name}</h5>
                                        <p class="card-text">
                                            <strong>Quantity:</strong> ${item.quantity} ${item.unit_name}<br>
                                            <strong>Price/Unit:</strong> Ksh ${item.price_per_quantity}<br>
                                            <strong>Total:</strong> Ksh ${item.total_price}<br>
                                            <strong>Status:</strong> ${item.status_name}<br>
                                            <strong>${type === 'sales' ? 'Buyer' : 'Seller'}:</strong> ${type === 'sales' ? item.buyer_name : item.seller_name}<br>
                                            <strong>Date:</strong> ${new Date(item.created_at).toLocaleDateString()}
                                        </p>
                                    </div>
                                </div>
                            </div>`;
                    });

                    // Update summary statistics
                    const totalTransactions = data.data.length;
                    const totalAmount = data.data.reduce((sum, item) => sum + parseFloat(item.total_price), 0);
                    const successfulTransactions = data.data.filter(item => item.status_name.toLowerCase() === 'completed').length;
                    const successRate = totalTransactions > 0 ? (successfulTransactions / totalTransactions * 100).toFixed(1) : 0;

                    document.getElementById('totalTransactions').textContent = totalTransactions;
                    document.getElementById('totalAmount').textContent = `Ksh ${totalAmount.toLocaleString()}`;
                    document.getElementById('successRate').textContent = `${successRate}%`;

                    // Update transaction history chart
                    updateTransactionChart(data.data, type);

                    // Update status distribution chart
                    updateStatusChart(data.data);
                })
                .catch(error => {
                    console.error(`Error loading ${type}:`, error);
                    document.getElementById(`${type}Listings`).innerHTML = 
                        `<p class='text-center text-danger'>Error loading ${type}</p>`;
                });
        }

        function updateTransactionChart(data, type) {
            const ctx = document.getElementById('transactionChart').getContext('2d');
            
            if (transactionChart) {
                transactionChart.destroy();
            }

            // Group transactions by date
            const groupedData = data.reduce((acc, item) => {
                const date = new Date(item.created_at).toLocaleDateString();
                acc[date] = (acc[date] || 0) + parseFloat(item.total_price);
                return acc;
            }, {});

            const sortedDates = Object.keys(groupedData).sort();

            transactionChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: sortedDates,
                    datasets: [{
                        label: `${type.charAt(0).toUpperCase() + type.slice(1)} Amount`,
                        data: sortedDates.map(date => groupedData[date]),
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

        function updateStatusChart(data) {
            const ctx = document.getElementById('statusChart').getContext('2d');
            
            if (statusChart) {
                statusChart.destroy();
            }

            // Count transactions by status
            const statusCount = data.reduce((acc, item) => {
                acc[item.status_name] = (acc[item.status_name] || 0) + 1;
                return acc;
            }, {});

            const labels = Object.keys(statusCount);
            const counts = Object.values(statusCount);
            const backgroundColors = [
                '#198754', // Success/Completed
                '#ffc107', // Warning/Pending
                '#dc3545', // Danger/Cancelled
                '#6c757d'  // Secondary/Other
            ];

            statusChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: counts,
                        backgroundColor: backgroundColors.slice(0, labels.length)
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
                .then(function() {
                    // Success case handled in previous .then()
                })
                .catch(error => {
                    console.error(`Error loading ${type}:`, error);
                    document.getElementById(`${type}Listings`).innerHTML = 
                        `<p class='text-center text-danger'>Error loading ${type}</p>`;
                });
        

        function loadUser() {
            const user = localStorage.getItem("user");
            if (user) {
                const userData = JSON.parse(user);
                document.getElementById("welcomeMessage").innerText = `Welcome, ${userData.name}`;
                loadTransactions(userData.id, 'sales');
                loadTransactions(userData.id, 'purchases');
            } else {
                window.location.href = "login.php";
            }
        }

        function logout() {
            localStorage.removeItem("user");
            window.location.href = "logout.php";
        }

        // Initialize tabs
        document.addEventListener('DOMContentLoaded', function() {
            const triggerTabList = document.querySelectorAll('#myTab button');
            triggerTabList.forEach(triggerEl => {
                triggerEl.addEventListener('click', event => {
                    event.preventDefault();
                    const trigger = new bootstrap.Tab(triggerEl);
                    trigger.show();
                });
            });
        });

        window.onload = loadUser;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>