<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Maize Board</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .auth-card {
            max-width: 400px;
            margin: 5rem auto;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
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
            <h2 class="card-title mb-4 text-center">Welcome Back!</h2>

            <form id="loginForm">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" id="username" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="password" required>
                </div>
                <button type="submit" class="btn btn-success w-100 btn-lg">Login now</button>
            </form>

            <div class="text-center mt-4">
                <p>Don't have an account? <a href="signup.php" class="text-success">Register here</a></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById("loginForm").addEventListener("submit", async function(e) {
        e.preventDefault();

        let formData = new FormData(this);
        let jsonData = Object.fromEntries(formData.entries());

        let response = await fetch("login_process.php", {
            method: "POST",
            body: new URLSearchParams(jsonData),
            headers: { "Content-Type": "application/x-www-form-urlencoded" }
        });

        let result = await response.json();

        if (result.status === 200) {
            // Save user data in localStorage
            localStorage.setItem("user", JSON.stringify(result.user));

            // Console log user details
            console.log("User Logged In:", result.user);

            Swal.fire({
                icon: "success",
                title: "Success!",
                text: result.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                // Redirect based on role
                window.location.href = result.user.role === "farmer" ? "farmer-dashboard.php" : "board-dashboard.php";
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Login Failed",
                text: result.message
            });
        }
    });

    // ✅ FIX: Only redirect if NOT already on the correct page
    const loggedInUser = localStorage.getItem("user");
    if (loggedInUser) {
        const userData = JSON.parse(loggedInUser);
        console.log("User already logged in:", userData);
        console.log("User already logged in:", userData.role);

        const currentPage = window.location.pathname;
        const farmerPage = "/maizemarket/farmer-dashboard.php";
        const boardPage = "/maizemarket/board-dashboard.php";

        if ((userData.role === "farmer" && currentPage !== farmerPage) ||
            (userData.role === "boardMember" && currentPage !== boardPage)) {
            setTimeout(() => {
                console.log("Page to: ", userData.role === "farmer" ? farmerPage : boardPage);
                window.location.href = userData.role === "farmer" ? farmerPage : boardPage;
            }, 1000);
        }
    }
</script>
</body>
</html>