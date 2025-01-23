<?php
$servername = "localhost";
$username = "sean"; // Your MySQL username
$password = "your_password"; // Your MySQL password
$dbname = "your_database_name"; // Desired database name

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check if database exists
    $db_check_query = "SHOW DATABASES LIKE '$dbname'";
    $result = $conn->query($db_check_query);

    if ($result->num_rows == 0) {
        // Database doesn't exist, create it
        $create_db_query = "CREATE DATABASE $dbname";
        if ($conn->query($create_db_query) === TRUE) {
            echo "Database created successfully";
        } else {
            throw new Exception("Error creating database: " . $conn->error);
        }
    } else {
        echo "Database already exists";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    // Close connection
    $conn->close();
}
?>