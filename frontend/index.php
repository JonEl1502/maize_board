<?php include 'config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Maize Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?ixlib=rb-1.2.1&auto=format&fit=crop&w=2000&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
        }
        .feature-card {
            border: none;
            border-radius: 10px;
            transition: transform 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 2.5rem;
            color: #198754;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 mb-4">Welcome to Maize Market</h1>
            <p class="lead mb-5">Your trusted platform for buying and selling quality farm produce</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="login.php" class="btn btn-success btn-lg">Login</a>
                <a href="signup.php" class="btn btn-outline-light btn-lg">Sign Up</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Why Choose Maize Market?</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100 p-4 text-center">
                        <div class="card-body">
                            <i class="fas fa-handshake feature-icon"></i>
                            <h4>Direct Trading</h4>
                            <p>Connect directly with farmers and buyers without intermediaries</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 p-4 text-center">
                        <div class="card-body">
                            <i class="fas fa-check-circle feature-icon"></i>
                            <h4>Quality Assurance</h4>
                            <p>All produce is verified by our board members for quality standards</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 p-4 text-center">
                        <div class="card-body">
                            <i class="fas fa-chart-line feature-icon"></i>
                            <h4>Market Insights</h4>
                            <p>Access real-time market prices and trading analytics</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Farm Produce Section -->
    <section class="bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-5">Our Farm Produce</h2>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card h-100">
                        <img src="https://images.unsplash.com/photo-1576188973526-0e5d7047b0cf?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" class="card-img-top" alt="Maize">
                        <div class="card-body text-center">
                            <h5>Fresh Maize</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <img src="https://images.unsplash.com/photo-1603197830476-26e191d4c34a?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" class="card-img-top" alt="Grains">
                        <div class="card-body text-center">
                            <h5>Quality Grains</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <img src="https://images.unsplash.com/photo-1603197830476-26e191d4c34a?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" class="card-img-top" alt="Seeds">
                        <div class="card-body text-center">
                            <h5>Certified Seeds</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <img src="https://images.unsplash.com/photo-1603197830476-26e191d4c34a?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" class="card-img-top" alt="Feed">
                        <div class="card-body text-center">
                            <h5>Animal Feed</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2024 Maize Market. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>