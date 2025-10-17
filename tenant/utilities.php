<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
  header("Location: ../index.php");
  exit();
}

$db = new Database();
$conn = $db->connect();
$tenant_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM utilities WHERE tenant_id = ? ORDER BY created_at DESC");
$stmt->execute([$tenant_id]);
$utilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Utilities | ApartmentHub</title>
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
      margin-top: 50px;
      flex: 1;
    }

    /* Title shimmer gold accent */
    h2 {
      font-weight: 700;
      color: var(--primary-dark);
      font-size: 2.2rem;
      margin-bottom: 2rem;
      position: relative;
      display: inline-block;
      animation: fadeInDown 0.8s ease;
    
      
      
    }

    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes shimmer {
      0% { background-position: -200px 0; }
      100% { background-position: 200px 0; }
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
      from { opacity: 0; transform: translateY(50px); }
      to { opacity: 1; transform: translateY(0); }
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

    .table-responsive {
      border-radius: 20px;
      overflow: hidden;
    }

    .table {
      margin-bottom: 0;
    }

    /* Updated gold-accent readable header */
    .table thead {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
    }

    .table thead th {
      color: #f8f5f0 !important;
      text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
      font-weight: 700;
      letter-spacing: 1px;
      border-bottom: 2px solid var(--accent-gold);
      font-size: 0.9rem;
      padding: 1.2rem 1rem;
      background: none !important;
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

    footer {
      background: linear-gradient(135deg, var(--deep-navy) 0%, var(--primary-dark) 100%);
      color: white;
      padding: 30px 20px;
      text-align: center;
      border-top: 3px solid var(--accent-gold);
      margin-top: auto;
      font-weight: 500;
      letter-spacing: 0.5px;
    }
  </style>
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>My Utility Bills</h2>
    <button class="btn btn-back" onclick="history.back()"><i class="bi bi-arrow-left"></i> Back</button>
  </div>

  <div class="card shadow-sm">
    <div class="card-body table-responsive">
      <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
          <tr>
            <th>Month</th>
            <th>Electricity (kWh)</th>
            <th>Water (m³)</th>
            <th>Electricity Bill</th>
            <th>Water Bill</th>
            <th>Total Bill</th>
            <th>Status</th>
            <th>Generated</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($utilities): ?>
            <?php foreach ($utilities as $u): ?>
              <tr>
                <td><?= htmlspecialchars($u['month_year']) ?></td>
                <td><?= $u['electricity_usage'] ?></td>
                <td><?= $u['water_usage'] ?></td>
                <td>₱<?= number_format($u['electricity_bill'], 2) ?></td>
                <td>₱<?= number_format($u['water_bill'], 2) ?></td>
                <td><strong>₱<?= number_format($u['total_bill'], 2) ?></strong></td>
                <td>
                  <span class="badge bg-<?= $u['status'] === 'Paid' ? 'success' : 'warning' ?>">
                    <?= htmlspecialchars($u['status']) ?>
                  </span>
                </td>
                <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-muted">No utility records found.</td></tr>
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


