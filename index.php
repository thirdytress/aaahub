<?php
session_start();
require_once "classes/database.php";
$db = new Database();
$conn = $db->connect();

// Fetch only available apartments
$stmt = $conn->prepare("SELECT * FROM apartments WHERE Status = 'Available' ORDER BY DateAdded DESC");
$stmt->execute();
$apartments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ApartmentHub</title>
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

    body {
      background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
      font-family: 'Poppins', sans-serif;
      overflow-x: hidden;
    }

    .navbar {
      background: linear-gradient(135deg, var(--deep-navy), var(--primary-dark));
      border-bottom: 3px solid var(--accent-gold);
    }

    .navbar-brand {
      color: white !important;
      font-weight: 700;
    }

    .nav-link {
      color: rgba(255,255,255,0.8) !important;
      transition: .3s;
    }

    .nav-link:hover {
      color: var(--accent-gold) !important;
    }

    .hero {
      text-align: center;
      padding: 120px 20px;
    }

    .hero h1 {
      font-weight: 800;
      font-size: 3rem;
      color: var(--primary-dark);
    }

    .hero p {
      font-size: 1.3rem;
      color: var(--earth-brown);
      margin: 20px 0 30px;
    }

    .hero .btn {
      background: linear-gradient(135deg, var(--accent-gold), var(--luxury-gold));
      border: none;
      color: var(--deep-navy);
      font-weight: 700;
      padding: 12px 40px;
      border-radius: 25px;
    }

    .apartment-card {
      border-radius: 20px;
      overflow: hidden;
      transition: transform 0.2s ease-in-out;
      max-width: 280px;
      margin: 0 auto;
    }

    .apartment-card:hover {
      transform: scale(1.03);
    }

    .apartment-card img {
      height: 180px;
      object-fit: cover;
    }

    .card-body {
      padding: 15px;
      font-size: 14px;
    }

    .card-title {
      font-size: 16px;
      font-weight: 600;
    }

    .card-text {
      font-size: 13px;
    }

    .btn-success {
      font-size: 13px;
      padding: 6px 10px;
      border-radius: 10px;
    }

    h2.text-primary {
      font-size: 26px;
      text-align: center;
      font-weight: 700;
    }

    .apartment-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 25px;
      justify-items: center;
    }

    section.container {
      max-width: 1000px;
    }

    footer {
      background: linear-gradient(135deg, var(--deep-navy), var(--primary-dark));
      color: white;
      text-align: center;
      padding: 30px;
      margin-top: 80px;
      border-top: 3px solid var(--accent-gold);
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand" href="#">ApartmentHub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item mx-2"><a class="nav-link active" href="#">Home</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#registerModal">Register</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="container">
    <h1>Welcome to ApartmentHub</h1>
    <p>Find your perfect apartment with ease. Connecting tenants and property managers in one smart platform.</p>
    <a href="#" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#registerModal">Get Started</a>
  </div>
</section>

<!-- APARTMENTS -->
<section class="container mt-5">
  <h2 class="mb-4 text-primary fw-bold">Available Apartments</h2>
  <div class="apartment-grid">
    <?php if ($apartments): ?>
      <?php foreach ($apartments as $apt): ?>
        <?php
          // Fetch main image from ApartmentPictures
          $imgStmt = $conn->prepare("SELECT PicPath FROM ApartmentPictures WHERE ApartmentID = :id AND Status = 'active' ORDER BY DateAdded ASC LIMIT 1");
          $imgStmt->bindParam(':id', $apt['ApartmentID']);
          $imgStmt->execute();
          $image = $imgStmt->fetchColumn() ?: 'uploads/default.jpg';
        ?>
        <div class="card apartment-card clickable-card"
             data-bs-toggle="modal"
             data-bs-target="#apartmentModal"
             data-name="<?= htmlspecialchars($apt['Name']) ?>"
             data-description="<?= htmlspecialchars($apt['Description']) ?>"
             data-rate="<?= number_format($apt['MonthlyRate'],2) ?>"
             data-image="<?= htmlspecialchars($image) ?>">

          <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($apt['Name']) ?>">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?= htmlspecialchars($apt['Name']) ?></h5>
            <p class="card-text"><?= htmlspecialchars($apt['Description']) ?></p>
            <p class="card-text"><strong>Monthly Rate:</strong> ₱<?= number_format($apt['MonthlyRate'], 2) ?></p>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'tenant'): ?>
              <button class="btn btn-success btn-sm mt-auto apply-btn" data-apartment="<?= $apt['ApartmentID'] ?>">Apply Now</button>
            <?php else: ?>
              <button class="btn btn-success btn-sm mt-auto" data-bs-toggle="modal" data-bs-target="#loginModal">Apply Now</button>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-muted text-center">No apartments available right now. Please check back later.</p>
    <?php endif; ?>
  </div>
</section>

<footer>
  <p class="mb-0">&copy; 2025 ApartmentHub. All rights reserved.</p>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Show apartment modal with info
  const apartmentModal = document.getElementById('apartmentModal');
  apartmentModal.addEventListener('show.bs.modal', event => {
    const card = event.relatedTarget;
    $('#apartmentModalLabel').text(card.dataset.name);
    $('#apartmentModalDescription').text(card.dataset.description);
    $('#apartmentModalRate').text(card.dataset.rate);
    $('#apartmentModalImage').attr('src', card.dataset.image);
  });
</script>
</body>
</html>
