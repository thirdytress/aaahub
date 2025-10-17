<?php
session_start();
require_once "../classes/database.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($username) || empty($password)) {
  echo json_encode(['success' => false, 'message' => 'All fields are required']);
  exit;
}

$db = new Database();
$conn = $db->connect();

// ✅ Check in tenants table
$stmt = $conn->prepare("SELECT * FROM tenants WHERE username = :username OR email = :username LIMIT 1");
$stmt->bindParam(':username', $username);
$stmt->execute();
$tenant = $stmt->fetch(PDO::FETCH_ASSOC);

if ($tenant && password_verify($password, $tenant['password'])) {
  $_SESSION['user_id'] = $tenant['tenant_id'];
  $_SESSION['username'] = $tenant['username'];
  $_SESSION['email'] = $tenant['email'];
  $_SESSION['role'] = 'tenant';
  $_SESSION['name'] = $tenant['firstname'] . ' ' . $tenant['lastname'];

  // ✅ If may pending redirect after login (e.g. from Apply button)
  $redirect = $_SESSION['redirect_after_login'] ?? 'tenant/dashboard.php';
  unset($_SESSION['redirect_after_login']);

  echo json_encode([
    'success' => true,
    'name' => $tenant['firstname'],
    'redirect' => $redirect
  ]);
  exit;
}

// ✅ Check in admins table
$stmt = $conn->prepare("SELECT * FROM admins WHERE username = :username OR email = :username LIMIT 1");
$stmt->bindParam(':username', $username);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin && password_verify($password, $admin['password'])) {
  $_SESSION['user_id'] = $admin['admin_id'];
  $_SESSION['username'] = $admin['username'];
  $_SESSION['email'] = $admin['email'];
  $_SESSION['role'] = 'admin';
  $_SESSION['name'] = $admin['fullname'];

  echo json_encode([
    'success' => true,
    'name' => $admin['fullname'],
    'redirect' => 'admin/dashboard.php'
  ]);
  exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
?>
