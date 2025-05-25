<?php
// session_start();

// Include server.php to get database connection
include('server.php');

// Fetch donation summary
$stmt = $db->prepare("SELECT COUNT(*) as count, SUM(amount) as total FROM donations");
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();

// Fetch role if not set
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

// Handle bulk approve action
if (isset($_POST['bulk_approve']) && $_SESSION['role'] === 'admin') {
    $stmt = $db->prepare("UPDATE donations SET status = 'approved' WHERE status != 'approved'");
    if ($stmt === false) {
        error_log("Prepare failed for bulk approve: " . $db->error, 3, "error.log");
    } else {
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = "All pending donations approved successfully!";
        } else {
            $_SESSION['msg'] = "No pending donations to approve.";
        }
        $stmt->close();
    }
    header('location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle bulk complete action
if (isset($_POST['bulk_complete']) && $_SESSION['role'] === 'admin') {
    $stmt = $db->prepare("UPDATE donations SET status = 'completed' WHERE status = 'approved'");
    if ($stmt === false) {
        error_log("Prepare failed for bulk complete: " . $db->error, 3, "error.log");
    } else {
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = "All approved donations marked as completed!";
        } else {
            $_SESSION['msg'] = "No approved donations to complete.";
        }
        $stmt->close();
    }
    header('location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle individual status update
if (isset($_POST['update_status']) && isset($_POST['donation_id']) && $_SESSION['role'] === 'admin') {
    $donation_id = intval($_POST['donation_id']);
    $new_status = trim($_POST['new_status']);
    $valid_statuses = ['pending', 'approved', 'rejected', 'completed'];

    if (!in_array($new_status, $valid_statuses)) {
        $_SESSION['msg'] = "Invalid status selected.";
    } else {
        $stmt = $db->prepare("UPDATE donations SET status = ? WHERE id = ?");
        if ($stmt === false) {
            error_log("Prepare failed for status update: " . $db->error, 3, "error.log");
        } else {
            $stmt->bind_param("si", $new_status, $donation_id);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $_SESSION['success'] = "Donation status updated to '$new_status'!";
            } else {
                $_SESSION['msg'] = "Donation not found.";
            }
            $stmt->close();
        }
    }
    header('location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle delete action
if (isset($_POST['delete']) && isset($_POST['donation_id']) && $_SESSION['role'] === 'admin') {
    $donation_id = intval($_POST['donation_id']);
    $stmt = $db->prepare("DELETE FROM donations WHERE id = ?");
    if ($stmt === false) {
        error_log("Prepare failed for delete: " . $db->error, 3, "error.log");
    } else {
        $stmt->bind_param("i", $donation_id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = "Donation deleted successfully!";
        } else {
            $_SESSION['msg'] = "Donation not found.";
        }
        $stmt->close();
    }
    header('location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch all donations
$sql = "SELECT d.*, u.username 
        FROM donations d 
        LEFT JOIN users u ON d.user_id = u.id 
        ORDER BY d.created_at DESC";
$result = $db->query($sql);

// Categorize donations
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

<!DOCTYPE html>
<html lang="en">

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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
        }

        .content {
            padding: 20px;
        }

        .dashboard-card {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease-in-out;
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

        .table-responsive {
            margin-top: 20px;
        }

        .table thead th {
            background-color: #3e2093;
            color: white;
        }

        .btn-custom {
            background-color: #3e2093;
            color: white;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            background-color: #5029bc;
            transform: translateY(-2px);
        }

        .btn-danger,
        .btn-success {
            transition: all 0.3s ease;
        }

        .btn-danger:hover,
        .btn-success:hover {
            transform: translateY(-2px);
        }

        .filter-dropdown {
            margin-bottom: 15px;
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
            transition: all 0.3s ease;
        }

        #backToTop:hover {
            background-color: #0056b3;
            transform: scale(1.1);
        }

        .loading {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2000;
        }

        .loading.active {
            display: block;
        }

        footer a:hover {
            text-decoration: none;
            color: #ffc107;
        }

        .status-form select {
            max-width: 120px;
        }

        .status-form {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        @media (max-width: 768px) {
            .table {
                font-size: 14px;
            }

            .d-flex {
                flex-direction: column;
                gap: 10px !important;
            }

            .status-form {
                flex-direction: column;
                align-items: flex-start;
            }

            .status-form select,
            .status-form button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-sm bg-info navbar-dark fixed-top">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="collapsibleNavbar">
            <div class="navbar-header d-flex align-items-center" style="margin-right:auto; margin-left: 2vw;">
                <i class="fas fa-hand-holding-heart fa-lg text-white mr-2"></i>
                <a class="navbar-brand" href="index.php">NGO MANAGEMENT SYSTEM</a>
            </div>
            <ul class="navbar-nav ml-auto d-flex align-items-center">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home mr-1"></i> Home</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php"><i class="fas fa-info-circle mr-1"></i>
                        About</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="contact.php"><i class="fas fa-envelope mr-1"></i>
                        Contact</a></li>
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle mr-1"></i>
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="index.php?logout='1'"><i class="fas fa-sign-out-alt mr-2"></i>
                                Logout</a>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container content">
        <h2 class="text-primary">Admin Donation Dashboard
            <small class="text-muted">Last Updated: <?= date('Y-m-d H:i:s') ?></small>
        </h2>

        <?php if (isset($_SESSION['msg'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['msg']) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Admin Actions -->
        <div class="mb-4 d-flex gap-2 flex-wrap align-items-center">
            <form style="margin-right: 10px; method=" post" style="display:inline;">
                <button type="submit" name="bulk_approve" class="btn btn-success">Approve All Pending</button>
            </form>
            <form style="margin-right: 10px; method=" post" style="display:inline;">
                <button type="submit" name="bulk_complete" class="btn btn-primary">Complete All Approved</button>
            </form>
            <button style="margin-right: 10px; type=" button" class="btn btn-info"
                onclick="window.location.reload();">Refresh</button>
            <div class="input-group" style="max-width: 300px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by username or email..."
                    aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" onclick="filterTable()">Search</button>
                </div>
            </div>
        </div>

        <!-- Summary Dashboard -->
        <div class="dashboard-card">
            <h5>Donation Summary</h5>
            <p>Total Donations: <?= $summary['count'] ?></p>
            <p>Total Cash Amount: $<?= number_format($summary['total'], 2) ?></p>
            <div class="progress mt-2">
                <div class="progress-bar bg-success" role="progressbar"
                    style="width: <?= ($summary['total'] / ($summary['total'] + 1) * 100) ?>%"
                    aria-valuenow="<?= $summary['total'] ?>" aria-valuemin="0" aria-valuemax="100">
                    <?= number_format($summary['total'], 2) ?>
                </div>
            </div>
        </div>

        <!-- Filter Dropdown -->
        <div class="filter-dropdown mb-3">
            <select id="filterStatus" class="form-control" onchange="filterTable()">
                <option value="all">Filter by Status: All</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="completed">Completed</option>
            </select>
        </div>

        <!-- Donations Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Type</th>
                        <th>Details</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="donationsTable">
                    <?php
                    $result->data_seek(0); // Reset pointer
                    while ($row = $result->fetch_assoc()) {
                        $details = '';
                        if ($row['type'] === 'cash') {
                            $details = '$' . number_format($row['amount'], 2);
                        } elseif ($row['type'] === 'blood') {
                            $details = "Type: {$row['blood_type']}, Liters: {$row['blood_liters']}";
                        } elseif (in_array($row['type'], ['food', 'shelter', 'food/shelter'])) {
                            $details = $row['food_description'];
                        }
                        echo "<tr class='donation-row' data-status='" . htmlspecialchars($row['status']) . "' data-search='" . htmlspecialchars(strtolower($row['username'] . ' ' . $row['email'])) . "'>
                            <td>" . htmlspecialchars($row['id']) . "</td>
                            <td>" . (isset($row['username']) ? htmlspecialchars($row['username']) : 'N/A') . "</td>
                            <td>" . htmlspecialchars(ucfirst($row['type'])) . "</td>
                            <td>" . htmlspecialchars($details) . "</td>
                            <td>" . htmlspecialchars($row['full_name']) . "</td>
                            <td>" . htmlspecialchars($row['email']) . "</td>
                            <td>" . htmlspecialchars($row['phone']) . "</td>
                            <td>" . htmlspecialchars($row['status']) . "</td>
                            <td>" . htmlspecialchars($row['created_at']) . "</td>
                            <td>
                                <div class='d-flex gap-2'>
                                    <form method='post' class='status-form' style='display:inline;'>
                                        <input type='hidden' name='donation_id' value='" . htmlspecialchars($row['id']) . "'>
                                        <select name='new_status' class='form-control'>
                                            <option value='pending' " . ($row['status'] === 'pending' ? 'selected' : '') . ">Pending</option>
                                            <option value='approved' " . ($row['status'] === 'approved' ? 'selected' : '') . ">Approved</option>
                                            <option value='rejected' " . ($row['status'] === 'rejected' ? 'selected' : '') . ">Rejected</option>
                                            <option value='completed' " . ($row['status'] === 'completed' ? 'selected' : '') . ">Completed</option>
                                        </select>
                                        <button type='submit' name='update_status' class='btn btn-success btn-sm'>Update</button>
                                        <form method='post' style='display:inline;' onsubmit='return confirm(\"Are you sure?\");'>
                                        <input type='hidden' name='donation_id' value='" . htmlspecialchars($row['id']) . "'>
                                        <button type='submit' name='delete' class='btn btn-danger btn-sm'>Delete</button>
                                    </form>
                                    </form>
                                    
                                </div>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <a href="index.php" class="btn btn-custom mt-3">Back to Home</a>
    </div>

    <button id="backToTop" title="Go to top"><i class="fas fa-arrow-up"></i></button>
    <div class="loading" id="loadingSpinner">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <script>
        // Filter and Search Functionality
        function filterTable() {
            const status = document.getElementById('filterStatus').value;
            const search = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#donationsTable .donation-row');

            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                const rowSearch = row.getAttribute('data-search');
                const showByStatus = status === 'all' || rowStatus === status;
                const showBySearch = !search || rowSearch.includes(search);
                row.style.display = showByStatus && showBySearch ? '' : 'none';
            });
        }

        // Show loading spinner (simulated)
        function showLoading() {
            document.getElementById('loadingSpinner').classList.add('active');
            setTimeout(() => {
                document.getElementById('loadingSpinner').classList.remove('active');
            }, 1000); // Simulate 1-second load
        }

        // Refresh with loading effect
        document.querySelector('.btn-info').addEventListener('click', () => {
            showLoading();
            setTimeout(() => location.reload(), 500);
        });

        // Initial filter
        window.onload = () => filterTable();

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
                <div class="col-md-6">
                    <p class="mb-0">© 2025 Hope Foundation. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>

<?php $db->close(); ?>