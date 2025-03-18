<?php
include 'config.php';

// Fetch all maize listings for this farmer (filtered later in JS)
// $query = $conn->prepare("SELECT * FROM maize_listings");
// $query->execute();
// $result = $query->get_result();
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
        <!-- 🚀 Maize Listings Load Here -->
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
                        <label>Moisture Percentage (%)</label>
                        <input type="number" class="form-control" name="moisture_percentage" step="0.1" required>
                    </div>

                    <div class="mb-3">
                        <label>Aflatoxin Level (ppb)</label>
                        <input type="number" class="form-control" name="aflatoxin_level" step="0.1" required>
                    </div>

                    <div class="mb-3">
                        <label>Price per kg ($)</label>
                        <input type="number" class="form-control" name="price_per_unit" required>
                    </div>

                    <div class="mb-3">
                        <label>Location</label>
                        <input type="text" class="form-control" name="location" required>
                    </div>

                    <div class="mb-3">
                        <label>Need Transport?</label>
                        <select class="form-control" name="need_transport">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Add Post</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function loadUser() {
        const user = localStorage.getItem("user");
        console.log("Logged in Farmer:", user);
        if (user) {
            const userData = JSON.parse(user);
            document.getElementById("welcomeMessage").innerText = `Welcome, ${userData.name} - Farmer`;
            document.getElementById("farmerId").value = userData.id;
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
    fetch(`${window.location.origin}/maizemarket/backend/fetch_f_maize_listings.php?farmer_id=${farmerId}`)
        .then(response => response.json())
        .then(data => {
            console.log("API Response:", data); // ✅ Debugging line
            
            // Check if data is an array before using forEach
            if (!Array.isArray(data.data)) {
                console.error("Unexpected API Response:", data);
                Swal.fire({ icon: "error", title: "Error", text: "Failed to load listings. Try again!" });
                return;
            }

            const listingsContainer = document.getElementById("maizeListings");
            listingsContainer.innerHTML = "";

            if (data.data.length === 0) {
                listingsContainer.innerHTML = `<p class="text-center">No maize listings found.</p>`;
                return;
            }

            data.data.forEach(maize => {
                listingsContainer.innerHTML += `
                    <div class="col-md-4 mb-3">
                        <div class="card p-3">
                            <h5>${maize.quantity} kg - ${maize.quality}</h5>
                            <p><strong>Moisture:</strong> ${maize.moisture_percentage}%</p>
                            <p><strong>Aflatoxin:</strong> ${maize.aflatoxin_level} ppb</p>
                            <p><strong>Price:</strong> $${maize.price_per_unit}/kg</p>
                            <p><strong>Location:</strong> ${maize.location}</p>
                            <p><strong>Need Transport:</strong> ${maize.need_transport ? 'Yes' : 'No'}</p>
                            <p><strong>Status:</strong> ${maize.status}</p>
                            <p><strong>Listed On:</strong> ${maize.listing_date}</p>
                        </div>
                    </div>
                `;
            });
        })
        .catch(error => {
            console.error("Fetch Error:", error);
            Swal.fire({ icon: "error", title: "Error", text: "Failed to load maize listings." });
        });
}

    document.getElementById("addPostForm").addEventListener("submit", function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        fetch("add_post.php", { method: "POST", body: formData })
        .then(response => response.json())
        .then(result => {
            if (result.status === 200) {
                Swal.fire({ icon: "success", title: "Post Added!", text: result.message });
                loadMaizeListings(JSON.parse(localStorage.getItem("user")).id);
                document.getElementById("addPostForm").reset();
                document.querySelector("#addPostModal .btn-close").click();
            } else {
                Swal.fire({ icon: "error", title: "Error", text: result.message });
            }
        });
    });

    function logout() {
        localStorage.removeItem("user");
        window.location.href = "logout.php";
    }

    window.onload = loadUser;
</script>
</body>
</html>