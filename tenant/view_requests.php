<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
  header("Location: ../index.php");
  exit();
}

$db = new Database();
$tenant_id = $_SESSION['user_id'];

$stmt = $db->connect()->prepare("
  SELECT id, subject, description, status, created_at 
  FROM maintenance_requests 
  WHERE tenant_id = ? 
  ORDER BY created_at DESC
");
$stmt->execute([$tenant_id]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Maintenance Requests | ApartmentHub</title>
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

    body {
      display: flex;             /* Added for footer at bottom */
      flex-direction: column;    /* Stack content vertically */
      min-height: 100vh;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
      position: relative;
      overflow-x: hidden;
      margin: 0;
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
      flex: 1;                  /* Added so container fills space above footer */
      position: relative;
      z-index: 1;
      margin-top: 50px;
    }

    h2 {
      font-weight: 700;
      color: var(--primary-dark);
      font-size: 2.2rem;
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

    .text-primary {
      color: var(--primary-dark) !important;
    }

    .btn-outline-primary {
      border: 2px solid var(--accent-gold);
      color: var(--primary-dark);
      background: transparent;
      border-radius: 20px;
      padding: 10px 25px;
      font-weight: 600;
      transition: all 0.4s ease;
    }

    .btn-outline-primary:hover {
      background: linear-gradient(135deg, var(--accent-gold) 0%, var(--luxury-gold) 100%);
      border-color: var(--accent-gold);
      color: var(--deep-navy);
      transform: translateY(-3px);
      box-shadow: 0 5px 20px rgba(212, 175, 55, 0.4);
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

    .card {
      border: none;
      border-radius: 25px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.15);
      background: linear-gradient(145deg, #ffffff 0%, #f8f5f0 100%);
      border: 2px solid rgba(212, 175, 55, 0.2);
      position: relative;
      animation: fadeInUp 0.8s ease;
      overflow: hidden;
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
    }

    .table {
      margin-bottom: 0;
    }

    .table thead {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
    }

    .table thead th {
    color: #f8f5f0 !important;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
    font-weight: 700;
    letter-spacing: 1px;
    background: none !important;
    border-bottom: 2px solid var(--accent-gold);
  }

    .table-primary {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
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

    .bg-secondary {
      background: linear-gradient(135deg, var(--soft-gray) 0%, var(--primary-dark) 100%) !important;
      box-shadow: 0 3px 10px rgba(149, 165, 166, 0.3);
    }

    .text-muted {
      color: var(--earth-brown) !important;
      font-size: 1.1rem;
      font-weight: 500;
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
      h2 {
        font-size: 1.5rem;
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
      margin-top: auto;  /* ensures footer stays at bottom */
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

  </style>
</head>
<body class="bg-light">

<div class="floating-decoration deco-1"></div>
<div class="floating-decoration deco-2"></div>

<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-primary">My Maintenance Requests</h2>
    <div class="d-flex gap-2">
      <button class="btn btn-back" onclick="history.back()"><i class="bi bi-arrow-left"></i> Back</button>
      <a href="maintenance_request.php" class="btn btn-outline-primary">+ New Request</a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table table-striped table-hover mb-0">
        <thead class="table-primary">
          <tr>
            <th>#</th>
            <th>Subject</th>
            <th>Description</th>
            <th>Status</th>
            <th>Submitted On</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($requests): ?>
            <?php foreach ($requests as $r): ?>
              <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['subject']) ?></td>
                <td><?= htmlspecialchars($r['description']) ?></td>
                <td>
                  <span class="badge bg-<?= 
                    $r['status'] === 'Completed' ? 'success' : 
                    ($r['status'] === 'In Progress' ? 'warning' : 'secondary') ?>">
                    <?= $r['status'] ?>
                  </span>
                </td>
                <td><?= date("M d, Y h:i A", strtotime($r['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center text-muted py-4">
                You haven't submitted any maintenance requests yet.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<footer>
  <p class="mb-0">&copy; 2025 ApartmentHub. All rights reserved.</p>
</footer>

</body>
</html>

