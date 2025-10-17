<?php
session_start();
require_once "../classes/database.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

$db = new Database();
$conn = $db->connect();

if (isset($_POST['action'])) {

    // ---------- REGISTER: generate OTP and send email ----------
    if ($_POST['action'] == 'register') {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Check if username or email already exists
        $checkStmt = $conn->prepare("SELECT * FROM tenants WHERE username = ? OR email = ?");
        $checkStmt->execute([$username, $email]);
        if ($checkStmt->rowCount() > 0) {
            echo "USERNAME_OR_EMAIL_EXISTS";
            exit;
        }

        // Generate OTP and expiry (10 minutes)
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_time'] = time();
        $_SESSION['registration_data'] = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'password' => $password
        ];

        // Send OTP email
        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'martynjosephseloterio@gmail.com';
            $mail->Password = 'urak cjjk jwbk vnao'; // use app password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $mail->setFrom('martynjosephseloterio@gmail.com', 'ApartmentHub');
            $mail->addAddress($email, $firstname);
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email - ApartmentHub';
            $mail->Body = "<h2>Hi {$firstname}, your OTP is: <strong>{$otp}</strong></h2>
                           <p>This OTP will expire in 10 minutes.</p>";

            $mail->send();
            echo "OTP_SENT";

        } catch (Exception $e) {
            echo "FAILED_OTP: " . $mail->ErrorInfo;
        }
    }

    // ---------- VERIFY OTP: insert user into database ----------
    if ($_POST['action'] == 'verify_otp') {
        if (!isset($_SESSION['otp']) || !isset($_SESSION['registration_data'])) {
            echo "SESSION_EXPIRED";
            exit;
        }

        // Check OTP expiry
        if (time() - $_SESSION['otp_time'] > 600) { // 10 minutes
            unset($_SESSION['otp'], $_SESSION['registration_data'], $_SESSION['otp_time']);
            echo "OTP_EXPIRED";
            exit;
        }

        // Check OTP
        if ($_POST['otp'] == $_SESSION['otp']) {
            $data = $_SESSION['registration_data'];

            // Insert tenant into DB
            $stmt = $conn->prepare("INSERT INTO tenants (firstname, lastname, username, email, phone, password) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$data['firstname'], $data['lastname'], $data['username'], $data['email'], $data['phone'], $data['password']])) {

                // Optional: send welcome email
                $welcomeMail = new PHPMailer(true);
                try {
                    $welcomeMail->SMTPDebug = 0;
                    $welcomeMail->isSMTP();
                    $welcomeMail->Host = 'smtp.gmail.com';
                    $welcomeMail->SMTPAuth = true;
                    $welcomeMail->Username = 'martynjosephseloterio@gmail.com';
                    $welcomeMail->Password = 'urak cjjk jwbk vnao';
                    $welcomeMail->SMTPSecure = 'tls';
                    $welcomeMail->Port = 587;
                    $welcomeMail->CharSet = 'UTF-8';
                    $welcomeMail->SMTPOptions = [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        ]
                    ];

                    $welcomeMail->setFrom('martynjosephseloterio@gmail.com', 'ApartmentHub');
                    $welcomeMail->addAddress($data['email'], $data['firstname']);
                    $welcomeMail->isHTML(true);
                    $welcomeMail->Subject = 'Registration Successful - Welcome to ApartmentHub!';
                    $welcomeMail->Body = "<h2>Hi {$data['firstname']}, your registration is successful!</h2>
                                          <p>You can now log in and start browsing available apartments.</p>";
                    $welcomeMail->send();
                } catch (Exception $e) {
                    error_log("Welcome email failed: " . $e->getMessage());
                }

                // Clear session
                unset($_SESSION['otp'], $_SESSION['registration_data'], $_SESSION['otp_time']);
                echo "OTP_VALID"; // frontend can redirect to login page
            } else {
                echo "DB_ERROR";
            }
        } else {
            echo "INVALID_OTP";
        }
    }
}
