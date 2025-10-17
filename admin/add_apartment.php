<?php
session_start();
require_once "../classes/database.php";

$db = new Database();
$conn = $db->connect();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $rate = floatval($_POST['rate']);
    $status = 'Available';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = "../uploads/apartments/"; // correct path
    $filename = basename($_FILES['image']['name']);
    $targetFile = $uploadDir . time() . "_" . $filename;
    $fileType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO apartments (Name, Description, MonthlyRate, Image, Status, DateAdded) VALUES (?, ?, ?, ?, ?, NOW())");
            if ($stmt->execute([$name, $description, $rate, $targetFile, $status])) {
                $message = "Apartment added successfully!";
            } else {
                $message = "Database error while adding apartment.";
            }
        } else {
            $message = "Failed to move uploaded file. Check folder permissions.";
        }
    } else {
        $message = "Only JPG, PNG, and GIF files are allowed.";
    }
} else {
    $message = "Please upload an image for the apartment.";
}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Apartment | ApartmentHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Add New Apartment</h2>
    
    <?php if($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Apartment Name</label>
            <input type="text" class="form-control" name="name" required>
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea class="form-control" name="description" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label>Monthly Rate</label>
            <input type="number" step="0.01" class="form-control" name="rate" required>
        </div>
        <div class="mb-3">
            <label>Apartment Image</label>
            <input type="file" class="form-control" name="image" accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-success">Add Apartment</button>
    </form>
</div>
</body>
</html>
