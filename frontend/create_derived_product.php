<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Derived Product</title>
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
        .material-item {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
        }
        .remove-material {
            cursor: pointer;
            color: #dc3545;
        }
    </style>
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-success">
        <div class="container">
            <div class="d-flex align-items-center">
                <a class="btn btn-outline-light me-3" href="dashboard.php">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <a class="navbar-brand" href="#" id="welcomeMessage">Loading...</a>
            </div>
            <div class="d-flex align-items-end">
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        Menu
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                        <li><a class="dropdown-item" href="sales.php">My Sales</a></li>
                        <li><a class="dropdown-item" href="purchases.php">My Purchases</a></li>
                        <li id="buyMenuItem"><a class="dropdown-item" href="home.php">Buy</a></li>
                        <li><a class="dropdown-item" onclick="logout()">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4">Create Derived Product</h2>
        <p class="text-muted mb-4">Create a new product derived from materials you've purchased.</p>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <form id="derivedProductForm">
                            <div class="mb-3">
                                <label for="productName" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="productName" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" rows="3" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="processingMethod" class="form-label">Processing Method</label>
                                <textarea class="form-control" id="processingMethod" rows="3" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" required>
                                    <option value="">Select Category</option>
                                    <!-- Categories will be loaded dynamically -->
                                </select>
                            </div>

                            <h4 class="mt-4 mb-3">Source Materials</h4>
                            <div id="materialsList">
                                <!-- Materials will be added here dynamically -->
                            </div>

                            <button type="button" class="btn btn-outline-primary mt-3" id="addMaterialBtn">
                                <i class="fas fa-plus"></i> Add Material
                            </button>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-success">Create Product</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Available Materials</h5>
                    </div>
                    <div class="card-body">
                        <div id="availableMaterials">
                            <p class="text-center">Loading your materials...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Material Modal -->
    <div class="modal fade" id="addMaterialModal" tabindex="-1" aria-labelledby="addMaterialModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMaterialModalLabel">Add Source Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="materialSelect" class="form-label">Select Material</label>
                        <select class="form-select" id="materialSelect">
                            <option value="">Select a material</option>
                            <!-- Materials will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantityUsed" class="form-label">Quantity Used</label>
                        <input type="number" class="form-control" id="quantityUsed" min="0.01" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label for="quantityUnit" class="form-label">Unit</label>
                        <select class="form-select" id="quantityUnit" disabled>
                            <!-- Units will be set based on the selected material -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <p class="mb-1">Available: <span id="availableQuantity">0</span> <span id="unitName"></span></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmAddMaterial">Add Material</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let userId = null;
        let availableMaterials = [];
        let selectedMaterials = [];
        let materialCounter = 0;

        // Load user data and check if they are a wholesaler
        async function loadUser() {
            const user = localStorage.getItem('user');
            if (user) {
                const userData = JSON.parse(user);
                userId = userData.id;
                
                // Check if user is a wholesaler (role_id 2)
                if (userData.role_id !== 2) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Access Denied',
                        text: 'Only wholesalers can create derived products.',
                        confirmButtonText: 'Go to Dashboard'
                    }).then(() => {
                        window.location.href = 'dashboard.php';
                    });
                    return;
                }
                
                let entity_name = userData.entity_name ?? userData.name;
                document.getElementById("welcomeMessage").innerText = `Welcome, ${entity_name} (${userData.role})`;
                
                // Load available materials
                loadAvailableMaterials();
                
                // Load categories
                loadCategories();
            } else {
                window.location.href = 'login.php';
            }
        }

        // Load available materials from purchases
        async function loadAvailableMaterials() {
            try {
                const response = await fetch(`${window.location.origin}/maizemarket/backend/get_user_materials.php?user_id=${userId}`);
                const data = await response.json();
                
                if (data.status === 200) {
                    availableMaterials = data.materials;
                    displayAvailableMaterials();
                    populateMaterialSelect();
                } else {
                    document.getElementById('availableMaterials').innerHTML = 
                        `<p class="text-danger">Error: ${data.message}</p>`;
                }
            } catch (error) {
                console.error('Error loading materials:', error);
                document.getElementById('availableMaterials').innerHTML = 
                    `<p class="text-danger">Failed to load materials. Please try again.</p>`;
            }
        }

        // Display available materials in the sidebar
        function displayAvailableMaterials() {
            const container = document.getElementById('availableMaterials');
            
            if (availableMaterials.length === 0) {
                container.innerHTML = `<p class="text-center">You don't have any materials available. Please purchase some first.</p>`;
                return;
            }
            
            let html = '';
            availableMaterials.forEach(material => {
                html += `
                <div class="mb-3">
                    <h6>${material.product_name}</h6>
                    <p class="mb-1">Available: ${material.available_quantity} ${material.unit_name}</p>
                    <small class="text-muted">${material.product_description || 'No description'}</small>
                </div>
                <hr>`;
            });
            
            container.innerHTML = html;
        }

        // Populate the material select dropdown in the add material modal
        function populateMaterialSelect() {
            const select = document.getElementById('materialSelect');
            select.innerHTML = '<option value="">Select a material</option>';
            
            availableMaterials.forEach(material => {
                if (material.available_quantity > 0) {
                    select.innerHTML += `<option value="${material.product_id}" 
                        data-quantity="${material.available_quantity}"
                        data-unit="${material.unit_name}"
                        data-unit-id="${material.quantity_type_id}">
                        ${material.product_name} (${material.available_quantity} ${material.unit_name} available)
                    </option>`;
                }
            });
        }

        // Load categories for the dropdown
        async function loadCategories() {
            try {
                const response = await fetch(`${window.location.origin}/maizemarket/backend/get_categories.php`);
                const data = await response.json();
                
                const categorySelect = document.getElementById('category');
                
                if (data.status === 200) {
                    categorySelect.innerHTML = '<option value="">Select Category</option>';
                    data.categories.forEach(category => {
                        categorySelect.innerHTML += `<option value="${category.id}">${category.name}</option>`;
                    });
                } else {
                    categorySelect.innerHTML = '<option value="">Error loading categories</option>';
                }
            } catch (error) {
                console.error('Error loading categories:', error);
                document.getElementById('category').innerHTML = 
                    '<option value="">Failed to load categories</option>';
            }
        }

        // Event listener for material selection
        document.getElementById('materialSelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const quantityUnitSelect = document.getElementById('quantityUnit');
            
            if (this.value) {
                const availableQty = selectedOption.getAttribute('data-quantity');
                const unitName = selectedOption.getAttribute('data-unit');
                const unitId = selectedOption.getAttribute('data-unit-id');
                
                document.getElementById('availableQuantity').textContent = availableQty;
                document.getElementById('unitName').textContent = unitName;
                
                // Set the unit in the dropdown
                quantityUnitSelect.innerHTML = `<option value="${unitId}">${unitName}</option>`;
                
                // Set max value for quantity input
                document.getElementById('quantityUsed').max = availableQty;
            } else {
                document.getElementById('availableQuantity').textContent = '0';
                document.getElementById('unitName').textContent = '';
                quantityUnitSelect.innerHTML = '';
            }
        });

        // Open the add material modal
        document.getElementById('addMaterialBtn').addEventListener('click', function() {
            // Reset the form
            document.getElementById('materialSelect').value = '';
            document.getElementById('quantityUsed').value = '';
            document.getElementById('quantityUnit').innerHTML = '';
            document.getElementById('availableQuantity').textContent = '0';
            document.getElementById('unitName').textContent = '';
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('addMaterialModal'));
            modal.show();
        });

        // Add material to the list
        document.getElementById('confirmAddMaterial').addEventListener('click', function() {
            const materialSelect = document.getElementById('materialSelect');
            const quantityUsed = document.getElementById('quantityUsed');
            const quantityUnit = document.getElementById('quantityUnit');
            
            if (!materialSelect.value || !quantityUsed.value || parseFloat(quantityUsed.value) <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Input',
                    text: 'Please select a material and enter a valid quantity.'
                });
                return;
            }
            
            const selectedOption = materialSelect.options[materialSelect.selectedIndex];
            const materialId = materialSelect.value;
            const materialName = selectedOption.text.split(' (')[0];
            const quantity = parseFloat(quantityUsed.value);
            const unitId = quantityUnit.value;
            const unitName = selectedOption.getAttribute('data-unit');
            const availableQty = parseFloat(selectedOption.getAttribute('data-quantity'));
            
            if (quantity > availableQty) {
                Swal.fire({
                    icon: 'error',
                    title: 'Not Enough Material',
                    text: `You only have ${availableQty} ${unitName} available.`
                });
                return;
            }
            
            // Add to selected materials array
            const materialItem = {
                id: materialCounter++,
                source_product_id: materialId,
                product_name: materialName,
                quantity_used: quantity,
                quantity_type_id: unitId,
                unit_name: unitName
            };
            
            selectedMaterials.push(materialItem);
            
            // Update the UI
            updateMaterialsList();
            
            // Close the modal
            bootstrap.Modal.getInstance(document.getElementById('addMaterialModal')).hide();
        });

        // Update the materials list in the UI
        function updateMaterialsList() {
            const container = document.getElementById('materialsList');
            
            if (selectedMaterials.length === 0) {
                container.innerHTML = '<p class="text-muted">No materials added yet. Click "Add Material" to add source materials.</p>';
                return;
            }
            
            let html = '';
            selectedMaterials.forEach(material => {
                html += `
                <div class="material-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>${material.product_name}</h6>
                        <i class="fas fa-times remove-material" onclick="removeMaterial(${material.id})"></i>
                    </div>
                    <p class="mb-0">Quantity: ${material.quantity_used} ${material.unit_name}</p>
                </div>`;
            });
            
            container.innerHTML = html;
        }

        // Remove a material from the list
        function removeMaterial(id) {
            selectedMaterials = selectedMaterials.filter(material => material.id !== id);
            updateMaterialsList();
        }

        // Submit the form to create a derived product
        document.getElementById('derivedProductForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (selectedMaterials.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'No Materials',
                    text: 'Please add at least one source material.'
                });
                return;
            }
            
            const productName = document.getElementById('productName').value;
            const description = document.getElementById('description').value;
            const processingMethod = document.getElementById('processingMethod').value;
            const categoryId = document.getElementById('category').value;
            
            // Prepare the data
            const data = {
                wholesaler_id: userId,
                product_name: productName,
                description: description,
                processing_method: processingMethod,
                category_id: categoryId,
                materials: selectedMaterials.map(material => ({
                    source_product_id: material.source_product_id,
                    quantity_used: material.quantity_used,
                    quantity_type_id: material.quantity_type_id
                }))
            };
            
            try {
                // Show loading
                Swal.fire({
                    title: 'Creating Product',
                    text: 'Please wait...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                const response = await fetch(`${window.location.origin}/maizemarket/backend/create_derived_product.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Your derived product has been created successfully.',
                        confirmButtonText: 'Go to Dashboard'
                    }).then(() => {
                        window.location.href = 'dashboard.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message
                    });
                }
            } catch (error) {
                console.error('Error creating product:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to create product. Please try again.'
                });
            }
        });

        function logout() {
            localStorage.removeItem('user');
            window.location.href = 'login.php';
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            loadUser();
            updateMaterialsList();
        });
    </script>
</body>
</html>
