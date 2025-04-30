<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    // Connection

    function connect() 
    {
        $db_host = 'localhost';
        $db_username = 'u809407821_santosopticals';
        $db_password = '8Bt?Q0]=w';
        $db_name = 'u809407821_santosopticals';

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
        $db_username = 'u809407821_santosopticals';
        $db_password = '8Bt?Q0]=w';

        // Create connection
        $conn = new mysqli($db_host, $db_username, $db_password);

        // Check connection
        if ($conn->connect_error) 
        {
            die("Connection failed: " . $conn->connect_error);
        }

        // Creating database
        $sql = "CREATE DATABASE u809407821_santosopticals";

        if ($conn->query($sql) === TRUE) 
        {
        } 
        else 
        {
            echo "There is an error in creating the database: " . $conn->error;
        }

        $conn->close();
    }

    // Create Role Master Table

    function create_RoleMasterTable()
    {
        $conn = connect();
        
        $sql = "CREATE TABLE roleMaster (
                RoleID INT(10) PRIMARY KEY,
                Description VARCHAR(30)
                )";

        if (mysqli_query($conn, $sql))
        {
            
            $sql = "INSERT INTO roleMaster
                    (RoleID, Description)
                    VALUES
                    ('1','Admin'
                    )";

            mysqli_query($conn, $sql);

            $sql = "INSERT INTO roleMaster
                    (RoleID, Description)
                    VALUES
                    ('2','Employee'
                    )";

            mysqli_query($conn, $sql);
        }
        else
        {
            echo "<br>There is an error in creating the table: " . $conn->connect_error;
        }

        $conn->close();
    }

    // Create Order_hdr Table

    function create_Order_hdrTable()
    {
        $conn = connect();
        $sql = "CREATE TABLE Order_hdr (
                Orderhdr_id INT(10) PRIMARY KEY,
                CustomerID INT(10),
                BranchCode INT(10),
                Created_by VARCHAR(50),
                Created_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (CustomerID) REFERENCES customer(CustomerID)
                )";

        if (mysqli_query($conn, $sql))
        {
            $id = generate_Order_hdr_ID();
            $id2 = generate_CustomerID();  
            --$id2;
            $sql = "INSERT INTO Order_hdr
                    (Orderhdr_id,CustomerID,BranchCode,Created_by)
                    VALUES
                    ('$id', '$id2', '2025160000', 'Bien Ven P. Santos'
                    )";

            mysqli_query($conn, $sql);
        }
        else
        {
            echo "<br>There is an error in creating the table: " . $conn->connect_error;
        }

        $conn->close();
    }

    // Generate Order_hdr_ID

    function generate_Order_hdr_ID()
    {
        $conn = connect();

        $query = "SELECT COUNT(*) as count FROM Order_hdr";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        $rowCount = $row["count"];

        // Get the current day
        $currentYear = date("Y");

        // Generate the ID
        $genID = (int)($currentYear . str_pad(1, 2, "2", STR_PAD_LEFT) . str_pad($rowCount, 4, "0", STR_PAD_LEFT));
        
        $checkQuery = "SELECT Orderhdr_id FROM Order_hdr WHERE Orderhdr_id = ?";
        $genID = checkDuplication($genID,$checkQuery);
        $conn->close();
        return $genID;
    }

    // Create Logs Table

    function create_LogsTable()
    {
        $conn = connect();
        $sql = "CREATE TABLE Logs (
                LogsID INT(10) PRIMARY KEY,
                EmployeeID INT(10),
                TargetID INT(10),
                TargetType ENUM('customer', 'employee', 'product', 'order') NOT NULL,
                ActivityCode INT(10),
                Upd_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (EmployeeID) REFERENCES employee(EmployeeID),                
                FOREIGN KEY (ActivityCode) REFERENCES activityMaster(ActivityCode)
                )";
    
        if (mysqli_query($conn, $sql))
        {
            $id = generate_LogsID(); 
            $id2 = generate_EmployeeID();
            --$id2;
            
            // Sample log entry for an employee activity
            $sql = "INSERT INTO Logs
                    (LogsID, EmployeeID, TargetID, TargetType, ActivityCode)
                    VALUES
                    ('$id', '$id2', '$id2', 'employee', '2')";
    
            mysqli_query($conn, $sql);
        }
        else
        {
            echo "<br>There is an error in creating the table: " . $conn->connect_error;
        }
    
        $conn->close();
    }

    // Generate LogsID

    function generate_LogsID()
    {
        $conn = connect();

        $query = "SELECT COUNT(*) as count FROM Logs";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        $rowCount = $row["count"];

        // Get the current day
        $currentYear = date("Y");

        // Generate the ID
        $genID = (int)($currentYear . str_pad(0, 2, "2", STR_PAD_LEFT) . str_pad($rowCount, 4, "0", STR_PAD_LEFT));
        
        $checkQuery = "SELECT LogsID FROM Logs WHERE LogsID = ?";
        $genID = checkDuplication($genID,$checkQuery);
        $conn->close();
        return $genID;
    }

    // Create Activity Master Table

    function create_ActivityhMstrTable()
    {
        $conn = connect();
        $sql = "CREATE TABLE activityMaster (
                ActivityCode INT(10) PRIMARY KEY,
                Description VARCHAR(30)
                )";

        if (mysqli_query($conn, $sql))
        {
            
            $sql = "INSERT INTO activityMaster
                    (ActivityCode, Description)
                    VALUES
                    ('1','Purchased'),
                    ('2','Added'),
                    ('3','Archived'),
                    ('4','Edited')
                    ";

            mysqli_query($conn, $sql);
        }
        else
        {
            echo "<br>There is an error in creating the table: " . $conn->connect_error;
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
                Upd_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (ProductID) REFERENCES productMstr(ProductID),
                FOREIGN KEY (BranchCode) REFERENCES BranchMaster(BranchCode)
                )";

        if (mysqli_query($conn, $sql)) {
            for ($i = 0; $i < 12; $i++) {
                $id = generate_ProductBrnchMstrID();    
                $id2 = 2025140000 + $i; 
                $id3 = generate_BranchCode();
                $id3 = ($id3-4)+(rand(0,3));

                $count = rand(3, 50); // Generate a random count between 3 and 50
                
                $sql = "INSERT INTO ProductBranchMaster
                        (ProductBranchID, ProductID, BranchCode, Count, Avail_FL, Upd_by)
                        VALUES
                        ('$id', '$id2', '$id3', '$count', 'Available', 'Bien Ven P. Santos')";
                mysqli_query($conn, $sql);             
            }
        }
        else
        {
            echo "<br>There is an error in creating the table: " . $conn->connect_error;
        }

        $conn->close();
    }

    // Generate ProductBrnchMstrID

    function generate_ProductBrnchMstrID()
    {
        $conn = connect();

        $query = "SELECT COUNT(*) as count FROM ProductBranchMaster";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        $rowCount = $row["count"];

        // Get the current day
        $currentYear = date("Y");

        // Generate the ID
        $genID = (int)($currentYear . str_pad(9, 2, "1", STR_PAD_LEFT) . str_pad($rowCount, 4, "0", STR_PAD_LEFT));
        
        $checkQuery = "SELECT ProductBranchID FROM ProductBranchMaster WHERE ProductBranchID = ?";
        $genID = checkDuplication($genID,$checkQuery);
        $conn->close();
        return $genID;
    }

    // Create Order Details Table

    function create_orderDetailsTable()
    {
        $conn = connect();
        $sql = "CREATE TABLE orderDetails (
                OrderDtlID INT(10) PRIMARY KEY, 
                OrderHdr_id INT(10),
                ProductBranchID INT(10),
                Quantity INT(100),
                ActivityCode INT(10),
                Status VARCHAR(10),
                FOREIGN KEY (OrderHdr_id) REFERENCES Order_hdr(OrderHdr_id),
                FOREIGN KEY (ProductBranchID) REFERENCES ProductBranchMaster(ProductBranchID),
                FOREIGN KEY (ActivityCode) REFERENCES activityMaster(ActivityCode)
                )";

        if (mysqli_query($conn, $sql))
        {            
            $id = generate_OrderDtlID(); 
            $id2 = generate_Order_hdr_ID(); 
            $id3 = generate_ProductBrnchMstrID(); 
            --$id2;  
            --$id3;         
            $sql = "INSERT INTO orderDetails
                    (OrderDtlID, OrderHdr_id , ProductBranchID, Quantity, 
                    ActivityCode, Status)
                    VALUES
                    ('$id','$id2', '$id3', '5' , '2', 'Available'
                    )";

            mysqli_query($conn, $sql);
        }
        else
        {
            echo "<br>There is an error in creating the table: " . $conn->connect_error;
        }

        $conn->close();
    }

    // Generate orderDetails

    function generate_OrderHdr_id()
    {
        $conn = connect();

        $query = "SELECT COUNT(*) as count FROM orderDetails";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        $rowCount = $row["count"];

        // Get the current day
        $currentYear = date("Y");

        // Generate the ID
        $genID = (int)($currentYear . str_pad(7, 2, "1", STR_PAD_LEFT) . str_pad($rowCount, 4, "0", STR_PAD_LEFT));
        
        $checkQuery = "SELECT OrderHdr_id FROM orderDetails WHERE OrderHdr_id = ?";
        $genID = checkDuplication($genID,$checkQuery);
        $conn->close();
        return $genID;
    }

     // Generate OrderDtlID

     function generate_OrderDtlID()
     {
         $conn = connect();
 
         $query = "SELECT COUNT(*) as count FROM orderDetails";
         $result = $conn->query($query);
         $row = $result->fetch_assoc();
         $rowCount = $row["count"];
 
         // Get the current day
         $currentYear = date("Y");
 
         // Generate the ID
         $genID = (int)($currentYear . str_pad(8, 2, "1", STR_PAD_LEFT) . str_pad($rowCount, 4, "0", STR_PAD_LEFT));
         
         $checkQuery = "SELECT OrderDtlID FROM orderDetails WHERE OrderDtlID = ?";
         $genID = checkDuplication($genID,$checkQuery);
         $conn->close();
         return $genID;
     }

    // Create Branch Master Table

    function create_BranchMasterTable()
    {
        $conn = connect();
        $sql = "CREATE TABLE BranchMaster (
                BranchCode INT(10) PRIMARY KEY,
                BranchName VARCHAR(100),
                BranchLocation VARCHAR(500),
                ContactNo VARCHAR(11)
                )";

        if (mysqli_query($conn, $sql))
        {
            
            $id = generate_BranchCode(); 
            $id1 = $id+1;
            $id2 = $id+2;
            $id3 = $id+3;           
            $sql = "INSERT INTO BranchMaster
                    (BranchCode,BranchName,BranchLocation,ContactNo)
                    VALUES
                    ('$id','Malabon Branch - Pascual St.','Pascual St, Malabon', '0288183480'),
                    ('$id1','Malabon Branch - Bayan','Bayan, Malabon', '0286321972'),
                    ('$id2','Manila Branch','Quiapo, Manila', '9328447068'),
                    ('$id3','Navotas Branch','Tangos, Navotas', '9658798565')
                    ";

            mysqli_query($conn, $sql);
        }
        else
        {
            echo "<br>There is an error in creating the table: " . $conn->connect_error;
        }

        $conn->close();
    }

    // Generate BranchCode

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
                BrandID INT(10) PRIMARY KEY,
                BrandName VARCHAR(500)
                )";
       
        if (mysqli_query($conn, $sql))
        {            
            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Minima'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','IMAX'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Paul Hueman'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Caradin'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Lee Cooper'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Bobby Jones'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Light Tech'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Ray-Ban'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Oakley'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Persol'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Acuvue'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Air Optix'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Biofinity'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','EyeMo'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Essilor'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Hoya'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Zeiss'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Bausch + Lomb'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Rodenstock'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Maui Jim'
                    )";
            mysqli_query($conn, $sql);

            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster
                    (BrandID,BrandName)
                    VALUES
                    ('$genID','Nikon'
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
                ShapeID INT(1) PRIMARY KEY,
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
                    default:                    
                    break;                
                }
                $sql = "INSERT INTO shapeMaster
                (ShapeID, Description)
                VALUES
                ('$i','$shape')";
                mysqli_query($conn, $sql);
            } while ($i < 5);
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
                CategoryType VARCHAR(50) PRIMARY KEY,
                Description VARCHAR(500)
                )";
        $desc = mysqli_real_escape_string($conn, "Frames that will be used for the customer's Glasses");
        if (mysqli_query($conn, $sql))
        {            
            $sql = "INSERT INTO categoryType
                    (CategoryType,Description)
                    VALUES
                    ('Frame', '$desc'),
                    ('Contact Lenses', 'Contact lenses are thin lenses placed directly on the surface of the eye.'),
                    ('Sunglasses', 'Sunglasses are eyewear designed to protect the eyes from sunlight and high-energy visible light.'),
                    ('Convex Lens', 'Convex lenses are thicker in the center than at the edges and are used to correct hyperopia (farsightedness).'),
                    ('Concave Lens', 'Concave lenses are thinner in the center than at the edges and are used to correct myopia (nearsightedness).'),
                    ('Bifocal Lens', 'Bifocal lenses have two distinct optical powers, one for distance and one for near vision.'),
                    ('Trifocal Lens', 'Trifocal lenses have three distinct optical powers for distance, intermediate, and near vision.'),
                    ('Progressive Lens', 'Progressive lenses provide a smooth transition between multiple lens powers without visible lines.'),
                    ('Photochromic Lens', 'Photochromic lenses darken in response to sunlight and clear up indoors.'),
                    ('Polarized Lens', 'Polarized lenses reduce glare from reflective surfaces, improving visual comfort and clarity.')";

            mysqli_query($conn, $sql);
        }
        else
        {
            echo "<br>There is an error in creating the table: " . $conn->connect_error;
        }

        $conn->close();
    }

    // Create Product Master Table
    function create_ProductMstrTable() {
        $conn = connect();
        $sql = "CREATE TABLE productMstr (
            ProductID INT(10) PRIMARY KEY,
            CategoryType VARCHAR (50),
            ShapeID INT (1),
            BrandID INT (10),                              
            Model VARCHAR(50),
            Material VARCHAR(50),
            Price VARCHAR(20),
            ProductImage VARCHAR(255),
            Avail_FL VARCHAR (50),  
            Upd_by VARCHAR(50),
            Upd_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (CategoryType) REFERENCES categoryType(CategoryType),
            FOREIGN KEY (ShapeID) REFERENCES shapeMaster(ShapeID),
            FOREIGN KEY (BrandID) REFERENCES brandMaster(BrandID)
            )";

        mysqli_query($conn, $sql);
        $conn->close();
    }

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
                EmployeePicture VARCHAR(255),
                EmployeeEmail VARCHAR(100),
                EmployeeNumber VARCHAR(11),
                RoleID INT(10),
                LoginName VARCHAR(50),
                Password VARCHAR(255),
                BranchCode int(10),
                Status VARCHAR(50),
                Upd_by VARCHAR(50),
                Upd_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (RoleID) REFERENCES roleMaster(RoleID),
                FOREIGN KEY (BranchCode) REFERENCES BranchMaster(BranchCode)
                )";

        mysqli_query($conn, $sql);        

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
    
        mysqli_query($conn, $sql);
    
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
    function populateCoreTablesWith15Records() {
        $conn = connect();
        
        try {
            // 1. Populate 15 Employees
            $employees = [
                ['Bien Ven P. Santos', 'BVPSantosOptical@gmail.com', '09864571325', 1, 'BVSantos1', 'JPSantos123', 2025160000],
                ['Sean Genesis V. Morse', 'SeanGenesis@gmail.com', '09438945698', 2, 'SGMorse1', 'Morse123', 2025160001],
                ['Maria Cristina L. Reyes', 'MCReyes@gmail.com', '09123456789', 2, 'MCReyes1', 'Reyes123', 2025160002],
                ['Juan Dela Cruz', 'JDCruz@gmail.com', '09234567890', 2, 'JDCruz1', 'Cruz123', 2025160003],
                ['Ana Marie S. Lopez', 'AMLopez@gmail.com', '09345678901', 2, 'AMLopez1', 'Lopez123', 2025160000],
                ['Carlos Miguel G. Tan', 'CMTan@gmail.com', '09456789012', 2, 'CMTan1', 'Tan123', 2025160001],
                ['Lourdes F. Mendoza', 'LFMendoza@gmail.com', '09567890123', 2, 'LFMendoza1', 'Mendoza123', 2025160002],
                ['Ricardo B. Gonzales', 'RBGonzales@gmail.com', '09678901234', 2, 'RBGonzales1', 'Gonzales123', 2025160003],
                ['Patricia Ann Q. Santos', 'PAQSantos@gmail.com', '09789012345', 2, 'PAQSantos1', 'Santos123', 2025160000],
                ['Francisco M. Lim', 'FMLim@gmail.com', '09890123456', 2, 'FMLim1', 'Lim123', 2025160001],
                ['Elena R. Castillo', 'ECastillo@gmail.com', '09901234567', 2, 'ECastillo1', 'Castillo123', 2025160002],
                ['Miguel A. Santiago', 'MSantiago@gmail.com', '09112233445', 2, 'MSantiago1', 'Santiago123', 2025160003],
                ['Sophia G. Ramirez', 'SRamirez@gmail.com', '09223344556', 2, 'SRamirez1', 'Ramirez123', 2025160000],
                ['Daniel L. Ocampo', 'DOcampo@gmail.com', '09334455667', 2, 'DOcampo1', 'Ocampo123', 2025160001],
                ['Isabella T. Navarro', 'INavarro@gmail.com', '09445566778', 2, 'INavarro1', 'Navarro123', 2025160002]
            ];
    
            echo "Populating 15 employees...\n";
            foreach ($employees as $emp) {
                $id = generate_EmployeeID();
                $hashed_pw = password_hash($emp[5], PASSWORD_DEFAULT);
                $img_path = "Images/default.jpg";
    
                $sql = "INSERT INTO employee VALUES (
                    $id, 
                    '{$emp[0]}', 
                    '$img_path', 
                    '{$emp[1]}', 
                    '{$emp[2]}', 
                    {$emp[3]}, 
                    '{$emp[4]}', 
                    '$hashed_pw', 
                    {$emp[6]}, 
                    'Active', 
                    'System', 
                    NOW()
                )";
    
                if (!mysqli_query($conn, $sql)) {
                    throw new Exception("Failed to insert employee: " . $conn->error);
                }
                echo "Added employee: {$emp[0]}\n";
            }
    
            // 2. Populate 15 Customers with Logs
            $customers = [
                ['Sean Genesis', '231 Visayas St, Malabon', '09864325874', '60 Years old \n185cm \nMale', 'Round Face Shape'],
                ['Maria Teresa Cruz', '123 Main St, Quezon City', '09123456789', '45 Years old \n160cm \nFemale', 'Oval Face Shape'],
                ['Juan Dela Peña', '456 Oak Ave, Makati', '09234567890', '35 Years old \n175cm \nMale', 'Square Face Shape'],
                ['Ana Marie Santos', '789 Pine Rd, Manila', '09345678901', '28 Years old \n165cm \nFemale', 'Heart Face Shape'],
                ['Carlos Miguel Reyes', '321 Elm St, Pasig', '09456789012', '50 Years old \n170cm \nMale', 'Oval Face Shape'],
                ['Lourdes Fernandez', '654 Maple Ln, Mandaluyong', '09567890123', '55 Years old \n158cm \nFemale', 'Round Face Shape'],
                ['Ricardo Gonzales', '987 Cedar Blvd, Taguig', '09678901234', '40 Years old \n180cm \nMale', 'Square Face Shape'],
                ['Patricia Ann Lim', '135 Walnut St, Paranaque', '09789012345', '30 Years old \n162cm \nFemale', 'Oval Face Shape'],
                ['Francisco Martinez', '246 Birch Rd, Las Piñas', '09890123456', '65 Years old \n172cm \nMale', 'Round Face Shape'],
                ['Elena Rodriguez', '369 Spruce Ave, Muntinlupa', '09901234567', '42 Years old \n166cm \nFemale', 'Heart Face Shape'],
                ['Antonio B. Reyes', '159 Acacia St, Valenzuela', '09112233445', '48 Years old \n170cm \nMale', 'Oval Face Shape'],
                ['Gabriela S. Mendoza', '753 Pineapple St, Caloocan', '09223344556', '33 Years old \n163cm \nFemale', 'Heart Face Shape'],
                ['Rafael T. Castro', '852 Mango Ave, Marikina', '09334455667', '52 Years old \n175cm \nMale', 'Square Face Shape'],
                ['Isabel V. Dela Cruz', '456 Banana Rd, San Juan', '09445566778', '29 Years old \n160cm \nFemale', 'Oval Face Shape'],
                ['Luis M. Navarro', '789 Coconut Ln, Pasay', '09556677889', '38 Years old \n178cm \nMale', 'Round Face Shape']
            ];
    
            echo "\nPopulating 15 customers with logs...\n";
            foreach ($customers as $cust) {
                $id = generate_CustomerID();
                
                $sql = "INSERT INTO customer VALUES (
                    '$id', 
                    '{$cust[0]}', 
                    '{$cust[1]}', 
                    '{$cust[2]}', 
                    '{$cust[3]}', 
                    '{$cust[4]}', 
                    'System', 
                    NOW()
                )";
    
                if (!mysqli_query($conn, $sql)) {
                    throw new Exception("Failed to insert customer: " . $conn->error);
                }
    
                // Create log entry
                $logId = generate_LogsID();
                $employee_id = 1; // Default to admin
                
                $logSql = "INSERT INTO Logs VALUES (
                    '$logId', 
                    '$employee_id', 
                    '$id', 
                    'customer', 
                    2, 
                    NOW()
                )";
    
                if (!mysqli_query($conn, $logSql)) {
                    throw new Exception("Failed to insert customer log: " . $conn->error);
                }
                echo "Added customer: {$cust[0]}\n";
            }
    
            // 3. Populate 15 Products
            $products = [
                ['Minima M-508C _144 867', 2025150000, 'Frame', 3500, 'Magnesium'],
                ['IMAX 5565 54-17-140', 2025150001, 'Frame', 4200, 'Beryllium'],
                ['Paul Hueman PHF-300A', 2025150002, 'Frame', 3800, 'Pure aluminum'],
                ['Caradin CR-2020', 2025150003, 'Frame', 4500, 'Ticral'],
                ['Lee Cooper LC-101', 2025150004, 'Frame', 3900, 'Stainless'],
                ['Bobby Jones BJ-505', 2025150005, 'Frame', 4100, 'Nickel titanium'],
                ['LIGHT TECH 7783L', 2025150006, 'Frame', 3700, 'Monel'],
                ['Ray-Ban RB2140', 2025150007, 'Sunglasses', 5200, 'Plastic'],
                ['Oakley OO9438', 2025150008, 'Sunglasses', 5800, 'Gliamide'],
                ['Persol PO3254', 2025150009, 'Sunglasses', 5400, 'Magnesium'],
                ['Acuvue Oasys', 2025150010, 'Contact Lenses', 3200, 'Silicone hydrogel'],
                ['Air Optix Aqua', 2025150011, 'Contact Lenses', 3400, 'Lotrafilcon B'],
                ['Biofinity', 2025150012, 'Contact Lenses', 3600, 'Comfilcon A'],
                ['Essilor Varilux', 2025150013, 'Progressive Lens', 7800, 'Plastic'],
                ['Hoya EnRoute', 2025150014, 'Photochromic Lens', 8200, 'Polycarbonate']
            ];
    
            echo "\nPopulating 15 products...\n";
            foreach ($products as $index => $prod) {
                $id = generate_ProductMstrID();
                $shape = rand(1,5);
                $img_num = str_pad(69 + $index, 5, '0', STR_PAD_LEFT);
                $img_path = "Images/$img_num.jpg";
    
                $sql = "INSERT INTO productMstr VALUES (
                    '$id', 
                    '{$prod[2]}', 
                    $shape, 
                    '{$prod[1]}', 
                    '{$prod[0]}', 
                    '{$prod[4]}', 
                    '{$prod[3]}', 
                    '$img_path', 
                    'Available', 
                    'System', 
                    NOW()
                )";
    
                if (!mysqli_query($conn, $sql)) {
                    throw new Exception("Failed to insert product: " . $conn->error);
                }
                echo "Added product: {$prod[0]}\n";
            }
    
            echo "\nSuccessfully populated all tables with 15 records each!\n";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        } finally {
            $conn->close();
        }
    }
?>

<?php
    // Configuration
        $db_host = 'localhost';
        $db_username = 'u809407821_santosopticals';
        $db_password = '8Bt?Q0]=w';

        // Create connection
        $conn = new mysqli($db_host, $db_username, $db_password);
        
     $db_check_query = "SHOW DATABASES LIKE 'u809407821_santosopticals'";
 
     $result = mysqli_query($conn, $db_check_query);    
     if (mysqli_num_rows($result) == 0) 
    {
        createDB();

        $conn->close();        
    }
    $conn = connect();
    
    // Check if Role Master Table exists
    $table_check_query = "SHOW TABLES LIKE 'roleMaster'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_RoleMasterTable();
    }
     // Check if Branch Master Table exists
    $table_check_query = "SHOW TABLES LIKE 'BranchMaster'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_BranchMasterTable();
    }    
    // Check if Activity Master Table exists
    $table_check_query = "SHOW TABLES LIKE 'activityMaster'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_ActivityhMstrTable();
    }    
    // Check if Product Master Table exists
    $table_check_query = "SHOW TABLES LIKE 'categoryType'";  // Changed from 'CategoryType' to 'categoryType'
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
    $table_check_query = "SHOW TABLES LIKE 'brandMaster'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_BrandMasterTable();
    }
    // Check if employee table exists
    $table_check_query = "SHOW TABLES LIKE 'employee'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_EmployeesTable();
    }
    // Check if Shape Master Table exists
    $table_check_query = "SHOW TABLES LIKE 'shapeMaster'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_ShapeMasterTable();
    }
    // Check if Shape Master Table exists
    // Check if customer table exists
    $table_check_query = "SHOW TABLES LIKE 'customer'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_CustomersTable();
    }    
    // Check if Order_hdr Table exists
    $table_check_query = "SHOW TABLES LIKE 'Order_hdr'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_Order_hdrTable();
    }
    
    // Check if Product Master Table exists
    $table_check_query = "SHOW TABLES LIKE 'productMstr'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_ProductMstrTable();
    }
    // Check if Product Branch Master Table exists
    $table_check_query = "SHOW TABLES LIKE 'ProductBranchMaster'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_ProductBrnchMstrTable();
    }
    // Check if Order Details Table exists
    $table_check_query = "SHOW TABLES LIKE 'orderDetails'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_orderDetailsTable();
    }    
    
    // Check if Logs Table exists
    $table_check_query = "SHOW TABLES LIKE 'Logs'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_LogsTable();
    }    
    $emp_count = $conn->query("SELECT COUNT(*) FROM employee")->fetch_row()[0];
    $cust_count = $conn->query("SELECT COUNT(*) FROM customer")->fetch_row()[0];
    $prod_count = $conn->query("SELECT COUNT(*) FROM productMstr")->fetch_row()[0];
    $conn->close();

    if ($emp_count < 15 || $cust_count < 15 || $prod_count < 15) {
        populateCoreTablesWith15Records();
    }
?>