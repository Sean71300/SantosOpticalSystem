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
                ProductBranchID INT(10),
                ActivityCode INT(10),
                Count INT(10),
                Upd_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (EmployeeID) REFERENCES employee(EmployeeID),                
                FOREIGN KEY (ProductBranchID) REFERENCES ProductBranchMaster(ProductBranchID),
                FOREIGN KEY (ActivityCode) REFERENCES activityMaster(ActivityCode)
                )";

        if (mysqli_query($conn, $sql))
        {
            $id = generate_LogsID(); 
            $id2 = generate_EmployeeID(); 
            $id3 = generate_ProductBrnchMstrID();  
            --$id2;
            --$id3;
            $sql = "INSERT INTO Logs
                    (LogsID,EmployeeID,ProductBranchID,ActivityCode,Count)
                    VALUES
                    ('$id', '$id2', '$id3', '2','1'
                    )";

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
                    ('2','Purchased'
                    )";

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
                --$id3;

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
                CategoryType VARCHAR(50) PRIMARY KEY,
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
    function create_ProductMstrTable() {
        $conn = connect();
        $sql = "CREATE TABLE productMstr (
            ProductID INT(10) PRIMARY KEY,
            CategoryType VARCHAR (50),
            ShapeID INT (1),
            BrandID INT (10),                              
            Model VARCHAR(50),
            Remarks VARCHAR(500),
            ProductImage VARCHAR(255),
            Avail_FL VARCHAR (50),  
            Upd_by VARCHAR(50),
            Upd_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (CategoryType) REFERENCES categoryType(CategoryType),
            FOREIGN KEY (ShapeID) REFERENCES shapeMaster(ShapeID),
            FOREIGN KEY (BrandID) REFERENCES brandMaster(BrandID)
            )";

        if (mysqli_query($conn, $sql)) {
        
            for ($i = 0; $i <= 11; $i++) {
                $img_path = "Images/" . str_pad(69 + $i, 5, '0', STR_PAD_LEFT) . ".jpg";                
                $id = generate_ProductMstrID();
                $model = '';

                switch ($i) {
                    case 0: 
                        $model = 'Minima M-508C _144 867';
                        $brandID = 2025150000;
                        break;
                    case 1: 
                        $model = 'IMAX 5565 54-17-140'; 
                        $brandID = 2025150001;
                        break;
                    case 2: 
                        $model = 'Paul Hueman';
                        $brandID = 2025150002; 
                        break;
                    case 3: 
                        $model = 'PAUL HUEMAN PHF-300A Col.5 50-201-42';
                        $brandID = 2025150002;
                        break;
                    case 4: 
                        $model = 'Caradin'; 
                        $brandID = 2025150003;
                        break;
                    case 5: 
                        $model = 'Lee Cooper'; 
                        $brandID = 2025150004;
                        break;
                    case 6: 
                        $model = 'Bobby Jones'; 
                        $brandID = 2025150005;
                        break;
                    case 7: 
                        $model = 'LIGHT TECH 3PC 7783L 54-16-140 BB 072'; 
                        $brandID = 2025150006;
                        break;
                    case 8: 
                        $model = 'LIGHT TECH 3PC 7775LBG 007'; 
                        $brandID = 2025150006;
                        break;
                    case 9: 
                        $model = 'LIGHT TECH'; 
                        $brandID = 2025150006;
                        break;
                    case 10: 
                        $model = 'LIGHT TECH';
                        $brandID = 2025150006; 
                        break;
                    case 11: 
                        $model = 'LIGHT TECH';
                        $brandID = 2025150006; 
                        break;
                }

                $sql = "INSERT INTO productMstr
                            (ProductID, CategoryType, ShapeID, BrandID, Model, Remarks,
                            ProductImage, Avail_FL, Upd_by)
                            VALUES
                            ('$id', 'Frame', '1', '$brandID', 
                            '$model', 'New Model', '$img_path', 'Available', 
                            'Bien Ven P. Santos')";

                mysqli_query($conn, $sql);
            }
        } else {
            echo "<br>There is an error in creating the table: " . $conn->connect_error;
        }
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

        if (mysqli_query($conn, $sql))
        {
            $img_path = "Images/default.jpg";           
            $id = generate_EmployeeID();
            $id2 = generate_BranchCode();
            --$id2;
            $password = "JPSantos123";
            $email = "BVPSantosOptical@gmail.com";
            
            $hashed_pw = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO employee
                    (EmployeeID, EmployeeName, EmployeePicture,EmployeeEmail,
                    EmployeeNumber,RoleID,LoginName,Password,BranchCode,Status,
                    Upd_by)
                    VALUES
                    ($id, 'Bien Ven P. Santos', '$img_path', '$email', 
                    '09864571325', '1', 'BVSantos1', '$hashed_pw', '$id2', 'Active',
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
    
    // Check if customer table exists
    $table_check_query = "SHOW TABLES LIKE 'customer'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_CustomersTable();
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
    // Check if Order_hdr Table exists
    $table_check_query = "SHOW TABLES LIKE 'Order_hdr'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_Order_hdrTable();
    }
    // Check if Role Master Table exists
    $table_check_query = "SHOW TABLES LIKE 'roleMaster'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_RoleMasterTable();
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
    // Check if employee table exists
    $table_check_query = "SHOW TABLES LIKE 'employee'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_EmployeesTable();
    }
    // Check if Logs Table exists
    $table_check_query = "SHOW TABLES LIKE 'Logs'";
    $result = mysqli_query($conn, $table_check_query);

    if (mysqli_num_rows($result) == 0) 
    {
        create_LogsTable();
    }
?>
