<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
  header("Location: ../index.php");
  exit();
}

$db = new Database();
$tenant_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Maintenance Request | ApartmentHub</title>
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

    * { margin: 0; padding: 0; box-sizing: border-box; }

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
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-image: 
        repeating-linear-gradient(90deg, rgba(212, 175, 55, 0.03) 0px, transparent 1px, transparent 40px, rgba(212, 175, 55, 0.03) 41px),
        repeating-linear-gradient(0deg, rgba(212, 175, 55, 0.03) 0px, transparent 1px, transparent 40px, rgba(212, 175, 55, 0.03) 41px);
      z-index: 0;
      pointer-events: none;
    }

    .container { position: relative; z-index: 1; margin-top: 80px; }

    h2 {
      font-weight: 800;
      color: var(--primary-dark);
      font-size: 2.5rem;
      margin-bottom: 3rem;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
      position: relative;
      display: inline-block;
      animation: fadeInDown 0.8s ease;
    }

    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 100px; height: 4px;
      background: linear-gradient(90deg, transparent, var(--accent-gold), transparent);
      border-radius: 2px;
    }

    h2 i { color: var(--accent-gold); filter: drop-shadow(0 2px 4px rgba(212, 175, 55, 0.3)); }

    .text-primary { color: var(--primary-dark) !important; }

    .alert {
      border: none;
      border-radius: 20px;
      font-weight: 500;
      padding: 1.2rem 1.5rem;
      animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    .alert-success { background: linear-gradient(135deg, rgba(39, 174, 96, 0.15) 0%, rgba(34, 153, 84, 0.15) 100%); border: 2px solid rgba(39, 174, 96, 0.3); color: #27ae60; }
    .alert-danger { background: linear-gradient(135deg, rgba(231, 76, 60, 0.15) 0%, rgba(192, 57, 43, 0.15) 100%); border: 2px solid rgba(231, 76, 60, 0.3); color: #e74c3c; }

    .card {
      border: none;
      border-radius: 30px;
      box-shadow: 0 30px 80px rgba(0,0,0,0.2), inset 0 1px 0 rgba(255,255,255,0.6);
      background: linear-gradient(145deg, #ffffff 0%, #f8f5f0 100%);
      border: 2px solid rgba(212, 175, 55, 0.3);
      position: relative;
      animation: fadeInUp 0.8s ease;
      max-width: 600px;
      margin-bottom: 30px;
    }

    @keyframes fadeInUp { from { opacity: 0; transform: translateY(50px); } to { opacity: 1; transform: translateY(0); } }

    .card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 6px;
      background: linear-gradient(90deg, var(--primary-dark) 0%, var(--primary-blue) 50%, var(--accent-gold) 100%);
      border-radius: 30px 30px 0 0;
    }

    .form-label { color: var(--primary-dark); font-weight: 600; margin-bottom: 0.8rem; font-size: 1rem; }
    .form-control {
      border: 2px solid rgba(212, 175, 55, 0.3);
      border-radius: 15px;
      padding: 12px 20px;
      transition: all 0.3s ease;
      background: white;
      color: var(--earth-brown);
      font-weight: 500;
    }
    .form-control:focus { box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25); border-color: var(--accent-gold); background: white; }
    .form-control::placeholder { color: var(--soft-gray); opacity: 0.7; }
    textarea.form-control { resize: vertical; min-height: 120px; }

    .btn-primary {
      background: linear-gradient(135deg, var(--accent-gold) 0%, var(--luxury-gold) 100%);
      border: none;
      color: var(--deep-navy);
      padding: 15px;
      border-radius: 20px;
      font-weight: 700;
      letter-spacing: 1px;
      transition: all 0.4s ease;
      box-shadow: 0 5px 20px rgba(212, 175, 55, 0.4);
      text-transform: uppercase;
      font-size: 1rem;
    }
    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 30px rgba(212, 175, 55, 0.6);
      background: linear-gradient(135deg, var(--luxury-gold) 0%, var(--accent-gold) 100%);
      color: var(--deep-navy);
    }

    .text-decoration-none { color: var(--primary-dark); font-weight: 600; transition: all 0.3s ease; }
    .text-decoration-none:hover { color: var(--accent-gold); transform: translateX(-5px); display: inline-block; }

    .floating-decoration { position: fixed; pointer-events: none; z-index: 0; }
    .deco-1 { top: 15%; left: 5%; width: 150px; height: 150px; background: radial-gradient(circle, rgba(212, 175, 55, 0.1), transparent); border-radius: 50%; animation: float 6s ease-in-out infinite; }
    .deco-2 { bottom: 20%; right: 8%; width: 200px; height: 200px; background: radial-gradient(circle, rgba(52, 152, 219, 0.1), transparent); border-radius: 50%; animation: float 8s ease-in-out infinite reverse; }
    @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-30px); } }

    @media (max-width: 768px) { h2 { font-size: 2rem; } .card { padding: 2rem !important; } }

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

<div class="container mt-5">
  <h2 class="text-center text-primary mb-4"><i class="bi bi-tools"></i> Submit Maintenance Request</h2>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success text-center"><?= htmlspecialchars($_GET['msg']); ?></div>
  <?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger text-center"><?= htmlspecialchars($_GET['error']); ?></div>
  <?php endif; ?>

  <div class="card shadow p-4 mx-auto">
    <form action="../actions/submit_request.php" method="POST">
      <div class="mb-3">
        <label class="form-label">Subject</label>
        <input type="text" name="subject" class="form-control" placeholder="Short summary (e.g., Broken Aircon)" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="5" placeholder="Describe the issue in detail..." required></textarea>
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-send-fill me-2"></i>Submit Request</button>
      </div>
    </form>
    <div class="text-center mt-3">
      <a href="dashboard.php" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
    </div>
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


