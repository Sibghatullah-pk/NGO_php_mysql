<?php include('server.php'); ?>
<!DOCTYPE html>
<html>

<head>
	<title>Sign Up Student | NIC System</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
	<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
	<style>
		body {
			padding-top: 70px;
			background-color: #f9f9f9;
		}

		.container {
			max-width: 800px;
			background: #fff;
			padding: 40px;
			border-radius: 10px;
			box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
		}

		.form-control {
			border-radius: 10px;
		}

		.form-header {
			text-align: center;
			margin-bottom: 30px;
		}

		.profile-icon {
			font-size: 80px;
			color: rgb(118, 179, 245);
			margin: 0 auto 20px;
			display: flex;
			justify-content: center;
		}

		.btn-primary {
			background: rgb(97, 162, 232);
			border: none;
			border-radius: 8px;
			font-weight: bold;
			padding: 12px;
		}

		.btn-primary:hover {
			background: rgb(104, 163, 226);
		}

		.login-link {
			text-align: center;
			margin-top: 15px;
		}

		.login-link a {
			color: rgb(97, 162, 232);
			text-decoration: none;
		}

		.login-link a:hover {
			text-decoration: underline;
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
				<div class="navbar-header" style="margin-right:45vw; margin-left: 2vw;">
					<a class="navbar-brand" href="#">NGO MANAGEMENT SYSTEM</a>
				</div>
				<ul class="navbar-nav">
					<?php if (isset($_SESSION['username'])): ?>
						<li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
						<li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
						<li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
						<li class="nav-item"><a class="nav-link" href="index.php?logout='1'">Logout</a></li>
					<?php else: ?>
						<li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
						<li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
					<?php endif; ?>
				</ul>
			</div>
		</nav>
	</div>

	<div class="container mt-5">
		<div class="profile-icon">
			<i class="fas fa-user-circle"></i>
		</div>
		<div class="form-header">
			<h4>Sign up your account</h4>
		</div>

		<form method="post" action="register.php" enctype="multipart/form-data">
			<?php include('errors.php'); ?>
			<div class="row">
				<div class="col-md-6 form-group">
					<input type="text" class="form-control" name="first_name" placeholder="First Name" required>
				</div>
				<div class="col-md-6 form-group">
					<input type="text" class="form-control" name="last_name" placeholder="Last Name" required>
				</div>
				<div class="col-md-6 form-group">
					<input type="text" class="form-control" name="username" placeholder="Username" required>
				</div>
				<div class="col-md-6 form-group">
					<input type="email" class="form-control" name="email" placeholder="Email" required>
				</div>

				<div class="col-md-6 form-group">
					<input type="password" class="form-control" name="password_1" placeholder="Password" required>
				</div>
				<div class="col-md-6 form-group">
					<input type="password" class="form-control" name="password_2" placeholder="Confirm Password"
						required>
				</div>
			</div>

			<button type="submit" class="btn btn-primary btn-block mt-3" name="reg_user">
				<i class="fas fa-user-plus mr-2"></i> SIGN UP
			</button>
			<div class="login-link">
				Already have an account? <a href="login.php">Login</a>
			</div>
		</form>

	</div>
	<!-- Footer Section -->
	<footer class="bg-dark text-black mt-5 p-4 text-center ">
		<div class="container">
			<div class="row">
				<!-- Social Media -->
				<div class="col-md-6 mb-3 mb-md-0">
					<h5>Follow Us</h5>
					<a href="https://www.facebook.com" class="text-black mx-2" target="_blank"><i
							class="fab fa-facebook fa-lg"></i></a>
					<a href="https://www.twitter.com" class="text-black mx-2" target="_blank"><i
							class="fab fa-twitter fa-lg"></i></a>
					<a href="https://www.instagram.com" class="text-black mx-2" target="_blank"><i
							class="fab fa-instagram fa-lg"></i></a>
					<a href="https://www.youtube.com" class="text-black mx-2" target="_blank"><i
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