<?php include 'config.php';
include 'header.php'; // Ensure the header is included ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Listings</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="bg-light">
        <nav class="navbar navbar-dark bg-success">
            <div class="container">
                <a class="navbar-brand" href="#" id="welcomeMessage">Loading...</a>
                <button onclick="logout()" class="btn btn-light">Logout</button>
            </div>
        </nav>
    </div>

    <div class="container mt-4">
        <div class="row">
            <!-- Filter Sidebar -->
            <div class="col-md-3">
                <h5>Filter Products</h5>
                <form id="filterForm">
                    <div class="mb-3">
                        <label for="filterName" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="filterName" name="filterName"
                            placeholder="Search by name">
                    </div>
                    <div class="mb-3">
                        <label for="filterCategory" class="form-label">Category</label>
                        <select class="form-select" id="filterCategory" name="filterCategory">
                            <option value="">All Categories</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="filterPriceMin" class="form-label">Min Price (Ksh)</label>
                        <input type="number" class="form-control" id="filterPriceMin" name="filterPriceMin">
                    </div>
                    <div class="mb-3">
                        <label for="filterPriceMax" class="form-label">Max Price (Ksh)</label>
                        <input type="number" class="form-control" id="filterPriceMax" name="filterPriceMax">
                    </div>
                    <button type="submit" class="btn btn-success w-100">Apply Filters</button>
                </form>
            </div>

            <!-- Product Listings -->
            <div class="col-md-9">
                <h3 id="title_name">Available Products</h3>
                <div class="row" id="productListings">
                    <!-- Product listings will be dynamically inserted here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS & Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            loadUser();
            // loadDropdowns();
            loadCategories();
        });

        function loadUser() {
            const user = localStorage.getItem("user");
            console.log("Logged in :", user);
            if (user) {
                const userData = JSON.parse(user);
                document.getElementById("welcomeMessage").innerText = `Welcome, ${userData.name} - ${userData.role}`;
                if (userData.role_id === 2) {
                    document.getElementById("title_name").innerText = "My Product Listings";
                }
                loadProductListings(userData.id, userData.role_id);
            } else {
                Swal.fire({
                    icon: "warning",
                    title: "Session Expired",
                    text: "Redirecting to login...",
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "login.php";
                });
            }
        }

        // Load categories into filter dropdown
        function loadCategories() {
            fetch(`${window.location.origin}/maizemarket/backend/get_categories.php`)
                .then(response => response.json())
                .then(data => {
                    let categoryDropdown = document.getElementById("filterCategory");
                    data.categories.forEach(category => {
                        categoryDropdown.innerHTML += `<option value="${category.id}">${category.name}</option>`;
                    });
                });
        }

        // Handle filter form submission
        document.getElementById("filterForm").addEventListener("submit", function (e) {
            e.preventDefault();
            const filterName = document.getElementById("filterName").value.trim();
            const filterCategory = document.getElementById("filterCategory").value;
            const filterPriceMin = document.getElementById("filterPriceMin").value;
            const filterPriceMax = document.getElementById("filterPriceMax").value;

            loadProductListings(null, null, { filterName, filterCategory, filterPriceMin, filterPriceMax });
        });

        function loadProductListings(userId, roleId, filters = {}) {
            let url = `${window.location.origin}/maizemarket/backend/home_backend.php`;

            let params = new URLSearchParams();
            if (filters.filterName) params.append("filterName", filters.filterName);
            if (filters.filterCategory) params.append("category_id", filters.filterCategory);
            if (filters.filterPriceMin) params.append("min_price", filters.filterPriceMin);
            if (filters.filterPriceMax) params.append("max_price", filters.filterPriceMax);

            // if ([3, 4].includes(roleId)) {
            //     params.append("role_id", 2);
            // } else if ([4, 5].includes(roleId)) {
            //     params.append("role_id", "3,4");
            // } else if (roleId) {
            //     params.append("role_id", roleId);
            // }

            if (userId) params.append("user_id", userId);

            url += `?${params.toString()}`;

            fetch(url)
                .then(response => response.json())
                .then(response => {
                    let listingsContainer = document.getElementById("productListings");
                    if (!listingsContainer) {
                        console.error("Element with ID 'productListings' not found.");
                        return;
                    }

                    listingsContainer.innerHTML = "";
                    let data = response.data;
                    console.log("Product Listings:", JSON.stringify(data));
                    if (data.length > 0) {
                        data.forEach(row => {
                            listingsContainer.innerHTML += `
                            <div class="col-md-4 mb-3">
                                <div class="card shadow-sm">
                                    <img src="${row.product_image_url || 'https://www.istockphoto.com/photos/farm-produce'}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Product Image">
                                    <div class="card-body">
                                        <h5 class="card-title">${row.product_name}</h5>
                                        <p><strong>Category:</strong> ${row.category_name}</p>
                                        <p><strong>Quantity:</strong> ${row.quantity} ${row.unit_name}</p>
                                        <p><strong>Price:</strong> Ksh ${parseFloat(row.price_per_quantity).toFixed(2)} per ${row.unit_name}</p>
                                        <p><strong>Seller:</strong> ${row.user_name} (${row.user_email}, ${row.user_phone})</p>
                                        <button class="btn btn-primary btn-sm" onclick="buyProduct(${row.id})">
                                            Buy Now
                                        </button>
                                        <button class="btn btn-info btn-sm mt-2" onclick="viewSellerDetails(${row.user_id}, '${row.user_name}', '${row.user_email}', '${row.user_phone}')">
                                            View Seller Details
                                        </button>
                                    </div>
                                </div>
                            </div>`;
                        });
                    } else {
                        listingsContainer.innerHTML = '<p class="text-center">No products found.</p>';
                    }
                })
                .catch(error => {
                    console.error("Error fetching product listings:", error);
                    let listingsContainer = document.getElementById("productListings");
                    if (listingsContainer) {
                        listingsContainer.innerHTML = '<p class="text-center">Error loading products.</p>';
                    }
                });
        }

        function buyProduct(productId) {
            alert("Redirecting to purchase page for product ID: " + productId);
            // Implement the purchase functionality
        }

        function viewSellerDetails(userId, userName, userEmail, userPhone) {
            Swal.fire({
                title: `Seller Details`,
                html: `<p><strong>Name:</strong> ${userName}</p>
                       <p><strong>Email:</strong> ${userEmail}</p>
                       <p><strong>Phone:</strong> ${userPhone}</p>`,
                icon: "info"
            });
        }

        function logout() {
            localStorage.removeItem("user");
            window.location.href = "logout.php";
        }

    </script>

    <?php include 'footer.php'; ?>
</body>

</html>