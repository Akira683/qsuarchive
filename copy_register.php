<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Include Composer's autoloader

// Database configuration
$host = 'localhost';
$db = 'database';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $verification_code = rand(100000, 999999); // Generate a 6-digit verification code

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM userss WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        echo "Email already exists!";
    } else {
        // Insert user into the database with the verification code
        $stmt = $pdo->prepare("INSERT INTO userss (email, password, token) VALUES (?, ?, ?)");
        if ($stmt->execute([$email, $password, $verification_code])) {
            // Send verification email with the code
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'gdiola550@gmail.com'; // Your Gmail address
                $mail->Password = 'zntj wwez kvpz xzqg'; // Your Gmail App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587; // Use port 587 for TLS, 465 for SSL

                $mail->setFrom('gdiola550@gmail.com', 'Your Name');
                $mail->addAddress($email);
                $mail->Subject = 'Email Verification Code';
                $mail->isHTML(true);
                $mail->Body = "
                    <p>Your verification code is:</p>
                    <h2>$verification_code</h2>
                    <p>Please enter this code to complete your registration.</p>
                ";

                $mail->send();

                // Redirect to a verification page
                header("Location: verify.php?email=" . urlencode($email));
                exit();

            } catch (Exception $e) {
                echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Registration failed!";
        }
    }
}
?>

<form method="post" action="">
    Email: <input type="email" name="email" required>
    Password: <input type="password" name="password" required>
    <input type="submit" value="Register">
</form>
