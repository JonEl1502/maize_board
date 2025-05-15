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
                    <li><a class="dropdown-item" href="purchases.php">My Purchases</a></li>
                    <li id="buyMenuItem"><a class="dropdown-item" href="home.php">Buy</a></li>
                    <li><a class="dropdown-item" onclick="logout()">Logout</a></li>
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

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusUpdateModal" tabindex="-1" aria-labelledby="statusUpdateModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusUpdateModalLabel">Update Sale Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Sale ID: <span id="updateTransactionId"></span></p>
                    <p>Current Status: <span id="currentStatus" class="badge bg-secondary"></span></p>
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">Select New Status:</label>
                        <select class="form-select" id="newStatus">
                            <option value="">Select a status...</option>
                            <!-- Status options will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="form-text text-muted">
                        Changing the status will update this sale across the system.
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
        let currentFilter = 'all';
        let statusOptions = [];
        let currentTransactionId = null;

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
                                            <p class="mb-1"><strong>Payment Status:</strong> ${sale.payment_status || 'Sold'}</p>
                                        </div>
                                        <div class="mt-3">
                                            <button class="btn btn-sm btn-primary w-100"
                                                    onclick="openStatusUpdateModal(${sale.id}, '${sale.status_name}', ${sale.status_id || 0})">
                                                Update Status
                                            </button>
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
                        // Default statuses if API fails - using valid enum values
                        statusOptions = [
                            { id: 1, name: 'Listed', payment_status: 'pending' },
                            { id: 2, name: 'Spoken For', payment_status: 'completed' },
                            { id: 3, name: 'Paid For', payment_status: 'completed' },
                            { id: 4, name: 'Sold', payment_status: 'completed' },
                        ];
                    }
                })
                .catch(error => {
                    console.error('Error loading statuses:', error);
                    // Default statuses if API fails - using valid enum values
                    statusOptions = [
                        { id: 1, name: 'Pending', payment_status: 'pending' },
                        { id: 2, name: 'Completed', payment_status: 'completed' },
                        { id: 3, name: 'Cancelled', payment_status: 'failed' },
                        { id: 4, name: 'Refunded', payment_status: 'refunded' }
                    ];
                });
        }

        // Function to open the status update modal
        function openStatusUpdateModal(transactionId, currentStatus, statusId) {
            currentTransactionId = transactionId;

            // Update modal content
            document.getElementById('updateTransactionId').textContent = transactionId;

            const currentStatusElement = document.getElementById('currentStatus');
            currentStatusElement.textContent = currentStatus;
            currentStatusElement.className = `badge ${getStatusClass(currentStatus)}`;

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
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('confirmStatusUpdate').addEventListener('click', updateTransactionStatus);
            // Load statuses when page loads
            loadStatusOptions();
        });

        // Function to update transaction status
        function updateTransactionStatus() {
            const newStatusId = document.getElementById('newStatus').value;
            const userData = JSON.parse(localStorage.getItem('user'));

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

            // Send update request
            fetch(`${window.location.origin}/maizemarket/backend/update_transaction_status_report.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    transaction_id: currentTransactionId,
                    new_status_id: newStatusId,
                    user_id: userData.id,
                    transaction_type: 'sale'
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
                        text: `Sale status updated to ${statusName} successfully`
                    });

                    // Reload sales data to reflect the changes
                    loadSales(userData.id);
                } else {
                    // Show error message with details
                    console.error('Status update error:', data);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to update sale status'
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

        function loadUser() {
            const user = localStorage.getItem('user');
            if (user) {
                const userData = JSON.parse(user);
                let entity_name = userData.entity_name??userData.name;
                document.getElementById("welcomeMessage").innerText = `Welcome, ${entity_name}  (${userData.role})`;

                // document.getElementById('welcomeMessage').innerText = `Welcome, ${userData.name}`;
                if (userData.role_id === 2) {
                    document.getElementById("buyMenuItem").style.display = "none";
                }
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