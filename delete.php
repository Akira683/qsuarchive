<?php
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

// Check if the id is set in the POST request
if (isset($_POST['id'])) {
    $id = intval($_POST['id']); // Ensure the id is an integer

    // Prepare the SQL delete statement
    $sql = "DELETE FROM research_file WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        
        // Execute the statement
        if ($stmt->execute()) {
            // Redirect back to the master list page after deletion
            header("Location: mstrlist.php");
            exit();
        } else {
            echo "Error deleting record: " . $conn->error;
        }
        
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

$conn->close();
?>
