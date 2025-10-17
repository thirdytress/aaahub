<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $monthly_rate = trim($_POST['monthly_rate']);

    // Step 1: Add main apartment record
    $stmt = $db->connect()->prepare("
        INSERT INTO apartments (name, type, location, description, monthly_rate)
        VALUES (:n, :t, :l, :d, :r)
    ");
    $stmt->bindParam(':n', $name);
    $stmt->bindParam(':t', $type);
    $stmt->bindParam(':l', $location);
    $stmt->bindParam(':d', $description);
    $stmt->bindParam(':r', $monthly_rate);
    $stmt->execute();

    $apartment_id = $db->connect()->lastInsertId();

    // Step 2: Handle multiple image uploads
    $upload_dir = "../uploads/apartments/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] == 0) {
            $ext = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
            $file_path = 'uploads/apartments/' . uniqid() . '.' . $ext;
            move_uploaded_file($tmp_name, '../' . $file_path);

            // Save each image path
            $insertPic = $db->connect()->prepare("
                INSERT INTO ApartmentPictures (ApartmentID, PicPath, Status)
                VALUES (:aid, :path, 'active')
            ");
            $insertPic->bindParam(':aid', $apartment_id);
            $insertPic->bindParam(':path', $file_path);
            $insertPic->execute();
        }
    }

    echo "<script>alert('Apartment and pictures added successfully!'); window.location.href='dashboard.php';</script>";
}
?>





<!-- HTML Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Apartment | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow col-md-8 mx-auto">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">Add New Apartment</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Type</label>
                    <input type="text" name="type" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label>Monthly Rate</label>
                    <input type="number" step="0.01" name="monthly_rate" class="form-control" required>
                </div>
                <div class="mb-3">
  <label>Upload Apartment Images</label>
  <input type="file" name="images[]" class="form-control" accept="image/*" multiple required>
</div>

                <button type="submit" class="btn btn-success w-100">Add Apartment</button>
                <a href="dashboard.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
