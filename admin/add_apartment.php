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
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
  --primary-dark: #1a252f;
  --primary-blue: #3498db;
  --accent-gold: #d4af37;
  --luxury-gold: #c9a961;
  --earth-brown: #8b7355;
  --soft-white: #f8f5f0;
}

/* ===== Base Styles ===== */
body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 50%, #f5f1e8 100%);
  margin: 0;
  position: relative;
  overflow-x: hidden;
}

/* ===== Floating Decorations ===== */
body::before, body::after {
  content: '';
  position: fixed;
  border-radius: 50%;
  opacity: 0.1;
  pointer-events: none;
}

body::before {
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, var(--accent-gold), transparent);
  top: -50px;
  left: -50px;
}

body::after {
  width: 200px;
  height: 200px;
  background: radial-gradient(circle, var(--primary-blue), transparent);
  bottom: 20%;
  right: 10%;
}

/* ===== Back Button ===== */
.btn-back {
    border: 2px solid var(--accent-gold);
    color: var(--primary-dark);
    font-weight: 600;
    border-radius: 20px;
    padding: 6px 18px;
    background: linear-gradient(45deg, rgba(212,175,55,0.1), transparent);
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
}

.btn-back:hover {
    background: linear-gradient(45deg, var(--accent-gold), rgba(212,175,55,0.3));
    color: var(--primary-dark);
    transform: translateY(-2px) scale(1.05);
}

/* ===== Container Card ===== */
.container {
  max-width: 700px;
}

.card {
  border-radius: 25px;
  background: linear-gradient(145deg, var(--soft-white) 0%, #f0ece5 100%);
  border: 2px solid rgba(212,175,55,0.3);
  box-shadow: 0 20px 60px rgba(0,0,0,0.15), inset 0 1px 0 rgba(255,255,255,0.4);
  transition: transform 0.3s, box-shadow 0.3s, border-top 0.3s;
  border-top: 6px solid transparent;
  position: relative;
  padding: 2rem;
  margin-top: 20px;
}

.card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 6px;
  background: linear-gradient(90deg, var(--primary-dark), var(--primary-blue), var(--accent-gold));
  border-radius: 25px 25px 0 0;
}

.card:hover {
  transform: translateY(-8px);
  box-shadow: 0 30px 90px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.5);
  border-top: 6px solid var(--luxury-gold);
}

/* ===== Header with Back Button Layout ===== */
.header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

h2 {
  color: var(--primary-dark);
  border-bottom: 2px solid var(--accent-gold);
  display: inline-block;
  padding-bottom: 5px;
  font-weight: 700;
  margin: 0;
}

/* ===== Form Styling ===== */
form label {
  font-weight: 600;
  color: var(--primary-dark);
}

.form-control {
  border-radius: 15px;
  border: 2px solid rgba(212,175,55,0.3);
  box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
  transition: all 0.3s ease;
}

.form-control:focus {
  border-color: var(--accent-gold);
  box-shadow: 0 0 10px rgba(212,175,55,0.3);
  outline: none;
}

/* ===== Buttons ===== */
.btn-success {
  background: linear-gradient(45deg, var(--primary-blue), var(--accent-gold));
  border: none;
  border-radius: 20px;
  padding: 8px 25px;
  font-weight: 600;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-success:hover {
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 8px 20px rgba(0,0,0,0.3);
  background: linear-gradient(45deg, var(--accent-gold), var(--primary-blue));
}

/* ===== Alert Styling ===== */
.alert {
  border-radius: 20px;
  border: 1px solid rgba(212,175,55,0.3);
  background: linear-gradient(145deg, #fff8e1, #fff3d1);
  color: var(--primary-dark);
  font-weight: 500;
  box-shadow: 0 5px 20px rgba(0,0,0,0.1);
  padding: 1rem 1.5rem;
  transition: all 0.3s ease;
}

/* ===== Responsive ===== */
@media (max-width: 576px) {
  .card {
    padding: 1.5rem;
  }
  .btn-success {
    width: 100%;
  }
  .header-row {
    flex-direction: column;
    align-items: flex-start;
  }
  .btn-back {
    margin-top: 10px;
  }
}
</style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="header-row">
            <h2>Add New Apartment</h2>
            <!-- Back button on the right -->
            <a href="dashboard.php" class="btn btn-outline-secondary btn-back">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
        
        <?php if($message): ?>
            <div class="alert"><?= htmlspecialchars($message) ?></div>
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
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

