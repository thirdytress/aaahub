<?php
session_start();
require_once "../classes/database.php";

$db = new Database();
$conn = $db->connect();

// Require tenant login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'tenant') {
    $_SESSION['redirect_after_login'] = "tenant/apartment_details.php?id=" . ($_GET['apartment_id'] ?? 0);
    header("Location: ../index.php");
    exit;
}

// Get tenant ID and apartment ID
$tenant_id = $_SESSION['user_id'];
$apartment_id = intval($_GET['apartment_id'] ?? 0);

if ($apartment_id <= 0) {
    die("Invalid apartment.");
}

// Check if application already exists
$stmt = $conn->prepare("SELECT * FROM applications WHERE tenant_id = :tenant AND apartment_id = :apartment");
$stmt->execute(['tenant' => $tenant_id, 'apartment' => $apartment_id]);

if ($stmt->rowCount() > 0) {
    $_SESSION['message'] = "You have already applied for this apartment.";
    header("Location: apartment_details.php?id=$apartment_id");
    exit;
}

// Insert new application
$stmt = $conn->prepare("INSERT INTO applications (tenant_id, apartment_id, app_status, date_applied) VALUES (:tenant, :apartment, 'Pending', NOW())");
$stmt->execute(['tenant' => $tenant_id, 'apartment' => $apartment_id]);

$_SESSION['message'] = "Application submitted successfully!";
header("Location: apartment_details.php?id=$apartment_id");
exit;
