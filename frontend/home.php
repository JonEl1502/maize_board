

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-commerce Marketplace</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Include SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .product-card {
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .product-img {
            height: 180px;
            object-fit: cover;
        }
        .category-filter {
            cursor: pointer;
            transition: all 0.3s;
            border-radius: 20px;
            padding: 8px 15px;
        }
        .category-filter:hover, .category-filter.active {
            background-color: #198754;
            color: white;
        }
    </style>
</head>

<body>

    <div class="bg-light">
        <nav class="navbar navbar-dark " style="background-color:rgb(49, 92, 59);">
            <div class="container">
                <a class="navbar-brand" href="#" id="welcomeMessage">Loading...</a>

            <div class="d-flex align-items-end">
                <button class="btn btn-outline-light me-4" onclick="openCartModal()"><i class="fas fa-shopping-cart"></i> Cart <span class="badge bg-light text-dark" id="cartCount">0</span></button>
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        Menu
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li id="menuItem"> <a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                    <li><a class="dropdown-item" href="purchases.php">My Purchases</a></li>
                    <li><a class="dropdown-item" onclick="logout()">Logout</a></li>
                    </ul>
                </div>
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

    <script>
        // Cart Management
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        function updateCartCount() {
            document.getElementById('cartCount').textContent = cart.length;
        }

        function addToCart(productId, productName, price, unit) {
            const quantity = 1; // Default quantity
            const item = {
                id: productId,
                name: productName,
                price: price,
                unit: unit,
                quantity: quantity,
                total: price * quantity
            };
            cart.push(item);
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            Swal.fire({
                icon: 'success',
                title: 'Added to Cart!',
                text: `${productName} has been added to your cart.`,
                timer: 1500,
                showConfirmButton: false
            });
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            displayCart();
        }

        function updateQuantity(index, newQuantity) {
            if (newQuantity > 0) {
                cart[index].quantity = newQuantity;
                cart[index].total = cart[index].price * newQuantity;
                localStorage.setItem('cart', JSON.stringify(cart));
                displayCart();
            }
        }

        function displayCart() {
            const cartItems = document.getElementById('cartItems');
            const cartTotal = document.getElementById('cartTotal');
            let total = 0;

            if (cart.length === 0) {
                cartItems.innerHTML = '<p class="text-center">Your cart is empty</p>';
                cartTotal.textContent = '0.00';
                return;
            }

            let html = '<div class="table-responsive"><table class="table"><thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th>Action</th></tr></thead><tbody>';

            cart.forEach((item, index) => {
                total += item.total;
                html += `
                    <tr>
                        <td>${item.name}</td>
                        <td>Ksh ${item.price.toFixed(2)} per ${item.unit}</td>
                        <td>
                            <input type="number" min="1" value="${item.quantity}"
                                class="form-control form-control-sm w-75"
                                onchange="updateQuantity(${index}, this.value)">
                        </td>
                        <td>Ksh ${item.total.toFixed(2)}</td>
                        <td>
                            <button class="btn btn-danger btn-sm" onclick="removeFromCart(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;
            });

            html += '</tbody></table></div>';
            cartItems.innerHTML = html;
            cartTotal.textContent = total.toFixed(2);
        }

        function openCartModal() {
            displayCart();
            new bootstrap.Modal(document.getElementById('cartModal')).show();
        }

        function proceedToCheckout() {
            if (cart.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Empty Cart',
                    text: 'Please add items to your cart before checking out.'
                });
                return;
            }

            // Implement checkout logic here
            Swal.fire({
                icon: 'info',
                title: 'Proceeding to Checkout',
                text: 'This feature will be implemented soon!'
            });
        }

        // Initialize cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });

        function loadProductListings(userId, roleId, filters = {}) {
            let url = `${window.location.origin}/maizemarket/backend/product_listings.php`;
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
                    listingsContainer.innerHTML = "";
                    let data = response.data;
                    if (data.length > 0) {
                        data.forEach(row => {
                            listingsContainer.innerHTML += `
                            <div class="col-md-4 mb-3">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">${row.product_name}</h5>
                                        <p class="card-text">${row.description}</p>
                                        <p><strong>Quantity:</strong> ${row.quantity} ${row.unit_name}</p>
                                        <p><strong>Sold:</strong> ${row.sold_quantity} ${row.unit_name}</p>
                                        <p><strong>Remaining:</strong> ${row.remaining_quantity} ${row.unit_name}</p>
                                        <p><strong>Price:</strong> Ksh ${parseFloat(row.price_per_quantity).toFixed(2)} per ${row.unit_name}</p>
                                        <button class="btn btn-primary btn-sm" onclick="addToCart(${row.id}, '${row.product_name}', ${row.price_per_quantity}, '${row.unit_name}')">
                                            <i class="fas fa-cart-plus"></i> Add to Cart
                                        </button>
                                        <button class="btn btn-info btn-sm mt-2" onclick="openSellerModal('${row.user_name}', '${row.user_email}', '${row.user_phone}')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
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
                    listingsContainer.innerHTML = '<p class="text-center">Error loading products.</p>';
                });
        }
    </script>
</body>

<!-- Seller Details Modal -->
<div class="modal fade" id="sellerModal" tabindex="-1" aria-labelledby="sellerModalLabel">
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

<!-- Shopping Cart Modal -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cartModalLabel">Shopping Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="cartItems"></div>
                <div class="text-end mt-3">
                    <h5>Total: Ksh <span id="cartTotal">0.00</span></h5>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="proceedToCheckout()">Proceed to Checkout</button>
            </div>
        </div>
    </div>
</div>

<!-- Buy Confirmation Modal -->
<div class="modal fade" id="buyModal" tabindex="-1" aria-labelledby="buyModalLabel">
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
    // Initialize cart in localStorage if it doesn't exist
    if (!localStorage.getItem('cart')) {
        localStorage.setItem('cart', JSON.stringify([]));
    }

    document.addEventListener("DOMContentLoaded", async function () {
        await loadUser();
        loadCategories();
        loadProducts(); // Load products for filtering
        updateCartCount(); // Update cart count on page load
    });

    // Function to validate user exists in the database
    async function validateUser(userId) {
        try {
            const response = await fetch(`${window.location.origin}/maizemarket/backend/validate_user.php?user_id=${userId}`);
            const data = await response.json();
            return data.status === 200;
        } catch (error) {
            console.error("Error validating user:", error);
            return false;
        }
    }

    async function loadUser() {
        const user = localStorage.getItem("user");
        console.log("Logged in :", user);
        if (user) {
            const userData = JSON.parse(user);

            // Validate that the user exists in the database
            const isValid = await validateUser(userData.id);
            if (!isValid) {
                // User doesn't exist in the database, clear localStorage and redirect to login
                localStorage.removeItem("user");
                Swal.fire({
                    icon: "error",
                    title: "Invalid User",
                    text: "Your user account could not be found. Please log in again.",
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "login.php";
                });
                return;
            }

            document.getElementById("welcomeMessage").innerText = `Welcome, ${userData.entity_name}  (${userData.role})`;

            console.log(`Logged IDD  ed:${userData.name} ${userData.role_id}`);
            if(userData.role_id >= 3){
                document.getElementById("welcomeMessage").innerText = `Welcome, ${userData.name}  (${userData.role})`;
            }

            if (userData.role_id >= 4) {
                    document.getElementById("menuItem").style.display = "none";
            }

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

    function addToCart(productId, productName, price, unit) {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        const existingItem = cart.find(item => item.productId === productId);

        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                productId: productId,
                productName: productName,
                price: price,
                unit: unit,
                quantity: 1
            });
        }

        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        Swal.fire({
            icon: 'success',
            title: 'Added to Cart!',
            text: 'Item has been added to your cart.',
            showConfirmButton: false,
            timer: 1500
        });
    }

    function updateCartCount() {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
        document.getElementById('cartCount').textContent = totalItems;
    }

    function openCartModal() {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        const cartItemsContainer = document.getElementById('cartItems');
        let total = 0;

        if (cart.length === 0) {
            cartItemsContainer.innerHTML = '<p class="text-center">Your cart is empty</p>';
            document.getElementById('cartTotal').textContent = '0.00';
        } else {
            let html = '<div class="table-responsive"><table class="table"><thead><tr><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th><th>Actions</th></tr></thead><tbody>';

            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                html += `
                    <tr>
                        <td>${item.productName}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary" onclick="updateCartItemQuantity(${index}, -1)">-</button>
                            <span class="mx-2">${item.quantity}</span>
                            <button class="btn btn-sm btn-outline-secondary" onclick="updateCartItemQuantity(${index}, 1)">+</button>
                        </td>
                        <td>Ksh ${item.price} per ${item.unit}</td>
                        <td>Ksh ${itemTotal.toFixed(2)}</td>
                        <td>
                            <button class="btn btn-sm btn-danger" onclick="removeFromCart(${index})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table></div>';
            cartItemsContainer.innerHTML = html;
            document.getElementById('cartTotal').textContent = total.toFixed(2);
        }

        new bootstrap.Modal(document.getElementById('cartModal')).show();
    }

    function updateCartItemQuantity(index, change) {
        const cart = JSON.parse(localStorage.getItem('cart'));
        cart[index].quantity = Math.max(1, cart[index].quantity + change);
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        openCartModal(); // Refresh cart modal
    }

    function removeFromCart(index) {
        const cart = JSON.parse(localStorage.getItem('cart'));
        cart.splice(index, 1);
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        openCartModal(); // Refresh cart modal
    }

    function proceedToCheckout() {
        const cart = JSON.parse(localStorage.getItem('cart'));
        if (cart.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Empty Cart',
                text: 'Please add items to your cart before checking out.'
            });
            return;
        }

        // Close cart modal
        bootstrap.Modal.getInstance(document.getElementById('cartModal')).hide();

        // Update the buy modal to show cart summary
        const buyModalBody = document.querySelector('#buyModal .modal-body');
        let cartSummaryHTML = '<h5>Cart Summary</h5><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th></tr></thead><tbody>';

        let grandTotal = 0;
        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            grandTotal += itemTotal;
            cartSummaryHTML += `
                <tr>
                    <td>${item.productName}</td>
                    <td>${item.quantity}</td>
                    <td>Ksh ${item.price} per ${item.unit}</td>
                    <td>Ksh ${itemTotal.toFixed(2)}</td>
                </tr>
            `;
        });

        cartSummaryHTML += `</tbody></table></div>
        <div class="text-end mb-3">
            <h5>Grand Total: Ksh ${grandTotal.toFixed(2)}</h5>
        </div>
        <div class="mb-3">
            <label for="mpesaCode" class="form-label">Enter Mpesa Code</label>
            <input type="text" class="form-control" id="mpesaCode" placeholder="e.g., MPESA123456">
        </div>`;

        buyModalBody.innerHTML = cartSummaryHTML;

        // Update the modal footer button to call the new function
        const confirmButton = document.querySelector('#buyModal .modal-footer .btn-success');
        confirmButton.setAttribute('onclick', 'confirmCartPurchase()');

        // Show the modal
        new bootstrap.Modal(document.getElementById('buyModal')).show();
    }

    function loadProductListings(userId, roleId, filters = {}) {
        let url = `${window.location.origin}/maizemarket/backend/product_listings.php`;

        let params = new URLSearchParams();
        if (filters.filterName) params.append("filterName", filters.filterName);
        if (filters.filterProduct) params.append("product_id", filters.filterProduct);
        if (filters.filterCategory) params.append("category_id", filters.filterCategory);
        if (filters.filterPriceMin) params.append("min_price", filters.filterPriceMin);
        if (filters.filterPriceMax) params.append("max_price", filters.filterPriceMax);

        if (userId) {
            params.append("user_id", userId);
            // Add buyer_id parameter to prevent users from seeing their own products
            params.append("buyer_id", userId);
        }

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
                        // Check if this is a derived product and has source materials
                        const isDerived = row.is_derived === 1;
                        let sourceMaterialsHtml = '';

                        if (isDerived && row.source_materials) {
                            try {
                                const materials = JSON.parse(row.source_materials);
                                if (materials && materials.length > 0) {
                                    sourceMaterialsHtml = `
                                    <div class="mt-2">
                                        <p class="mb-1"><strong>Derived Product</strong></p>
                                        <p class="mb-1"><small>Source Materials:</small></p>
                                        <ul class="small">`;

                                    materials.forEach(material => {
                                        sourceMaterialsHtml += `
                                            <li>${material.source_product_name} (${material.quantity_used} ${material.unit_name})</li>`;
                                    });

                                    sourceMaterialsHtml += `</ul>
                                    </div>`;
                                }
                            } catch (e) {
                                console.error("Error parsing source materials:", e);
                            }
                        }

                        listingsContainer.innerHTML += `
                        <div class="col-md-4 mb-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">${row.product_name} ${isDerived ? '<span class="badge bg-info">Derived</span>' : ''}</h5>
                                    <p class="small text-muted">${row.product_description || 'No description available'}</p>
                                    <p><strong>Quantity:</strong> ${row.quantity} ${row.unit_name}</p>
                                    <p><strong>Price:</strong> Ksh ${parseFloat(row.price_per_quantity).toFixed(2)} per ${row.unit_name}</p>
                                    ${sourceMaterialsHtml}
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <button class="btn btn-success btn-sm" onclick="addToCart(${row.id}, '${row.product_name}', ${row.price_per_quantity}, '${row.unit_name}')">
                                            <i class="fas fa-cart-plus"></i> Add to Cart
                                        </button>
                                        <button class="btn btn-info btn-sm" onclick="openSellerModal('${row.user_name}', '${row.user_email}', '${row.user_phone}')">
                                            <i class="fas fa-eye"></i> Seller
                                        </button>
                                    </div>
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

    // Function to handle single product purchase
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

    // Function to handle cart purchase
    function confirmCartPurchase() {
        const mpesaCode = document.getElementById("mpesaCode").value.trim();

        if (!mpesaCode) {
            Swal.fire({
                icon: "error",
                title: "Missing Information",
                text: "Please enter an Mpesa code!"
            });
            return;
        }

        const cart = JSON.parse(localStorage.getItem('cart'));
        if (cart.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "Empty Cart",
                text: "Your cart is empty!"
            });
            return;
        }

        const user = localStorage.getItem("user");
        if (!user) {
            Swal.fire({
                icon: "warning",
                title: "Session Expired",
                text: "Please log in again."
            }).then(() => {
                window.location.href = "login.php";
            });
            return;
        }

        const userData = JSON.parse(user);

        const payload = {
            cart_items: cart,
            buyer_id: userData.id,
            mpesa_code: mpesaCode
        };

        Swal.fire({
            title: 'Processing Purchase',
            text: 'Please wait while we process your order...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`${window.location.origin}/maizemarket/backend/process_cart_purchase.php`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 200) {
                // Clear the cart after successful purchase
                localStorage.setItem('cart', JSON.stringify([]));
                updateCartCount();

                let message = "Your purchase was successful!";
                if (data.failed && data.failed.length > 0) {
                    message ;
                }

                Swal.fire({
                    icon: "success",
                    title: "Purchase Complete",
                    text: message,
                    confirmButtonText: "OK"
                }).then(() => {
                    // Close the modal and reload the page
                    bootstrap.Modal.getInstance(document.getElementById('buyModal')).hide();
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Purchase Failed",
                    text: data.message || "An error occurred while processing your purchase."
                });
            }
        })
        .catch(error => {
            console.error("Error processing cart purchase:", error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Something went wrong while processing your purchase. Please try again later."
            });
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


</body>

</html>
