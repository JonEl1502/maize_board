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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Include SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <div class="bg-light">
        <nav class="navbar navbar-dark bg-success">
            <div class="container">
                <a class="navbar-brand" href="#" id="welcomeMessage">Loading...</a>
                
            <div class="d-flex align-items-end">
                <a class="btn btn-outline-light me-4" href="sales_and_purchases.php">Sales & Purchases</a>
                <button onclick="logout()" class="btn btn-light">Logout</button>
            </div>
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
                        <label for="filterProduct" class="form-label">Product</label>
                        <select class="form-select" id="filterProduct" name="filterProduct">
                            <option value="">All Products</option>
                        </select>
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

    <!-- Seller Details Modal -->
    <div class="modal fade" id="sellerModal" tabindex="-1" aria-labelledby="sellerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sellerModalLabel">Seller Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Name:</strong> <span id="sellerName"></span></p>
                    <p><strong>Email:</strong> <span id="sellerEmail"></span></p>
                    <p><strong>Phone:</strong> <span id="sellerPhone"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Buy Confirmation Modal -->
    <div class="modal fade" id="buyModal" tabindex="-1" aria-labelledby="buyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="buyModalLabel">Confirm Purchase</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Product:</strong> <span id="buyProductName"></span></p>
                    <p><strong>Price:</strong> Ksh <span id="buyProductPrice"></span> per <span
                            id="buyProductUnit"></span></p>
                    <div class="mb-3">
                        <label for="quantityAmount" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantityAmount" min="1" placeholder="Enter quantity">
                    </div>
                    <div class="mb-3">
                        <label for="mpesaCode" class="form-label">Enter Mpesa Code</label>
                        <input type="text" class="form-control" id="mpesaCode" placeholder="e.g., MPESA123456">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="confirmPurchase()">Confirm & Pay</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS & Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            loadUser();
            loadCategories();
            loadProducts(); // Load products for filtering
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

        // Load products into filter dropdown
        function loadProducts() {
            fetch(`${window.location.origin}/maizemarket/backend/get_products.php`)
                .then(response => response.json())
                .then(data => {
                    let productDropdown = document.getElementById("filterProduct");
                    data.products.forEach(product => {
                        productDropdown.innerHTML += `<option value="${product.id}">${product.name}</option>`;
                    });
                });
        }

        // Handle filter form submission
        document.getElementById("filterForm").addEventListener("submit", function (e) {
            e.preventDefault();
            const filterName = document.getElementById("filterName").value.trim();
            const filterProduct = document.getElementById("filterProduct").value;
            const filterCategory = document.getElementById("filterCategory").value;
            const filterPriceMin = document.getElementById("filterPriceMin").value;
            const filterPriceMax = document.getElementById("filterPriceMax").value;

            loadProductListings(null, null, { filterName, filterProduct, filterCategory, filterPriceMin, filterPriceMax });
        });

        function loadProductListings(userId, roleId, filters = {}) {
            let url = `${window.location.origin}/maizemarket/backend/home_backend.php`;

            let params = new URLSearchParams();
            if (filters.filterName) params.append("filterName", filters.filterName);
            if (filters.filterProduct) params.append("product_id", filters.filterProduct);
            if (filters.filterCategory) params.append("category_id", filters.filterCategory);
            if (filters.filterPriceMin) params.append("min_price", filters.filterPriceMin);
            if (filters.filterPriceMax) params.append("max_price", filters.filterPriceMax);

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
                    // console.log("Product Listings:", JSON.stringify(data));
                    if (data.length > 0) {
                        data.forEach(row => {
                            listingsContainer.innerHTML += `
                            <div class="col-md-4 mb-3">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">${row.product_name}</h5>
                                        <p><strong>Quantity:</strong> ${row.quantity} ${row.unit_name}</p>
                                        <p><strong>Price:</strong> Ksh ${parseFloat(row.price_per_quantity).toFixed(2)} per ${row.unit_name}</p>
                                        <button class="btn ${row.status_id === 1 ? 'btn-primary' : 'btn-secondary'} btn-sm" 
                                                onclick="${row.status_id === 1 ? `openBuyModal(${row.id}, '${row.seller_id}', '${row.product_name}', '${row.price_per_quantity}', '${row.unit_name}')` : ''}" 
                                                ${row.status_id !== 1 ? 'disabled' : ''}>
                                            ${row.status_id === 1 ? 'Buy Now' : row.status_name}
                                        </button>
                                        <button class="btn btn-info btn-sm mt-2" onclick="openSellerModal('${row.user_name}', '${row.user_email}', '${row.user_phone}')">
                                            <i class="fas fa-eye"></i>
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

        function openBuyModal(id, sellerId, productName, price, unit) {
            document.getElementById("buyProductName").innerText = productName;
            document.getElementById("buyProductPrice").innerText = price;
            document.getElementById("buyProductUnit").innerText = unit;
            document.getElementById("mpesaCode").value = ""; // Clear previous input
            console.log("Clicked Buy Now for Listing ID:", id, "Seller ID:", sellerId); // Debugging log
            document.getElementById("mpesaCode").setAttribute("data-listing-id", id); // Store listing ID
            document.getElementById("mpesaCode").setAttribute("data-seller-id", sellerId); // Store seller ID

            let buyModal = new bootstrap.Modal(document.getElementById("buyModal"));
            buyModal.show();
        }

        // Update the confirmPurchase function
        function confirmPurchase() {
            const mpesaCode = document.getElementById("mpesaCode").value.trim();
            const quantity = parseInt(document.getElementById("quantityAmount").value);
            const listingId = document.getElementById("mpesaCode").getAttribute("data-listing-id");
        
            if (!mpesaCode) {
                Swal.fire("Error", "Please enter an Mpesa code!", "error");
                return;
            }
            if (!quantity || quantity < 1) {
                Swal.fire("Error", "Please enter a valid quantity!", "error");
                return;
            }
        
            const user = localStorage.getItem("user");
            const userData = JSON.parse(user);
        
            const payload = {
                listing_id: parseInt(listingId),
                buyer_id: userData.id,
                mpesa_code: mpesaCode,
                quantity: quantity
            };
        
            console.log("Sending payload:", payload);
        
            fetch(`${window.location.origin}/maizemarket/backend/process_purchase.php`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200) {
                        Swal.fire("Success", "Purchase successful!", "success").then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire("Error", data.message, "error");
                    }
                })
                .catch(error => {
                    console.error("Error processing purchase:", error);
                    Swal.fire("Error", "Something went wrong!", "error");
                });
        }

        function buyProduct(id) {
            alert("Redirecting to purchase page for product ID: " + id);
            // Implement the purchase functionality
        }

        function openSellerModal(name, email, phone) {
            document.getElementById("sellerName").innerText = name;
            document.getElementById("sellerEmail").innerText = email;
            document.getElementById("sellerPhone").innerText = phone;

            let sellerModal = new bootstrap.Modal(document.getElementById("sellerModal"));
            sellerModal.show();
        }

        function logout() {
            localStorage.removeItem("user");
            window.location.href = "logout.php";
        }

    </script>

    <?php include 'footer.php'; ?>
</body>

</html>