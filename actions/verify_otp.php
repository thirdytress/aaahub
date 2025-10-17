<?php
session_start();
require_once "../classes/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['otp'])) {
        echo "NO_OTP_PROVIDED";
        exit;
    }

    $otpInput = $_POST['otp'];

    if (!isset($_SESSION['otp']) || !isset($_SESSION['registration_data'])) {
        echo "SESSION_EXPIRED";
        exit;
    }

    // Check OTP expiry (10 minutes)
    if (time() - $_SESSION['otp_time'] > 600) {
        unset($_SESSION['otp'], $_SESSION['registration_data'], $_SESSION['otp_time']);
        echo "OTP_EXPIRED";
        exit;
    }

    if ($otpInput == $_SESSION['otp']) {
        $data = $_SESSION['registration_data'];

        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("INSERT INTO tenants (firstname, lastname, username, email, phone, password) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$data['firstname'], $data['lastname'], $data['username'], $data['email'], $data['phone'], $data['password']])) {
            
            // Clear session
            unset($_SESSION['otp'], $_SESSION['registration_data'], $_SESSION['otp_time']);
            echo "OTP_VALID";
        } else {
            echo "DB_ERROR";
        }
    } else {
        echo "INVALID_OTP";
    }
}
?>
