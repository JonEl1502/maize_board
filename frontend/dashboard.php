

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .dropdown-menu {
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .dropdown-item {
            padding: 0.5rem 1rem;
            transition: background-color 0.2s;
        }
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        .dropdown-divider {
            margin: 0.5rem 0;
        }
    </style>
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-success">
        <div class="container">
            <div class="d-flex align-items-center">
                <a href="home.php" class="btn btn-outline-light me-3"><i class="fas fa-arrow-left"></i> Back to Home</a>
                <a class="navbar-brand" href="#" id="welcomeMessage">Loading...</a>
            </div>

            <div class="d-flex align-items-end">
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bars"></i> Menu
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                        <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="sales.php"><i class="fas fa-chart-line me-2"></i>My Sales</a></li>
                        <li><a class="dropdown-item" href="purchases.php"><i class="fas fa-shopping-bag me-2"></i>My Purchases</a></li>
                        <li><a class="dropdown-item" href="reports.php"><i class="fas fa-file-alt me-2"></i>Reports</a></li>
                        <li id="buyMenuItem"><a class="dropdown-item" href="home.php"><i class="fas fa-shopping-cart me-2"></i>Buy</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="logout()"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Your Product Listings</h3>
            <div>
                <button type="button" class="btn btn-success me-2" id="openModalBtn">+ Add Post</button>
                <button type="button" class="btn btn-primary" id="createDerivedBtn" style="display: none;">Create Derived Product</button>
            </div>
        </div>
        <div class="row" id="maizeListings">
            <!-- 🚀  Listings Load Here -->
        </div>
    </div>

    <!-- Add Post Modal -->
    <div class="modal fade" id="addPostModal" tabindex="-1" aria-labelledby="addPostModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addPostForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPostModalLabel">Add Product Listing</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="seller_id" id="farmerId">
                        <input type="hidden" name="status" value="pending">
                        <input type="hidden" name="approved_by" value="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Product</label>
                                <select class="form-select" name="product_id" id="product_id" required>
                                    <option value="">Loading...</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Quantity</label>
                                <input type="number" class="form-control" name="quantity" required>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <label>Quantity Unit</label>
                                <select class="form-select" name="quantity_type_id" id="quantity_type_id" required>
                                    <option value="">Loading...</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Price per unit (Ksh)</label>
                                <input type="number" class="form-control" name="price_per_quantity" required>
                            </div>
                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Add Listing</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Post Modal -->
    <div class="modal fade" id="editPostModal" tabindex="-1" aria-labelledby="editPostModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editPostForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPostModalLabel">Edit Product Listing</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editListingId">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Product</label>
                                <select class="form-select" name="product_id" id="editProductUnitId" required>
                                    <option value="">Loading...</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Quantity</label>
                                <input type="number" class="form-control" name="quantity" id="editQuantity" required>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-6">
                                <label>Quantity Unit</label>
                                <select class="form-select" name="quantity_type_id" id="editQuantityUnitId" required>
                                    <option value="">Loading...</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Price per unit (Kes)</label>
                                <input type="number" class="form-control" name="price_per_quantity" id="editPricePerUnit"
                                    required>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Update Listing</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Counties dropdown functionality removed as elements don't exist in the DOM
        // If you need to add county selection, add the HTML elements first

        document.addEventListener("DOMContentLoaded", async function() {
            let productSelect = document.getElementById("product_id");

            try {
                let response = await fetch(`${window.location.origin}/maizemarket/backend/get_products.php`);
                let data = await response.json();

                if (data.status === 200) {
                    productSelect.innerHTML = '<option value="">Select Product</option>';
                    data.products.forEach(product => {
                        productSelect.innerHTML += `<option value="${product.id}">${product.name}</option>`;
                    });
                } else {
                    productSelect.innerHTML = '<option value="">Error loading products</option>';
                }
            } catch (error) {
                productSelect.innerHTML = '<option value="">Error fetching products</option>';
            }
        });

        document.addEventListener("DOMContentLoaded", async function() {
            let farmerTypeSelect = document.getElementById("quantity_type_id");
            let editQuantityUnitSelect = document.getElementById("editQuantityUnitId");

            try {
                let response = await fetch(`${window.location.origin}/maizemarket/backend/quantity_units.php`);
                let data = await response.json();

                if (data.status === 200) {
                    farmerTypeSelect.innerHTML = '<option value="">Select Quantity Unit</option>';
                    editQuantityUnitSelect.innerHTML = '<option value="">Select Quantity Unit</option>';
                    data.quantity_units.forEach(type => {
                        farmerTypeSelect.innerHTML += `<option value="${type.id}">${type.unit_name}</option>`;
                        editQuantityUnitSelect.innerHTML += `<option value="${type.id}">${type.unit_name}</option>`;
                    });
                } else {
                    farmerTypeSelect.innerHTML = '<option value="">Error loading types</option>';
                    editQuantityUnitSelect.innerHTML = '<option value="">Error loading types</option>';
                }
            } catch (error) {
                farmerTypeSelect.innerHTML = '<option value="">Error fetching data</option>';
                editQuantityUnitSelect.innerHTML = '<option value="">Error fetching data</option>';
            }
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
            console.log("Logged in Farmer:", user);
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

                let entity_name = userData.entity_name??userData.name;
                document.getElementById("welcomeMessage").innerText = `Welcome, ${entity_name}  (${userData.role})`;
                document.getElementById("farmerId").value = userData.id;

                // Hide "Buy" menu item if user is role_id 2 (Farmer)
                if (userData.role_id === 2) {
                    document.getElementById("buyMenuItem").style.display = "none";
                    // Don't show "Create Derived Product" button for farmers
                    document.getElementById("createDerivedBtn").style.display = "none";
                }

                // Show "Create Derived Product" button only for wholesalers (role_id 3)
                if (userData.role_id === 3) {
                    document.getElementById("createDerivedBtn").style.display = "inline-block";
                }

                loadMaizeListings(userData.id);
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

        function loadMaizeListings(farmerId) {
            fetch(`${window.location.origin}/maizemarket/backend/fetch_f_maize_listings.php?user_id=${farmerId}`)
                .then(response => response.json())
                .then(data => {
                    console.log("API Response:", data);

                    if (!Array.isArray(data.data)) {
                        console.error("Unexpected API Response:", data);
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Failed to load listings. Try again!"
                        });
                        return;
                    }

                    const listingsContainer = document.getElementById("maizeListings");
                    listingsContainer.innerHTML = "";

                    if (data.data.length === 0) {
                        listingsContainer.innerHTML = `<p class="text-center">No product listings found.</p>`;
                        return;
                    }

                    data.data.forEach(product => {
                        // Check if this is a derived product and has source materials
                        const isDerived = product.is_derived === 1;
                        let sourceMaterialsHtml = '';

                        if (isDerived && product.source_materials) {
                            try {
                                const materials = JSON.parse(product.source_materials);
                                if (materials && materials.length > 0) {
                                    sourceMaterialsHtml = `
                                    <div class="mt-2 mb-3">
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
                        <div class="card p-3">
                            <h5>${product.product_name} ${isDerived ? '<span class="badge bg-info">Derived</span>' : ''}</h5>
                            <p class="small text-muted">${product.product_description || 'No description available'}</p>
                            <p><strong>Quantity:</strong> ${product.quantity} ${product.unit_name}</p>
                            <p><strong>Price:</strong> Ksh ${product.price_per_quantity} per ${product.unit_name}</p>
                            <p><strong>Status:</strong> ${product.status_name}</p>
                            <p><strong>Listed On:</strong> ${new Date(product.created_at).toLocaleDateString()}</p>
                            ${sourceMaterialsHtml}
                            ${product.product_image_url ? `<img src="${product.product_image_url}" class="img-fluid mb-2" alt="Product Image">` : ''}
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-primary"
                                    onclick='openEditModal(${JSON.stringify(product)})'>
                                    Update
                                </button>
                                <button class="btn btn-danger"
                                    onclick="deleteProduct(${product.id})">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>`;
                    });
                })
                .catch(error => {
                    console.error("Fetch Error:", error);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Failed to load product listings."
                    });
                });
        }

        // The delete function that handles the confirmation and deletion
        function deleteProduct(productId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`${window.location.origin}/maizemarket/backend/delete_post.php`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                id: productId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 200) {
                                Swal.fire('Deleted!', 'Your listing has been deleted.', 'success');
                                loadMaizeListings(JSON.parse(localStorage.getItem("user")).id);
                            } else {
                                Swal.fire('Error!', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Delete Error:', error);
                            Swal.fire('Error!', 'Failed to delete listing.', 'error');
                        });
                }
            });
        }

        // Load products and quantity units for edit modal
        document.addEventListener("DOMContentLoaded", async function() {
            let editProductSelect = document.getElementById("editProductUnitId");
            let editQuantityUnitSelect = document.getElementById("editQuantityUnitId");

            try {
                // Load products
                let productResponse = await fetch(`${window.location.origin}/maizemarket/backend/get_products.php`);
                let productData = await productResponse.json();

                if (productData.status === 200) {
                    editProductSelect.innerHTML = '<option value="">Select Product</option>';
                    productData.products.forEach(product => {
                        editProductSelect.innerHTML += `<option value="${product.id}">${product.name}</option>`;
                    });
                }

                // Load quantity units
                let quantityResponse = await fetch(`${window.location.origin}/maizemarket/backend/quantity_units.php`);
                let quantityData = await quantityResponse.json();

                if (quantityData.status === 200) {
                    editQuantityUnitSelect.innerHTML = '<option value="">Select Quantity Unit</option>';
                    quantityData.quantity_units.forEach(unit => {
                        editQuantityUnitSelect.innerHTML += `<option value="${unit.id}">${unit.unit_name}</option>`;
                    });
                }
            } catch (error) {
                console.error("Error loading data:", error);
            }
        });

        function openEditModal(product) {
            console.log("Opening modal with product:", product);

            // Set values for all fields
            document.getElementById("editListingId").value = product.id;
            document.getElementById("editQuantity").value = product.quantity;
            document.getElementById("editPricePerUnit").value = product.price_per_quantity;

            // Set selected values for dropdowns
            let productSelect = document.getElementById("editProductUnitId");
            let quantityUnitSelect = document.getElementById("editQuantityUnitId");

            // Set product dropdown
            if (productSelect.options.length > 0) {
                productSelect.value = product.product_id;
            }

            // Set quantity unit dropdown
            if (quantityUnitSelect.options.length > 0) {
                quantityUnitSelect.value = product.quantity_type_id;
            }

            // Show the modal
            let modal = new bootstrap.Modal(document.getElementById("editPostModal"));
            modal.show();
        }

        document.getElementById("editPostForm").addEventListener("submit", function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            console.log("Update Form Data:", Object.fromEntries(formData.entries()));

            fetch(`${window.location.origin}/maizemarket/backend/edit_post.php`, {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 200) {
                        Swal.fire({
                            icon: "success",
                            title: "Post Updated!",
                            text: result.message
                        });
                        loadMaizeListings(JSON.parse(localStorage.getItem("user")).id);
                        document.getElementById("editPostForm").reset();
                        document.querySelector("#editPostModal .btn-close").click();
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: result.message
                        });
                    }
                })
                .catch(error => {
                    console.error("Update Error:", error);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Failed to update listing"
                    });
                });
        });

        document.getElementById("addPostForm").addEventListener("submit", function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            console.log("Add Post Form Data:", Object.fromEntries(formData.entries()));
            fetch(`${window.location.origin}/maizemarket/backend/add_post.php`, {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 200) {
                        Swal.fire({
                            icon: "success",
                            title: "Post Added!",
                            text: result.message
                        });
                        loadMaizeListings(JSON.parse(localStorage.getItem("user")).id);
                        document.getElementById("addPostForm").reset();
                        document.querySelector("#addPostModal .btn-close").click();
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: result.message
                        });
                    }
                });
        });

        function logout() {
            localStorage.removeItem("user");
            window.location.href = "logout.php";
        }

        document.getElementById("openModalBtn").addEventListener("click", function() {
            let modal = new bootstrap.Modal(document.getElementById("addPostModal"));
            modal.show();
        });

        // Add event listener for Create Derived Product button
        document.getElementById("createDerivedBtn").addEventListener("click", function() {
            window.location.href = "create_derived_product.php";
        });

        window.onload = async function() {
            await loadUser();
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>