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
        $error_message = "Email already exists!";
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

                $mail->setFrom('gdiola550@gmail.com', 'Qsu Research');
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
                $error_message = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error_message = "Registration failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }
        /*body {
           position: relative;
           margin: 0;
           padding-top: 70px; /* Padding for fixed header */
           /*font-family: 'Roboto', sans-serif;
        /*}*/
 
        body::before {
           content: "";
           position: fixed;
           top: 0;
           left: 0;
           width: 100%;
           height: 100%;
           background: url('images/itbuilding.jpg') no-repeat center center;
           background-size: cover;
           filter: blur(8px); /* Adjust the blur intensity */
           z-index: -2; /* Place it behind the overlay */
        }

        body::after {
           content: "";
           position: fixed;
           top: 0;
           left: 0;
           width: 100%;
           height: 100%;
           background: rgba(255, 255, 255, 0.4); /* Light white overlay */
           z-index: -1; /* Place it above the blurred background */
        }

        .register-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .register-container h2 {
            margin-bottom: 20px;
        }
        .register-container .form-label {
            float: left;
        }
        .register-container input[type="submit"], .register-container .btn-login {
            background-color: #198754;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            padding: 10px;
            width: 100%;
            margin-top: 15px;
        }
        .register-container input[type="submit"]:hover, .register-container .btn-login:hover {
            background-color: #157347;
        }
        .register-container .message {
            margin-top: 15px;
            font-weight: bold;
        }
        .register-container .error-message {
            color: #dc3545;
        }
        .register-container .success-message {
            color: #198754;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Create Your Account</h2>
        <?php if (isset($error_message)): ?>
            <div class="message error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php elseif (isset($success_message)): ?>
            <div class="message success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <input type="submit" value="Register">
        </form>
        <button onclick="window.location.href='login.php'" class="btn-login">Already have an account? Login</button>
    </div>
</body>
</html>
