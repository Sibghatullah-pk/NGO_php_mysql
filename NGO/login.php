<?php include('server.php'); ?>
<!DOCTYPE html>
<html>

<head>
	<title>Login | OIC System</title>
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
			max-width: 450px;
			background: #fff;
			padding: 30px;
			border-radius: 10px;
			box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
		}

		.form-header {
			text-align: center;
			margin-bottom: 30px;
		}

		.form-group input[type="password"] {
			padding-right: 40px;
		}

		.fa-eye,
		.fa-eye-slash {
			position: absolute;
			top: 50%;
			right: 15px;
			transform: translateY(-50%);
			cursor: pointer;
			color: #888;
			font-size: 1.1rem;
			z-index: 10;
		}

		.btn-login {
			background-color: rgb(97, 162, 232);
			color: white;
			border-radius: 10px;
			font-weight: bold;
			width: 100%;
			padding: 10px;
			border: none;
		}

		.btn-login:hover {
			background-color: rgb(77, 142, 212);
		}

		.login-link {
			text-align: center;
			margin-top: 15px;
		}

		.login-link a {
			color: #007bff;
			text-decoration: none;
		}

		.login-link a:hover {
			text-decoration: underline;
		}
	</style>
</head>

<body>
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

	<div class="container mt-5">
		<div class="form-header">
			<h4>Login to your account</h4>
		</div>

		<form method="post" action="login.php">
			<?php include('errors.php'); ?>
			<div class="form-group">
				<label>Email</label>
				<input type="text" class="form-control" name="username" placeholder="Enter email or username" required>
			</div>
			<div class="form-group">
				<label>Password</label>
				<div style="position: relative;">
					<input type="password" class="form-control" name="password" id="password" required>
					<i class="fas fa-eye" id="togglePassword" onclick="togglePassword()"></i>
				</div>
			</div>

			<button type="submit" class="btn-login mt-3" name="login_user">
				<i class="fas fa-sign-in-alt mr-2"></i> LOGIN
			</button>

			<div class="login-link">
				Don't have an account? <a href="register.php">Sign up</a>
			</div>
		</form>
	</div>

	<script>
		function togglePassword() {
			const pass = document.getElementById('password');
			const icon = document.getElementById('togglePassword');
			if (pass.type === 'password') {
				pass.type = 'text';
				icon.classList.replace('fa-eye', 'fa-eye-slash');
			} else {
				pass.type = 'password';
				icon.classList.replace('fa-eye-slash', 'fa-eye');
			}
		}
	</script>
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