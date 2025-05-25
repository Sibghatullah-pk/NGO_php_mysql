<?php
// server.php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'project';

$db = new mysqli($host, $user, $pass, $dbname);

if ($db->connect_error) {
  die("Database connection failed: " . $db->connect_error);
}

$errors = [];

// Registration
if (isset($_POST['reg_user'])) {
  $username = $db->real_escape_string($_POST['username']);
  $email = $db->real_escape_string($_POST['email']);
  $password_1 = $_POST['password_1'];
  $password_2 = $_POST['password_2'];
  $role = $db->real_escape_string($_POST['role']);

  if (empty($username))
    $errors[] = "Username is required";
  if (empty($email))
    $errors[] = "Email is required";
  if (empty($password_1))
    $errors[] = "Password is required";
  if ($password_1 !== $password_2)
    $errors[] = "Passwords do not match";

  // Check if user exists
  $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
  $stmt->bind_param("ss", $username, $email);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    $errors[] = "Username or Email already exists";
  }
  $stmt->close();

  if (empty($errors)) {
    $password = password_hash($password_1, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password, $role);
    $stmt->execute();
    $stmt->close();

    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;

    // Redirect based on role
    if ($role === 'admin') {
      header('Location: admin_dashboard.php');
    } elseif ($role === 'volunteer') {
      header('Location: volunteer_tasks.php');
    } elseif ($role === 'donor') {
      header('Location: donor_dashboard.php');
    } else {
      header('Location: login.php');
    }
    exit();
  }
}

// Login
if (isset($_POST['login_user'])) {
  $username = $db->real_escape_string($_POST['username']);
  $password = $_POST['password'];

  if (empty($username))
    $errors[] = "Username is required";
  if (empty($password))
    $errors[] = "Password is required";

  if (empty($errors)) {
    $stmt = $db->prepare("SELECT id, password, role FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($id, $hashed_password, $role);
    if ($stmt->fetch()) {
      if (password_verify($password, $hashed_password)) {
        session_regenerate_id(true);
        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = $id;
        $_SESSION['role'] = $role;
        $_SESSION['success'] = "You are now logged in";
        header('Location: index.php');
        exit();
      } else {
        $errors[] = "Wrong username/password combination";
      }
    } else {
      $errors[] = "Wrong username/password combination";
    }
    $stmt->close();
  }
}

// Donation logic moved to donation.php

?>