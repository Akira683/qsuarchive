<?php
include('db.php');

// Check if the connections were successful
if ($conn_users->connect_error || $conn_research->connect_error) {
    die("Connection failed: " . $conn_users->connect_error . " or " . $conn_research->connect_error);
}

// Fetch the number of users
$sql_users = "SELECT COUNT(*) AS total_users FROM userss"; // Ensure you are querying the right table
$result_users = $conn_users->query($sql_users);
$total_users = $result_users->fetch_assoc()['total_users'];

// Fetch the number of downloads
//$sql_downloads = "SELECT COUNT(*) AS total_downloads FROM downloads";
//$result_downloads = $conn_research->query($sql_downloads);
//$total_downloads = $result_downloads->fetch_assoc()['total_downloads'];

// Fetch the number of research files
$sql_research_files = "SELECT COUNT(*) AS total_research_files FROM research_file";
$result_research_files = $conn_research->query($sql_research_files);
$total_research_files = $result_research_files->fetch_assoc()['total_research_files'];

// Fetch the number of users by role
$sql_roles = "SELECT role, COUNT(*) AS count FROM userss GROUP BY role"; // Make sure this is in the users database
$result_roles = $conn_users->query($sql_roles);

$roles_data = [];
while ($row = $result_roles->fetch_assoc()) {
    $roles_data[] = $row;
}

// Assuming the user's role is stored in the session or a variable
$userRole = $_SESSION['role'] ?? 'user'; // Adjust this according to your session management
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
        .menu-button:focus {
            outline: none; /* Remove the white border */
            box-shadow: none; /* Remove any shadow */
            background: transparent; /* Keep background transparent */
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
        
        .dashboard {
            margin: 20px;
        }
        .card {
            margin-bottom: 20px;
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
        <h1 class="my-4">Admin Dashboard</h1>
        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <p class="card-text"><?php echo $total_users; ?></p>
                    </div>
                </div>
            </div>
            <!--div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Total Downloads</h5>
                        <p class="card-text"><?php echo $total_downloads; ?></p>
                    </div>
                </div>
            </div-->
            
            <div class="col-md-4">
    <div class="card text-white bg-warning">
        <div class="card-body">
            <h5 class="card-title">Users by Role</h5>
            <ul class="list-group">
                <?php foreach ($roles_data as $role): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($role['role']); ?>
                        <span class="badge badge-light">
                            <?php echo htmlspecialchars($role['count']); ?> 
                            <?php echo htmlspecialchars($role['role']) === 'admin' ? 'Administrator' : 'Regular User'; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<div class="col-md-4">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Total Research Files</h5>
                        <p class="card-text"><?php echo $total_research_files; ?></p>
                    </div>
                </div>
            </div>
</body>
</html>

<?php
// Close the connections
$conn_users->close();  // Close users database connection
$conn_research->close(); // Close research database connection
?>
