<?php
session_start();
require_once "../classes/database.php";

$db = new Database();
$conn = $db->connect();

// ✅ Require tenant login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'tenant') {
  $_SESSION['redirect_after_login'] = "apartment_details.php?id=" . ($_GET['id'] ?? 0);
  header("Location: ../index.php"); // fixed path
  exit;
}

// ✅ Validate and fetch apartment
$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM apartments WHERE ApartmentID = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$apartment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$apartment) {
  die("Apartment not found!");
}

// ✅ Fetch apartment pictures (if any)
$pictures = $conn->prepare("SELECT PicPath FROM ApartmentPictures WHERE ApartmentID = :id AND Status='active'");
$pictures->bindParam(':id', $id, PDO::PARAM_INT);
$pictures->execute();
$images = $pictures->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($apartment['Name']) ?> | Apartment Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <!-- Back Button -->
  <a href="dashboard.php" class="btn btn-secondary mb-3">
    &larr; Back to Home
  </a>

  <!-- Apartment Name -->
  <h2 class="text-center mb-3"><?= htmlspecialchars($apartment['Name']) ?></h2>

  <!-- Apartment Image Carousel -->
  <div class="mb-4">
  <?php if ($images && count($images) > 0): ?>
    <div id="apartmentCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        <?php foreach ($images as $index => $img): ?>
          <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
            <img src="../<?= htmlspecialchars($img['PicPath']) ?>" 
                 class="d-block w-100 rounded shadow-sm" 
                 alt="Apartment Image <?= $index + 1 ?>" 
                 style="height: 450px; object-fit: cover;">
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Carousel Controls -->
      <button class="carousel-control-prev" type="button" data-bs-target="#apartmentCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#apartmentCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
        <span class="visually-hidden">Next</span>
      </button>

      <!-- Carousel Indicators -->
      <div class="carousel-indicators">
        <?php foreach ($images as $index => $img): ?>
          <button type="button" data-bs-target="#apartmentCarousel" data-bs-slide-to="<?= $index ?>" 
                  class="<?= $index === 0 ? 'active' : '' ?>" 
                  aria-label="Slide <?= $index + 1 ?>"></button>
        <?php endforeach; ?>
      </div>
    </div>
  <?php else: ?>
    <div class="text-center text-muted py-5 border rounded bg-white">
      No images available for this apartment.
    </div>
  <?php endif; ?>
</div>

  <!-- Apartment Info -->
  <div class="bg-white rounded p-4 shadow-sm">
    <h5><strong>Location:</strong> <?= htmlspecialchars($apartment['Location']) ?></h5>
    <p><strong>Type:</strong> <?= htmlspecialchars($apartment['Type']) ?></p>
    <p><?= htmlspecialchars($apartment['Description']) ?></p>
    <p><strong>Monthly Rate:</strong> ₱<?= number_format($apartment['MonthlyRate'], 2) ?></p>

    <a href="apply.php?apartment_id=<?= $apartment['ApartmentID'] ?>" 
       class="btn btn-success w-100 mt-3">Apply for this Apartment</a>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
