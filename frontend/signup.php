<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Maize Board</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .auth-card {
            max-width: 500px;
            margin: 5rem auto;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="/">Maize Board</a>
        </div>
    </nav>

    <div class="container">
        <div class="card auth-card">
            <div class="card-body p-5">
                <h2 class="card-title mb-2 text-center">Create Account</h2>
                <form id="registerForm">
                    <div class="mb-2">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" id="password" minlength="6"
                            required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" id="phone" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" id="address" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" id="role" required>
                            <option value="">Loading...</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100 btn-lg">Register</button>
                </form>
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="login.php" class="text-success">Login here</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", async function () {
            let selectedRole = document.getElementById("role");

            try {
                let response = await fetch(`${window.location.origin}/maizemarket/backend/get_roles.php`);
                let data = await response.json();

                if (data.status === 200) {
                    selectedRole.innerHTML = '<option value="">Select Role</option>';
                    data.roles.forEach(role => {  // Use `roles`, not `farmer_types`
                        if (role.name === "Admin") return;  // Skip Admin role
                        selectedRole.innerHTML += `<option value="${role.id}">${role.name}</option>`;
                    });
                } else {
                    selectedRole.innerHTML = '<option value="">Error loading roles</option>';
                }
            } catch (error) {
                selectedRole.innerHTML = '<option value="">Error fetching data</option>';
            }
        });

        document.getElementById("registerForm").addEventListener("submit", async function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            let jsonData = Object.fromEntries(formData.entries());

            let response = await fetch(`${window.location.origin}/maizemarket/backend/signup_process.php`, {
                method: "POST",
                body: new URLSearchParams(jsonData),
                headers: { "Content-Type": "application/x-www-form-urlencoded" }
            });

            let resultText = await response.text();  // Get the raw response
            console.log("Raw response:", resultText); // Log raw response for debugging

            let result = JSON.parse(resultText);  // Try to parse JSON


            // let result = await response.json();
            if (result.status === 200) {
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: result.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "login.php";
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Oops!",
                    text: result.message
                });
            }
        });
    </script>
</body>

</html>