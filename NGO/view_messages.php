<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
}

// Include database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'project';

$db = new mysqli($host, $user, $pass, $dbname);

if ($db->connect_error) {
    error_log("Connection failed: " . $db->connect_error, 3, "error.log");
    die("Database connection failed. Please try again later.");
}

// Check admin role
$is_admin = false;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $role_query = "SELECT role FROM users WHERE username = ?";
    $stmt = $db->prepare($role_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $role_result = $stmt->get_result();
    $user = $role_result->fetch_assoc();
    if ($user && $user['role'] == 'admin') {
        $is_admin = true;
    }
    $stmt->close();
}

// Restrict access to admin only
if (!$is_admin) {
    $_SESSION['msg'] = "You do not have permission to view messages.";
    header('location: index.php');
    exit;
}

// Fetch all messages
$sql = "SELECT * FROM messages ORDER BY created_at DESC";
$result = $db->query($sql);

if (!$result) {
    error_log("Query failed: " . $db->error, 3, "error.log");
    die("Error fetching messages. Please try again later.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Messages | NGO Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <style>
        body {
            padding-top: 70px;
        }

        .content {
            padding: 20px;
        }

        .message-card {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .message-card p {
            margin: 5px 0;
        }

        .btn-back {
            background: #3e2093;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
        }

        .btn-back:hover {
            background: #5029bc;
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

        /* Animations */
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

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .carousel-caption {
                top: 20%;
                transform: translateY(0);
                max-width: 90%;
            }

            footer a:hover {
                text-decoration: none;
                color: #ffc107;
                width: 100%;
            }
    </style>
</head>

<body>
    <div class="w3-container">
        <nav class="navbar navbar-expand-sm bg-info navbar-dark fixed-top">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="collapsibleNavbar">
                <!-- Welcome Icon / Branding -->
                <div class="navbar-header d-flex align-items-center" style="margin-right:auto; margin-left: 2vw;">
                    <i class="fas fa-hand-holding-heart fa-lg text-white mr-2"></i>
                    <a class="navbar-brand" href="index.php">NGO MANAGEMENT SYSTEM</a>
                </div>

                <!-- Navigation Links -->
                <ul class="navbar-nav ml-auto d-flex align-items-center">

                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home mr-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">
                            <i class="fas fa-info-circle mr-1"></i> About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">
                            <i class="fas fa-envelope mr-1"></i> Contact
                        </a>
                    </li>

                    <!-- User Dropdown -->
                    <?php if (isset($_SESSION['username'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-user-circle mr-1"></i>
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="index.php?logout='1'">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </a>
                            </div>
                        </li>
                    <?php endif; ?>

                </ul>
            </div>
    </div>

    <div class="container">
        <div class="content">
            <h2>View All Messages</h2>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="message-card">
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($row['username']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                        <p><strong>Message:</strong> <?php echo htmlspecialchars($row['message']); ?></p>
                        <p><strong>Sent At:</strong> <?php echo $row['created_at']; ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No messages found.</p>
            <?php endif; ?>
            <a href="contact.php" class="btn-back mt-3">Back to Contact</a>
        </div>
        <button id="backToTop" title="Go to top"><i class="fas fa-arrow-up"></i></button>

        <!-- Footer Section -->
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
                        <p class="mb-0">&copy; 2025 Hope Foundation. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </footer>
        <script>
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
</body>

</html>

<?php $db->close(); ?>