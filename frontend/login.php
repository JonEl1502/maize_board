<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Farm Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .auth-card {
            max-width: 400px;
            margin: 5rem auto;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="/">Farm Market</a>
            <!-- <a class="btn btn-outline-light" href="index.php"><i class="fas fa-arrow-left"></i> Back</a> -->
        </div>
    </nav>

    <div class="container">
        <div class="card auth-card">
            <div class="card-body p-5">
                <h2 class="card-title mb-4 text-center">Welcome Back!</h2>

                <form id="loginForm">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="email" id="username" required>
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

            try {
                let response = await fetch(`${window.location.origin}/maizemarket/backend/login_process.php`, {
                    method: "POST",
                    body: new URLSearchParams(jsonData),
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    }
                });

                let result = await response.json();
                console.log("Logged in result:", JSON.stringify(result));

                if (result.status === 200) {
                    // Save user data in localStorage
                    localStorage.setItem("user", JSON.stringify(result.user));
                    let userData = result.user
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: result.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Redirect based on role_id
                        const currentPage = window.location.pathname;
                        const farmerPage = "/maizemarket/frontend/dashboard.php";
                        const homePage = "/maizemarket/frontend/home.php";

                        if ((userData.role_id === 5 && currentPage !== homePage) ||
                            (userData.role_id !== 5 && currentPage !== farmerPage)) {
                            setTimeout(() => {
                                console.log("Redirecting to:", userData.role_id === 5 ? homePage : farmerPage);
                                window.location.href = userData.role_id === 5 ? homePage : farmerPage;
                            }, 1000);
                        }
                        window.location.href = result.user.role_id === 2 ? farmerPage : homePage;
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Login Failed",
                        text: result.message
                    });
                }
            } catch (error) {
                console.error("Login Error:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Something went wrong. Please try again."
                });
            }
        });

        // ✅ Check if user is already logged in and redirect
        const loggedInUser = localStorage.getItem("user");
        if (loggedInUser) {
            const userData = JSON.parse(loggedInUser);
            console.log("User already logged in:", userData);

            const currentPage = window.location.pathname;
            const farmerPage = "/maizemarket/frontend/dashboard.php";
            const boardPage = "/maizemarket/frontend/board-dashboard.php";
            const homePage = "/maizemarket/frontend/home.php";

            if ((userData.role_id === 2 && currentPage !== farmerPage) ||
                (userData.role_id !== 2 && currentPage !== farmerPage)) {
                setTimeout(() => {
                    console.log("Redirecting to:", userData.role_id === 2 ? farmerPage : boardPage);
                    window.location.href = userData.role_id === 2 ? farmerPage : homePage;
                    // window.location.href = homePage;

                }, 1000);
            }
        }
    </script>
</body>

</html>