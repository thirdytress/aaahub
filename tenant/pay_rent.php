<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$tenant_id = $_SESSION['user_id'];

// Generate monthly billing automatically
$db->generateMonthlyPayments();

$payments = $db->getTenantPayments($tenant_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pay Rent | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
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

    html, body {
      height: 100%;
      display: flex;
      flex-direction: column;
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

    .container {
      position: relative;
      z-index: 1;
      padding-top: 50px;
      padding-bottom: 50px;
      flex: 1; /* Itulak ang footer pababa */
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

    .btn-success {
      background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
      border: none;
      color: white;
      padding: 8px 20px;
      border-radius: 15px;
      font-weight: 600;
      transition: all 0.4s ease;
      box-shadow: 0 3px 15px rgba(39, 174, 96, 0.3);
    }

    .btn-success:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 20px rgba(39, 174, 96, 0.5);
      background: linear-gradient(135deg, #229954 0%, #27ae60 100%);
    }

    .text-success {
      color: #27ae60 !important;
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

    footer {
      background: linear-gradient(135deg, var(--deep-navy) 0%, var(--primary-dark) 100%);
      color: white;
      padding: 30px 20px;
      text-align: center;
      border-top: 3px solid var(--accent-gold);
      margin-top: auto;
    }

  </style>
</head>
<body class="bg-light">

<div class="floating-decoration deco-1"></div>
<div class="floating-decoration deco-2"></div>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0 text-primary">My Rent Payments</h3>
    <button class="btn btn-back" onclick="history.back()"><i class="bi bi-arrow-left"></i> Back</button>
  </div>
  
  <div class="table-responsive">
    <table class="table table-bordered text-center align-middle">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Apartment</th>
          <th>Amount</th>
          <th>Due Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($payments as $p): ?>
        <tr>
          <td><?= $p['payment_id'] ?></td>
          <td><?= htmlspecialchars($p['apartment_name']) ?></td>
          <td>₱<?= number_format($p['amount'], 2) ?></td>
          <td><?= date('M d, Y', strtotime($p['due_date'])) ?></td>
          <td>
            <span class="badge bg-<?= $p['status'] === 'Paid' ? 'success' : 'warning' ?>">
              <?= $p['status'] ?>
            </span>
          </td>
          <td>
            <?php if ($p['status'] === 'Unpaid'): ?>
              <form action="../actions/pay_rent_action.php" method="POST">
                <input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>">
                <button class="btn btn-success btn-sm">Pay Now</button>
              </form>
            <?php else: ?>
              <span class="text-success fw-bold">Paid</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
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

