<?php
// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    echo "You do not have permission to upload articles.";
    exit();
}

// Database connection (adjust your credentials here)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "research";  // Ensure this matches your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? ''; // Capture the author name
    $content = $_POST['content'] ?? '';
    $image = $_FILES['image'] ?? null;

    // Basic validation
    if (empty($title) || empty($author) || empty($content)) {
        echo "Title, author, and content are required.";
        exit();
    }

    // Handle image upload
    if ($image && $image['error'] === 0) {
        $imageName = $image['name'];
        $imageTmpName = $image['tmp_name'];
        $imageSize = $image['size'];
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        // Allow only certain file types (e.g., jpg, png, jpeg)
        $allowedExts = ['jpg', 'jpeg', 'png'];
        if (!in_array($imageExt, $allowedExts)) {
            echo "Only JPG, JPEG, and PNG images are allowed.";
            exit();
        }

        // Set image upload directory and move file
        $uploadDir = 'uploads/'; // Directory to store uploaded images
        $newImageName = uniqid() . '.' . $imageExt;
        $imagePath = $uploadDir . $newImageName;

        if (!move_uploaded_file($imageTmpName, $imagePath)) {
            echo "Failed to upload image.";
            exit();
        }
    } else {
        $imagePath = null; // If no image was uploaded
    }

    // Insert the article into the database
    $stmt = $conn->prepare("INSERT INTO articles (title, author, content, image_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $author, $content, $imagePath);

    if ($stmt->execute()) {
        echo "Article uploaded successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Article</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom right, #4CAF50, #8BC34A); /* Green gradient background */
            color: white;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.9); /* Semi-transparent white background */
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 600px;
            text-align: center;
        }

        h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #333;
        }

        .message {
            font-size: 1.2em;
            margin-top: 20px;
            color: green;
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .submit-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            border-radius: 4px;
            margin-top: 20px;
        }

        .submit-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
</body>
</html>
