<?php
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
    $verification_code = $_POST['verification_code'];

    // Check if the email and verification code exist and the user is not verified
    $stmt = $pdo->prepare("SELECT id FROM userss WHERE email = ? AND token = ? AND verified = 0");
    $stmt->execute([$email, $verification_code]);

    if ($stmt->rowCount() > 0) {
        // Update user's verified status
        $stmt = $pdo->prepare("UPDATE userss SET verified = 1, token = NULL WHERE email = ? AND token = ?");
        if ($stmt->execute([$email, $verification_code])) {
            $success_message = "Email successfully verified! You may now Login.";
        } else {
            $error_message = "Verification failed!";
        }
    } else {
        $error_message = "Invalid verification code or email already verified!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
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
        body::before {
           content: "";
           position: fixed;
           top: 0;
           left: 0;
           width: 100%;
           height: 100%;
           background: url('images/qsu.jpg') no-repeat center center;
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
        .verify-form {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        .verify-form h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .verify-form input[type="submit"] {
            background-color: #00a65a;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            padding: 10px;
        }
        .verify-form input[type="submit"]:hover {
            background-color: #2ecc71;
        }
        .message {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .success-message {
            color: green;
        }
        .error-message {
            color: red;
        }
        .verify-form input[type="submit"], .btn-login {
            background-color: #198754;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            padding: 10px;
            width: 100%;
            margin-top: 15px;
        }
        .verify-form input[type="submit"]:hover, .register-container .btn-login:hover {
            background-color: #157347;
        }
    </style>
</head>
<body>
    <div class="verify-form">
        <h2>Email Verification</h2>
        <?php if (isset($error_message)): ?>
            <div class="message error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php elseif (isset($success_message)): ?>
            <div class="message success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Enter your Email" required>
            </div>
            <div class="mb-3">
                <label for="verification_code" class="form-label">Verification Code:</label>
                <input type="text" name="verification_code" id="verification_code" class="form-control" placeholder="Enter Verification Code" required>
            </div>
            <input type="submit" value="Verify" class="btn btn-primary w-100">
        </form>
        <button onclick="window.location.href='login.php'" class="btn-login">Login now</button>
    </div>
</body>
</html>
