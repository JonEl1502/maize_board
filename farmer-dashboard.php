<?php
include 'config.php';

// 🚀 Fetch all maize listings (later filtered by JS)
$query = $conn->prepare("SELECT * FROM maize_listings");
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard</title>
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
    <h3 class="mb-4">Your Maize Listings</h3>
    
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addPostModal">+ Add Post</button>

    <div class="row" id="maizeListings">
        <!-- 🚀 Maize Listings Will Load Here Dynamically -->
    </div>
</div>

<!-- Add Post Modal -->
<div class="modal fade" id="addPostModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addPostForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add Maize Listing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="farmer_id" id="farmerId">
                    <div class="mb-3">
                        <label>Quantity (kg)</label>
                        <input type="number" class="form-control" name="quantity" required>
                    </div>
                    <div class="mb-3">
                        <label>Quality</label>
                        <select class="form-control" name="quality" required>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Price per kg ($)</label>
                        <input type="number" class="form-control" name="price_per_unit" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Add Post</button>
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

            document.getElementById("welcomeMessage").innerText = `Welcome, ${userData.username} - Farmer`;
            document.getElementById("farmerId").value = userData.id;

            // 🚀 Load Maize Listings for This Farmer
            loadMaizeListings(userData.id);
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

    function loadMaizeListings(farmerId) {
        fetch(`fetch_f_maize_listings.php?farmer_id=${farmerId}`) // ✅ Pass farmer_id in URL
            .then(response => response.json())
            .then(data => {
                const listingsContainer = document.getElementById("maizeListings");
                listingsContainer.innerHTML = "";

                if (data.length === 0) {
                    listingsContainer.innerHTML = `<p class="text-center">No maize listings found.</p>`;
                    return;
                }

                data.forEach(maize => {
                    listingsContainer.innerHTML += `
                        <div class="col-md-4 mb-3">
                            <div class="card p-3">
                                <h5>${maize.quantity} kg - ${maize.quality}</h5>
                                <p><strong>Price:</strong> $${maize.price_per_unit}/kg</p>
                                <p><strong>Status:</strong> ${maize.status.charAt(0).toUpperCase() + maize.status.slice(1)}</p>
                                <p><strong>Listed On:</strong> ${maize.listing_date}</p>
                            </div>
                        </div>
                    `;
                });
            })
            .catch(error => {
                console.error("Error loading maize listings:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to load maize listings. Please try again.",
                });
            });
    }

    function logout() {
        localStorage.removeItem("user");
        window.location.href = "logout.php";
    }

    window.onload = loadUser;
</script>
</body>
</html>