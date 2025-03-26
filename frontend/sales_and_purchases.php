<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales and Purchases</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .nav-tabs .nav-link {
            color: #198754;
        }
        .nav-tabs .nav-link.active {
            color: #198754;
            font-weight: bold;
            border-bottom: 2px solid #198754;
        }
    </style>
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="#" id="welcomeMessage">Loading...</a>
            <button onclick="logout()" class="btn btn-light">Logout</button>
        </div>
    </nav>

    <div class="container mt-5">
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
        function loadTransactions(userId, type) {
            const params = type === 'sales' ? `seller_id=${userId}` : `buyer_id=${userId}`;
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
                })
                .catch(error => {
                    console.error(`Error loading ${type}:`, error);
                    document.getElementById(`${type}Listings`).innerHTML = 
                        `<p class='text-center text-danger'>Error loading ${type}</p>`;
                });
        }

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