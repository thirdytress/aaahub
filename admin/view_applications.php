<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();

if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] === 'approve') $db->approveApplication($id);
    elseif ($_GET['action'] === 'reject') $db->rejectApplication($id);
    header("Location: view_applications.php"); exit();
}

$applications = $db->getAllApplications();
?>
<!-- HTML table remains the same -->



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Applications | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
  --primary-dark: #1a252f;
  --primary-blue: #3498db;
  --accent-gold: #d4af37;
  --luxury-gold: #c9a961;
  --earth-brown: #8b7355;
  --soft-white: #f8f5f0;
}

/* ===== Base Styles ===== */
body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
  position: relative;
  overflow-x: hidden;
  margin: 0;
}

h3 {
  color: var(--primary-dark);
  border-bottom: 2px solid var(--accent-gold);
  display: inline-block;
  padding-bottom: 5px;
  font-weight: 700;
  transition: color 0.3s;
}

h3:hover {
  color: var(--luxury-gold);
}

/* ===== Floating Decorations ===== */
body::before, body::after {
  content: '';
  position: fixed;
  border-radius: 50%;
  opacity: 0.1;
  pointer-events: none;
}

body::before {
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, var(--accent-gold), transparent);
  top: -50px;
  left: -50px;
}

body::after {
  width: 200px;
  height: 200px;
  background: radial-gradient(circle, var(--primary-blue), transparent);
  bottom: 20%;
  right: 10%;
}

/* ===== Card Styling ===== */
.card {
  border-radius: 25px;
  background: linear-gradient(145deg, var(--soft-white) 0%, #f0ece5 100%);
  border: 2px solid rgba(212,175,55,0.3);
  box-shadow: 0 20px 60px rgba(0,0,0,0.15), inset 0 1px 0 rgba(255,255,255,0.4);
  transition: transform 0.3s, box-shadow 0.3s, border-top 0.3s;
  border-top: 6px solid transparent;
  position: relative;
  padding: 2rem;
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
  transform: translateY(-8px);
  box-shadow: 0 30px 90px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.5);
  border-top: 6px solid var(--luxury-gold);
}

/* ===== Table Styling ===== */
.table-responsive {
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 5px 25px rgba(0,0,0,0.1);
}

.table thead {
  background: linear-gradient(135deg, var(--primary-dark), var(--primary-blue));
}

.table thead th {
  color: var(--luxury-gold);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 1rem;
  border: none;
  font-size: 0.9rem;
}

.table tbody tr {
  transition: all 0.3s ease;
  border-bottom: 1px solid rgba(212,175,55,0.1);
}

.table tbody tr:hover {
  background: linear-gradient(90deg, rgba(212,175,55,0.05), transparent);
  transform: translateX(5px);
}

.table tbody td {
  padding: 1rem;
  color: var(--earth-brown);
  font-weight: 500;
  vertical-align: middle;
}

/* ===== Buttons ===== */
.btn-success, .btn-danger {
  border: none;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-success:hover {
  transform: scale(1.05) rotate(-2deg);
  box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.btn-danger:hover {
  transform: scale(1.05) rotate(-2deg);
  box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

/* ===== Back Button ===== */
.btn-back {
    border: 2px solid var(--accent-gold);
    color: var(--primary-dark);
    font-weight: 600;
    border-radius: 20px;
    padding: 6px 18px;
    background: linear-gradient(45deg, rgba(212,175,55,0.1), transparent);
    transition: all 0.3s ease;
}

.btn-back:hover {
    background: linear-gradient(45deg, var(--accent-gold), rgba(212,175,55,0.3));
    color: var(--primary-dark);
    transform: translateY(-2px) scale(1.05);
}

/* ===== Badges ===== */
.badge {
    font-weight: 600;
    border-radius: 12px;
}

/* ===== No applications message ===== */
.text-muted {
  font-weight: 500;
  font-size: 1.1rem;
  color: var(--earth-brown);
  padding: 2rem 0;
}

/* ===== Responsive ===== */
@media (max-width: 768px) {
  h3 {
    font-size: 1.5rem;
  }
  .card {
    padding: 1rem;
  }
}
</style>
</head>
<body>

<div class="container mt-4">
  <div class="card p-4">
    <!-- Back button added -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mb-0">Tenant Apartment Applications</h3>
      <a href="dashboard.php" class="btn btn-outline-secondary btn-back">
        <i class="bi bi-arrow-left me-1"></i> Back
      </a>
    </div>

    <?php if ($applications): ?>
    <div class="table-responsive">
      <table class="table table-bordered table-hover bg-white align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Tenant</th>
            <th>Apartment</th>
            <th>Location</th>
            <th>Date Applied</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($applications as $i => $app): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($app['firstname'] . ' ' . $app['lastname']) ?> (<?= htmlspecialchars($app['tenant_username']) ?>)</td>
            <td><?= htmlspecialchars($app['apartment_name']) ?></td>
            <td><?= htmlspecialchars($app['Location']) ?></td>
            <td><?= date('M d, Y H:i', strtotime($app['date_applied'])) ?></td>
            <td>
              <?php if ($app['app_status'] === 'Pending'): ?>
                <span class="badge bg-warning text-dark">Pending</span>
              <?php elseif ($app['app_status'] === 'Approved'): ?>
                <span class="badge bg-success">Approved</span>
              <?php else: ?>
                <span class="badge bg-danger">Rejected</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($app['app_status'] === 'Pending'): ?>
                <a href="?action=approve&id=<?= $app['application_id'] ?>" class="btn btn-success btn-sm mb-1">Approve</a>
                <a href="?action=reject&id=<?= $app['application_id'] ?>" class="btn btn-danger btn-sm">Reject</a>
              <?php else: ?>
                <span class="text-muted">No action</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <p class="text-muted">No applications found.</p>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

