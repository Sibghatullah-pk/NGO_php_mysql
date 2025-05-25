<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
	$_SESSION['msg'] = "You must log in first";
	header('location: login.php');
	exit();
}
if (isset($_GET['logout'])) {
	session_destroy();
	unset($_SESSION['username']);
	header("location: login.php");
	exit();
}

// Include DB and fetch role
// include('server.php');

if (!isset($_SESSION['role'])) {
	$username = $_SESSION['username'];
	$role_query = "SELECT role FROM users WHERE username = ?";
	$stmt = $db->prepare($role_query);
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$result = $stmt->get_result();
	$user = $result->fetch_assoc();
	$_SESSION['role'] = $user['role'];
	$stmt->close();
}
?>

<!DOCTYPE html>
<html>

<head>
	<title>Home | Hope Foundation</title>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Bootstrap & Font Awesome -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

	<!-- In <head> -->
	<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
	<link rel="stylesheet" type="text/css"
		href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />

	<!-- Before </body> -->
	<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">


	<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>

	<style>
		body {
			padding-top: 70px;
			background-color: #f8f9fa;
		}

		.hero-section {
			background: url('https://images.pexels.com/photos/2418826/pexels-photo-2418826.jpeg?auto=compress&cs=tinysrgb&w=600&auto=format&fit=crop&w=1350&q=80') no-repeat center center;
			background-size: cover;
			color: white;
			padding: 100px 20px;
			text-align: center;
			border-radius: 10px;
			margin-bottom: 20px;
		}

		.hero-section h1,
		.hero-section p {
			text-shadow: 1px 1px 4px #000;
		}

		.btn-custom {
			background: #3e2093;
			color: white;
			padding: 10px 20px;
			margin: 5px;
			border-radius: 5px;
			transition: transform 0.3s, background 0.3s;
		}

		.btn-custom:hover {
			background: #5029bc;
			transform: scale(1.05);
			color: white;
		}

		.progress {
			background-color: #d6d6d6;
			border-radius: 15px;
			overflow: hidden;
			box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2);
		}

		.progress-bar {
			font-size: 1rem;
			text-align: center;
			line-height: 25px;
			transition: width 1s ease-in-out;
		}

		.card img {
			height: 200px;
			object-fit: cover;
		}

		.hero-bg-slider {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			z-index: 1;
			animation: bgSlideshow 12s infinite;
			background-size: cover;
			background-position: center;
			opacity: 0.6;
			transition: background-image 1s ease-in-out;
			border-radius: 10px;
		}

		@keyframes bgSlideshow {
			0% {
				background-image: url('images/bg1.jpg');
			}

			33% {
				background-image: url('images/bg2.jpg');
			}

			66% {
				background-image: url('images/bg3.jpg');
			}

			100% {
				background-image: url('images/bg1.jpg');
			}
		}

		/* Banner wrapper (acts as a viewport) */
		.scrolling-banner-wrapper {
			width: 100%;
			overflow: hidden;
			height: 40px;
			position: relative;
		}

		/* Moving banner */
		.scrolling-banner {
			display: inline-block;
			white-space: nowrap;
			padding: 10px 0;
			background-color: #dc3545;
			color: white;
			font-weight: bold;
			font-size: 16px;
			animation: scrollBannerLinear 15s linear infinite;
			padding-left: 100%;
			/* Start off-screen */
		}

		/* Scroll Animation (continuous) */
		@keyframes scrollBannerLinear {
			0% {
				transform: translateX(0%);
			}

			100% {
				transform: translateX(-100%);
			}
		}

		.card-img-top {
			height: 200px;
			object-fit: cover;
			border-bottom: 3px solid #007bff;
		}

		.card {
			transition: transform 0.3s;
		}

		.card:hover {
			transform: translateY(-5px);
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

<body class="container-fluid">
	<!-- Navbar -->
	<nav class="navbar navbar-expand-sm bg-info navbar-dark fixed-top">
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="collapsibleNavbar">
			<div class="navbar-header d-flex align-items-center mr-auto ml-3">
				<i class="fas fa-hand-holding-heart fa-lg text-white mr-2"></i>
				<a class="navbar-brand" href="index.php">NGO MANAGEMENT SYSTEM</a>
			</div>

			<ul class="navbar-nav ml-auto d-flex align-items-center">
				<li class="nav-item">
					<a class="nav-link" href="index.php"><i class="fas fa-home mr-1"></i> Home</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="about.php"><i class="fas fa-info-circle mr-1"></i> About</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="contact.php"><i class="fas fa-envelope mr-1"></i> Contact</a>
				</li>
				<?php if (isset($_SESSION['username'])): ?>
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
							<i class="fas fa-user-circle mr-1"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
						</a>
						<div class="dropdown-menu dropdown-menu-right">
							<a class="dropdown-item" href="index.php?logout=1">
								<i class="fas fa-sign-out-alt mr-2"></i> Logout
							</a>
						</div>
					</li>
				<?php endif; ?>
			</ul>
		</div>
	</nav>

	<!-- Continuous Moving Event Banner -->
	<div class="scrolling-banner-wrapper">
		<div class="scrolling-banner">
			📢 Upcoming Event: Blood Donation Camp – 25th May at City Hall! Join us & Save Lives ❤️..
		</div>
	</div>



	<br>
	<br>
	<!-- Hero Section -->
	<div class="container position-relative">
		<div class="hero-bg-slider"></div>

		<div class="hero-section animate__animated animate__fadeIn text-white text-center"
			style="position: relative; z-index: 2;">
			<h1 class="animate__animated animate__fadeInDown">Welcome to Hope Foundation</h1>
			<p class="animate__animated animate__fadeInUp">We are dedicated to making a difference in the community
				through support, education, and care.</p>

			<?php if ($_SESSION['role'] == 'admin'): ?>
				<a href="users.php" class="btn btn-primary animate__animated animate__zoomIn">View Members</a>
				<a href="view_donations.php" class="btn btn-primary animate__animated animate__zoomIn">View Donations</a>
			<?php else: ?>
				<a href="donation.php" class="btn btn-primary animate__animated animate__zoomIn">Normal Donation</a>
			<?php endif; ?>
			<a href="contact.php" class="btn btn-primary animate__animated animate__zoomIn">Contact Us</a>
		</div>
	</div>

	<?php if (isset($_SESSION['success'])): ?>
		<div class="alert alert-success animate__animated animate__fadeInDown">
			<?php
			echo $_SESSION['success'];
			unset($_SESSION['success']);
			?>
		</div>
	<?php endif; ?>
	</div>


	<!-- Donation Progress -->
	<div class="animate__animated animate__fadeInUp">
		<h3 class="mt-5 text-center">Donation Progress</h3>
		<div class="progress mb-4" style="height: 25px;">
			<div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
				style="width: 65%; font-weight: bold;">
				$6,500 raised of $10,000
			</div>
		</div>
	</div>

	<br>
	<br>

	<!-- Initiatives -->
	<h3 class="text-center mb-5">Our Initiatives</h3>
	<div class="row">
		<div class="col-md-4 mb-4">
			<div class="card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
				<img src="https://images.pexels.com/photos/9532027/pexels-photo-9532027.jpeg?auto=compress&cs=tinysrgb&w=600"
					class="card-img-top" alt="Helping the Underprivileged">
				<div class="card-body d-flex flex-column">
					<h5 class="card-title">Helping the Underprivileged (2010)</h5>
					<p class="card-text flex-grow-1">Founded with a mission to help the underprivileged.</p>
				</div>
			</div>
		</div>

		<div class="col-md-4 mb-4">
			<div class="card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
				<img src="https://images.pexels.com/photos/256417/pexels-photo-256417.jpeg?auto=compress&cs=tinysrgb&w=600"
					class="card-img-top" alt="Education and Healthcare">
				<div class="card-body d-flex flex-column">
					<h5 class="card-title">Education & Healthcare Initiatives (2015)</h5>
					<p class="card-text flex-grow-1">Launched education and healthcare initiatives to empower
						communities.</p>
				</div>
			</div>
		</div>

		<div class="col-md-4 mb-4">
			<div class="card h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.6s;">
				<img src="https://images.pexels.com/photos/3184396/pexels-photo-3184396.jpeg?auto=compress&cs=tinysrgb&w=600"
					class="card-img-top" alt="Beneficiaries Reached">
				<div class="card-body d-flex flex-column">
					<h5 class="card-title">10,000+ Beneficiaries (2020)</h5>
					<p class="card-text flex-grow-1">Reached thousands across multiple regions through our programs.</p>
				</div>
			</div>
		</div>
	</div>

	<!-- What We Offer Section -->

	<!-- What We Offer Section -->
	<h3 class="text-center mt-5 mb-4">What We Offer</h3>

	<div id="iconCarousel" class="carousel slide" data-ride="carousel" data-interval="2500">
		<div class="carousel-inner">

			<!-- Slide 1 -->
			<div class="carousel-item active">
				<div class="d-flex justify-content-center">
					<div class="text-center mx-4">
						<i class="fas fa-tint text-danger" style="font-size: 70px;"></i>
						<p>Blood Donation</p>
					</div>
					<div class="text-center mx-4">
						<i class="fas fa-hand-holding-usd text-success" style="font-size: 70px;"></i>
						<p>Cash Donation</p>
					</div>
					<div class="text-center mx-4">
						<i class="fas fa-apple-alt text-warning" style="font-size: 70px;"></i>
						<p>Food Support</p>
					</div>
				</div>
			</div>

			<!-- Slide 2 -->
			<div class="carousel-item">
				<div class="d-flex justify-content-center">
					<div class="text-center mx-4">
						<i class="fas fa-book-reader text-primary" style="font-size: 70px;"></i>
						<p>Education</p>
					</div>
					<div class="text-center mx-4">
						<i class="fas fa-stethoscope text-info" style="font-size: 70px;"></i>
						<p>Healthcare</p>
					</div>
					<div class="text-center mx-4">
						<i class="fas fa-users text-secondary" style="font-size: 70px;"></i>
						<p>Community</p>
					</div>
				</div>
			</div>

			<!-- Slide 3 -->
			<div class="carousel-item">
				<div class="d-flex justify-content-center">
					<div class="text-center mx-4">
						<i class="fas fa-heartbeat text-danger" style="font-size: 70px;"></i>
						<p>Medical Help</p>
					</div>
					<div class="text-center mx-4">
						<i class="fas fa-hands-helping text-success" style="font-size: 70px;"></i>
						<p>Volunteering</p>
					</div>
					<div class="text-center mx-4">
						<i class="fas fa-leaf text-success" style="font-size: 70px;"></i>
						<p>Environment</p>
					</div>
				</div>
			</div>

		</div>

		<!-- Controls -->
		<a class="carousel-control-prev" href="#iconCarousel" role="button" data-slide="prev">
			<span class="carousel-control-prev-icon"></span>
		</a>
		<a class="carousel-control-next" href="#iconCarousel" role="button" data-slide="next">
			<span class="carousel-control-next-icon"></span>
		</a>
	</div>

	<br>
	<br>


	<!-- Carousel controls -->
	<a class="carousel-control-prev" href="#offerCarousel" role="button" data-slide="prev">
		<span class="carousel-control-prev-icon"></span>
	</a>
	<a class="carousel-control-next" href="#offerCarousel" role="button" data-slide="next">
		<span class="carousel-control-next-icon"></span>
	</a>
	</div>

	<!-- Previous Events Section -->
	<section class="container mt-5 mb-5">
		<h3 class="text-center mb-4"> Events</h3>
		<div class="row">

			<!-- Event 1 -->
			<div class="col-md-4 mb-4">
				<div class="card shadow-sm">
					<img class="card-img-top"
						src="https://images.pexels.com/photos/1164531/pexels-photo-1164531.jpeg?auto=compress&cs=tinysrgb&w=600"
						alt="Blood Donation Camp">
					<div class="card-body">
						<h5 class="card-title">Blood Donation Camp</h5>
						<p class="card-text">Organized on 10th March 2025. Over 100 volunteers participated.</p>
					</div>
				</div>
			</div>

			<!-- Event 2 -->
			<div class="col-md-4 mb-4">
				<div class="card shadow-sm">
					<img class="card-img-top"
						src="https://images.pexels.com/photos/14025670/pexels-photo-14025670.jpeg?auto=compress&cs=tinysrgb&w=600"
						alt="Education Drive">
					<div class="card-body">
						<h5 class="card-title">Education Drive</h5>
						<p class="card-text">Free books distributed to underprivileged children in rural areas.</p>
					</div>
				</div>
			</div>

			<!-- Event 3 -->
			<div class="col-md-4 mb-4">
				<div class="card shadow-sm">
					<img class="card-img-top"
						src="https://images.pexels.com/photos/40568/medical-appointment-doctor-healthcare-40568.jpeg?auto=compress&cs=tinysrgb&w=600"
						alt="Health Camp">
					<div class="card-body">
						<h5 class="card-title">Free Health Check-up Camp</h5>
						<p class="card-text">Health check-ups and awareness session held in collaboration with local
							hospitals.</p>
					</div>
				</div>
			</div>

		</div>
	</section>


	<!-- NEWSLETTER SIGNUP -->
	<section class="bg-light py-5">
		<div class="container text-center">
			<h2 class="mb-4 animate__animated animate__fadeInDown">Subscribe to our Newsletter</h2>
			<form class="row justify-content-center">
				<div class="col-md-4 mb-2">
					<input type="email" class="form-control" placeholder="Enter your email" required>
				</div>
				<div class="col-md-2 mb-2">
					<button class="btn btn-primary w-100 animate__animated animate__bounceIn">Subscribe</button>
				</div>
			</form>
		</div>
	</section>


	<!-- Testimonials Carousel -->
	<h3 class="mt-5 text-center">Testimonials</h3>
	<div id="testimonialCarousel" class="carousel slide mb-5" data-ride="carousel">
		<div class="carousel-inner text-center">
			<div class="carousel-item active">
				<img src="https://source.unsplash.com/150x150/?person" class="rounded-circle mb-2" alt="">
				<p>"Hope Foundation changed my life. The education program helped me find a job."</p>
			</div>
			<div class="carousel-item">
				<img src="https://source.unsplash.com/150x150/?volunteer" class="rounded-circle mb-2" alt="">
				<p>"Volunteering with Hope Foundation has been the most rewarding experience."</p>
			</div>
		</div>
		<a class="carousel-control-prev" href="#testimonialCarousel" role="button" data-slide="prev">
			<span class="carousel-control-prev-icon"></span>
		</a>
		<a class="carousel-control-next" href="#testimonialCarousel" role="button" data-slide="next">
			<span class="carousel-control-next-icon"></span>
		</a>
	</div>

	<!-- Success Message -->
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