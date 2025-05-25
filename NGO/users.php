<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
}

// Connect to the database
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'project';

$db = new mysqli($host, $user, $pass, $dbname);

if ($db->connect_error) {
    error_log("Connection failed: " . $db->connect_error, 3, "error.log");
    die("Database connection failed. Please try again later.");
}
$per_page = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $per_page;
$stmt = $db->prepare("SELECT id, username, email FROM users LIMIT ?, ?");
$stmt->bind_param("ii", $start, $per_page);
$stmt->execute();
$result = $stmt->get_result();
$total = $db->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$pages = ceil($total / $per_page);
// Initialize errors array
$errors = [];

// Check user role for admin privileges
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

// Handle user deletion
if ($is_admin && isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM users WHERE id = ?";
    $stmt = $db->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "User deleted successfully!";
        header('location: users.php');
    } else {
        $errors[] = "Failed to delete user.";
    }
    $stmt->close();
}

// Fetch users
$sql = "SELECT id, username, email FROM users";
$result = $db->query($sql);

if (!$result) {
    $errors[] = "Error fetching users: " . $db->error;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>User List | Hope Foundation</title>
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

        .user-card {
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .alert {
            margin-bottom: 20px;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .btn-delete:hover {
            background: #c82333;
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
            <h2>User List</h2>
            <?php include('errors.php'); ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <p><?php echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']); ?></p>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <?php if ($is_admin): ?>
                                    <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row["id"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["username"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["email"]); ?></td>
                                    <?php if ($is_admin): ?>
                                        <td>
                                            <a href="users.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-delete btn-sm"
                                                onclick="return confirm('Are you sure you want to delete this user?')">
                                                Delete
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>
            <a href="index.php" class="btn btn-primary mt-3">Back to Home</a>
        </div>
        <nav>
            <!-- <ul class="pagination">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="users.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul> -->
        </nav>
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