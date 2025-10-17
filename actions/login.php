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

/* ============================
   HARD-CODED ADMIN LOGIN
   ============================ */
if ($username === 'admin1' && $password === 'admin123') {
  $_SESSION['user_id'] = 1;
  $_SESSION['username'] = 'admin1';
  $_SESSION['role'] = 'admin';
  $_SESSION['name'] = 'System Administrator';

  echo json_encode([
    'success' => true,
    'name' => 'Admin',
    'redirect' => '../ahub/admin/dashboard.php'
  ]);
  exit;
}

/* ============================
   TENANT LOGIN
   ============================ */
$stmt = $conn->prepare("SELECT * FROM tenants WHERE username = ? OR email = ?");
$stmt->execute([$username, $username]);
$tenant = $stmt->fetch(PDO::FETCH_ASSOC);

if ($tenant && password_verify($password, $tenant['password'])) {
  $_SESSION['user_id'] = $tenant['tenant_id'];
  $_SESSION['username'] = $tenant['username'];
  $_SESSION['email'] = $tenant['email'];
  $_SESSION['role'] = 'tenant';
  $_SESSION['name'] = $tenant['firstname'] . ' ' . $tenant['lastname'];

  echo json_encode([
    'success' => true,
    'name' => $tenant['firstname'],
    'redirect' => '../ahub/tenant/dashboard.php'
  ]);
  exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
exit;
?>
