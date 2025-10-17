<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$admin_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    $admin = $db->getAdminById($admin_id);
    if (!$admin || !password_verify($current_password, $admin['password'])) {
        echo "<script>alert('Current password is incorrect.'); window.history.back();</script>"; exit();
    }

    if ($new_password !== $confirm_password) {
        echo "<script>alert('New passwords do not match.'); window.history.back();</script>"; exit();
    }

    $db->changeAdminPassword($admin_id, password_hash($new_password, PASSWORD_DEFAULT));
    echo "<script>alert('Password changed successfully!'); window.location.href='dashboard.php';</script>";
}
?>
<!-- HTML form remains the same -->



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
  --primary-dark: #1a252f;
  --primary-blue: #3498db;
  --accent-gold: #d4af37;
  --luxury-gold: #c9a961;
  --soft-white: #f8f5f0;
  --earth-brown: #8b7355;
}

body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
  margin: 0;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
  overflow-x: hidden;
}

/* Floating ApartmentHub Text */
.floating-logo {
  position: absolute;
  top: 20%;
  left: 50%;
  transform: translateX(-50%);
  font-size: 6rem;
  font-weight: 900;
  color: rgba(212,175,55,0.08);
  letter-spacing: 10px;
  pointer-events: none;
  user-select: none;
  z-index: 0;
  animation: floatLogo 6s ease-in-out infinite alternate;
}

@keyframes floatLogo {
  0% { transform: translateX(-50%) translateY(0); }
  100% { transform: translateX(-50%) translateY(-20px); }
}

/* Card Styles */
.card {
  border-radius: 25px;
  background: linear-gradient(145deg, var(--soft-white) 0%, #f0ece5 100%);
  border: 2px solid rgba(212,175,55,0.3);
  box-shadow: 0 20px 60px rgba(0,0,0,0.15), inset 0 1px 0 rgba(255,255,255,0.4);
  padding: 2rem;
  width: 100%;
  max-width: 480px;
  position: relative;
  transition: transform 0.3s, box-shadow 0.3s;
  z-index: 1;
}
.card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 6px;
  background: linear-gradient(90deg, var(--primary-dark), var(--primary-blue), var(--accent-gold));
  border-radius: 25px 25px 0 0;
}
.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 30px 90px rgba(0,0,0,0.25), inset 0 1px 0 rgba(255,255,255,0.5);
  border-top: 6px solid var(--luxury-gold);
}

.card-header {
  background: linear-gradient(135deg, var(--primary-dark), var(--primary-blue));
  color: var(--soft-white);
  border-radius: 20px 20px 0 0;
  font-weight: 600;
  font-size: 1.25rem;
  text-align: center;
  padding: 0.75rem 1rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

/* Form Inputs */
.form-control {
  border-radius: 15px;
  border: 1px solid rgba(212,175,55,0.3);
  padding: 10px 15px;
  font-weight: 500;
  transition: all 0.3s ease;
}
.form-control:focus {
  border-color: var(--accent-gold);
  box-shadow: 0 0 10px rgba(212,175,55,0.2);
}

/* Buttons */
.btn-primary {
  background: linear-gradient(135deg, var(--primary-dark), var(--primary-blue));
  border: none;
  font-weight: 600;
  border-radius: 20px;
  transition: all 0.3s ease;
}
.btn-primary:hover {
  background: linear-gradient(135deg, var(--primary-blue), var(--accent-gold));
  transform: translateY(-2px);
}

.btn-back {
  background: linear-gradient(135deg, rgba(212,175,55,0.1), transparent);
  color: var(--primary-dark);
  border: 2px solid var(--accent-gold);
  font-weight: 600;
  border-radius: 20px;
  padding: 6px 18px;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  text-decoration: none;
}
.btn-back:hover {
  background: linear-gradient(45deg, var(--accent-gold), rgba(212,175,55,0.3));
  transform: translateY(-2px) scale(1.05);
}
</style>
</head>
<body>

<!-- Floating ApartmentHub Text -->
<div class="floating-logo">APARTMENTHUB</div>

<div class="card">
    <div class="card-header">
        Change Password
    </div>
    <form method="POST" action="">
        <div class="mb-3">
            <label>Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>New Password</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <button type="submit" class="btn btn-primary">Update Password</button>
            <a href="dashboard.php" class="btn-back"><i class="bi bi-arrow-left me-1"></i> Back</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
