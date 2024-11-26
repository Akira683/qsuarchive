<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Define database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "research";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the logged-in user's program from the session
$loggedInProgram = $_SESSION['user']['program'];

// Process the form when submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form inputs
    $title = $_POST['title'];
    $authors = $_POST['authors'];
    $year = $_POST['year'];
    $program = $_POST['program'];
    $abstract = $_POST['abstract'];
    $researchFile = $_FILES['research_file'];

    // Debugging output
    error_log("Logged-in program: $loggedInProgram");
    error_log("Submitted program: $program");

    // Validate inputs
    if (empty($title) || empty($authors) || empty($year) || empty($program) || empty($abstract) || empty($researchFile['name'])) {
        $error = "All fields are required.";
    } elseif ($program !== $loggedInProgram) {
        $error = "Sorry, you can only upload research to your own program.";
    } else {
        // Process the file upload
        $uploadDir = './uploads/';
        $uploadFile = $uploadDir . basename($researchFile['name']);

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
        }

        if (move_uploaded_file($researchFile['tmp_name'], $uploadFile)) {
            // Prepare and bind
            $stmt = $conn->prepare("INSERT INTO research_file (title, authors, year, program, file_path, abstract) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssisss", $title, $authors, $year, $program, $uploadFile, $abstract);
                if ($stmt->execute()) {
                    $success = "Research uploaded successfully.";
                    //header("Location: dashboard.php"); // Redirect to dashboard
                    //exit;
                } else {
                    $error = "Error saving details to the database: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Error preparing SQL statement: " . $conn->error;
            }
        } else {
            $error = "Failed to upload file.";
        }
    }
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Research</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #007bff;
            color: #fff;
            padding: 1rem 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 {
            margin: 0;
            font-size: 1.8rem;
        }
        .navbar ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }
        .navbar ul li {
            margin-left: 1.5rem;
        }
        .navbar ul li a {
            text-decoration: none;
            color: #fff;
            font-size: 1rem;
            transition: color 0.3s;
        }
        .navbar ul li a:hover {
            color: #ffdd57;
        }
        .container {
            width: 90%;
            max-width: 800px;
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
        label {
            display: block;
            margin: 0.5rem 0;
            font-size: 1.1rem;
            color: #333;
        }
        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 0.7rem;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: border-color 0.3s;
        }
        input[type="file"] {
            margin: 1rem 0;
        }
        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus {
            border-color: #007bff;
            outline: none;
        }
        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            margin-top: 1rem;
            font-size: 1rem;
        }
        .message.success {
            color: #28a745;
        }
        .message.error {
            color: #dc3545;
        }
        label {
           display: block;
           margin: 0.5rem 0;
           font-size: 1.1rem;
           color: #333;
        }

        textarea {
           width: 100%;
           padding: 0.7rem;
           font-size: 1rem;
           border-radius: 5px;
           border: 1px solid #ddd;
           box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
           resize: vertical; /* Allow the user to resize vertically */
           transition: border-color 0.3s, box-shadow 0.3s;
        }

        textarea:focus {
           border-color: #007bff;
           outline: none;
           box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
        }   
    </style>
</head>
<body>
    <header>
        <div class="navbar">
            <h1>QSU Student Research</h1>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
            </ul>
        </div>
    </header>
    <div class="container">
        <h2>Upload New Research (<?php echo htmlspecialchars($loggedInProgram); ?>)</h2>
        <?php if (isset($success)) { echo "<p class='message success'>$success</p>"; } ?>
        <?php if (isset($error)) { echo "<p class='message error'>$error</p>"; } ?>
        <form action="upload.php" method="post" enctype="multipart/form-data">
    <label for="title">Research Title:</label>
    <input type="text" id="title" name="title" required>

    <label for="authors">Authors:</label>
    <input type="text" id="authors" name="authors" required>

    <label for="year">Year:</label>
    <input type="number" id="year" name="year" min="1900" max="2099" required>

    <label for="abstract">Abstract:</label>
    <textarea id="abstract" name="abstract" rows="4" required></textarea>


    <!-- Remove the select element for program -->
    <!-- Automatically assign the logged-in user's program -->
    <input type="hidden" name="program" value="<?php echo htmlspecialchars($loggedInProgram); ?>">

    <label for="research-file">Upload Research File (PDF):</label>
    <input type="file" id="research-file" name="research_file" accept=".pdf" required>

    <button type="submit">Upload Research</button>
</form>

    </div>
</body>
</html>











<div class="container">
        <h1 class="text-center mb-5">What's New</h1>

        <div class="news-section">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <img src="images/itlogo.jpg" class="img-fluid" alt="BSIT Research Image">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title">Innovative Research Unveiled: Explore the Latest BSIT Projects at QSU</h5>
                                    <p class="card-text"><small class="text-muted">Posted on August 14, 2024</small></p>
                                    <p class="card-text">
                                        Diffun, Quirino - QSU's BSIT program proudly presents its latest research achievements. Dive into the innovative projects that showcase the forefront of technology and student ingenuity. From groundbreaking software solutions to cutting-edge hardware designs, our students are pushing the boundaries of what’s possible in the world of IT. Explore the full range of projects and see how these young innovators are shaping the future of technology.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <img src="images/crimlogo.jpg" class="img-fluid" alt="Criminology Research Image">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title">Breaking New Ground: Explore the Latest Research in QSU's Criminology Program</h5>
                                    <p class="card-text"><small class="text-muted">Posted on August 14, 2024</small></p>
                                    <p class="card-text">
                                        Diffun, Quirino - QSU's Criminology program is excited to present its latest research initiatives. Discover how our students are contributing to the field of criminology with innovative studies and impactful findings. These projects not only enhance the academic landscape but also offer practical solutions to current challenges in law enforcement and criminal justice. Delve into the research that's making waves and setting new standards in the field.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="news-section">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <img src="images/education.jpg" class="img-fluid" alt="Education Research Image">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title">Empowering Minds: Explore the Latest Research from QSU's Education Program</h5>
                                    <p class="card-text"><small class="text-muted">Posted on August 14, 2024</small></p>
                                    <p class="card-text">
                                        Diffun, Quirino - QSU’s Education program proudly showcases its recent research endeavors. Discover the innovative projects and scholarly work our students are contributing to advance the field of education. These research initiatives are not only enhancing teaching methodologies but are also addressing key educational challenges. Learn how our future educators are shaping the minds of tomorrow with their pioneering research.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <img src="images/hrm.png" class="img-fluid" alt="Hospitality Management Research Image">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title">Elevating Service Excellence: Discover the Latest Research in QSU's Hospitality Management Program</h5>
                                    <p class="card-text"><small class="text-muted">Posted on August 14, 2024</small></p>
                                    <p class="card-text">
                                        Diffun, Quirino - QSU’s Hospitality Management program is thrilled to present its latest research contributions. Explore how our students are innovating and enhancing the hospitality industry through their groundbreaking projects. From new approaches to customer service to innovative management strategies, these research projects are setting new benchmarks for excellence in hospitality. Get inspired by the ideas and solutions that are elevating the standards of service and hospitality.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Your Institution Name. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>



.about-us {
            background-color: #e8f5e9; /* Light green background */
            padding: 40px;
            text-align: center;
            font-family: 'Arial'; /* Use a serif font */
            margin: 40px 0; /* Margin top and bottom to create space */
            border: 2px solid #c5e1a5; /* Slightly darker green border */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow effect */
        }
        .about-us h3 {
            font-size: 2rem;
            color: #2e7d32; /* Dark green color */
            margin-bottom: 20px;
        }
        .about-us p {
            font-size: 1rem;
            color: #666;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }




        footer {
            background-color: #004f23;
            color: white;
            padding: 10px;
            text-align: center;
            margin-top: 20px; /* Add some space above the footer */
            box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.15); /* Subtle shadow effect on top of the footer */
        }
        footer p {
            margin: 0;
        }


        .about-us {
            background-color: #e8f5e9; /* Light green background */
            padding: 20px;
            text-align: center;
            font-family: 'Arial'; /* Use a serif font */
            margin-top: 20px; /* Add some space above the section */
            border: 2px solid #c5e1a5; /* Slightly darker green border */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow effect */
        }

        .about-us h3 {
            font-size: 2rem;
            color: #2e7d32; /* Dark green color */
            margin-bottom: 20px;
        }
        .about-us p {
            font-size: 1rem;
            color: #666;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }



        <?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Get the logged-in user's program
$loggedInProgram = $_SESSION['user']['program'];

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
        .filter-section {
            margin-bottom: 20px;
            padding: 1rem;
        }
        .filter-section label {
            display: block;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: #333;
        }
        .filter-section select {
            padding: 5px;
            font-size: 16px;
            width: 100%;
            padding: 0.7rem;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: border-color 0.3s;
        }
        .research-item {
            padding: 15px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
            background-color: #fafafa;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .research-item h3 {
            margin-top: 0;
            font-size: 20px;
            color: #333;
        }
        .research-item p {
            margin: 5px 0;
            color: #555;
        }
        .research-item a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s;
        }
        .research-item a:hover {
            color: #0056b3;
        }
        .research-links {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
        }
        .research-links a {
            text-decoration: none;
            color: #007bff;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            transition: color 0.3s;
            cursor: pointer;
        }
        .research-links a:hover {
            color: #0056b3;
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
        <h2>QSU Student Research</h2>
        <button class="menu-button" onclick="toggleMenu()">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        <div id="menu" class="menu">
            <ul>
                <li><a href="mstrlist.php">View Masterlist</a></li>
                <li><a href="upload.php">Upload Research</a></li>
                <li><a href="whatsnew.php">What's New</a></li>
                <li><a href="support.php">Help & Support</a></li>
                <!--li><a href="contact.php">Contact Us</a></li-->
                <li><a href="about.php">About</a></li>
                <li><a href="logout.php" class="btn-logout">Logout</a></li>
            </ul>
        </div>
    </header>
    <div class="container">
        <h2>Welcome to the QSU Student Researches!</h2>
        <p>You have successfully logged in as <?php echo htmlspecialchars($loggedInProgram); ?>. Explore the features below:</p>

        <!-- Show upload link only if selected program matches logged-in program -->
        <?php if ($selected_program === $loggedInProgram): ?>
            <div class="research-links">
                <a href="upload.php">Upload New Research to <?php echo htmlspecialchars($loggedInProgram); ?></a>
            </div>
        <?php endif; ?>

        <!-- Combo-box for selecting program -->
        <div class="filter-section">
            <form method="POST" action="">
                <label for="program">Select Program:</label>
                <select name="program" id="program" onchange="persistProgram(); this.form.submit();">
                    <!--option value="">--Select Program--</option-->
                    <option value="BSIT" <?php if ($selected_program === 'BSIT') echo 'selected'; ?>>BSIT</option>
                    <option value="BSOA" <?php if ($selected_program === 'BSOA') echo 'selected'; ?>>BSOA</option>
                    <option value="CRIM" <?php if ($selected_program === 'CRIM') echo 'selected'; ?>>CRIM</option>
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


<!--copy of upload.php-->
<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Define database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "research";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the logged-in user's program from the session
$loggedInProgram = $_SESSION['user']['program'];

// Process the form when submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form inputs
    $title = $_POST['title'];
    $authors = $_POST['authors'];
    $year = $_POST['year'];
    $program = $_POST['program'];
    $abstract = $_POST['abstract'];
    $researchFile = $_FILES['research_file'];

    // Debugging output
    error_log("Logged-in program: $loggedInProgram");
    error_log("Submitted program: $program");

    // Validate inputs
    if (empty($title) || empty($authors) || empty($year) || empty($program) || empty($abstract) || empty($researchFile['name'])) {
        $error = "All fields are required.";
    } elseif ($program !== $loggedInProgram) {
        $error = "Sorry, you can only upload research to your own program.";
    } else {
        // Process the file upload
        $uploadDir = './uploads/';
        $uploadFile = $uploadDir . basename($researchFile['name']);

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
        }

        if (move_uploaded_file($researchFile['tmp_name'], $uploadFile)) {
            // Prepare and bind
            $stmt = $conn->prepare("INSERT INTO research_file (title, authors, year, program, file_path, abstract) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssisss", $title, $authors, $year, $program, $uploadFile, $abstract);
                if ($stmt->execute()) {
                    $success = "Research uploaded successfully.";
                    //header("Location: dashboard.php"); // Redirect to dashboard
                    //exit;
                } else {
                    $error = "Error saving details to the database: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Error preparing SQL statement: " . $conn->error;
            }
        } else {
            $error = "Failed to upload file.";
        }
    }
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Upload Research</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            margin: 0;
            padding-top: 70px;
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
            background: linear-gradient(90deg, #ffffff, #a5d6a7);
            padding: 3px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
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
            max-width: 800px;
            margin: 2rem auto;
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 1rem;
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
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: #333;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.7rem;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: border-color 0.3s;
        }
        .form-group input[type="file"] {
            border: none;
        }
        .form-group textarea {
            resize: vertical;
        }
        .form-group button {
            background-color: #388e3c;
            color: #fff;
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        .form-group button:hover {
            background-color: #2e7d32;
        }
        .message {
            margin: 1rem 0;
            padding: 0.5rem;
            border-radius: 5px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
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
                <li><a href="support.php">Help & Support</a></li>
                <li><a href="whatsnew.php">What's New</a></li>
                <li><a href="mstrlist.php">View Masterlist</a></li>
                <!--li><a href="contact.php">Contact Us</a></li-->
                <li><a href="about.php">About</a></li>
                <li><a href="logout.php" class="btn-logout">Logout</a></li>
            </ul>
    </div>
</header>
<div class="container">
    <h2>Upload New Research (<?php echo htmlspecialchars($loggedInProgram); ?>)</h2>
    <?php if (isset($success)) { echo "<p class='message success'>$success</p>"; } ?>
    <?php if (isset($error)) { echo "<p class='message error'>$error</p>"; } ?>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Research Title:</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="authors">Authors:</label>
            <input type="text" id="authors" name="authors" required>
        </div>
        <div class="form-group">
            <label for="year">Year:</label>
            <input type="number" id="year" name="year" min="1900" max="2099" required>
        </div>
        <div class="form-group">
            <label for="abstract">Abstract:</label>
            <textarea id="abstract" name="abstract" rows="4" required></textarea>
        </div>
        <!-- Remove the select element for program -->
        <!-- Automatically assign the logged-in user's program -->
        <input type="hidden" name="program" value="<?php echo htmlspecialchars($loggedInProgram); ?>">
        <div class="form-group">
            <label for="research-file">Upload Research File (PDF):</label>
            <input type="file" id="research-file" name="research_file" accept=".pdf" required>
        </div>
        <div class="form-group">
            <button type="submit">Upload Research</button>
        </div>
    </form>
</div>
</body>
</html>
