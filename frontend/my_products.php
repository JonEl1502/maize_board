

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
        <div class="container mt-5">
            <h3 class="mb-4" id="title_name">Available Product Listings</h3>
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#listingModal">+ Add Product
                Listing</button>
            <div class="row" id="productListings">
                <p>Loading product listings...</p>
            </div>
        </div>
    </div>
    <!-- Add/Edit Listing Modal -->
    <div class="modal fade" id="listingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="listingForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Add Product Listing</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="listing_id" id="listingId">
                        <div class="mb-3">
                            <label>Product</label>
                            <select class="form-select" name="product_id" id="product_id" required>
                                <option value="">Loading...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Quantity</label>
                            <input type="number" class="form-control" name="quantity" id="quantity" required>
                        </div>
                        <div class="mb-3">
                            <label>Quantity Unit</label>
                            <select class="form-select" name="quantity_type_id" id="quantity_unit_id" required>
                                <option value="">Loading...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Price per Unit (Ksh)</label>
                            <input type="number" class="form-control" name="price_per_quantity" id="price_per_quantity"
                                required>
                        </div>
                        <div class="mb-3">
                            <label>Product Image (Optional)</label>
                            <input type="file" class="form-control" name="product_image" id="product_image">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save Listing</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS & Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            loadUser();
            loadDropdowns();
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

        function loadProductListings(userId, roleId) {
            let url = `${window.location.origin}/maizemarket/backend/my_products_backend.php?user_id=${userId}`;

            fetch(url)
                .then(response => response.json())
                .then(response => {
                    let listingsContainer = document.getElementById("productListings");
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
                                        <p><strong>Quantity:</strong> ${row.quantity} ${row.unit_name}</p>
                                        <p><strong>Price:</strong> Ksh ${parseFloat(row.price_per_quantity).toFixed(2)} per ${row.unit_name}</p>
                           <p><strong>Seller:</strong> ${row.user_name} (${row.user_email}, ${row.user_phone})</p>
                                        <button class="btn btn-primary btn-sm" onclick="editListing(${row.id}, '${row.product_id}', '${row.quantity}', '${row.quantity_unit_id}', '${row.price_per_quantity}', '${row.product_image_url}')">
                                            Edit
                                        </button>
                                    </div>
                                </div>
                            </div>`;
                        });
                    } else {
                        listingsContainer.innerHTML = '<p class="text-center">No product listings available.</p>';
                    }
                })
                .catch(error => {
                    console.error("Error fetching product listings:", error);
                    document.getElementById("productListings").innerHTML = '<p class="text-center">Error loading products.</p>';
                });
        }

        function loadDropdowns() {
            fetch(`${window.location.origin}/maizemarket/backend/get_products.php`)
                .then(response => response.json())
                .then(data => {
                    let productDropdown = document.getElementById("product_id");
                    productDropdown.innerHTML = '<option value="">Select Product</option>';
                    let data1 = data.products;
                    data1.forEach(product => {
                        productDropdown.innerHTML += `<option value="${product.id}">${product.name}</option>`;
                    });
                });

            fetch(`${window.location.origin}/maizemarket/backend/quantity_units.php`)
                .then(response => response.json())
                .then(data => {
                    let unitDropdown = document.getElementById("quantity_unit_id");
                    unitDropdown.innerHTML = '<option value="">Select Quantity Unit</option>';
                    let data1 = data.quantity_units;
                    data1.forEach(unit => {
                        unitDropdown.innerHTML += `<option value="${unit.id}">${unit.unit_name}</option>`;
                    });
                });
        }

        function editListing(id, product_id, quantity, quantity_unit_id, price_per_quantity, image_url) {
            document.getElementById("listingId").value = id;
            document.getElementById("quantity").value = quantity;
            document.getElementById("price_per_quantity").value = price_per_quantity;

            // Populate dropdowns with correct selections
            populateDropdowns(product_id, quantity_unit_id);

            // Set modal title for editing
            document.getElementById("modalTitle").innerText = "Edit Product Listing";
            let modal = new bootstrap.Modal(document.getElementById("listingModal"));
            modal.show();
        }

        function populateDropdowns(selectedProductId, selectedUnitId) {
            // Populate Product Dropdown
            fetch(`${window.location.origin}/maizemarket/backend/get_products.php`)
                .then(response => response.json())
                .then(data => {
                    let productDropdown = document.getElementById("product_id");
                    productDropdown.innerHTML = '<option value="">Select Product</option>';
                    data.products.forEach(product => {
                        const selected = product.id == selectedProductId ? "selected" : "";
                        productDropdown.innerHTML += `<option value="${product.id}" ${selected}>${product.name}</option>`;
                    });
                });

            // Populate Quantity Unit Dropdown
            fetch(`${window.location.origin}/maizemarket/backend/quantity_units.php`)
                .then(response => response.json())
                .then(data => {
                    let unitDropdown = document.getElementById("quantity_unit_id");
                    unitDropdown.innerHTML = '<option value="">Select Quantity Unit</option>';
                    data.quantity_units.forEach(unit => {
                        const selected = unit.id == selectedUnitId ? "selected" : "";
                        unitDropdown.innerHTML += `<option value="${unit.id}" ${selected}>${unit.unit_name}</option>`;
                    });
                });
        }

        document.getElementById("listingForm").addEventListener("submit", function (e) {
            e.preventDefault();
            let formData = new FormData(this);

            // Retrieve user info
            const user = localStorage.getItem("user");
            if (!user) {
                console.error("No user found in localStorage.");
                alert("Session expired. Please log in again.");
                return;
            }

            const userData = JSON.parse(user);
            const userId = userData.id || null;
            const listingId = document.getElementById("listingId").value || null;

            if (!userId) {
                console.error("User ID is missing.");
                alert("User ID is missing. Cannot proceed.");
                return;
            }

            // Add seller_id to the form data
            formData.append("user_id", userId);
            formData.append("seller_id", userId);

            if (listingId) {
                formData.append("listing_id", listingId);
            }

            // Log form data for debugging
            const formDataObj = {};
            for (let [key, value] of formData.entries()) {
                formDataObj[key] = value;
            }
            console.log("Form Data:", formDataObj);

            // Send the request
            fetch(`${window.location.origin}/maizemarket/backend/add_edit_listing.php`, {
                method: "POST",
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                try {
                    // Try to parse the response as JSON
                    const result = JSON.parse(text);
                    if (result.status === 200) {
                        alert("Listing Saved!");
                        loadProductListings(userId, userData.role_id);
                        document.getElementById("listingForm").reset();
                        document.querySelector("#listingModal .btn-close").click();
                    } else {
                        alert("Error: " + result.message);
                    }
                } catch (e) {
                    // If parsing fails, show the raw response
                    console.error("Failed to parse JSON response:", e);
                    console.error("Raw response:", text);
                    alert("Error: The server returned an invalid response. Check the console for details.");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Error: " + error.message);
            });
        });

        function logout() {
            localStorage.removeItem("user");
            window.location.href = "logout.php";
        }

    </script>

    <?php include 'footer.php'; ?>
</body>

</html>