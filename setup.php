<?php
    function connect() 
    {
        $db_host = 'localhost';
        $db_username = 'root';
        $db_password = '';
        $db_name = 'SantosOpticals';

        $conn = new mysqli($db_host, $db_username, $db_password, $db_name);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        return $conn;
    }
    function createDB()
    {
        // Configuration
        $db_host = 'localhost';
        $db_username = 'root';
        $db_password = '';

        // Create connection
        $conn = new mysqli($db_host, $db_username, $db_password);

        // Check connection
        if ($conn->connect_error) 
        {
            die("Connection failed: " . $conn->connect_error);
        }

        // Creating database
        $sql = "CREATE DATABASE SantosOpticals";

        if ($conn->query($sql) === TRUE) 
        {
        } 
        else 
        {
            echo "There is an error in creating the database: " . $conn->error;
        }

        $conn->close();
    }
?>

<?php
     $conn = new mysqli('localhost','root','');
     $db_check_query = "SHOW DATABASES LIKE 'SantosOpticals'";
 
     $result = mysqli_query($conn, $db_check_query);

     if (mysqli_num_rows($result) == 0) 
    {
        createDB();
        $conn->close();        
    }

?>