<?php
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php"); // Redirect to login if not an admin
    exit();
}

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

// Fetch total number of users
$sql_users = "SELECT COUNT(*) AS total_users FROM userss";
$result_users = $conn->query($sql_users);
$total_users = $result_users->fetch_assoc()['total_users'];

// Fetch total number of downloads
$sql_downloads = "SELECT SUM(download_count) AS total_downloads FROM research_file";
$result_downloads = $conn->query($sql_downloads);
$total_downloads = $result_downloads->fetch_assoc()['total_downloads'];

// Fetch top 5 most downloaded research files
$sql_top_files = "SELECT title, download_count FROM research_file ORDER BY download_count DESC LIMIT 5";
$result_top_files = $conn->query($sql_top_files);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Admin Analytics Dashboard</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .dashboard-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 20px 0;
        }
        h2 {
            color: #333;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #a5d6a7;
            color: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 2rem;
        }
        .stat-card p {
            margin: 0;
            font-size: 1.2rem;
        }
        .table-container {
            margin-top: 40px;
        }
        footer {
            background-color: #004f23;
            color: white;
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Admin Analytics Dashboard</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="stat-card">
                    <h3><?php echo $total_users; ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <h3><?php echo $total_downloads ? $total_downloads : 0; ?></h3>
                    <p>Total Downloads</p>
                </div>
            </div>
        </div>

        <div class="table-container">
            <h4>Top 5 Most Downloaded Research Files</h4>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Download Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_top_files->num_rows > 0) {
                        while($row = $result_top_files->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['download_count']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2'>No research files found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Quirino State University. All rights reserved.</p>
    </footer>
</body>
</html>

<?php
$conn->close();
?>
