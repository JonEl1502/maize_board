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
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle me-4" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        Menu
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                    <li><a class="dropdown-item" href="home.php">Home</a></li>
                    <li><a class="dropdown-item" onclick="logout()">Logout</a></li>
                    </ul>
                </div>
                <a href="dashboard.php" class="btn btn-outline-light"><i class="fas fa-arrow-left"></i> Back</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4" id="reportTitle">Loading...</h2>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card summary-card border-success">
                    <div class="card-body">
                        <h5 class="card-title">Total Sales</h5>
                        <h3 class="card-text text-success" id="totalSales">Loading...</h3>
                        <p class="text-muted small" id="salesCount">0 transactions</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card border-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total Purchases</h5>
                        <h3 class="card-text text-primary" id="totalPurchases">Loading...</h3>
                        <p class="text-muted small" id="purchasesCount">0 transactions</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card border-info">
                    <div class="card-body">
                        <h5 class="card-title">Success Rate</h5>
                        <h3 class="card-text text-info" id="successRate">Loading...</h3>
                        <p class="text-muted small" id="totalTransactions">0 total transactions</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card border-warning">
                    <div class="card-body">
                        <h5 class="card-title">Average Transaction</h5>
                        <h3 class="card-text text-warning" id="averageTransaction">Loading...</h3>
                        <p class="text-muted small" id="transactionTrend">0% from last month</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sales vs Purchases History</h5>
                        <div class="d-flex justify-content-end mb-2">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary active" id="weeklyBtn" onclick="changeTimeframe('weekly')">Weekly</button>
                                <button type="button" class="btn btn-outline-secondary" id="monthlyBtn" onclick="changeTimeframe('monthly')">Monthly</button>
                                <button type="button" class="btn btn-outline-secondary" id="yearlyBtn" onclick="changeTimeframe('yearly')">Yearly</button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="transactionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Transaction Status</h5>
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top Products Sold</h5>
                        <div class="chart-container">
                            <canvas id="topProductsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top Products Purchased</h5>
                        <div class="chart-container">
                            <canvas id="topPurchasesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Tables -->
        <div class="row mb-4">
            <div class="col-12">
                <ul class="nav nav-tabs" id="transactionTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-transactions" type="button" role="tab" aria-controls="all-transactions" aria-selected="true">All Transactions</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales-transactions" type="button" role="tab" aria-controls="sales-transactions" aria-selected="false">Sales</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="purchases-tab" data-bs-toggle="tab" data-bs-target="#purchases-transactions" type="button" role="tab" aria-controls="purchases-transactions" aria-selected="false">Purchases</button>
                    </li>
                </ul>

                <div class="tab-content" id="transactionTabsContent">
                    <!-- All Transactions Tab -->
                    <div class="tab-pane fade show active" id="all-transactions" role="tabpanel" aria-labelledby="all-tab">
                        <div class="card border-top-0 rounded-top-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">All Recent Transactions</h5>
                                    <div class="input-group input-group-sm" style="width: 200px;">
                                        <input type="text" class="form-control" id="transactionSearch" placeholder="Search transactions...">
                                        <button class="btn btn-outline-secondary" type="button" onclick="searchTransactions()"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="allTransactions">
                                            <tr>
                                                <td colspan="7" class="text-center">Loading...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Tab -->
                    <div class="tab-pane fade" id="sales-transactions" role="tabpanel" aria-labelledby="sales-tab">
                        <div class="card border-top-0 rounded-top-0">
                            <div class="card-body">
                                <h5 class="card-title">Recent Sales</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Buyer</th>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="salesTransactions">
                                            <tr>
                                                <td colspan="7" class="text-center">Loading...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Purchases Tab -->
                    <div class="tab-pane fade" id="purchases-transactions" role="tabpanel" aria-labelledby="purchases-tab">
                        <div class="card border-top-0 rounded-top-0">
                            <div class="card-body">
                                <h5 class="card-title">Recent Purchases</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Seller</th>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="purchasesTransactions">
                                            <tr>
                                                <td colspan="7" class="text-center">Loading...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Summary -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Transaction Statistics</h5>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th colspan="2" class="text-success">Sales Statistics</th>
                                </tr>
                            </thead>
                            <tbody id="salesStats">
                                <tr>
                                    <td>Total Sales</td>
                                    <td id="statTotalSales">Loading...</td>
                                </tr>
                                <tr>
                                    <td>Average Sale Value</td>
                                    <td id="statAvgSale">Loading...</td>
                                </tr>
                                <tr>
                                    <td>Highest Sale</td>
                                    <td id="statHighestSale">Loading...</td>
                                </tr>
                                <tr>
                                    <td>Most Sold Product</td>
                                    <td id="statTopProduct">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th colspan="2" class="text-primary">Purchase Statistics</th>
                                </tr>
                            </thead>
                            <tbody id="purchaseStats">
                                <tr>
                                    <td>Total Purchases</td>
                                    <td id="statTotalPurchases">Loading...</td>
                                </tr>
                                <tr>
                                    <td>Average Purchase Value</td>
                                    <td id="statAvgPurchase">Loading...</td>
                                </tr>
                                <tr>
                                    <td>Highest Purchase</td>
                                    <td id="statHighestPurchase">Loading...</td>
                                </tr>
                                <tr>
                                    <td>Most Purchased Product</td>
                                    <td id="statTopPurchased">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusUpdateModal" tabindex="-1" aria-labelledby="statusUpdateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusUpdateModalLabel">Update Transaction Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Transaction ID: <span id="updateTransactionId"></span></p>
                    <p>Current Status: <span id="currentStatus" class="badge bg-secondary"></span></p>
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">Select New Status:</label>
                        <select class="form-select" id="newStatus">
                            <option value="">Select a status...</option>
                            <!-- Status options will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="form-text text-muted">
                        Changing the status will update this transaction across the system.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmStatusUpdate">Update Status</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Chart objects
        let transactionChart = null;
        let statusChart = null;
        let topProductsChart = null;
        let topPurchasesChart = null;

        // User data
        let userId = null;
        let roleId = null;

        // Report data
        let reportData = null;
        let currentTimeframe = 'weekly';

        // Get user data from session storage
            // const user = localStorage.getItem("user");
        const userDataStr = localStorage.getItem('user');
        console.log("userDataStr: ",JSON.stringify(userDataStr));
        if (userDataStr) {
            const userData = JSON.parse(userDataStr);
            userId = userData.id;
            roleId = userData.role_id;
            console.log("roleId: ",JSON.stringify(roleId));

            // Set report title based on role_id
            const reportTitle = document.getElementById('reportTitle');
            switch(roleId) {
                case 1:
                    reportTitle.textContent = 'Admin Reports Dashboard';
                    break;
                case 2:
                    reportTitle.textContent = 'Farmer Reports Dashboard';
                    break;
                case 3:
                    reportTitle.textContent = 'Wholesaler Reports Dashboard';
                    break;
                case 4:
                    reportTitle.textContent = 'Customer Reports Dashboard';
                    break;
                case 5:
                    reportTitle.textContent = 'Transaction Reports Dashboard';
                    break;
                default:
                    reportTitle.textContent = 'Reports Dashboard';
            }

            // Load reports data
            loadReports(userId, roleId);
        } else {
            // window.location.href = 'login.php';
        }

        function loadReports(userId, roleId) {
            // Show loading spinner
            Swal.fire({
                title: 'Loading Reports',
                text: 'Please wait while we fetch your report data...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`${window.location.origin}/maizemarket/backend/reports_backend_enhanced.php?user_id=${userId}&role_id=${roleId}&timeframe=${currentTimeframe}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200) {
                        // Store the data globally for timeframe changes
                        reportData = data;

                        // Update all UI components
                        updateSummaryCards(data.summary);
                        updateTransactionChart(data.transaction_history);
                        updateStatusChart(data.status_distribution);

                        // Update product charts
                        if (data.top_products) {
                            updateTopProductsCharts(data.top_products);
                        }

                        // Update transaction tables
                        updateTransactionTables(data.transactions);

                        // Update statistics tables
                        updateStatisticsTables(data.statistics);

                        // Close loading spinner
                        Swal.close();
                    } else {
                        throw new Error(data.message || 'Failed to load reports');
                    }
                })
                .catch(error => {
                    console.error('Error loading reports:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load reports data: ' + error.message
                    });
                });
        }

        function updateSummaryCards(summary) {
            // Update sales card
            document.getElementById('totalSales').textContent = `Ksh ${summary.total_sales.toLocaleString()}`;
            document.getElementById('salesCount').textContent = `${summary.sales_count} transactions`;

            // Update purchases card
            document.getElementById('totalPurchases').textContent = `Ksh ${summary.total_purchases.toLocaleString()}`;
            document.getElementById('purchasesCount').textContent = `${summary.purchases_count} transactions`;

            // Update success rate card
            document.getElementById('successRate').textContent = `${summary.success_rate}%`;
            document.getElementById('totalTransactions').textContent = `${summary.total_transactions} total transactions`;

            // Update average transaction card
            document.getElementById('averageTransaction').textContent = `Ksh ${summary.average_transaction.toLocaleString()}`;

            const trend = summary.transaction_trend || 0;
            const trendElement = document.getElementById('transactionTrend');
            if (trend > 0) {
                trendElement.className = 'text-success small';
                trendElement.textContent = `↑ ${trend}% from last month`;
            } else if (trend < 0) {
                trendElement.className = 'text-danger small';
                trendElement.textContent = `↓ ${Math.abs(trend)}% from last month`;
            } else {
                trendElement.className = 'text-muted small';
                trendElement.textContent = `No change from last month`;
            }
        }

        function updateTransactionChart(history) {
            const ctx = document.getElementById('transactionChart').getContext('2d');

            if (transactionChart) {
                transactionChart.destroy();
            }

            // Prepare datasets
            const datasets = [];

            // Add sales dataset if available
            if (history.sales && history.sales.length > 0) {
                datasets.push({
                    label: 'Sales',
                    data: history.sales.map(item => item.amount),
                    borderColor: '#198754', // Green
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                });
            }

            // Add purchases dataset if available
            if (history.purchases && history.purchases.length > 0) {
                datasets.push({
                    label: 'Purchases',
                    data: history.purchases.map(item => item.amount),
                    borderColor: '#0d6efd', // Blue
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                });
            }

            // Fallback for old data format
            if (!history.sales && !history.purchases && history.length > 0) {
                // Determine label based on numeric role_id
                let label = 'Transactions';
                if (roleId === 1) {
                    label = 'Sales';
                } else if (roleId === 2) {
                    label = 'Sales';
                } else if (roleId === 3) {
                    label = 'Transactions';
                } else if (roleId === 4) {
                    label = 'Purchases';
                } else if (roleId === 5) {
                    label = 'Transactions';
                }

                datasets.push({
                    label: label,
                    data: history.map(item => item.amount),
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                });
            }

            // Get labels based on available data
            let labels = [];
            if (history.sales && history.sales.length > 0) {
                labels = history.sales.map(item => item.label);
            } else if (history.purchases && history.purchases.length > 0) {
                labels = history.purchases.map(item => item.label);
            } else if (history.length > 0) {
                labels = history.map(item => item.date);
            }

            transactionChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += 'Ksh ' + context.parsed.y.toLocaleString();
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => `Ksh ${value.toLocaleString()}`
                            },
                            title: {
                                display: true,
                                text: 'Amount (Ksh)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: getTimeframeLabel()
                            }
                        }
                    }
                }
            });
        }

        function getTimeframeLabel() {
            switch(currentTimeframe) {
                case 'weekly': return 'Day';
                case 'monthly': return 'Week';
                case 'yearly': return 'Month';
                default: return 'Period';
            }
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

        function updateTopProductsCharts(products) {
            // Update Top Products Sold chart
            const soldCtx = document.getElementById('topProductsChart').getContext('2d');
            if (topProductsChart) {
                topProductsChart.destroy();
            }

            if (products.sold && products.sold.length > 0) {
                topProductsChart = new Chart(soldCtx, {
                    type: 'bar',
                    data: {
                        labels: products.sold.map(p => p.product_name),
                        datasets: [{
                            label: 'Quantity Sold',
                            data: products.sold.map(p => p.quantity),
                            backgroundColor: 'rgba(25, 135, 84, 0.7)',
                            borderColor: '#198754',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Quantity'
                                }
                            },
                            x: {
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            }
                        }
                    }
                });
            } else {
                // Show empty chart
                topProductsChart = new Chart(soldCtx, {
                    type: 'bar',
                    data: {
                        labels: ['No Data Available'],
                        datasets: [{
                            label: 'No Products Data',
                            data: [0],
                            backgroundColor: '#6c757d'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }

            // Update Top Products Purchased chart
            const purchasedCtx = document.getElementById('topPurchasesChart').getContext('2d');
            if (topPurchasesChart) {
                topPurchasesChart.destroy();
            }

            if (products.purchased && products.purchased.length > 0) {
                topPurchasesChart = new Chart(purchasedCtx, {
                    type: 'bar',
                    data: {
                        labels: products.purchased.map(p => p.product_name),
                        datasets: [{
                            label: 'Quantity Purchased',
                            data: products.purchased.map(p => p.quantity),
                            backgroundColor: 'rgba(13, 110, 253, 0.7)',
                            borderColor: '#0d6efd',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Quantity'
                                }
                            },
                            x: {
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            }
                        }
                    }
                });
            } else {
                // Show empty chart
                topPurchasesChart = new Chart(purchasedCtx, {
                    type: 'bar',
                    data: {
                        labels: ['No Data Available'],
                        datasets: [{
                            label: 'No Products Data',
                            data: [0],
                            backgroundColor: '#6c757d'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
        }

        function updateTransactionTables(transactions) {
            // Update All Transactions table
            const allTransactionsTable = document.getElementById('allTransactions');
            if (transactions.all && transactions.all.length > 0) {
                allTransactionsTable.innerHTML = transactions.all.map(t => `
                    <tr data-id="${t.id}" data-status="${t.status}" data-status-id="${t.status_id || ''}">
                        <td>${new Date(t.date).toLocaleDateString()}</td>
                        <td><span class="badge ${t.type === 'sale' ? 'bg-success' : 'bg-primary'}">${t.type === 'sale' ? 'Sale' : 'Purchase'}</span></td>
                        <td>${t.product_name}</td>
                        <td>${t.quantity} ${t.unit_name}</td>
                        <td>Ksh ${parseFloat(t.amount).toLocaleString()}</td>
                        <td><span class="badge ${getStatusBadgeClass(t.status)}">${t.status}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary update-status-btn"
                                    onclick="openStatusUpdateModal(${t.id}, '${t.status}', ${t.status_id || 0}, '${t.type}')">
                                Update Status
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                allTransactionsTable.innerHTML = '<tr><td colspan="7" class="text-center">No transactions found</td></tr>';
            }

            // Update Sales Transactions table
            const salesTransactionsTable = document.getElementById('salesTransactions');
            if (transactions.sales && transactions.sales.length > 0) {
                salesTransactionsTable.innerHTML = transactions.sales.map(t => `
                    <tr data-id="${t.id}" data-status="${t.status}" data-status-id="${t.status_id || ''}">
                        <td>${new Date(t.date).toLocaleDateString()}</td>
                        <td>${t.buyer_name || 'Unknown'}</td>
                        <td>${t.product_name}</td>
                        <td>${t.quantity} ${t.unit_name}</td>
                        <td>Ksh ${parseFloat(t.amount).toLocaleString()}</td>
                        <td><span class="badge ${getStatusBadgeClass(t.status)}">${t.status}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-success update-status-btn"
                                    onclick="openStatusUpdateModal(${t.id}, '${t.status}', ${t.status_id || 0}, 'sale')">
                                Update Status
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                salesTransactionsTable.innerHTML = '<tr><td colspan="7" class="text-center">No sales found</td></tr>';
            }

            // Update Purchases Transactions table
            const purchasesTransactionsTable = document.getElementById('purchasesTransactions');
            if (transactions.purchases && transactions.purchases.length > 0) {
                purchasesTransactionsTable.innerHTML = transactions.purchases.map(t => `
                    <tr data-id="${t.id}" data-status="${t.status}" data-status-id="${t.status_id || ''}">
                        <td>${new Date(t.date).toLocaleDateString()}</td>
                        <td>${t.seller_name || 'Unknown'}</td>
                        <td>${t.product_name}</td>
                        <td>${t.quantity} ${t.unit_name}</td>
                        <td>Ksh ${parseFloat(t.amount).toLocaleString()}</td>
                        <td><span class="badge ${getStatusBadgeClass(t.status)}">${t.status}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary update-status-btn"
                                    onclick="openStatusUpdateModal(${t.id}, '${t.status}', ${t.status_id || 0}, 'purchase')">
                                Update Status
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                purchasesTransactionsTable.innerHTML = '<tr><td colspan="7" class="text-center">No purchases found</td></tr>';
            }
        }

        function updateStatisticsTables(statistics) {
            // Update Sales Statistics
            if (statistics.sales) {
                document.getElementById('statTotalSales').textContent = `Ksh ${statistics.sales.total.toLocaleString()}`;
                document.getElementById('statAvgSale').textContent = `Ksh ${statistics.sales.average.toLocaleString()}`;
                document.getElementById('statHighestSale').textContent = `Ksh ${statistics.sales.highest.toLocaleString()}`;
                document.getElementById('statTopProduct').textContent = statistics.sales.top_product || 'N/A';
            }

            // Update Purchase Statistics
            if (statistics.purchases) {
                document.getElementById('statTotalPurchases').textContent = `Ksh ${statistics.purchases.total.toLocaleString()}`;
                document.getElementById('statAvgPurchase').textContent = `Ksh ${statistics.purchases.average.toLocaleString()}`;
                document.getElementById('statHighestPurchase').textContent = `Ksh ${statistics.purchases.highest.toLocaleString()}`;
                document.getElementById('statTopPurchased').textContent = statistics.purchases.top_product || 'N/A';
            }
        }

        function changeTimeframe(timeframe) {
            // Update active button
            document.getElementById('weeklyBtn').classList.remove('active');
            document.getElementById('monthlyBtn').classList.remove('active');
            document.getElementById('yearlyBtn').classList.remove('active');
            document.getElementById(`${timeframe}Btn`).classList.add('active');

            // Update current timeframe
            currentTimeframe = timeframe;

            // Reload reports with new timeframe
            loadReports(userId, roleId);
        }

        function searchTransactions() {
            const searchTerm = document.getElementById('transactionSearch').value.toLowerCase();
            const allTransactions = reportData.transactions.all || [];

            if (searchTerm.trim() === '') {
                // If search is empty, show all transactions
                updateTransactionTables(reportData.transactions);
                return;
            }

            // Filter transactions based on search term
            const filteredTransactions = allTransactions.filter(t =>
                t.product_name.toLowerCase().includes(searchTerm) ||
                (t.buyer_name && t.buyer_name.toLowerCase().includes(searchTerm)) ||
                (t.seller_name && t.seller_name.toLowerCase().includes(searchTerm)) ||
                t.status.toLowerCase().includes(searchTerm)
            );

            // Update the all transactions table with filtered results
            const allTransactionsTable = document.getElementById('allTransactions');
            if (filteredTransactions.length > 0) {
                allTransactionsTable.innerHTML = filteredTransactions.map(t => `
                    <tr data-id="${t.id}" data-status="${t.status}" data-status-id="${t.status_id || ''}">
                        <td>${new Date(t.date).toLocaleDateString()}</td>
                        <td><span class="badge ${t.type === 'sale' ? 'bg-success' : 'bg-primary'}">${t.type === 'sale' ? 'Sale' : 'Purchase'}</span></td>
                        <td>${t.product_name}</td>
                        <td>${t.quantity} ${t.unit_name}</td>
                        <td>Ksh ${parseFloat(t.amount).toLocaleString()}</td>
                        <td><span class="badge ${getStatusBadgeClass(t.status)}">${t.status}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary update-status-btn"
                                    onclick="openStatusUpdateModal(${t.id}, '${t.status}', ${t.status_id || 0}, '${t.type}')">
                                Update Status
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                allTransactionsTable.innerHTML = '<tr><td colspan="7" class="text-center">No matching transactions found</td></tr>';
            }
        }

        // Status update functions
        let statusOptions = [];
        let currentTransactionId = null;
        let currentTransactionType = null;

        // Function to load available statuses
        function loadStatusOptions() {
            fetch(`${window.location.origin}/maizemarket/backend/get_statuses.php`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200) {
                        statusOptions = data.statuses;
                        console.log('Loaded statuses:', statusOptions);
                    } else {
                        console.error('Error loading statuses:', data.message);
                        // Default statuses if API fails
                        statusOptions = [
                            { id: 1, name: 'Listed', payment_status: 'pending' },
                            { id: 2, name: 'Spoken For', payment_status: 'completed' },
                            { id: 3, name: 'Paid For', payment_status: 'cancelled' },
                            { id: 4, name: 'Sold', payment_status: 'sold' },
                        ];
                    }
                })
                .catch(error => {
                    console.error('Error loading statuses:', error);
                    // Default statuses if API fails
                    statusOptions = [
                        { id: 1, name: 'Pending', payment_status: 'pending' },
                        { id: 2, name: 'Completed', payment_status: 'completed' },
                        { id: 3, name: 'Cancelled', payment_status: 'cancelled' }
                    ];
                });
        }

        // Load statuses when page loads
        loadStatusOptions();

        // Function to open the status update modal
        function openStatusUpdateModal(transactionId, currentStatus, statusId, transactionType) {
            currentTransactionId = transactionId;
            currentTransactionType = transactionType;

            // Update modal content
            document.getElementById('updateTransactionId').textContent = transactionId;

            const currentStatusElement = document.getElementById('currentStatus');
            currentStatusElement.textContent = currentStatus;
            currentStatusElement.className = `badge ${getStatusBadgeClass(currentStatus)}`;

            // Populate status dropdown
            const statusSelect = document.getElementById('newStatus');
            statusSelect.innerHTML = '<option value="">Select a status...</option>';

            // Add status options
            statusOptions.forEach(status => {
                if (status.name !== currentStatus) {
                    statusSelect.innerHTML += `<option value="${status.id}">${status.name}</option>`;
                }
            });

            // Show the modal
            const statusModal = new bootstrap.Modal(document.getElementById('statusUpdateModal'));
            statusModal.show();
        }

        // Add event listener for status update confirmation
        document.getElementById('confirmStatusUpdate').addEventListener('click', updateTransactionStatus);

        // Function to update transaction status
        function updateTransactionStatus() {
            const newStatusId = document.getElementById('newStatus').value;

            if (!newStatusId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select a new status'
                });
                return;
            }

            // Find the selected status name for better user feedback
            const selectedStatus = statusOptions.find(s => s.id == newStatusId);
            const statusName = selectedStatus ? selectedStatus.name : 'new status';

            // Show loading
            Swal.fire({
                title: 'Updating Status',
                text: `Changing status to ${statusName}. Please wait...`,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            console.log('Updating transaction', {
                transaction_id: currentTransactionId,
                new_status_id: newStatusId,
                user_id: userId,
                transaction_type: currentTransactionType
            });

            // Send update request
            fetch(`${window.location.origin}/maizemarket/backend/update_transaction_status_report.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    transaction_id: currentTransactionId,
                    new_status_id: newStatusId,
                    user_id: userId,
                    transaction_type: currentTransactionType
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Hide the modal
                const statusModal = bootstrap.Modal.getInstance(document.getElementById('statusUpdateModal'));
                statusModal.hide();

                if (data.status === 200) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: `Transaction status updated to ${statusName} successfully`
                    });

                    // Reload reports data to reflect the changes
                    loadReports(userId, roleId);
                } else {
                    // Show error message with details
                    console.error('Status update error:', data);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to update transaction status'
                    });
                }
            })
            .catch(error => {
                console.error('Error updating status:', error);

                // Hide the modal
                try {
                    const statusModal = bootstrap.Modal.getInstance(document.getElementById('statusUpdateModal'));
                    if (statusModal) {
                        statusModal.hide();
                    }
                } catch (e) {
                    console.error('Error hiding modal:', e);
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: `An error occurred while updating the status: ${error.message}`
                });
            });
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

                // Make sure we're using role_id (numeric) instead of role (string)
                const roleId = userData.role_id || (userData.role ? getRoleIdFromName(userData.role) : 1);
                loadReports(userData.id, roleId);
            } else {
                window.location.href = 'login.php';
            }
        }

        // Helper function to convert role name to role_id if needed
        function getRoleIdFromName(roleName) {
            switch(roleName.toLowerCase()) {
                case 'admin': return 1;
                case 'farmer': return 2;
                case 'wholesaler': return 3;
                case 'customer': return 4;
                case 'custom': return 5;
                default: return 1; // Default to admin
            }
        }

        function logout() {
            localStorage.removeItem('user');
            window.location.href = 'login.php';
        }

        window.onload = loadUser;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>