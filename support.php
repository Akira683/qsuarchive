<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Get the logged-in user's program
$loggedInProgram = $_SESSION['user']['program'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Help & Support</title>
    <style>
        body {
            position: relative;
            margin: 0;
            padding-top: 70px; /* Padding for fixed header */
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
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 2rem auto;
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        p {
            color: #555;
            font-size: 1.2rem;
        }
        ul.support-list {
            list-style: none;
            padding-left: 0;
            margin: 2rem 0;
        }
        ul.support-list li {
            padding: 10px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        ul.support-list li strong {
            color: #388e3c;
            font-size: 1.2rem;
        }
        ul.support-list li p {
            margin: 5px 0;
            font-size: 1rem;
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
        footer {
           background-color: #004f23;
           color: white;
           padding: 5px;
           text-align: center;
           margin-top: 0; /* No margin at the top */
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
        <h2>Help & Support</h2>
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
        <h2>Help & Support</h2>
        <p>If you have any questions or need assistance, you can find answers below or contact our support team.</p>

        <h3>Frequently Asked Questions (FAQs)</h3>
        <ul class="support-list">
            <li>
                <strong>How do I upload a research paper?</strong>
                <p>To upload a research paper, navigate to the "Upload Research" section, select your program, and follow the instructions provided on the page.</p>
            </li>
            <li>
                <strong>How can I view the research masterlist?</strong>
                <p>You can view the research masterlist by clicking on the "View Masterlist" option in the menu. This will display all the research papers available for your selected program.</p>
            </li>
            <!--li>
                <strong>What if I forgot my password?</strong>
                <p>If you forgot your password, please contact our support team for assistance in resetting it.</p>
            </li-->
            <li>
                <strong>Who can I contact for further assistance?</strong>
                <p>If you need further help, please reach out to our support team through our email @support@qsu.edu.ph</p>
            </li>
        </ul>

        <h3>Support Contact Information</h3>
        <p>If you need more personalized support, feel free to contact us via email or phone:</p>
        <ul class="support-list">
            <li>
                <strong>Email:</strong>
                <p>support@qsu.edu.ph</p>
            </li>
            <li>
                <strong>Phone:</strong>
                <p>+63 999 999 9999</p>
            </li>
            <li>
                <strong>Office Hours:</strong>
                <p>Monday - Friday: 8:00 AM - 5:00 PM</p>
            </li>
        </ul>
    </div>
    <footer>
        <p>&copy; 2024 Quirino State University. All rights reserved.</p>
    </footer>
</body>
</html>
