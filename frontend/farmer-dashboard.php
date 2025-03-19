<?php
include 'config.php';
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
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
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
        <button type="button" class="btn btn-success mb-3" id="openModalBtn">+ Add Post</button>
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
                        <input type="hidden" name="status" value="pending">
                        <input type="hidden" name="approved_by" value="">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Quantity</label>
                                <input type="number" class="form-control" name="quantity" required>
                            </div>
                            <div class="col-md-6">
                                <label>Quantity Unit</label>
                                <select class="form-select" name="quantity_unit_id" id="quantity_unit_id" required>
                                    <option value="">Loading...</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Price per unit ($)</label>
                            <input type="number" class="form-control" name="price_per_unit" required>
                        </div>


                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Counties</label>
                                <select class="form-select" name="county_id" id="counties" required>
                                    <option value="">Loading...</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Location</label>
                                <input type="text" class="form-control"  name="location" id="location" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Need Transport?</label>
                            <select class="form-control" name="need_transport">
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
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
    <div class="modal fade" id="editPostModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editPostForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Maize Listing</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editListingId">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Quantity</label>
                                <input type="number" class="form-control" name="quantity" id="editQuantity" required>
                            </div>
                            <div class="col-md-6">
                                <label>Quantity Unit</label>
                                <select class="form-select" name="quantity_unit_id" id="editQuantityUnitId" required>
                                    <option value="">Loading...</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Price per unit ($)</label>
                            <input type="number" class="form-control" name="price_per_unit" id="editPricePerUnit"
                                required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label>County</label>
                                <select class="form-select" name="county_id" id="editCountyId" required>
                                    <option value="">Loading...</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Location</label>
                                <input type="text" class="form-control" name="location" id="editLocation" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Need Transport?</label>
                            <select class="form-control" name="need_transport" id="editNeedTransport">
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
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
        document.addEventListener("DOMContentLoaded", async function () {
            let countryTypeSelect = document.getElementById("counties");
            let editCountySelect = document.getElementById("editCountyId");

            try {
                let response = await fetch(`${window.location.origin}/maizemarket/backend/get_counties.php`);
                let data = await response.json();

                if (data.status === 200) {
                    countryTypeSelect.innerHTML = '<option value="">Select Counties</option>';
                    data.counties.forEach(type => {
                        countryTypeSelect.innerHTML += `<option value="${type.id}">${type.name}</option>`;
                    });
                } else {
                    countryTypeSelect.innerHTML = '<option value="">Error loading types</option>';
                }
            } catch (error) {
                countryTypeSelect.innerHTML = '<option value="">Error fetching data</option>';
            }
        });

        document.addEventListener("DOMContentLoaded", async function () {
            let farmerTypeSelect = document.getElementById("quantity_unit_id");
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
                             <h5>${maize.quantity} - ${maize.unit_name}</h5>
                            <p><strong>Moisture:</strong> ${maize.moisture_percentage !== null && maize.moisture_percentage !== undefined ? maize.moisture_percentage + "%" : "n/a"}</p>
                            <p><strong>Aflatoxin:</strong> ${maize.aflatoxin_level !== null && maize.aflatoxin_level !== undefined ? maize.aflatoxin_level + " ppb" : "n/a"}</p>
                            <p><strong>Price:</strong> Kes. ${maize.price_per_unit} / ${maize.unit_name}</p>
                            <p><strong>Location:</strong> ${maize.location}</p>
                            <p><strong>Need Transport:</strong> ${maize.need_transport}</p>
                            <p><strong>Status:</strong> ${maize.status}</p>
                            <p><strong>Listed On:</strong> ${maize.listing_date}</p>
                            <button class="btn btn-primary" 
                                onclick="openEditModal(${maize.id}, '${maize.quantity}', '${maize.quantity_unit_id}', '${maize.price_per_unit}', '${maize.county_id}', '${maize.location}', '${maize.need_transport}')">
                            Edit </button>
                        </div>
                    </div>
                `;

                        console.log(`Editing #:${maize.id}, '${maize.quantity}', '${maize.quantity_unit_id}',`);
                    });
                })
                .catch(error => {
                    console.error("Fetch Error:", error);
                    Swal.fire({ icon: "error", title: "Error", text: "Failed to load maize listings." });
                });
        }

        function openEditModal(id, quantity, quantityUnitId, pricePerUnit, countyId, location, needTransport) {
            document.getElementById("editListingId").value = id;
            document.getElementById("editQuantity").value = quantity;
            document.getElementById("editQuantityUnitId").value = quantityUnitId;
            document.getElementById("editPricePerUnit").value = pricePerUnit;
            document.getElementById("editCountyId").value = countyId;
            document.getElementById("editLocation").value = location;
            // Ensure the correct transport option is selected
            document.getElementById("editNeedTransport").value = needTransport;

            // Debugging log to verify values
            console.log("Editing Listing:", { id, quantity, quantityUnitId, pricePerUnit, location, needTransport });

            let modal = new bootstrap.Modal(document.getElementById("editPostModal"));
            modal.show();
        }

        document.getElementById("editPostForm").addEventListener("submit", function (e) {
            e.preventDefault();
            let formData = new FormData(this);

            // Debugging output to ensure correct id
            let formDataObject = {};
            formData.forEach((value, key) => {
                formDataObject[key] = value;
            });

            console.log("Update Form Data:", formDataObject);

            fetch(`${window.location.origin}/maizemarket/backend/update_post.php`, {
                method: "POST",
                body: formData
            })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 200) {
                        Swal.fire({ icon: "success", title: "Post Updated!", text: result.message });
                        loadMaizeListings(JSON.parse(localStorage.getItem("user")).id);
                        document.getElementById("editPostForm").reset();
                        document.querySelector("#editPostModal .btn-close").click();
                    } else {
                        Swal.fire({ icon: "error", title: "Error", text: result.message });
                    }
                })
                .catch(error => console.error("Update Error:", error));
        });

        document.getElementById("addPostForm").addEventListener("submit", function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            console.log("Add Post Form Data:", Object.fromEntries(formData.entries()));
            fetch(`${window.location.origin}/maizemarket/backend/add_post.php`, { method: "POST", body: formData })
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

        document.getElementById("openModalBtn").addEventListener("click", function () {
            let modal = new bootstrap.Modal(document.getElementById("addPostModal"));
            modal.show();
        });

        window.onload = loadUser;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>