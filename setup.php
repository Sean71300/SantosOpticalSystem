<?php

    // Connection

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

    // Create database

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
    
    // Create Employee Table

    function create_EmployeesTable()
    {
        $conn = connect();

        $sql = "CREATE TABLE employee (
                EmployeeID INT(10) PRIMARY KEY,
                EmployeeName VARCHAR(100),
                EmployeePicture LONGBLOB,
                EmployeeEmail VARCHAR(100),
                EmployeeNumber VARCHAR(11),
                RoleID INT(10),
                LoginName VARCHAR(50),
                Password VARCHAR(255),
                BranchCode int(10),
                Status VARCHAR(50),
                Upd_by VARCHAR(50),
                Upd_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";

        if (mysqli_query($conn, $sql))
        {
            $img_path = "Images/default.jpg";
            $img_clean= file_get_contents($img_path);
            $employee_pic = mysqli_real_escape_string($conn, $img_clean);
            $id = generate_EmployeeID();
            $password = "JPSantos123";
            $email = "BVPSantosOptical@gmail.com";

            $hashed_pw = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO employee
                    (EmployeeID, EmployeeName, EmployeePicture,EmployeeEmail,
                    EmployeeNumber,RoleID,LoginName,Password,BranchCode,Status,
                    Upd_by)
                    VALUES
                    ($id, 'Bien Ven P. Santos', '$employee_pic', '$email', 
                    '09864571325', '1', 'BVSantos1', '$hashed_pw', '1', 'Active',
                     'Admin')";

            mysqli_query($conn, $sql);
        }
        else
        {
            echo "<br>There is an error in creating the table: " . $conn->connect_error;
        }

        $conn->close();
    }

    //Create Customer Table

    function create_CustomersTable()
    {
        $conn = connect();

        $sql = "CREATE TABLE customer (
                CustomerID INT(10) PRIMARY KEY,
                CustomerName VARCHAR(100),
                CustomerAddress VARCHAR(100),
                CustomerContact VARCHAR(11),
                CustomerInfo VARCHAR(500),
                Notes VARCHAR(500),
                Upd_by VARCHAR(50),
                Upd_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";

        if (mysqli_query($conn, $sql))
        {           
            $id = generate_CustomerID();           
            $sql = "INSERT INTO customer 
                    (CustomerID,CustomerName,CustomerAddress,CustomerContact,
                    CustomerInfo,Notes,Upd_by) 
                    VALUES
                    ('$id','SeanGenesis','231 Visayas Street, Malabon City', 
                    '09864325874', '60 Years old \n185cm \nMale', 
                    'Round Face Shape','Bien Ven P. Santos')
                    ";
            
            mysqli_query($conn, $sql);
        }
        else
        {
            echo "<br>There is an error in creating the table: " . $conn->connect_error;
        }

        $conn->close();
    }

    //Generate Employee ID

    function generate_EmployeeID()
    {
        $conn = connect();

        $query = "SELECT COUNT(*) as count FROM employee";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        $rowCount = $row["count"];

        // Get the current year
        $currentYear = date("Y");

        // Generate the ID
        $genID = (int)($currentYear . str_pad(5, 2, "0", STR_PAD_LEFT) . str_pad($rowCount, 4, "0", STR_PAD_LEFT));
        
        $checkQuery = "SELECT EmployeeID FROM employee WHERE EmployeeID = ?";
        $genID = checkDuplication($genID,$checkQuery);
        $conn->close();
        return $genID;
    }

    //Generate Customer ID

    function generate_CustomerID()
    {
        $conn = connect();

        $query = "SELECT COUNT(*) as count FROM customer";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        $rowCount = $row["count"];

        // Get the current year
        $currentYear = date("Y");

        // Generate the ID
        $genID = (int)($currentYear . str_pad(5, 2, "0", STR_PAD_LEFT) . str_pad($rowCount, 4, "0", STR_PAD_LEFT));
        
        $checkQuery = "SELECT CustomerID FROM customer WHERE CustomerID = ?";
        $genID = checkDuplication($genID,$checkQuery);
        $conn->close();
        return $genID;
    }

    // Check Id Duplication

    function checkDuplication($id, $checkQuery) {
        $conn = connect();
        // Function to check for duplicate ID
        while (true) {
            // Prepare the query to check for the duplicate ID
            $stmt = $conn->prepare($checkQuery);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->store_result();
    
            if ($stmt->num_rows == 0) {
                break;
            }
            $id++;
    
            $stmt->close();
        }
        $stmt->close();
        $conn->close();
        return $id;
    }
?>

<?php
    // Check if the database exists
     $conn = new mysqli('localhost','root','');
     $db_check_query = "SHOW DATABASES LIKE 'SantosOpticals'";
 
     $result = mysqli_query($conn, $db_check_query);    
     if (mysqli_num_rows($result) == 0) 
    {
        createDB();

        $conn->close();        
    }
    $conn = connect();

    // Check if employee table exists
    $table_check_query = "SHOW TABLES LIKE 'employee'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_EmployeesTable();
    }

    // Check if customer table exists
    $table_check_query = "SHOW TABLES LIKE 'customer'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_CustomersTable();
    }
?>