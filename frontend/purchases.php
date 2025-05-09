<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Purchases</title>
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
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle me-4" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        Menu
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                    <li><a class="dropdown-item" href="sales.php">My Sales</a></li>
                    <li id="buyMenuItem"><a class="dropdown-item" href="home.php">Buy</a></li>
                    <li><a class="dropdown-item" onclick="logout()">Logout</a></li>
                    </ul>
                </div>
                <a href="dashboard.php" class="btn btn-outline-light"><i class="fas fa-arrow-left"></i> Back</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Purchases</h2>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-success active" onclick="filterPurchases('all')">All</button>
                <button type="button" class="btn btn-outline-success" onclick="filterPurchases('pending')">Pending</button>
                <button type="button" class="btn btn-outline-success" onclick="filterPurchases('completed')">Completed</button>
            </div>
        </div>

        <div class="row" id="purchasesListings">
            <!-- Purchases will be loaded here dynamically -->
        </div>
    </div>

    <script>
        let currentFilter = 'all';

        function loadPurchases(userId) {
            fetch(`${window.location.origin}/maizemarket/backend/sales_and_purchases_backend.php?buyer_id=${userId}&status=${currentFilter}`)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('purchasesListings');

                    if (data.status !== 200 || !data.data || data.data.length === 0) {
                        container.innerHTML = `<div class="col-12 text-center"><p class="text-muted">No purchases found</p></div>`;
                        return;
                    }

                    container.innerHTML = "";
                    data.data.forEach(purchase => {
                        const statusClass = getStatusClass(purchase.status_name);
                        container.innerHTML += `
                            <div class="col-md-4 mb-4">
                                <div class="card position-relative">
                                    <span class="status-badge ${statusClass}">${purchase.status_name}</span>
                                    <div class="card-body">
                                        <h5 class="card-title">${purchase.product_name}</h5>
                                        <div class="card-text">
                                            <p class="mb-1"><strong>Quantity:</strong> ${purchase.quantity} ${purchase.unit_name}</p>
                                            <p class="mb-1"><strong>Price/Unit:</strong> Ksh ${purchase.price_per_quantity}</p>
                                            <p class="mb-1"><strong>Total:</strong> Ksh ${purchase.total_price}</p>
                                            <p class="mb-1"><strong>Seller:</strong> ${purchase.seller_name}</p>
                                            <p class="mb-1"><strong>Date:</strong> ${formatDate(purchase.created_at)}</p>
                                            <p class="mb-1"><strong>Payment Status:</strong> ${purchase.payment_status || 'Pending'}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                    });
                })
                .catch(error => {
                    console.error('Error loading purchases:', error);
                    document.getElementById('purchasesListings').innerHTML =
                        `<div class="col-12 text-center"><p class="text-danger">Error loading purchases</p></div>`;
                });
        }

        function filterPurchases(status) {
            currentFilter = status;
            const buttons = document.querySelectorAll('.btn-group .btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            const userData = JSON.parse(localStorage.getItem('user'));
            if (userData) {
                loadPurchases(userData.id);
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
                let entity_name = userData.entity_name??userData.name;
                document.getElementById("welcomeMessage").innerText = `Welcome, ${entity_name}  (${userData.role})`;

                // document.getElementById('welcomeMessage').innerText = `Welcome, ${userData.name}`;
                console.log("rrr:",JSON.stringify(userData));
                if (userData.role_id === 2) {
                    document.getElementById("buyMenuItem").style.display = "none";
                }
                loadPurchases(userData.id);
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