<?php include 'config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Board Member Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .card { box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
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
    <h3 class="mb-4">Maize Listings</h3>
    
    <!-- 🚀 Status Filter Dropdown -->
    <label for="statusFilter"><strong>Filter by Status:</strong></label>
    <select id="statusFilter" class="form-select w-25 mb-4" onchange="loadListings()">
        <option value="pending" selected>Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
        <option value="">All</option>
    </select>

    <div class="row" id="maizeListings">
        <!-- 🚀 Listings Will Load Here Dynamically -->
    </div>
</div>

<!-- Approve/Reject Modal -->
<div class="modal fade" id="approveModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveForm">
                <div class="modal-header">
                    <h5 class="modal-title">Approve or Reject Listing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="maize_id" id="listingId">
                    <label>Approval Comments</label>
                    <textarea class="form-control" name="comments" id="approvalComments" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="submitApproval('reject')">Reject</button>
                    <button type="button" class="btn btn-success" onclick="submitApproval('approve')">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function loadUser() {
        const user = localStorage.getItem("user");

        if (user) {
            const userData = JSON.parse(user);
            console.log("Loaded User from localStorage:", userData);

            if (userData.role !== 2 || userData.role !== 3) {
                Swal.fire({
                    icon: "error",
                    title: "Access Denied",
                    text: "You are not authorized to access this page!",
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    localStorage.setItem("user", null);
                    window.location.href = "login.php";
                });
                return;
            }

            document.getElementById("welcomeMessage").innerText = `Welcome, ${userData.username} - Board Member`;

            // 🚀 Load Maize Listings
            loadListings();
        } else {
            console.warn("No user found in localStorage, redirecting to login.");
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

    function loadListings() {
        const status = document.getElementById("statusFilter").value; // Get selected status
        fetch(`${window.location.origin}/maizemarket/backend/fetch_bm_maize_listings.php?status=${status}`)
            .then(response => response.json())
            .then(data => {
                const listingsContainer = document.getElementById("maizeListings");
                listingsContainer.innerHTML = "";

                if (data.length === 0) {
                    listingsContainer.innerHTML = `<p class="text-center">No maize listings found for the selected status.</p>`;
                    return;
                }

                data.forEach(maize => {
                    console.log(JSON.stringify(maize));
                    listingsContainer.innerHTML += `
                        <div class="col-md-4 mb-3">
                            <div class="card p-3">
                                <h5>${maize.quantity} kg - ${maize.quality}</h5>
                                <p><strong>Price:</strong> $${maize.price_per_unit}/kg</p>
                                <p><strong>Status:</strong> ${maize.status.charAt(0).toUpperCase() + maize.status.slice(1)}</p>
                                ${maize.status === 'pending' ? `
                                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#approveModal" 
                                        onclick="setApproveData(${maize.id})">Approve/Reject</button>` 
                                : ""}
                            </div>
                        </div>
                    `;
                });
            })
            .catch(error => console.error("Error loading maize listings:", error));
    }

    function setApproveData(listingId) {
        document.getElementById("listingId").value = listingId;
    }

    function submitApproval(action) {
        let formData = new FormData(document.getElementById("approveForm"));
        formData.append("action", action);
        formData.append("board_member_id", JSON.parse(localStorage.getItem("user")).id);

        fetch("${window.location.origin}/maizemarket/backend/approve_listing.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 200) {
                Swal.fire({
                    icon: "success",
                    title: action === "approve" ? "Approved!" : "Rejected!",
                    text: result.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    loadListings();
                    document.querySelector("#approveModal .btn-close").click();
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: result.message
                });
            }
        })
        .catch(error => console.error("Error approving listing:", error));
    }

    function logout() {
        localStorage.removeItem("user");
        window.location.href = "logout.php";
    }

    window.onload = loadUser;
</script>
</body>
</html>