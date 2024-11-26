<?php
session_start();
require_once('config.php'); // Make sure this file contains your DB connection setup

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Fetch articles from the database
$sql = "SELECT * FROM articles ORDER BY created_at DESC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>What's New - QSU Student Research</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding-top: 7rem;
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

        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(90deg, #ffffff, #a5d6a7); /* Light green gradient background */
            padding: 3px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000; /* Ensure it stays on top of other elements */
        }
        header h2 {
            margin: 0;
            font-size: 1.8rem;
            color: #2e7d32;
            padding-right: 67.5rem;
        }

        .news-section {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            padding: 1rem;
        }

        .card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }

        .card:hover {
            transform: scale(1.02);
            box-shadow: 0px 6px 18px rgba(0, 0, 0, 0.15);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
        }

        .card-text {
            color: #666;
        }

        .img-fluid {
            object-fit: cover;
            height: 100%;
        }

        footer {
           background-color: #004f23;
           color: white;
           padding: 5px;
           text-align: center;
           margin-top: 0; /* No margin at the top */
        }
        .btn-logout {
            background-color: red;
            color: #fff;
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        .btn-logout:hover {
            background-color: #c0392b;
        }
        .menu-button {
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            width: 30px;
            height: 21px;
            background: transparent;
            border: none;
            cursor: pointer;
            z-index: 10;
            position: relative;
            padding-right: 3rem;
        }
        .menu-button .bar {
            width: 30px;
            height: 3px;
            background-color: #388e3c;
            transition: all 0.3s;
        }
        .menu-button.open .bar:nth-child(1) {
            transform: rotate(45deg);
            position: relative;
        }
        .menu-button.open .bar:nth-child(2) {
            opacity: 0;
        }
        .menu-button.open .bar:nth-child(3) {
            transform: rotate(-45deg);
            position: relative;
        }
        .menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: #ffffff;
            color: #333;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
            width: 200px;
            display: none;
            transition: opacity 0.3s, transform 0.3s;
        }
        .menu.show {
            display: block;
            transform: translateY(10px);
            opacity: 1;
        }
        .menu ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .menu ul li {
            padding: 0.8rem 1.2rem;
        }
        .menu ul li a {
            text-decoration: none;
            color: #333;
            display: block;
            transition: background-color 0.3s, color 0.3s;
        }
        .menu ul li a:hover {
            background-color: #a5d6a7;
            color: #fff;
        }
        .card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .card-body {
            max-height: none; /* Ensure the content is not limited */
        }

        .img-fluid {
            object-fit: cover;
            width: 100%;
            height: auto;
        }
    
    </style>
    <script>
            function toggleMenu() {
            var menu = document.getElementById('menu');
            var button = document.querySelector('.menu-button');
            menu.classList.toggle('show');
            button.classList.toggle('open');
        }
    </script>
</head>
<body>
    <header>
        <img src="images/qsu.png" alt="QSU Logo" style="height: 70px;">
        <h2>QSU Student Research</h2>
        <button class="menu-button" onclick="toggleMenu()">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        <div id="menu" class="menu">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'admin'): ?>
                <li><a href="upload.php">Upload Research</a></li>
            <?php endif; ?>
                <li><a href="whatsnew.php">What's New</a></li>
                <li><a href="mstrlist.php">View Masterlist</a></li>
                <!--li><a href="contact.php">Contact Us</a></li-->
                <li><a href="about.php">About</a></li>
                <li><a href="logout.php" class="btn-logout">Logout</a></li>
            </ul>
        </div>
    </header>

    <div class="container">
   
        <h1 class="text-center mb-5">What's New</h1>

        <!-- Add Article Button -->
        <div class="text-end mb-4">
            <a href="add_article.php" class="btn btn-success">+ Add Article</a>
        </div>

        <!-- Display Articles -->
        <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="row">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <?php
                                $imagePath = $row['image_path'];
                                if (!empty($row['image_path']) && file_exists($imagePath)): ?>
                                    <img src="<?php echo $imagePath; ?>" class="img-fluid" alt="Article Image">
                                <?php else: ?>
                                    <img src="images/default.jpg" class="img-fluid" alt="Default Image">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $row['title']; ?></h5>
                                    <p class="card-text"><small class="text-muted">By <?php echo $row['author']; ?> | Posted on <?php echo $row['created_at']; ?></small></p>
                                    <p class="card-text"><?php echo nl2br($row['content']); ?></p> <!-- Display full content -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
            <p>No articles found.</p>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2024 Quirino State University. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>