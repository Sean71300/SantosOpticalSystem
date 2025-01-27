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

    // Create Product Branch Master Table

    function create_ProductBrnchMstrTable()
    {
        $conn = connect();
        $sql = "CREATE TABLE ProductBranchMaster (
                ProductBranchID INT(10) PRIMARY KEY,
                ProductID INT(10),
                BranchCode INT(10),
                Count INT(100),
                Avail_FL VARCHAR(50), 
                Upd_by VARCHAR(50),
                Upd_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";

        if (mysqli_query($conn, $sql))
        {
            
            $id = generate_ProductBrnchMstrID();            
            $sql = "INSERT INTO ProductBranchMaster
                    (ProductBranchID,ProductID,BranchCode,Count,Avail_FL,Upd_by)
                    VALUES
                    ('$id', '2025140000', '1', '5', 'Available',
                    'Bien Ven P. Santos'
                    )";

            mysqli_query($conn, $sql);
        }
        else
        {
            echo "<br>There is an error in creating the table: " . $conn->connect_error;
        }

        $conn->close();
    }

    // Create Branch Master Table

    function create_BranchMasterTable()
    {
        $conn = connect();
        $sql = "CREATE TABLE BranchMaster (
                BranchCode INT(10),
                BranchName VARCHAR(100),
                BranchLocation VARCHAR(500),
                ContactNo INT(11)
                )";

        if (mysqli_query($conn, $sql))
        {
            
            $id = generate_BranchCode();            
            $sql = "INSERT INTO BranchMaster
                    (BranchCode,BranchName,BranchLocation,ContactNo)
                    VALUES
                    ('$id','Malabon Branch','12 Balabon Tinajeros, Rizal Street', '09658798565')";

            mysqli_query($conn, $sql);
        }
        else
        {
            echo "<br>There is an error in creating the table: " . $conn->connect_error;
        }

        $conn->close();
    }

    // Generate Product

    function generate_BranchCode()
    {
        $conn = connect();

        $query = "SELECT COUNT(*) as count FROM BranchMaster";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        $rowCount = $row["count"];

        // Get the current day
        $currentYear = date("Y");

        // Generate the ID
        $genID = (int)($currentYear . str_pad(6, 2, "1", STR_PAD_LEFT) . str_pad($rowCount, 4, "0", STR_PAD_LEFT));
        
        $checkQuery = "SELECT BranchCode FROM BranchMaster WHERE BranchCode = ?";
        $genID = checkDuplication($genID,$checkQuery);
        $conn->close();
        return $genID;
    }


    // Create Category Type Table

    function create_BrandMasterTable()
    {
        $conn = connect();

        $sql = "CREATE TABLE brandMaster (
                BrandID INT(10),
                BrandName VARCHAR(500)
                )";
       
        if (mysqli_query($conn, $sql))
        {            
            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Adensco'
                    )";

            mysqli_query($conn, $sql);
        }
        else
        {
            echo "<br>There is an error in creating the table: " . $conn->connect_error;
        }

        $conn->close();
    }

    // Generate Brand ID

    function generate_BrandID()
    {
        $conn = connect();

        $query = "SELECT COUNT(*) as count FROM brandMaster";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        $rowCount = $row["count"];

        // Get the current day
        $currentYear = date("Y");

        // Generate the ID
        $genID = (int)($currentYear . str_pad(5, 2, "1", STR_PAD_LEFT) . str_pad($rowCount, 4, "0", STR_PAD_LEFT));
        
        $checkQuery = "SELECT BrandID FROM brandMaster WHERE BrandID = ?";
        $genID = checkDuplication($genID,$checkQuery);
        $conn->close();
        return $genID;
    }

    // Create Shape Master Table

    function create_ShapeMasterTable()
    {
        $conn = connect();

        $sql = "CREATE TABLE shapeMaster (
                ShapeID INT(1),
                Description VARCHAR(30)
                )";
       
        if (mysqli_query($conn, $sql))
        {            
            $i = 0;
            do {                
                $i++;                       
                switch ($i) {
                    case 1:                        
                        $shape = 'Oval';
                        break;
                    case 2:                        
                        $shape = 'Triangle';
                        break;
                    case 3:                        
                        $shape = 'Diamond';
                        break;
                    case 4:                        
                        $shape = 'Round';
                        break;    
                    case 5:                        
                        $shape = 'Square';
                        break; 
                    case 6:                        
                        $shape = 'Square';
                        break; 
                    default:                    
                    break;                
                }
                $sql = "INSERT INTO shapeMaster
                (ShapeID,Description)
                VALUES
                ('$i','$shape')";
                mysqli_query($conn, $sql);
            } while ($i < 6);
        }
        else
        {
            echo "<br>There is an error in creating the table: " . $conn->connect_error;
        }

        $conn->close();
    }

    // Create Category Type Table

    function create_CategoryTypeTable()
    {
        $conn = connect();

        $sql = "CREATE TABLE categoryType (
                CategoryType VARCHAR(50),
                Description VARCHAR(500)
                )";
        $desc="Frames that will be used for the 
                    customer\'s Glasses";
        if (mysqli_query($conn, $sql))
        {            
            $sql = "INSERT INTO categoryType
                    (CategoryType,Description)
                    VALUES
                    ('Frame','$desc'
                    )";

            mysqli_query($conn, $sql);
        }
        else
        {
            echo "<br>There is an error in creating the table: " . $conn->connect_error;
        }

        $conn->close();
    }

    // Create Product Master Table

    function create_ProductMstrTable()
    {
        $conn = connect();
        $sql = "CREATE TABLE productMstr (
                ProductID INT(10) PRIMARY KEY,
                CategoryType VARCHAR(50),
                ShapeID INT(1),
                BrandID INT(10),
                Model VARCHAR(50),
                Remarks VARCHAR(500),
                ProductImage LONGBLOB,
                Avail_FL VARCHAR (50),  
                Upd_by VARCHAR(50),
                Upd_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";

        if (mysqli_query($conn, $sql))
        {
            $img_path = "Images/Frame1.jpg";
            $img_clean= file_get_contents($img_path);
            $product_pic = mysqli_real_escape_string($conn, $img_clean);
            $id = generate_ProductMstrID();
            
            $sql = "INSERT INTO productMstr
                    (ProductID,CategoryType,ShapeID,BrandID,Model,Remarks,
                    ProductImage,Avail_FL,Upd_by)
                    VALUES
                    ('$id', 'Frame', '1', '2025070011', 
                    'Model1', 'New Model', '$product_pic', 'Available', 
                    'Bien Ven P. Santos'
                    )";

            mysqli_query($conn, $sql);
        }
        else
        {
            echo "<br>There is an error in creating the table: " . $conn->connect_error;
        }

        $conn->close();
    }

    //Generate Product Master ID

    function generate_ProductMstrID()
    {
        $conn = connect();

        $query = "SELECT COUNT(*) as count FROM productMstr";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        $rowCount = $row["count"];

        // Get the current day
        $currentYear = date("Y");

        // Generate the ID
        $genID = (int)($currentYear . str_pad(4, 2, "1", STR_PAD_LEFT) . str_pad($rowCount, 4, "0", STR_PAD_LEFT));
        
        $checkQuery = "SELECT ProductID FROM productMstr WHERE ProductID = ?";
        $genID = checkDuplication($genID,$checkQuery);
        $conn->close();
        return $genID;
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
        $genID = (int)($currentYear . str_pad(3, 2, "1", STR_PAD_LEFT) . str_pad($rowCount, 4, "0", STR_PAD_LEFT));
        
        $checkQuery = "SELECT EmployeeID FROM employee WHERE EmployeeID = ?";
        $genID = checkDuplication($genID,$checkQuery);
        $conn->close();
        return $genID;
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
        $currentMonth = date("m");
        
        // Generate the ID
        $genID = (int)($currentYear . $currentMonth . str_pad($rowCount, 4, "0", STR_PAD_LEFT));        
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

    // Check if Product Master Table exists
    $table_check_query = "SHOW TABLES LIKE 'productMstr'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_ProductMstrTable();
    }

    // Check if Product Master Table exists
    $table_check_query = "SHOW TABLES LIKE 'CategoryType'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_CategoryTypeTable();
    }
    // Check if Shape Master Table exists
    $table_check_query = "SHOW TABLES LIKE 'shapeMaster'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_ShapeMasterTable();
    }
    // Check if Shape Master Table exists
    $table_check_query = "SHOW TABLES LIKE 'brandMaster'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_BrandMasterTable();
    }
    // Check if Product Branch Master Table exists
    $table_check_query = "SHOW TABLES LIKE 'ProductBranchMaster'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_ProductBrnchMstrTable();
    }
    // Check if Branch Master Table exists
    $table_check_query = "SHOW TABLES LIKE 'BranchMaster'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_BranchMasterTable();
    }
    
?>