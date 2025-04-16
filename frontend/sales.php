<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Sales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .card { 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
        }
    </style>
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="#" id="welcomeMessage">Loading...</a>
            <div class="d-flex align-items-end">
                <!-- <a class="btn btn-outline-light me-2" href="purchases.php">My Purchases</a> -->
                <!-- <a class="btn btn-outline-light me-2" href="home.php">Home</a> -->
                <!-- <button onclick="logout()" class="btn btn-light">Logout</button> -->

                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle me-4" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        Menu
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="sales.php">My Sales</a></li>
                    <li><a class="dropdown-item" href="purchases.php">My Purchases</a></li>
                        <li><a class="dropdown-item" href="home.php">Buy</a></li>
                        <li><a class="dropdown-item" onclick="logout()">Logout</a></li>
                        <!-- Add more dropdown items here if needed -->
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Sales</h2>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-success active" onclick="filterSales('all')">All</button>
                <button type="button" class="btn btn-outline-success" onclick="filterSales('pending')">Pending</button>
                <button type="button" class="btn btn-outline-success" onclick="filterSales('completed')">Completed</button>
            </div>
        </div>

        <div class="row" id="salesListings">
            <!-- Sales will be loaded here dynamically -->
        </div>
    </div>

    <script>
        let currentFilter = 'all';

        function loadSales(userId) {
            fetch(`${window.location.origin}/maizemarket/backend/sales_and_purchases_backend.php?seller_id=${userId}&status=${currentFilter}`)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('salesListings');
                    
                    if (data.status !== 200 || !data.data || data.data.length === 0) {
                        container.innerHTML = `<div class="col-12 text-center"><p class="text-muted">No sales found</p></div>`;
                        return;
                    }

                    container.innerHTML = "";
                    data.data.forEach(sale => {
                        const statusClass = getStatusClass(sale.status_name);
                        container.innerHTML += `
                            <div class="col-md-4 mb-4">
                                <div class="card position-relative">
                                    <span class="status-badge ${statusClass}">${sale.status_name}</span>
                                    <div class="card-body">
                                        <h5 class="card-title">${sale.product_name}</h5>
                                        <div class="card-text">
                                            <p class="mb-1"><strong>Quantity:</strong> ${sale.quantity} ${sale.unit_name}</p>
                                            <p class="mb-1"><strong>Price/Unit:</strong> Ksh ${sale.price_per_quantity}</p>
                                            <p class="mb-1"><strong>Total:</strong> Ksh ${sale.total_price}</p>
                                            <p class="mb-1"><strong>Buyer:</strong> ${sale.buyer_name}</p>
                                            <p class="mb-1"><strong>Date:</strong> ${formatDate(sale.created_at)}</p>
                                            <p class="mb-1"><strong>Payment Status:</strong> ${sale.payment_status || 'Pending'}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                    });
                })
                .catch(error => {
                    console.error('Error loading sales:', error);
                    document.getElementById('salesListings').innerHTML = 
                        `<div class="col-12 text-center"><p class="text-danger">Error loading sales</p></div>`;
                });
        }

        function filterSales(status) {
            currentFilter = status;
            const buttons = document.querySelectorAll('.btn-group .btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            const userData = JSON.parse(localStorage.getItem('user'));
            if (userData) {
                loadSales(userData.id);
            }
        }

        function getStatusClass(status) {
            switch(status.toLowerCase()) {
                case 'pending': return 'bg-warning text-dark';
                case 'completed': return 'bg-success text-white';
                case 'cancelled': return 'bg-danger text-white';
                default: return 'bg-secondary text-white';
            }
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function loadUser() {
            const user = localStorage.getItem('user');
            if (user) {
                const userData = JSON.parse(user);
                document.getElementById('welcomeMessage').innerText = `Welcome, ${userData.name}`;
                loadSales(userData.id);
            } else {
                window.location.href = 'login.php';
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