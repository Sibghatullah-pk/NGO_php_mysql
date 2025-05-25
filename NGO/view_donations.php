<?php
// session_start();

// Include server.php to get database connection
include('server.php');
$stmt = $db->prepare("SELECT COUNT(*) as count, SUM(amount) as total FROM donations");
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();

// Fetch role if not set (same logic as index.php)
if (!isset($_SESSION['role']) && isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $role_query = "SELECT role FROM users WHERE username = ?";
    $stmt = $db->prepare($role_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user) {
        $_SESSION['role'] = $user['role'];
    }
    $stmt->close();
}

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    $_SESSION['msg'] = "You must be an admin to view donations.";
    header('location: login.php');
    exit;
}

$sql = "SELECT d.*, u.username 
        FROM donations d 
        LEFT JOIN users u ON d.user_id = u.id 
        ORDER BY d.created_at DESC";

$result = $db->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>View Donations | Hope Foundation</title>
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

        .donation-card {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        footer a:hover {
            text-decoration: none;
            color: #ffc107;
            width: 100%;
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
            <h2>View All Donations</h2>
            <?php include('errors.php'); ?>

            <!-- Filter Buttons -->
            <div class="btn-group mb-4" role="group" aria-label="Filter Donations">
                <button type="button" class="btn btn-primary" onclick="showDonations('cash')">Cash</button>
                <button type="button" class="btn btn-danger" onclick="showDonations('blood')">Blood</button>
                <button type="button" class="btn btn-warning" onclick="showDonations('food')">Food/Shelter</button>
                <button type="button" class="btn btn-secondary" onclick="showDonations('all')">Show All</button>
            </div>

            <?php
            $cashDonations = [];
            $bloodDonations = [];
            $foodDonations = [];

            while ($row = $result->fetch_assoc()) {
                switch (strtolower($row['type'])) {
                    case 'cash':
                        $cashDonations[] = $row;
                        break;
                    case 'blood':
                        $bloodDonations[] = $row;
                        break;
                    case 'food':
                    case 'shelter':
                    case 'food/shelter':
                        $foodDonations[] = $row;
                        break;
                }
            }
            ?>

            <div class="card mb-3">
                <div class="card-body">
                    <h5>Summary</h5>
                    <p>Total Cash Donations: <?= count($cashDonations) ?></p>
                    <p>Total Blood Donations: <?= count($bloodDonations) ?></p>
                    <p>Total Food/Shelter Donations: <?= count($foodDonations) ?></p>
                    <a href="viewall.php" class="btn btn-warning"
                        style="background-color: purple; color: white;">Donation
                        Status</a></button>
                </div>
            </div>
        </div>
        <!-- Cash Donations Card -->
        <?php if (count($cashDonations) > 0): ?>
            <div id="cash-section" class="card mb-4">
                <div class="card-header bg-success text-white">💵 Cash Donations</div>
                <div class="card-body">
                    <?php foreach ($cashDonations as $row): ?>
                        <div class="donation-card">
                            <p><strong>Username:</strong> <?= htmlspecialchars($row['username']) ?></p>
                            <p><strong>Amount:</strong> $<?= number_format($row['amount'], 2) ?></p>
                            <p><strong>Full Name:</strong> <?= htmlspecialchars($row['full_name']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
                            <p><strong>Phone:</strong> <?= htmlspecialchars($row['phone']) ?></p>
                            <p><strong>Date:</strong> <?= $row['created_at'] ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Blood Donations Card -->
        <?php if (count($bloodDonations) > 0): ?>
            <div id="blood-section" class="card mb-4">
                <div class="card-header bg-danger text-white">🩸 Blood Donations</div>
                <div class="card-body">
                    <?php foreach ($bloodDonations as $row): ?>
                        <div class="donation-card">
                            <p><strong>Username:</strong>
                                <?= isset($row['username']) ? htmlspecialchars($row['username']) : 'N/A' ?></p>
                            <p><strong>Blood Type:</strong>
                                <?= isset($row['blood_type']) && trim($row['blood_type']) !== '' ? htmlspecialchars($row['blood_type']) : 'N/A' ?>
                            </p>
                            <p><strong>Liters Donated:</strong>
                                <?= isset($row['blood_liters']) && trim($row['blood_liters']) !== '' ? htmlspecialchars($row['blood_liters']) : 'N/A' ?>
                            </p>
                            <p><strong>Full Name:</strong>
                                <?= isset($row['full_name']) ? htmlspecialchars($row['full_name']) : 'N/A' ?></p>
                            <p><strong>Email:</strong> <?= isset($row['email']) ? htmlspecialchars($row['email']) : 'N/A' ?>
                            </p>
                            <p><strong>Phone:</strong> <?= isset($row['phone']) ? htmlspecialchars($row['phone']) : 'N/A' ?>
                            </p>
                            <p><strong>Date:</strong> <?= isset($row['created_at']) ? $row['created_at'] : 'N/A' ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Food/Shelter Donations Card -->
        <?php if (count($foodDonations) > 0): ?>
            <div id="food-section" class="card mb-4">
                <div class="card-header bg-warning text-dark">🥫 Food / 🏠 Shelter Donations</div>
                <div class="card-body">
                    <?php foreach ($foodDonations as $row): ?>
                        <div class="donation-card">
                            <p><strong>Username:</strong>
                                <?= isset($row['username']) ? htmlspecialchars($row['username']) : 'N/A' ?></p>
                            <p><strong>Items Donated:</strong>
                                <?= isset($row['food_description']) && trim($row['food_description']) !== '' ? htmlspecialchars($row['food_description']) : 'N/A' ?>
                            </p>
                            <p><strong>Full Name:</strong>
                                <?= isset($row['full_name']) ? htmlspecialchars($row['full_name']) : 'N/A' ?></p>
                            <p><strong>Email:</strong> <?= isset($row['email']) ? htmlspecialchars($row['email']) : 'N/A' ?>
                            </p>
                            <p><strong>Phone:</strong> <?= isset($row['phone']) ? htmlspecialchars($row['phone']) : 'N/A' ?>
                            </p>
                            <p><strong>Date:</strong> <?= isset($row['created_at']) ? $row['created_at'] : 'N/A' ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>


        <a href="index.php" style="background-color:purple" class="btn btn-secondary mt-3">Back to Home</a>

    </div>
    <button id="backToTop" title="Go to top"><i class="fas fa-arrow-up"></i></button>


    <!-- JavaScript to Filter Sections -->
    <script>
        function showDonations(type) {
            const sections = {
                cash: document.getElementById('cash-section'),
                blood: document.getElementById('blood-section'),
                food: document.getElementById('food-section')
            };

            if (type === 'all') {
                for (let key in sections) {
                    if (sections[key]) sections[key].style.display = 'block';
                }
            } else {
                for (let key in sections) {
                    if (sections[key]) sections[key].style.display = (key === type ? 'block' : 'none');
                }
            }
        }

        // Show all sections by default
        window.onload = () => showDonations('all');

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
</body>

</html>

<?php $db->close(); ?>