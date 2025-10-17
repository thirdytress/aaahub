<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
  header("Location: ../index.php");
  exit();
}

$db = new Database();
$tenant_id = $_SESSION['user_id'];

// Generate monthly billing before showing payments
$db->generateMonthlyPayments();

// Get tenant's payments
$payments = $db->getTenantPayments($tenant_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Payments | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
      display: flex;
      flex-direction: column;
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

    .container {
      position: relative;
      z-index: 1;
      padding-top: 50px;
      padding-bottom: 50px;
      flex: 1;
    }

    h3 {
      font-weight: 700;
      color: var(--primary-dark);
      font-size: 2.2rem;
      margin-bottom: 2rem;
      position: relative;
      display: inline-block;
      animation: fadeInDown 0.8s ease;
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    h3::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 80px;
      height: 4px;
      background: var(--accent-gold);
      border-radius: 2px;
    }

    h3 i {
      color: var(--accent-gold);
      filter: drop-shadow(0 2px 4px rgba(212, 175, 55, 0.3));
    }

    .text-primary {
      color: var(--primary-dark) !important;
    }

    .btn-back {
      background: linear-gradient(135deg, var(--soft-gray) 0%, var(--primary-dark) 100%);
      color: white;
      border: none;
      border-radius: 20px;
      padding: 10px 25px;
      transition: all 0.4s ease;
      font-weight: 600;
      box-shadow: 0 4px 15px rgba(149, 165, 166, 0.3);
    }

    .btn-back:hover {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--soft-gray) 100%);
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(149, 165, 166, 0.5);
      color: white;
    }

    .table-responsive {
      border-radius: 25px;
      overflow: hidden;
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

    .table-responsive::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, var(--primary-dark) 0%, var(--primary-blue) 50%, var(--accent-gold) 100%);
    }

    .table {
      margin-bottom: 0;
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

    .bg-success {
      background: linear-gradient(135deg, #27ae60 0%, #229954 100%) !important;
      box-shadow: 0 3px 10px rgba(39, 174, 96, 0.3);
    }

    .bg-warning {
      background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%) !important;
      box-shadow: 0 3px 10px rgba(243, 156, 18, 0.3);
      color: white !important;
    }

    .text-muted {
      color: var(--earth-brown) !important;
      font-size: 1.1rem;
      font-weight: 500;
      padding: 3rem 0;
      text-align: center;
    }

    footer {
      background: linear-gradient(135deg, var(--deep-navy) 0%, var(--primary-dark) 100%);
      color: white;
      padding: 30px 20px;
      text-align: center;
      margin-top: auto;
      border-top: 3px solid var(--accent-gold);
      box-shadow: 0 -4px 30px rgba(0,0,0,0.3);
      position: relative;
      z-index: 1;
    }

    footer::before {
      content: '';
      position: absolute;
      top: -3px;
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

    footer p {
      margin-bottom: 0;
      font-weight: 500;
      letter-spacing: 0.5px;
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
      bottom: 20%;
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
      h3 {
        font-size: 1.8rem;
      }

      .table thead th,
      .table tbody td {
        padding: 1rem 0.5rem;
        font-size: 0.85rem;
      }
    }
  </style>
</head>
<body class="bg-light">

<div class="floating-decoration deco-1"></div>
<div class="floating-decoration deco-2"></div>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="text-primary mb-0"><i class="bi bi-credit-card me-2"></i>My Payments</h3>
    <button class="btn btn-back" onclick="history.back()"><i class="bi bi-arrow-left"></i> Back</button>
  </div>

  <?php if ($payments): ?>
    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-dark">
          <tr>
            <th>Apartment</th>
            <th>Amount (₱)</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Date Paid</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['apartment_name'] ?? 'N/A') ?></td>
              <td><?= number_format($p['amount'], 2) ?></td>
              <td><?= date('M d, Y', strtotime($p['due_date'])) ?></td>
              <td>
                <span class="badge bg-<?= strtolower($p['status']) === 'paid' ? 'success' : 'warning' ?>">
                  <?= htmlspecialchars($p['status']) ?>
                </span>
              </td>
              <td><?= $p['date_paid'] ? date('M d, Y', strtotime($p['date_paid'])) : '-' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-muted">No payment records yet.</p>
  <?php endif; ?>
</div>

<!-- Add this just above the <footer> -->
<div class="container mt-4 text-center">
  <div class="card shadow p-4 mx-auto" style="max-width: 600px;">
    <h3 class="text-primary mb-3"><i class="bi bi-envelope-paper"></i> Contact Us</h3>
    <p class="mb-1">
      <strong>Phone:</strong> 
      <a href="tel:+639123456789" class="text-decoration-none">+63 912 345 6789</a>
    </p>
    <p class="mb-0">
      <strong>Email:</strong> 
      <a href="mailto:support@apartmenthub.com" class="text-decoration-none">support@apartmenthub.com</a>
    </p>
  </div>
</div>

<footer>
  <p class="mb-1">&copy; 2025 ApartmentHub. All rights reserved.</p>
  <p class="mb-0">
    <strong>Contact:</strong> 
    <a href="tel:+639123456789" class="text-decoration-none text-white">0993962687</a> | 
    <strong>Email:</strong> 
    <a href="mailto:support@apartmenthub.com" class="text-decoration-none text-white">martynjosephseloterio@gmail.com</a>
  </p>
</footer>

</body>
</html>
