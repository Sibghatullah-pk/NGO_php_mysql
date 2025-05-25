<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['username']);
    unset($_SESSION['role']);
    header("location: login.php");
    exit();
}

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'project';

$db = new mysqli($host, $user, $pass, $dbname);

if ($db->connect_error) {
    error_log("Connection failed: " . $db->connect_error, 3, "error.log");
    die("Database connection failed: " . $db->connect_error);
}

// Set role if not already set
if (!isset($_SESSION['role'])) {
    $username = $_SESSION['username'];
    $role_query = "SELECT role FROM users WHERE username = ?";
    $stmt = $db->prepare($role_query);
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        if ($user) {
            $_SESSION['role'] = $user['role'];
        } else {
            $_SESSION['role'] = 'user'; // Default role if not found
            error_log("User not found for username: $username", 3, "error.log");
        }
        $stmt->close();
    } else {
        error_log("Prepare failed: " . $db->error, 3, "error.log");
        die("Database query preparation failed: " . $db->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>About Us | NGO Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Font Awesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <style>
        body {
            padding-top: 70px;
            background-color: #f4f6f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-brand {
            font-weight: bold;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .dropdown-menu {
            animation: fadeIn 0.3s ease-in-out;
        }

        .about-section {
            background: white;
            padding: 50px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1.5s ease;
        }

        .about-section h1 {
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .about-section p,
        .about-section ul li {
            font-size: 1.05rem;
            color: #555;
        }

        ul li::marker {
            color: #0d6efd;
        }

        .icon-box {
            font-size: 2rem;
            color: #0d6efd;
            margin-right: 10px;
            transition: transform 0.3s ease;
        }

        .icon-box:hover {
            transform: scale(1.2);
        }

        .about-img {
            width: 100%;
            border-radius: 12px;
            transition: transform 0.5s ease;
        }

        .about-img:hover {
            transform: scale(1.05);
        }

        .highlight {
            color: #0d6efd;
            font-weight: bold;
        }

        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .card i {
            transition: transform 0.3s ease;
        }

        .card:hover i {
            transform: rotate(360deg);
        }

        .carousel-item {
            transition: transform 0.6s ease-in-out;
        }

        .carousel-caption {
            border-radius: 10px;
            max-width: 80%;
            margin-left: auto;
            margin-right: auto;
            bottom: 20%;
            background: rgba(0, 0, 0, 0.6);
            padding: 20px;
            animation: fadeInCaption 0.8s ease-in-out;
        }

        @keyframes fadeInCaption {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .carousel-indicators button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #fff;
            border: 1px solid #007bff;
            opacity: 0.5;
        }

        .carousel-indicators .active {
            opacity: 1;
            background-color: #007bff;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            padding: 20px;
        }

        .carousel-control-prev,
        .carousel-control-next {
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        .carousel-item img {
            height: 400px;
            object-fit: cover;
        }

        #backToTop {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            display: none;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        #backToTop:hover {
            background-color: #0056b3;
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .carousel-item img {
                height: 300px;
            }

            .carousel-caption {
                bottom: 10%;
                padding: 10px;
            }

            .carousel-caption h5 {
                font-size: 1.2rem;
            }

            .carousel-caption p {
                font-size: 0.9rem;
            }

            #backToTop {
                position: fixed;
                bottom: 30px;
                right: 30px;
                z-index: 1000;
                display: none;
                background-color: #0d6efd;
                color: white;
                border: none;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                font-size: 24px;
                cursor: pointer;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
                transition: background-color 0.3s ease, transform 0.3s ease;
            }

            #backToTop:hover {
                background-color: #0056b3;
                transform: scale(1.1);
            }

            .about-section {
                padding: 30px;
            }

            .carousel-caption {
                max-width: 90%;
            }

            footer a:hover {
                text-decoration: none;
                color: #ffc107;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-info shadow fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="fas fa-hand-holding-heart fa-lg me-2"></i> NGO MANAGEMENT SYSTEM
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 d-flex align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php"><i class="fas fa-info-circle me-1"></i> About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php"><i class="fas fa-envelope me-1"></i> Contact</a>
                    </li>

                    <?php if (isset($_SESSION['username'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item text-danger" href="about.php?logout=1">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- About Section -->
    <div class="container mt-5">
        <div class="about-section">
            <h1><i class="fas fa-hands-helping icon-box"></i>About Our NGO Management System</h1>
            <p>
                A Non-Governmental Organization (NGO) is an independent, non-profit entity focused on social causes.
                NGOs operate across development, rights, and humanitarian sectors. Our platform enables seamless NGO
                management.
            </p>

            <p><strong>Key Characteristics:</strong></p>
            <ul>
                <li><strong>Independent:</strong> Free from direct government control or influence.</li>
                <li><strong>Non-Profit:</strong> Revenue is reinvested into social impact programs.</li>
                <li><strong>Value-Based:</strong> Driven by ethics, compassion, and community service.</li>
                <li><strong>Diverse Missions:</strong> From human rights to disaster relief, NGOs work broadly.</li>
            </ul>

            <p><strong>Popular NGOs:</strong></p>
            <ul>
                <li><strong>Aga Khan Foundation:</strong> Sustainable development in health, education, and rural
                    development.</li>
                <li><strong>Edhi Foundation:</strong> Largest network of ambulances, shelters, and hospitals.</li>
                <li><strong>Saylani Welfare Trust:</strong> Feeding thousands daily and offering vocational training.
                </li>
                <li><strong>Alkhidmat Foundation:</strong> Orphan care, clean water projects, and disaster response.
                </li>
                <li><strong>Amnesty International:</strong> Global human rights defense organization.</li>
            </ul>

            <p class="mt-4">
                <strong>Our team</strong> is made up of passionate individuals united by a common purpose: to create
                impact through unity and service. Join us on our mission to change lives.
            </p>
        </div>
    </div>

    <!-- About Info -->
    <div class="container py-5">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <img src="download.jpeg" alt="About NGO" class="about-img shadow"
                    onerror="this.src='https://via.placeholder.com/600x400?text=NGO+Image+Not+Found';">
            </div>
            <div class="col-md-6">
                <h2 class="mb-3">Who We Are</h2>
                <p>
                    We are a <span class="highlight">dedicated non-profit organization</span> committed to making a
                    meaningful difference in the lives of underprivileged communities.
                    Our mission is to promote <strong>education, health, and empowerment</strong> through sustainable
                    programs.
                </p>
                <p>
                    Founded in 2015, we’ve supported over <span class="highlight">10,000 beneficiaries</span> across
                    rural and urban areas through scholarships,
                    medical camps, food drives, and women's empowerment initiatives.
                </p>
            </div>
        </div>

        <!-- Vision & Mission Cards -->
        <div class="row text-center mb-5">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <i class="fas fa-bullseye fa-2x text-info mb-3"></i>
                        <h5 class="card-title">Our Mission</h5>
                        <p class="card-text">To uplift communities through accessible education, healthcare, and
                            opportunity.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <i class="fas fa-eye fa-2x text-info mb-3"></i>
                        <h5 class="card-title">Our Vision</h5>
                        <p class="card-text">To create a society where every individual lives with dignity, purpose, and
                            self-reliance.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <i class="fas fa-hands-helping fa-2x text-info mb-3"></i>
                        <h5 class="card-title">Our Values</h5>
                        <p class="card-text">Compassion, integrity, inclusivity, transparency, and sustainability in
                            everything we do.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Carousel Section -->
        <div class="container my-5">
            <div class="text-center">
                <h3 class="mb-5 display-4 fw-bold text-primary">Our Journey</h3>
                <p class="lead mb-5">Discover the milestones, stories, and vision that define our mission to empower
                    communities.</p>
            </div>
            <div id="journeyCarousel" class="carousel slide" data-bs-ride="carousel">
                <!-- Indicators -->
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#journeyCarousel" data-bs-slide-to="0" class="active"
                        aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#journeyCarousel" data-bs-slide-to="1"
                        aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#journeyCarousel" data-bs-slide-to="2"
                        aria-label="Slide 3"></button>
                    <button type="button" data-bs-target="#journeyCarousel" data-bs-slide-to="3"
                        aria-label="Slide 4"></button>
                </div>

                <!-- Slides -->
                <div class="carousel-inner">
                    <!-- Slide 1: Founding and Early Impact -->
                    <div class="carousel-item active" data-bs-interval="5000">
                        <img src="images.jpeg" class="d-block w-100 rounded" alt="Founding Moment">
                        <div class="carousel-caption d-md-block p-3 rounded">
                            <h5 class="text-white">2015 - A Vision Takes Root</h5>
                            <p class="text-white">Founded by 5 passionate individuals, we began our journey to transform
                                lives through education, healthcare, and disaster relief, reaching over 500 families in
                                our first year.</p>
                        </div>
                    </div>

                    <!-- Slide 2: Community Impact -->
                    <div class="carousel-item" data-bs-interval="5000">
                        <img src="images (1).jpeg" class="d-block w-100 rounded" alt="Flood Relief">
                        <div class="carousel-caption d-md-block p-3 rounded">
                            <h5 class="text-white">2023 - Flood Relief Success</h5>
                            <p class="text-white">Distributed over 50,000 meals and provided shelter to 2,000 families
                                during flood relief, showcasing our commitment to emergency response.</p>
                        </div>
                    </div>

                    <!-- Slide 3: Testimonial -->
                    <div class="carousel-item" data-bs-interval="5000">
                        <img src="download (2).jpeg" class="d-block w-100 rounded" alt="Testimonial">
                        <div class="carousel-caption d-md-block p-3 rounded">
                            <h5 class="text-white">A Voice from the Community</h5>
                            <p class="text-white">"Thanks to this NGO, my children now attend school, and we have clean
                                water. Their work changed our lives!" - Amina, Rural Beneficiary</p>
                        </div>
                    </div>

                    <!-- Slide 4: Future Vision -->
                    <div class="carousel-item" data-bs-interval="5000">
                        <img src="download (1).jpeg" class="d-block w-100 rounded" alt="Future Vision">
                        <div class="carousel-caption d-md-block p-3 rounded">
                            <h5 class="text-white">2025 and Beyond</h5>
                            <p class="text-white">Join us in our mission! We’re launching a global campaign to combat
                                child malnutrition, partnering with international NGOs as of May 2025.</p>
                            <a href="donation.php" class="btn btn-primary mt-3">Get Involved</a>
                        </div>
                    </div>
                </div>

                <!-- Controls -->
                <button class="carousel-control-prev" type="button" data-bs-target="#journeyCarousel"
                    data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#journeyCarousel"
                    data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>

        <!-- Back to Top Button -->
        <button id="backToTop" title="Go to top"><i class="fas fa-arrow-up"></i></button>

        <!-- Footer -->
        <footer class="bg-dark text-white mt-5 p-4 text-center">
            <div class="container">
                <div class="row">
                    <!-- Social Media -->
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h5>Follow Us</h5>
                        <a href="https://www.facebook.com" class="text-white mx-2" target="_blank"><i
                                class="fab fa-facebook fa-lg"></i></a>
                        <a href="https://www.twitter.com" class="text-white mx-2" target="_blank"><i
                                class="fab fa-twitter fa-lg"></i></a>
                        <a href="https://www.instagram.com" class="text-white mx-2" target="_blank"><i
                                class="fab fa-instagram fa-lg"></i></a>
                        <a href="https://www.youtube.com" class="text-white mx-2" target="_blank"><i
                                class="fab fa-youtube fa-lg"></i></a>
                    </div>
                    <!-- Copyright -->
                    <div class="col-md-6">
                        <p class="mb-0">© 2025 Hope Foundation. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script>
        // Fallback manual initialization of the carousel
        document.addEventListener('DOMContentLoaded', function () {
            const carousel = document.querySelector('#journeyCarousel');
            if (carousel) {
                new bootstrap.Carousel(carousel, {
                    interval: 5000,
                    ride: 'carousel'
                });
            }
        });

        // Back to Top Button Functionality
        const backToTopButton = document.getElementById('backToTop');
        window.onscroll = function () {
            if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                backToTopButton.style.display = "block";
            } else {
                backToTopButton.style.display = "none";
            }
        };

        backToTopButton.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>

    <?php
    // Close database connection
    $db->close();
    ?>
</body>

</html>