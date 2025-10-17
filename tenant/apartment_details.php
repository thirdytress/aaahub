<?php
session_start();
require_once "classes/database.php";
$db = new Database();
$conn = $db->connect();

// ✅ Require tenant login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'tenant') {
  // I-save kung saan gustong pumunta after login
  $_SESSION['redirect_after_login'] = "apartment_details.php?id=" . ($_GET['id'] ?? 0);
  header("Location: index.php");
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
  <a href="index.php" class="btn btn-secondary mb-3">&larr; Back to Home</a>
  <h2 class="text-center mb-3"><?= htmlspecialchars($apartment['Name']) ?></h2>

  <div class="row mb-4">
    <?php if ($images): ?>
      <?php foreach ($images as $img): ?>
        <div class="col-md-4 mb-3">
          <img src="<?= htmlspecialchars($img['PicPath']) ?>" class="img-fluid rounded shadow-sm" alt="">
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12 text-center text-muted">No images available for this apartment.</div>
    <?php endif; ?>
  </div>

  <h5><strong>Location:</strong> <?= htmlspecialchars($apartment['Location']) ?></h5>
  <p><strong>Type:</strong> <?= htmlspecialchars($apartment['Type']) ?></p>
  <p><?= htmlspecialchars($apartment['Description']) ?></p>
  <p><strong>Monthly Rate:</strong> ₱<?= number_format($apartment['MonthlyRate'], 2) ?></p>

  <a href="apply.php?apartment_id=<?= $apartment['ApartmentID'] ?>" class="btn btn-success w-100">Apply for this Apartment</a>
</div>
</body>
</html>
