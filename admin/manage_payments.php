<?php
session_start();
require_once "../classes/database.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$payments = $db->getAllPayments();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Payments | ApartmentHub</title>
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

/* ===== Base Styles ===== */
body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
  margin: 0;
  position: relative;
  overflow-x: hidden;
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

/* ===== Container Card ===== */
.container {
  max-width: 900px;
}

.card {
  border-radius: 25px;
  background: linear-gradient(145deg, var(--soft-white) 0%, #f0ece5 100%);
  border: 2px solid rgba(212,175,55,0.3);
  box-shadow: 0 20px 60px rgba(0,0,0,0.15), inset 0 1px 0 rgba(255,255,255,0.4);
  transition: transform 0.3s, box-shadow 0.3s, border-top 0.3s;
  border-top: 6px solid transparent;
  position: relative;
  padding: 2rem;
  margin-top: 20px;
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

/* ===== Header with Back Button Layout ===== */
.header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

h3 {
  color: var(--primary-dark);
  border-bottom: 2px solid var(--accent-gold);
  display: inline-block;
  padding-bottom: 5px;
  font-weight: 700;
  margin: 0;
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
    display: inline-flex;
    align-items: center;
}

.btn-back:hover {
    background: linear-gradient(45deg, var(--accent-gold), rgba(212,175,55,0.3));
    transform: translateY(-2px) scale(1.05);
}

/* ===== Table Styling ===== */
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
  font-size: 0.9rem;
}

.table tbody td {
  padding: 1rem;
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

.badge-danger {
  background: linear-gradient(45deg, #dc3545, var(--accent-gold));
  color: white;
  font-weight: 600;
}

.text-muted {
  color: var(--earth-brown) !important;
  font-size: 1.1rem;
  font-weight: 500;
  padding: 3rem 0;
  text-align: center;
}

/* ===== Responsive ===== */
@media (max-width: 576px) {
  .header-row {
    flex-direction: column;
    align-items: flex-start;
  }
  .btn-back {
    margin-top: 10px;
  }
}
</style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="header-row">
            <h3>Manage Tenant Payments</h3>
            <a href="dashboard.php" class="btn btn-outline-secondary btn-back">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <?php if ($payments): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Date Paid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['firstname'].' '.$p['lastname']) ?></td>
                        <td>₱<?= number_format($p['amount'],2) ?></td>
                        <td><?= date('M d, Y', strtotime($p['due_date'])) ?></td>
                        <td>
                          <span class="badge <?= $p['status']==='Paid' ? 'badge-success' : 'badge-danger' ?>">
                            <?= $p['status'] ?>
                          </span>
                        </td>
                        <td><?= $p['date_paid'] ? date('M d, Y h:i A', strtotime($p['date_paid'])) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="text-muted">No payments found.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

