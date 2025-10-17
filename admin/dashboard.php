<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();

// --- get admin fullname dynamically ---
$fullname = $_SESSION['fullname'] ?? '';
$username = $_SESSION['username'] ?? '';

if (empty($fullname) && !empty($username)) {
    $admin = $db->getAdminByUsername($username);
    if ($admin) {
        $fullname = trim($admin['firstname'] . ' ' . $admin['lastname']);
        $_SESSION['fullname'] = $fullname;
        $_SESSION['username'] = $admin['username'] ?? '';
    }
}

// --- fetch counts for dashboard cards ---
$totalTenants = $db->countTenants();
$totalApplications = $db->countApplications();
$totalApartments = $db->countApartments();
$totalLeases = $db->countLeases();
$totalUtilities = $db->countUtilities();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-dark: #2c3e50;
      --primary-blue: #3498db;
      --accent-gold: #d4af37;
      --warm-beige: #f5f1e8;
      --soft-gray: #95a5a6;
      --deep-navy: #1a252f;
      --luxury-gold: #c9a961;
      --earth-brown: #8b7355;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      position: relative;
      overflow-x: hidden;
    }

    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image: 
        repeating-linear-gradient(90deg, rgba(212, 175, 55, 0.03) 0px, transparent 1px, transparent 40px, rgba(212, 175, 55, 0.03) 41px),
        repeating-linear-gradient(0deg, rgba(212, 175, 55, 0.03) 0px, transparent 1px, transparent 40px, rgba(212, 175, 55, 0.03) 41px);
      z-index: 0;
      pointer-events: none;
    }

    .navbar {
      background: linear-gradient(135deg, var(--deep-navy) 0%, var(--primary-dark) 100%) !important;
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 30px rgba(0,0,0,0.3);
      border-bottom: 3px solid var(--accent-gold);
      padding: 1rem 0;
      position: relative;
      z-index: 1000;
      margin-bottom: 0 !important;
    }

    .navbar::after {
      content: '';
      position: absolute;
      bottom: -3px;
      left: 0;
      width: 100%;
      height: 3px;
      background: linear-gradient(90deg, transparent, var(--luxury-gold), transparent);
      animation: shimmer 3s infinite;
    }

    @keyframes shimmer {
      0%, 100% { opacity: 0.5; }
      50% { opacity: 1; }
    }

    .navbar-brand {
      font-size: 1.8rem;
      font-weight: 700;
      color: white !important;
      letter-spacing: 1px;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }

    .btn-outline-danger {
      background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
      border: 2px solid rgba(255,255,255,0.2);
      color: white;
      padding: 8px 20px;
      border-radius: 20px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
    }

    .btn-outline-danger:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(231, 76, 60, 0.5);
    }

    .container {
      position: relative;
      z-index: 1;
      margin-top: 50px;
      margin-bottom: 50px;
    }

    .card {
      border: none;
      border-radius: 30px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.15);
      background: linear-gradient(145deg, #ffffff 0%, #f8f5f0 100%);
      border: 2px solid rgba(212, 175, 55, 0.2);
      position: relative;
      animation: fadeInUp 0.8s ease;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(50px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, var(--primary-dark) 0%, var(--primary-blue) 50%, var(--accent-gold) 100%);
      border-radius: 30px 30px 0 0;
    }

    h3, h2 {
      font-weight: 700;
      color: var(--primary-dark);
    }

    h3 {
      font-size: 2rem;
      margin-bottom: 1.5rem;
    }

    h2 {
      font-size: 2.2rem;
      margin-bottom: 2rem;
      position: relative;
      display: inline-block;
    }

    h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 80px;
      height: 4px;
      background: var(--accent-gold);
      border-radius: 2px;
    }

    h2 i {
      color: var(--accent-gold);
      filter: drop-shadow(0 2px 4px rgba(212, 175, 55, 0.3));
    }

    .text-primary {
      color: var(--primary-dark) !important;
    }

    hr {
      border-color: rgba(212, 175, 55, 0.3);
      opacity: 1;
    }

    .dashboard-card {
      border: none;
      border-radius: 25px;
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      cursor: pointer;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      background: linear-gradient(145deg, #ffffff 0%, #f8f5f0 100%);
      border: 2px solid rgba(212, 175, 55, 0.2);
      position: relative;
      overflow: hidden;
    }

    .dashboard-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary-dark) 0%, var(--primary-blue) 50%, var(--accent-gold) 100%);
      transform: scaleX(0);
      transition: transform 0.5s ease;
    }

    .dashboard-card:hover::before {
      transform: scaleX(1);
    }

    .dashboard-card:hover {
      transform: translateY(-10px) scale(1.03);
      box-shadow: 0 20px 60px rgba(0,0,0,0.2);
      background: linear-gradient(145deg, #ffffff 0%, #f5f1e8 100%);
    }

    .dashboard-card .icon {
      font-size: 3rem;
      color: var(--primary-blue);
      transition: all 0.5s ease;
    }

    .dashboard-card:hover .icon {
      transform: scale(1.2) rotateY(360deg);
      color: var(--accent-gold);
    }

    .dashboard-card h5 {
      font-weight: 700;
      color: var(--primary-dark);
      margin-top: 1rem;
    }

    .small.text-muted {
      color: var(--earth-brown) !important;
      font-weight: 500;
    }

    .btn {
      border-radius: 20px;
      font-weight: 600;
      transition: all 0.4s ease;
      padding: 8px 25px;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-dark) 100%);
      border: none;
      box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    }

    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(52, 152, 219, 0.5);
    }

    .btn-outline-primary {
      border: 2px solid var(--primary-blue);
      color: var(--primary-blue);
    }

    .btn-outline-primary:hover {
      background: var(--primary-blue);
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
    }

    .btn-success {
      background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
      border: none;
      box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
    }

    .btn-success:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(39, 174, 96, 0.5);
    }

    .btn-info {
      background: linear-gradient(135deg, var(--primary-blue) 0%, #5dade2 100%);
      border: none;
      box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    }

    .btn-info:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(52, 152, 219, 0.5);
    }

    .btn-secondary {
      background: linear-gradient(135deg, var(--soft-gray) 0%, var(--primary-dark) 100%);
      border: none;
      box-shadow: 0 4px 15px rgba(149, 165, 166, 0.3);
    }

    .btn-secondary:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(149, 165, 166, 0.5);
    }

    .btn-warning {
      background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
      border: none;
      box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
      color: white;
    }

    .btn-warning:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(243, 156, 18, 0.5);
      color: white;
    }

    .table-responsive {
      border-radius: 25px;
      overflow: hidden;
      box-shadow: 0 15px 50px rgba(0,0,0,0.15);
      background: linear-gradient(145deg, #ffffff 0%, #f8f5f0 100%);
      border: 2px solid rgba(212, 175, 55, 0.2);
      position: relative;
    }

    .table-responsive::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, var(--primary-dark) 0%, var(--primary-blue) 50%, var(--accent-gold) 100%);
    }

    .table thead {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
    }

    .table-dark {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
    }

    .table thead th {
      color: white;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 1.2rem 1rem;
      border: none;
      font-size: 0.9rem;
    }

    .table tbody tr {
      transition: all 0.3s ease;
      border-bottom: 1px solid rgba(212, 175, 55, 0.1);
    }

    .table tbody tr:hover {
      background: linear-gradient(90deg, rgba(212, 175, 55, 0.05), transparent);
      transform: translateX(5px);
    }

    .table tbody td {
      padding: 1.2rem 1rem;
      color: var(--earth-brown);
      font-weight: 500;
      vertical-align: middle;
    }

    .badge {
      padding: 8px 16px;
      border-radius: 15px;
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    .bg-warning {
      background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%) !important;
      box-shadow: 0 3px 10px rgba(243, 156, 18, 0.3);
      color: white !important;
    }

    .bg-info {
      background: linear-gradient(135deg, var(--primary-blue) 0%, #5dade2 100%) !important;
      box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3);
      color: white !important;
    }

    .bg-success {
      background: linear-gradient(135deg, #27ae60 0%, #229954 100%) !important;
      box-shadow: 0 3px 10px rgba(39, 174, 96, 0.3);
    }

    .form-select {
      border: 2px solid rgba(212, 175, 55, 0.3);
      border-radius: 15px;
      color: var(--earth-brown);
      font-weight: 500;
    }

    .form-select:focus {
      border-color: var(--accent-gold);
      box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
    }

    .text-muted {
      color: var(--earth-brown) !important;
    }

    .floating-decoration {
      position: fixed;
      pointer-events: none;
      z-index: 0;
    }

    .deco-1 {
      top: 15%;
      left: 5%;
      width: 150px;
      height: 150px;
      background: radial-gradient(circle, rgba(212, 175, 55, 0.1), transparent);
      border-radius: 50%;
      animation: float 6s ease-in-out infinite;
    }

    .deco-2 {
      bottom: 10%;
      right: 8%;
      width: 200px;
      height: 200px;
      background: radial-gradient(circle, rgba(52, 152, 219, 0.1), transparent);
      border-radius: 50%;
      animation: float 8s ease-in-out infinite reverse;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-30px); }
    }

    @media (max-width: 768px) {
      h2, h3 {
        font-size: 1.8rem;
      }

      .dashboard-card h5 {
        font-size: 1rem;
      }

      .table thead th,
      .table tbody td {
        padding: 1rem 0.5rem;
        font-size: 0.85rem;
      }
    }
  </style>
</head>
<body>

<div class="floating-decoration deco-1"></div>
<div class="floating-decoration deco-2"></div>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg bg-white mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary" href="#">ApartmentHub Admin</a>
    <div class="d-flex">
      <a href="../logout.php" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-box-arrow-right me-1"></i>Logout
      </a>
    </div>
  </div>
</nav>

<!-- MAIN DASHBOARD -->
<div class="container">
  <div class="card p-4 shadow-sm">
    <h3 class="text-primary">Welcome, <?= htmlspecialchars($fullname ?: 'Admin'); ?>!</h3>
    <hr>
    <p>This is your admin dashboard. You can manage tenants, applications, apartments, utilities, and maintenance requests here.</p>

    <!-- DASHBOARD CARDS -->
    <div class="row mt-4">
      <!-- Manage Tenants -->
      <div class="col-md-3 mb-3">
        <div class="card text-center h-100 p-3 shadow-sm dashboard-card">
          <div class="mb-2"><i class="bi bi-people-fill icon"></i></div>
          <h5>Manage Tenants</h5>
          <p class="small text-muted"><?= $totalTenants ?> tenants</p>
          <a href="manage_tenants.php" class="btn btn-primary btn-sm mt-auto">Go</a>
        </div>
      </div>

      <!-- View Applications -->
      <div class="col-md-3 mb-3">
        <div class="card text-center h-100 p-3 shadow-sm dashboard-card">
          <div class="mb-2"><i class="bi bi-file-earmark-text-fill icon"></i></div>
          <h5>View Applications</h5>
          <p class="small text-muted"><?= $totalApplications ?> applications</p>
          <a href="view_applications.php" class="btn btn-outline-primary btn-sm mt-auto">Go</a>
        </div>
      </div>

      <!-- Add Apartment -->
      <div class="col-md-3 mb-3">
        <div class="card text-center h-100 p-3 shadow-sm dashboard-card">
          <div class="mb-2"><i class="bi bi-building-fill icon"></i></div>
          <h5>Add Apartment</h5>
          <p class="small text-muted"><?= $totalApartments ?> apartments</p>
          <a href="add_apartment.php" class="btn btn-success btn-sm mt-auto">Go</a>
        </div>
      </div>

      <!-- View Leases -->
      <div class="col-md-3 mb-3">
        <div class="card text-center h-100 p-3 shadow-sm dashboard-card">
          <div class="mb-2"><i class="bi bi-file-text-fill icon"></i></div>
          <h5>View Leases</h5>
          <p class="small text-muted"><?= $totalLeases ?> active leases</p>
          <a href="view_leases.php" class="btn btn-info btn-sm mt-auto">Go</a>
        </div>
      </div>

      <!-- Manage Payments -->
<div class="col-md-3 mb-3">
  <div class="card text-center h-100 p-3 shadow-sm dashboard-card">
    <div class="mb-2"><i class="bi bi-cash-coin icon"></i></div>
    <h5>Manage Payments</h5>
    <p class="small text-muted">View and update payment records</p>
    <a href="manage_payments.php" class="btn btn-success btn-sm mt-auto">Go</a>
  </div>
</div>


      <!-- Utilities Management -->
      <div class="col-md-3 mb-3">
        <div class="card text-center h-100 p-3 shadow-sm dashboard-card">
          <div class="mb-2"><i class="bi bi-droplet-half icon text-primary"></i></div>
          <h5>Utilities</h5>
          <p class="small text-muted"><?= $totalUtilities ?> bills</p>
          <a href="manage_utilities.php" class="btn btn-secondary btn-sm mt-auto">Manage</a>
        </div>
      </div>

      <!-- Change Password -->
      <div class="col-md-3 mb-3">
        <div class="card text-center h-100 p-3 shadow-sm dashboard-card">
          <div class="mb-2"><i class="bi bi-key-fill icon"></i></div>
          <h5>Change Password</h5>
          <p class="small text-muted">Secure your account</p>
          <a href="change_password.php" class="btn btn-warning btn-sm mt-auto">Go</a>
        </div>
      </div>
    </div>
  </div>

  <!-- MAINTENANCE REQUEST SECTION -->
  <section class="mt-5">
    <h2 class="mb-4 text-primary"><i class="bi bi-tools me-2"></i>Maintenance Requests</h2>

    <?php
    $stmt = $db->connect()->query("
      SELECT r.*, CONCAT(t.firstname, ' ', t.lastname) AS tenant_name
      FROM maintenance_requests r
      JOIN tenants t ON r.tenant_id = t.tenant_id
      ORDER BY r.created_at DESC
    ");
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php if ($requests): ?>
      <div class="table-responsive shadow-sm rounded-3">
        <table class="table table-hover table-bordered align-middle text-center">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Tenant</th>
              <th>Subject</th>
              <th>Description</th>
              <th>Status</th>
              <th>Created</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($requests as $req): ?>
            <tr>
              <td><?= $req['id'] ?></td>
              <td><?= htmlspecialchars($req['tenant_name']) ?></td>
              <td><?= htmlspecialchars($req['subject']) ?></td>
              <td><?= htmlspecialchars($req['description']) ?></td>
              <td>
                <span class="badge bg-<?=
                  $req['status'] === 'Pending' ? 'warning' :
                  ($req['status'] === 'In Progress' ? 'info' : 'success')
                ?>">
                  <?= htmlspecialchars($req['status']) ?>
                </span>
              </td>
              <td><?= date('M d, Y h:i A', strtotime($req['created_at'])) ?></td>
              <td>
                <form action="../actions/update_request.php" method="POST" class="d-inline">
                  <input type="hidden" name="id" value="<?= $req['id'] ?>">
                  <select name="status" class="form-select form-select-sm d-inline w-auto">
                    <option <?= $req['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option <?= $req['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                    <option <?= $req['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                  </select>
                  <button class="btn btn-primary btn-sm">Update</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-muted">No maintenance requests yet.</p>
    <?php endif; ?>
  </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
