<?php
require_once "classes/database.php";
$db = new Database();

$id = $_GET['id'] ?? 0;
$stmt = $db->connect()->prepare("SELECT * FROM apartments WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$apartment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$apartment) {
  die("Apartment not found!");
}

$pictures = $db->connect()->prepare("SELECT PicPath FROM ApartmentPictures WHERE ApartmentID = :id AND Status='active'");
$pictures->bindParam(':id', $id);
$pictures->execute();
$images = $pictures->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($apartment['name']) ?> | Apartment Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="text-center mb-3"><?= htmlspecialchars($apartment['name']) ?></h2>
  <div class="row mb-4">
    <?php foreach ($images as $img): ?>
      <div class="col-md-4 mb-3">
        <img src="<?= htmlspecialchars($img['PicPath']) ?>" class="img-fluid rounded shadow-sm" alt="">
      </div>
    <?php endforeach; ?>
  </div>

  <h5><strong>Location:</strong> <?= htmlspecialchars($apartment['location']) ?></h5>
  <p><strong>Type:</strong> <?= htmlspecialchars($apartment['type']) ?></p>
  <p><?= htmlspecialchars($apartment['description']) ?></p>
  <p><strong>Monthly Rate:</strong> ₱<?= number_format($apartment['monthly_rate'], 2) ?></p>

  <a href="apply.php?apartment_id=<?= $apartment['id'] ?>" class="btn btn-success w-100">Apply for this Apartment</a>
</div>
</body>
</html>
