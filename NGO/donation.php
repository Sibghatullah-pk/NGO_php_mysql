<?php
// session_start();
require_once 'server.php'; // DB connection & session start assumed

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user role if not already set
if (!isset($_SESSION['role'])) {
    $username = $_SESSION['username'];
    $role_query = "SELECT role FROM users WHERE username = ?";
    $stmt = $db->prepare($role_query);
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $_SESSION['role'] = $user ? $user['role'] : 'user'; // Default to 'user' if not found
        $stmt->close();
    } else {
        error_log("Prepare failed for role query: " . $db->error, 3, "error.log");
        $_SESSION['role'] = 'user'; // Fallback
    }
}

$errors = [];
$success = '';

// Handle donation submission (only for non-admin users)
if (isset($_POST['submit']) && $_SESSION['role'] !== 'admin') {
    $user_id = $_SESSION['user_id'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $donation_type = trim($_POST['donation_type']);

    // Initialize all optional fields as null
    $amount = 0;
    $blood_type = null;
    $blood_liters = null;
    $food_description = null;

    // Validate common fields
    if (empty($full_name))
        $errors[] = "Full name is required";
    if (empty($email))
        $errors[] = "Email is required";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = "Email is invalid";
    if (empty($phone))
        $errors[] = "Phone number is required";
    if (empty($donation_type))
        $errors[] = "Donation type is required";

    // Validate and assign based on donation type
    if ($donation_type === 'cash') {
        if (empty($_POST['cash_amount']) || floatval($_POST['cash_amount']) <= 0) {
            $errors[] = "Please enter a valid cash amount.";
        } else {
            $amount = floatval($_POST['cash_amount']);
        }
    } elseif ($donation_type === 'blood') {
        if (empty($_POST['blood_liters']) || floatval($_POST['blood_liters']) <= 0) {
            $errors[] = "Please enter valid liters of blood.";
        } else {
            $blood_liters = floatval($_POST['blood_liters']);
        }
        $blood_type = !empty($_POST['blood_type']) ? $_POST['blood_type'] : null;
    } elseif ($donation_type === 'food') {
        if (empty(trim($_POST['food_description']))) {
            $errors[] = "Please enter details for food/shelter donation.";
        } else {
            $food_description = trim($_POST['food_description']);
        }
    } else {
        $errors[] = "Invalid donation type selected.";
    }

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO donations (user_id, amount, type, blood_type, blood_liters, food_description, full_name, email, phone, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        if ($stmt === false) {
            error_log("Prepare failed for donation insert: " . $db->error, 3, "error.log");
            die("Prepare failed: (" . $db->errno . ") " . $db->error);
        }
        $stmt->bind_param("idsssisss", $user_id, $amount, $donation_type, $blood_type, $blood_liters, $food_description, $full_name, $email, $phone);
        if ($stmt->execute()) {
            $success = "Donation recorded successfully!";
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle status update (for admins)
$update_errors = [];
$update_success = '';
if (isset($_POST['update_status']) && $_SESSION['role'] === 'admin') {
    $donation_id = intval($_POST['donation_id']);
    $new_status = trim($_POST['new_status']);

    // Validate new status
    $valid_statuses = ['pending', 'approved', 'rejected', 'completed'];
    if (!in_array($new_status, $valid_statuses)) {
        $update_errors[] = "Invalid status selected.";
    } else {
        $stmt = $db->prepare("UPDATE donations SET status = ? WHERE id = ?");
        if ($stmt === false) {
            error_log("Prepare failed for status update: " . $db->error, 3, "error.log");
            die("Prepare failed for update: (" . $db->errno . ") " . $db->error);
        }
        $stmt->bind_param("si", $new_status, $donation_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $update_success = "Status updated successfully!";
            } else {
                $update_errors[] = "No donation found with ID: $donation_id.";
                error_log("No donation found with ID: $donation_id", 3, "error.log");
            }
        } else {
            $update_errors[] = "Error updating status: " . $stmt->error;
            error_log("Error updating status: " . $stmt->error, 3, "error.log");
        }
        $stmt->close();
    }
}

// Fetch donation history: Admins see all donations, users see only their own
if ($_SESSION['role'] === 'admin') {
    $stmt2 = $db->prepare("SELECT d.*, u.username FROM donations d JOIN users u ON d.user_id = u.id ORDER BY d.created_at DESC");
    if ($stmt2 === false) {
        error_log("Prepare failed for admin donation history: " . $db->error, 3, "error.log");
        die("Prepare failed for admin donation history: (" . $db->errno . ") " . $db->error);
    }
} else {
    $stmt2 = $db->prepare("SELECT d.*, u.username FROM donations d JOIN users u ON d.user_id = u.id WHERE d.user_id = ? ORDER BY d.created_at DESC");
    if ($stmt2 === false) {
        error_log("Prepare failed for user donation history: " . $db->error, 3, "error.log");
        die("Prepare failed for user donation history: (" . $db->errno . ") " . $db->error);
    }
    $stmt2->bind_param("i", $_SESSION['user_id']);
}
$stmt2->execute();
$result = $stmt2->get_result();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Donation | Hope Foundation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <style>
        body {
            padding-top: 70px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .content {
            padding: 20px;
        }

        .btn-custom {
            background: #3e2093;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .btn-custom:hover {
            background: #5029bc;
            transform: scale(1.05);
        }

        .btn-update {
            background: #28a745;
            color: white;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .btn-update:hover {
            background: #218838;
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

        .status-form {
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-form select {
            max-width: 150px;
        }

        .card {
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Fade-in animation for cards */
        .card {
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <script>
        $(document).ready(function () {
            $('#donation_type').on('change', function () {
                var type = $(this).val();
                $('#cash-fields, #blood-fields, #food-fields').hide();

                if (type === 'cash') {
                    $('#cash-fields').show();
                } else if (type === 'blood') {
                    $('#blood-fields').show();
                } else if (type === 'food') {
                    $('#food-fields').show();
                }
            });
        });
    </script>
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
                        About</a></li>
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
        <?php if ($_SESSION['role'] !== 'admin'): ?>
            <h2>Make a Donation</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <p><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="form-group">
                    <label for="donation_type">Donation Type</label>
                    <select name="donation_type" id="donation_type" class="form-control" required>
                        <option value="">-- Select Donation Type --</option>
                        <option value="cash">Cash</option>
                        <option value="blood">Blood</option>
                        <option value="food">Food/Shelter</option>
                    </select>
                </div>

                <div class="form-group" id="cash-fields" style="display:none;">
                    <label>Amount ($)</label>
                    <input type="number" name="cash_amount" class="form-control" step="0.01" min="0.01"
                        placeholder="Enter amount">
                </div>

                <div id="blood-fields" style="display:none;">
                    <div class="form-group">
                        <label>Liters of Blood</label>
                        <input type="number" name="blood_liters" class="form-control" step="0.1" min="0.1"
                            placeholder="Enter liters">
                    </div>
                    <div class="form-group">
                        <label>Blood Type</label>
                        <select name="blood_type" class="form-control">
                            <option value="">-- Select Blood Type --</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="food-fields" style="display:none;">
                    <label>Details (Food or Shelter)</label>
                    <input type="text" name="food_description" class="form-control"
                        placeholder="e.g. 20 Food Boxes or 1 Tent">
                </div>

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" class="form-control" required>
                </div>

                <button type="submit" name="submit" class="btn btn-custom">Donate Now</button>
            </form>

            <a href="index.php" class="btn btn-secondary mt-3">Back to Home</a>
        <?php endif; ?>

        <h2 class="mt-5"><?php echo $_SESSION['role'] === 'admin' ? 'All Donations' : 'Your Donation History'; ?></h2>

        <?php if (!empty($update_errors)): ?>
            <div class="alert alert-danger mt-3">
                <?php foreach ($update_errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($update_success): ?>
            <div class="alert alert-success mt-3">
                <p><?php echo htmlspecialchars($update_success); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo ucfirst(htmlspecialchars($row['type'])); ?> Donation</h5>
                        <p class="card-text">
                            <?php
                            if ($row['type'] == 'cash') {
                                echo "Amount: $" . htmlspecialchars($row['amount']);
                            } elseif ($row['type'] == 'blood') {
                                echo "Liters: " . htmlspecialchars($row['blood_liters']) . " | Blood Type: " . htmlspecialchars($row['blood_type']);
                            } elseif ($row['type'] == 'food') {
                                echo "Details: " . htmlspecialchars($row['food_description']);
                            }
                            ?>
                        </p>
                        <p class="card-text">Donated by: <?php echo htmlspecialchars($row['username']); ?> (ID:
                            <?php echo htmlspecialchars($row['user_id']); ?>)</p>
                        <p class="card-text">Status: <?php echo htmlspecialchars($row['status']); ?></p>
                        <p class="card-text"><small class="text-muted">Donated on
                                <?php echo htmlspecialchars($row['created_at']); ?></small></p>

                        <!-- Admin Controls for Status Update -->
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <form method="post" action="" class="status-form">
                                <input type="hidden" name="donation_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <label for="new_status_<?php echo $row['id']; ?>">Update Status:</label>
                                <select name="new_status" id="new_status_<?php echo $row['id']; ?>" class="form-control">
                                    <option value="pending" <?php echo $row['status'] === 'pending' ? 'selected' : ''; ?>>Pending
                                    </option>
                                    <option value="approved" <?php echo $row['status'] === 'approved' ? 'selected' : ''; ?>>Approved
                                    </option>
                                    <option value="rejected" <?php echo $row['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected
                                    </option>
                                    <option value="completed" <?php echo $row['status'] === 'completed' ? 'selected' : ''; ?>>
                                        Completed</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-update">Update</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No donations found.</p>
        <?php endif; ?>
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
                    <p class="mb-0">© <?php echo date("Y"); ?> Hope Foundation. All rights reserved.</p>
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
<?php $stmt2->close(); ?>