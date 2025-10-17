<?php
session_start();
require_once "../classes/database.php";

// Server-side restriction: Only tenant can access
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$tenant_id = $_SESSION['user_id'];
$apartments = $db->getAvailableApartments($tenant_id);
$leases = $db->getTenantLeases($tenant_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tenant Dashboard | ApartmentHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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


.container {
    position: relative;
    z-index: 1;
    margin-top: 50px;
}

h2 {
    font-weight: 700;
    color: var(--primary-dark);
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

.card {
    border: none;
    border-radius: 25px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    background: linear-gradient(145deg, #ffffff 0%, #f8f5f0 100%);
    overflow: hidden;
    position: relative;
    border: 2px solid rgba(212, 175, 55, 0.2);
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, var(--primary-dark) 0%, var(--primary-blue) 50%, var(--accent-gold) 100%);
    transform: scaleX(0);
    transition: transform 0.5s ease;
    z-index: 1;
}

.card:hover::before {
    transform: scaleX(1);
}

.card:hover {
    transform: translateY(-15px) scale(1.02);
    box-shadow: 0 30px 80px rgba(0,0,0,0.25);
}

.card-img-top {
    height: 200px;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.card:hover .card-img-top {
    transform: scale(1.1);
}

.card-body {
    padding: 2rem;
}

.card-body h5 {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--primary-dark);
    margin-bottom: 1rem;
}

.card-body p {
    color: var(--earth-brown);
    font-weight: 500;
    line-height: 1.6;
}

.card .btn-primary {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-dark) 100%);
    border: none;
    color: white;
    padding: 12px 30px;
    border-radius: 20px;
    font-weight: 600;
    transition: all 0.4s ease;
    box-shadow: 0 5px 20px rgba(52, 152, 219, 0.4);
}

.card .btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(52, 152, 219, 0.6);
}

.table-responsive {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    margin-bottom: 3rem;
    background: white;
}

.table {
    margin-bottom: 0;
}

.table thead {
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

.table-bordered {
    border: none;
}

.text-muted {
    color: var(--earth-brown) !important;
    font-size: 1.1rem;
    font-weight: 500;
    padding: 2rem 0;
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
        font-size: 1.8rem;
    }

    .card-body {
        padding: 1.5rem;
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
      margin-top: auto; /* Ito ang magic line */
    }

</style>
</head>
<body>

<div class="floating-decoration deco-1"></div>
<div class="floating-decoration deco-2"></div>

<div class="container mt-5">

    <section class="container mt-5">
    <h2 class="mb-4 text-primary fw-bold text-center">Available Apartments</h2>
    <button class="btn btn-back" onclick="history.back()">&larr; Back</button>
    
    <div id="message-area"></div>

    <div class="row g-4 justify-content-center">
        <?php if (!empty($apartments)): ?>
            <?php foreach ($apartments as $a): ?>
                <?php 
                // 1. DETERMINE THE FULL IMAGE PATH (similar to your index.php logic)
                // Assuming $a['Image'] contains the path/filename starting from 'apartments/' 
                // e.g., 'apartments/apt004.jpg'
                
                $image_db_path = $a['Image'] ?? '';
                
                // Set the path the browser will use in the <img> src
                // Since this file is likely in a subfolder (e.g., 'tenant/'), 
                // we need '../' to go up to the root level where 'upload' is.
                $image_src_path = !empty($image_db_path) 
                                ? '../upload/' . htmlspecialchars($image_db_path) 
                                : '../images/airbnb1.jpg';
                
                // 2. CHECK IF THE FILE ACTUALLY EXISTS (For PHP side error handling/fallback)
                // This check uses the file system path, which might be slightly different 
                // from the browser path, but for simplicity, we'll use the 'upload/' prefix 
                // if the script is running from the root, OR use a more robust check 
                // if you know the script's actual file system location.
                
                // For safety and clean code, we stick to the browser's path logic above 
                // and rely on the database for the image value.
                ?>
                
                <div class="col-12 col-md-6 col-lg-4 d-flex justify-content-center">
                    <div class="card apartment-card shadow-sm border-0 rounded-4 h-100"
                        style="width: 100%; max-width: 330px;">
                        
                        <img src="<?= $image_src_path ?>"
                             alt="Image of <?= htmlspecialchars($a['Name'] ?? 'Apartment') ?>"
                             class="card-img-top rounded-top-4"
                             style="height: 220px; object-fit: cover;">

                        <div class="card-body text-center d-flex flex-column">
                            <h5 class="fw-bold"><?= htmlspecialchars($a['Name'] ?? 'N/A') ?></h5>
                            
                            <?php if (isset($a['Location'])): ?>
                                <p class="text-muted mb-2"><?= htmlspecialchars($a['Location']) ?></p>
                            <?php endif; ?>

                            <p class="fw-bold text-dark mb-3">
                                ₱<?= number_format($a['MonthlyRate'] ?? 0, 2) ?>/month
                            </p>

                            <a href="../tenant/apartment_details.php?id=<?= urlencode($a['ApartmentID'] ?? '') ?>" 
                                class="btn btn-primary mt-auto w-100">
                                View Details / Apply
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-muted text-center">No available apartments at the moment. Please check back later.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

    <h2 class="text-primary mb-4">My Current Leases</h2>
    <?php if ($leases): ?>
        <div class="table-responsive">
            <table class="table table-bordered bg-white">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Apartment</th>
                        <th>Location</th>
                        <th>Monthly Rate</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leases as $i => $l): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($l['apartment_name']) ?></td>
                            <td><?= htmlspecialchars($l['Location']) ?></td>
                            <td>₱<?= number_format($l['MonthlyRate'], 2) ?></td>
                            <td><?= date('M d, Y', strtotime($l['start_date'])) ?></td>
                            <td><?= date('M d, Y', strtotime($l['end_date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">You currently have no active leases.</p>
    <?php endif; ?>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
