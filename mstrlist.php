<?php
// Start session to access session variables
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "research";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch search and sort parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'desc' : 'asc';
$programFilter = isset($_GET['program']) ? $_GET['program'] : '';

// Set the opposite order for the next sort
$nextOrder = $order === 'asc' ? 'desc' : 'asc';

// Fetch data with search, sorting, and filtering functionality
$sql = "SELECT id, program, title, authors, year, file_path 
        FROM research_file 
        WHERE (program LIKE '%$search%' 
        OR title LIKE '%$search%' 
        OR authors LIKE '%$search%' 
        OR year LIKE '%$search%') 
        AND (program = '$programFilter' OR '$programFilter' = '') 
        ORDER BY $sort $order";
$result = $conn->query($sql);

// Fetch distinct programs for dropdown
$programsSql = "SELECT DISTINCT program FROM research_file";
$programsResult = $conn->query($programsSql);

// Check if query was successful
if ($result === false) {
    die("Error executing query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
         body {
           position: relative;
           margin: 0;
           padding-top: 80px; /* Padding for fixed header */
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
            padding-right: 950px;
        }
        header h5 {
           margin: 0;
           font-size: 16px;
           color: black;
           font-style: italic;
           padding-left: 1px;
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
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding-top: 1rem;
            border-radius: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            margin-bottom: 20px;
            text-align: center;
        }
        input[type="text"] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        select {
            padding: 10px;
            width: 140px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
            margin-bottom: 20px;
            font-size: 16px;
        }
        .back-button:hover {
            background-color: #c0392b;
        }
        .back-button i {
            margin-right: 8px;
        }
        td, th {
            text-align: center; /* Horizontally centers the text */
            vertical-align: middle; /* Vertically centers the text */
            padding: 12px; /* Adds padding for better spacing */
        }
        .view-button {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .view-button:hover {
            background-color: #0056b3;
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
            border-radius: 4px;
        }
        .btn-logout:hover {
            background-color: darkred;
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
            <li><a href="dashboard.php">Dashboard</a></li>
            <?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'admin'): ?>
                <li><a href="upload.php">Upload Research</a></li>
            <?php endif; ?>
            <li><a href="whatsnew.php">What's New</a></li>
            <li><a href="support.php">Help & Support</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </div>
</header>
<div class="container">
    <a href="dashboard.php" class="back-button"><i class="fa fa-arrow-left"></i></a>
    <h1>Master List</h1>
    <form method="get" action="" class="mb-4">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search...">
        <input type="submit" value="Search">
    </form>
    <form method="get" action="" class="mb-4">
        <select name="program" id="program" onchange="this.form.submit()">
            <option value="">All Programs</option>
            <?php while ($program = $programsResult->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($program['program']); ?>" <?php echo $programFilter === $program['program'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($program['program']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <!--th><a href="?search=<?php echo htmlspecialchars($search); ?>&sort=id&order=<?php echo $sort === 'id' ? $nextOrder : 'asc'; ?>">ID</a></th-->
                    <th><a href="?search=<?php echo htmlspecialchars($search); ?>&program=<?php echo htmlspecialchars($programFilter); ?>&sort=program&order=<?php echo $sort === 'program' ? $nextOrder : 'asc'; ?>">Program</a></th>
                    <th><a href="?search=<?php echo htmlspecialchars($search); ?>&program=<?php echo htmlspecialchars($programFilter); ?>&sort=title&order=<?php echo $sort === 'title' ? $nextOrder : 'asc'; ?>">Title</a></th>
                    <th><a href="?search=<?php echo htmlspecialchars($search); ?>&program=<?php echo htmlspecialchars($programFilter); ?>&sort=authors&order=<?php echo $sort === 'authors' ? $nextOrder : 'asc'; ?>">Authors</a></th>
                    <th><a href="?search=<?php echo htmlspecialchars($search); ?>&program=<?php echo htmlspecialchars($programFilter); ?>&sort=year&order=<?php echo $sort === 'year' ? $nextOrder : 'asc'; ?>">Year</a></th>
                    <th>View</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <!--td><?php echo htmlspecialchars($row["id"]); ?></td-->
                        <td><?php echo htmlspecialchars($row["program"]); ?></td>
                        <td><?php echo htmlspecialchars($row["title"]); ?></td>
                        <td><?php echo htmlspecialchars($row["authors"]); ?></td>
                        <td><?php echo htmlspecialchars($row["year"]); ?></td>
                        <td><a href="<?php echo htmlspecialchars($row['file_path']); ?>" class="view-button" target="_blank">View</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No records found.</p>
    <?php endif; ?>
</div>
<?php $conn->close(); ?>
<footer>
    <p>&copy; 2024 Quirino State University. All rights reserved.</p>
</footer>
</body>
</html>
