<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Get the logged-in user's information
$loggedInProgram = $_SESSION['user']['program'];
$userRole = $_SESSION['user']['role']; // Get the user role from session

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "research";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle the selected program, storing it in the session
if (isset($_POST['program'])) {
    $selected_program = $_POST['program'];
    $_SESSION['selected_program'] = $selected_program;
} else {
    $selected_program = isset($_SESSION['selected_program']) ? $_SESSION['selected_program'] : $loggedInProgram;
}

// Ensure the SQL query selects files for the selected program
$sql = "SELECT * FROM research_file";
if ($selected_program) {
    $sql .= " WHERE program = '" . $conn->real_escape_string($selected_program) . "'";
}
$result = $conn->query($sql);

// Determine the welcome message based on user role
if ($userRole === 'admin') {
    $welcome_message = "Hello Welcome back, Master. The future is in your hands...";
} else {
    $welcome_message = "You have successfully logged in as $loggedInProgram. Explore the features below.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Research Archive Dashboard</title>
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
        h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        header h2 {
            margin: 0;
            font-size: 1.8rem;
            color: #2e7d32;
            padding-right: 950px;
        }

        header h5 {
           margin: 0;
           font-size: 16px;
           color: black;
           font-style: italic;
           padding-left: 1px;
        }
      /* Container */
.container {
    width: 90%;
    max-width: 1200px;
    margin: 2rem auto;
    background: #fff;
    padding: 2rem;
    border-radius: 15px; /* Slightly rounded corners */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1); /* Softer shadow */
    opacity: 0; /* Initially hidden for fade-in effect */
    transform: scale(0.98); /* Slightly scaled down for scale effect */
    animation: fadeInUp 0.6s ease-out forwards; /* Animation */
}

/* Animation for Container */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Headings */
h2 {
    color: #333;
    font-size: 2.2rem; /* Larger font size for emphasis */
    margin-bottom: 1rem;
    /*opacity: 0; /* Initially hidden for slide-in effect */
    /*transform: translateY(-20px); /* Start from above */
    /*animation: slideIn 0.6s ease-out forwards; /* Animation */
}

/* Animation for Headings */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Buttons */
.btn-logout {
    background-color: #e53935; /* Red color */
    color: #fff;
    border: none;
    padding: 0.8rem 1.5rem; /* Increased padding for better touch */
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s, transform 0.3s; /* Added transform transition */
}

.btn-logout:hover {
    background-color: #c62828; /* Darker red on hover */
    transform: scale(1.05); /* Slightly scale up */
}

/* Filter Section */
.filter-section {
    margin-bottom: 20px;
    padding: 1rem;
    background: #fff; /* Background for better contrast */
    border-radius: 8px; /* Rounded corners */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    opacity: 0; /* Initially hidden for slide-up effect */
    transform: translateY(20px); /* Start from below */
    animation: slideUp 0.6s ease-out forwards; /* Animation */
}

/* Animation for Filter Section */
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Reverted Dropdown Design */
.filter-section select {
    padding: 0.7rem;
    font-size: 1rem;
    width: 100%;
    border-radius: 5px;
    border: 1px solid #ddd;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease; /* Smooth transitions */
}

/* Animation for Focus Effect */
.filter-section select:focus {
    border-color: #a5d6a7; /* Highlight border color */
    box-shadow: 0 0 8px rgba(165, 214, 167, 0.5); /* Glow effect */
    transform: scale(1.02); /* Slightly enlarge */
}

/* Smooth Dropdown Appearance */
.filter-section select {
    transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease; /* Ensure transitions are smooth */
}


/* Research Items */
.research-item {
    padding: 1rem;
    border: 1px solid #ddd;
    margin-bottom: 20px;
    background-color: #fafafa;
    border-radius: 8px; /* Slightly rounded corners */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease; /* Added transition for hover effect */
}

/* Animation for Research Items on Hover */
.research-item:hover {
    transform: scale(1.05); /* Slightly scale up */
    box-shadow: 0 0 15px rgba(255, 223, 0, 0.5); /* Glowing effect with yellow light */
}

/* Research Links */
.research-links a {
    text-decoration: none;
    color: #007bff;
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    transition: color 0.3s, transform 0.3s; /* Added transform transition */
    cursor: pointer;
}

.research-links a:hover {
    color: #0056b3;
    transform: translateX(5px); /* Slide effect on hover */
}

/* Footer */
footer {
    background-color: #004f23;
    color: #fff;
    padding: 1rem; /* Increased padding for better appearance */
    text-align: center;
    margin-top: 2rem; /* Add margin at the top */
    border-top: 1px solid #ddd; /* Add a border to separate from content */
}

</style>
    <script>
        window.onload = function() {
            // Restore selected program from session if available
            var selectedProgram = "<?php echo $selected_program; ?>";
            if (selectedProgram) {
                document.getElementById('program').value = selectedProgram;
            }
        };

        function persistProgram() {
            var selectedProgram = document.getElementById('program').value;
            // Persist selected program to session
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("program=" + selectedProgram);
        }

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
        <div>
        <h2>Quirino State University Research</h2>
        <h5>Preserving Knowledge, Inspiring Discovery</h5>
        <!--h5>Innovating Minds, Shaping Futures</h5-->
        </div>
        <button class="menu-button" onclick="toggleMenu()">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        <div id="menu" class="menu">
            <ul>
                <li><a href="mstrlist.php">View Masterlist</a></li>
                <?php if ($userRole === 'admin'): ?>
                    <li><a href="upload.php">Upload Research</a></li>
                    <li><a href="admin_dashboard.php">Your Dashboard</a></li>
                <?php endif; ?>
                <li><a href="whatsnew.php">What's New</a></li>
                <li><a href="support.php">Help & Support</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="logout.php" class="btn-logout">Logout</a></li>
            </ul>
        </div>
    </header>
    <div class="container">
        <h2>Welcome to the QSU Student Researches!</h2>
        <p><?php echo htmlspecialchars($welcome_message); ?></p>

        <!-- Combo-box for selecting program -->
        <div class="filter-section">
            <form method="POST" action="">
                <label for="program">Select Program:</label>
                <select name="program" id="program" onchange="persistProgram(); this.form.submit();">
                    <option value="BSIT" <?php if ($selected_program === 'BSIT') echo 'selected'; ?>>BSIT</option>
                    <option value="BSOA" <?php if ($selected_program === 'BSOA') echo 'selected'; ?>>BSOA</option>
                    <option value="CRIM" <?php if ($selected_program === 'CRIM') echo 'selected'; ?>>CRIM</option>
                    <option value="BSHM" <?php if ($selected_program === 'BSHM') echo 'selected'; ?>>BSHM</option>
                    <option value="BSCS" <?php if ($selected_program === 'BSCS') echo 'selected'; ?>>BSCS</option>
                    <option value="BSED" <?php if ($selected_program === 'BSED') echo 'selected'; ?>>BSED</option>
                    <option value="BSBA" <?php if ($selected_program === 'BSBA') echo 'selected'; ?>>BSBA</option>
                    <option value="BSN" <?php if ($selected_program === 'BSN') echo 'selected'; ?>>BSN</option>
                    <option value="BSCHE" <?php if ($selected_program === 'BSCHE') echo 'selected'; ?>>BSCHE</option>
                </select>
            </form>
        </div>

        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<div class='research-item'>";
                echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
                echo "<p><strong>Authors:</strong> " . htmlspecialchars($row['authors']) . "</p>";
                echo "<p><strong>Year:</strong> " . htmlspecialchars($row['year']) . "</p>";
                echo "<p><strong>Abstract:</strong> " . htmlspecialchars($row['abstract']) . "</p>";
                echo "<p><a href='" . htmlspecialchars($row['file_path']) . "' target='_blank'>Download File</a></p>";
                echo "</div>";
            }
        } else {
            echo "<p>No research files found for the selected program.</p>";
        }
        ?>
    </div>
    <footer>
        <p>&copy; 2024 Quirino State University. All rights reserved.</p>
    </footer>
</body>
</html>

<?php
$conn->close();
?>