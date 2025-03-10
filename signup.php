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
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
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
                <h2 class="card-title mb-4 text-center">Create Account</h2>
                <form id="registerForm">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" id="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" id="password" minlength="6" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Account Type</label>
                        <select class="form-select" name="role" id="role" required>
                            <option value="">Select Account Type</option>
                            <option value="farmer">Farmer</option>
                            <option value="board_member">Board Member</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100 btn-lg">Register</button>
                </form>
                <div class="text-center mt-4">
                    <p>Already have an account? <a href="login.php" class="text-success">Login here</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("registerForm").addEventListener("submit", async function(e) {
            e.preventDefault();

            let formData = new FormData(this);
            let jsonData = Object.fromEntries(formData.entries());

            let response = await fetch("signup_process.php", {
                method: "POST",
                body: new URLSearchParams(jsonData),
                headers: { "Content-Type": "application/x-www-form-urlencoded" }
            });

            let result = await response.json();

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