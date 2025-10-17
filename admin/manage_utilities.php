<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../index.php");
  exit();
}

$db = new Database();
$conn = $db->connect();

// Fetch tenants
$tenants = $conn->query("SELECT tenant_id, CONCAT(firstname, ' ', lastname) AS fullname FROM tenants ORDER BY fullname")->fetchAll(PDO::FETCH_ASSOC);

// Handle insert
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tenant_id = $_POST['tenant_id'];
  $month_year = $_POST['month_year'];
  $electricity_usage = $_POST['electricity_usage'];
  $water_usage = $_POST['water_usage'];
  $electricity_bill = $_POST['electricity_bill'];
  $water_bill = $_POST['water_bill'];
  $total_bill = $electricity_bill + $water_bill;

  $stmt = $conn->prepare("INSERT INTO utilities (tenant_id, month_year, electricity_usage, water_usage, electricity_bill, water_bill, total_bill)
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([$tenant_id, $month_year, $electricity_usage, $water_usage, $electricity_bill, $water_bill, $total_bill]);
  header("Location: manage_utilities.php?msg=Added+Successfully");
  exit();
}

// Fetch utilities
$utilities = $conn->query("SELECT u.*, CONCAT(t.firstname, ' ', t.lastname) AS tenant_name 
                           FROM utilities u 
                           JOIN tenants t ON u.tenant_id = t.tenant_id 
                           ORDER BY u.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Utilities | ApartmentHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --primary-dark: #1a252f;
  --primary-blue: #3498db;
  --accent-gold: #d4af37;
  --luxury-gold: #c9a961;
  --earth-brown: #8b7355;
  --soft-white: #f8f5f0;
}

body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
  margin: 0;
}

/* Card Styles */
.card {
  border-radius: 25px;
  background: linear-gradient(145deg, var(--soft-white) 0%, #f0ece5 100%);
  border: 2px solid rgba(212,175,55,0.3);
  box-shadow: 0 20px 60px rgba(0,0,0,0.15), inset 0 1px 0 rgba(255,255,255,0.4);
  transition: transform 0.3s, box-shadow 0.3s, border-top 0.3s;
  border-top: 6px solid transparent;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  position: relative;
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

/* Header with Back Button on Far Right */
.header-row {
  display: flex;
  justify-content: space-between; /* Title left, back button right */
  align-items: center;
  margin-bottom: 1rem;
}

h2 {
  color: var(--primary-dark);
  border-bottom: 2px solid var(--accent-gold);
  display: inline-block;
  padding-bottom: 5px;
  font-weight: 700;
}

.btn-back {
  border: 2px solid var(--accent-gold);
  color: var(--primary-dark);
  font-weight: 600;
  border-radius: 20px;
  padding: 6px 18px;
  background: linear-gradient(45deg, rgba(212,175,55,0.1), transparent);
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  text-decoration: none;
}
.btn-back:hover {
  background: linear-gradient(45deg, var(--accent-gold), rgba(212,175,55,0.3));
  transform: translateY(-2px) scale(1.05);
}

/* Table Styling */
.table-responsive {
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}
.table thead {
  background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
}
.table thead th {
  color: var(--earth-brown);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 1rem;
  border: none;
  font-size: 0.85rem;
}
.table tbody td {
  padding: 0.9rem;
  color: var(--earth-brown);
  font-weight: 500;
  vertical-align: middle;
  transition: all 0.3s ease;
}
.table tbody tr:hover {
  background: linear-gradient(90deg, rgba(212,175,55,0.05), transparent);
  transform: translateX(5px);
}
.badge-success {
  background: linear-gradient(45deg, #28a745, var(--accent-gold));
  color: white;
  font-weight: 600;
}
.badge-warning {
  background: linear-gradient(45deg, #ffc107, var(--accent-gold));
  color: var(--primary-dark);
  font-weight: 600;
}

/* Always keep Back button on far right */
@media (max-width: 576px) {
  .header-row h2 {
    font-size: 1.5rem;
  }
}
</style>
</head>
<body>

<div class="container">
  <!-- Header with Back Button on Far Right -->
  <div class="card header-row">
    <h2 class="mb-0">Manage Utilities</h2>
    <a href="dashboard.php" class="btn btn-outline-secondary btn-back">
      <i class="bi bi-arrow-left me-1"></i> Back
    </a>
  </div>

  <!-- Add Form Card -->
  <div class="card">
    <div class="header-row">
      <h5 class="mb-0">Add Utility Record</h5>
    </div>
    <form method="POST">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Tenant</label>
          <select name="tenant_id" class="form-select" required>
            <option value="">Select Tenant</option>
            <?php foreach ($tenants as $t): ?>
              <option value="<?= $t['tenant_id'] ?>"><?= htmlspecialchars($t['fullname']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Month-Year</label>
          <input type="text" name="month_year" class="form-control" placeholder="e.g. October 2025" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Electricity (kWh)</label>
          <input type="number" step="0.01" name="electricity_usage" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Water (m³)</label>
          <input type="number" step="0.01" name="water_usage" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Electricity Bill</label>
          <input type="number" step="0.01" name="electricity_bill" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Water Bill</label>
          <input type="number" step="0.01" name="water_bill" class="form-control" required>
        </div>
      </div>
      <div class="mt-3 text-end">
        <button type="submit" class="btn btn-success">Add Record</button>
      </div>
    </form>
  </div>

  <!-- Utilities Table Card -->
  <div class="card">
    <div class="header-row">
      <h5 class="mb-0">All Utility Records</h5>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle text-center">
        <thead>
          <tr>
            <th>Tenant</th>
            <th>Month</th>
            <th>Electricity (kWh)</th>
            <th>Water (m³)</th>
            <th>Electricity Bill</th>
            <th>Water Bill</th>
            <th>Total</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($utilities as $u): ?>
          <tr>
            <td><?= htmlspecialchars($u['tenant_name']) ?></td>
            <td><?= htmlspecialchars($u['month_year']) ?></td>
            <td><?= $u['electricity_usage'] ?></td>
            <td><?= $u['water_usage'] ?></td>
            <td>₱<?= number_format($u['electricity_bill'], 2) ?></td>
            <td>₱<?= number_format($u['water_bill'], 2) ?></td>
            <td><strong>₱<?= number_format($u['total_bill'], 2) ?></strong></td>
            <td>
              <span class="badge <?= $u['status'] == 'Paid' ? 'badge-success' : 'badge-warning' ?>">
                <?= $u['status'] ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

