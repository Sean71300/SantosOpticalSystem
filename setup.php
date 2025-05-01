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
        mysqli_query($conn, $sql);        
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
            $Code = 0;
            $Actions = ['Ordered','Pending','Added','Archived','Edited'];
            foreach ($Actions as $Actions) {
                ++$Code;
                $sql = "INSERT INTO activityMaster
                    (ActivityCode, Description)
                    VALUES
                    ('$Code','Ordered')";

                mysqli_query($conn, $Actions);
            }
            
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
                Stocks INT(100),
                Avail_FL VARCHAR(50), 
                Upd_by VARCHAR(50),
                Upd_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (ProductID) REFERENCES productMstr(ProductID) ON DELETE CASCADE,
                FOREIGN KEY (BranchCode) REFERENCES BranchMaster(BranchCode) ON DELETE CASCADE
                )";
    
        if (mysqli_query($conn, $sql)) {
            for ($i = 0; $i < 15; $i++) {
                $id = generate_ProductBrnchMstrID();    
                $id2 = 2025140000 + $i; 
                $id3 = generate_BranchCode();
                $id3 = ($id3-4)+(rand(0,3));
    
                $count = rand(3, 50); // Generate a random count between 3 and 50
                
                $sql = "INSERT INTO ProductBranchMaster
                        (ProductBranchID, ProductID, BranchCode, Stocks, Avail_FL, Upd_by)
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

    function create_BrandMasterTable() {
    $conn = connect();

    $sql = "CREATE TABLE brandMaster (
            BrandID INT(10) PRIMARY KEY,
            BrandName VARCHAR(500)
            )";

    if (mysqli_query($conn, $sql)) {
        $brands = [
            'Minima',
            'IMAX',
            'Paul Hueman',
            'Caradin',
            'Lee Cooper',
            'Bobby Jones',
            'Light Tech',
            'Ray-Ban',
            'Oakley',
            'Persol',
            'Acuvue',
            'Air Optix',
            'Biofinity',
            'Essilor',
            'Hoya',
            'Zeiss',
            'Bausch + Lomb',
            'Rodenstock',
            'Maui Jim',
            'Nikon'
        ];

        foreach ($brands as $brandName) {
            $genID = generate_BrandID();
            $sql = "INSERT INTO brandMaster (BrandID, BrandName) VALUES ('$genID', '$brandName')";
            mysqli_query($conn, $sql);
        }
    } else {
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
                CategoryType VARCHAR(50),
                ShapeID INT(1),
                BrandID INT(10),                              
                Model VARCHAR(50),
                Material VARCHAR(50),
                Price VARCHAR(20),
                ProductImage VARCHAR(255),
                Avail_FL VARCHAR(50),  
                Upd_by VARCHAR(50),
                Upd_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (CategoryType) REFERENCES categoryType(CategoryType),
                FOREIGN KEY (ShapeID) REFERENCES shapeMaster(ShapeID),
                FOREIGN KEY (BrandID) REFERENCES brandMaster(BrandID)
                )";
    
        if (mysqli_query($conn, $sql)) {
            $products = [
                [
                    'model' => 'Minima M-508C _144 867',
                    'brandID' => 2025150000,
                    'category' => 'Frame',
                    'price' => '₱3500',
                    'material' => 'Magnesium',
                    'image' => 'Images/00069.jpg'
                ],
                [
                    'model' => 'IMAX 5565 54-17-140',
                    'brandID' => 2025150001,
                    'category' => 'Frame',
                    'price' => '₱4200',
                    'material' => 'Beryllium',
                    'image' => 'Images/00070.jpg'
                ],
                [
                    'model' => 'Paul Hueman PHF-300A',
                    'brandID' => 2025150002,
                    'category' => 'Frame',
                    'price' => '₱3800',
                    'material' => 'Pure aluminum',
                    'image' => 'Images/00071.jpg'
                ],
                [
                    'model' => 'Caradin CR-2020',
                    'brandID' => 2025150003,
                    'category' => 'Frame',
                    'price' => '₱4500',
                    'material' => 'Ticral',
                    'image' => 'Images/00072.jpg'
                ],
                [
                    'model' => 'Lee Cooper LC-101',
                    'brandID' => 2025150004,
                    'category' => 'Frame',
                    'price' => '₱3900',
                    'material' => 'Stainless',
                    'image' => 'Images/00073.jpg'
                ],
                [
                    'model' => 'Bobby Jones BJ-505',
                    'brandID' => 2025150005,
                    'category' => 'Frame',
                    'price' => '₱4100',
                    'material' => 'Nickel titanium',
                    'image' => 'Images/00074.jpg'
                ],
                [
                    'model' => 'LIGHT TECH 7783L',
                    'brandID' => 2025150006,
                    'category' => 'Frame',
                    'price' => '₱3700',
                    'material' => 'Monel',
                    'image' => 'Images/00075.jpg'
                ],
                [
                    'model' => 'Ray-Ban RB2140',
                    'brandID' => 2025150007,
                    'category' => 'Sunglasses',
                    'price' => '₱5200',
                    'material' => 'Plastic',
                    'image' => 'Images/00076.jpg'
                ],
                [
                    'model' => 'Oakley OO9438',
                    'brandID' => 2025150008,
                    'category' => 'Sunglasses',
                    'price' => '₱5800',
                    'material' => 'Gliamide',
                    'image' => 'Images/00077.jpg'
                ],
                [
                    'model' => 'Persol PO3254',
                    'brandID' => 2025150009,
                    'category' => 'Sunglasses',
                    'price' => '₱5400',
                    'material' => 'Magnesium',
                    'image' => 'Images/00078.jpg'
                ],
                [
                    'model' => 'Acuvue Oasys',
                    'brandID' => 2025150010,
                    'category' => 'Contact Lenses',
                    'price' => '₱3200',
                    'material' => 'Silicone hydrogel',
                    'image' => 'Images/00079.jpg'
                ],
                [
                    'model' => 'Air Optix Aqua',
                    'brandID' => 2025150011,
                    'category' => 'Contact Lenses',
                    'price' => '₱3400',
                    'material' => 'Lotrafilcon B',
                    'image' => 'Images/00080.jpg'
                ],
                [
                    'model' => 'Biofinity',
                    'brandID' => 2025150012,
                    'category' => 'Contact Lenses',
                    'price' => '₱3600',
                    'material' => 'Comfilcon A',
                    'image' => 'Images/00081.jpg'
                ],
                [
                    'model' => 'Essilor Varilux',
                    'brandID' => 2025150013,
                    'category' => 'Progressive Lens',
                    'price' => '₱7800',
                    'material' => 'Plastic',
                    'image' => 'Images/00082.jpg'
                ],
                [
                    'model' => 'Hoya EnRoute',
                    'brandID' => 2025150014,
                    'category' => 'Photochromic Lens',
                    'price' => '₱8200',
                    'material' => 'Polycarbonate',
                    'image' => 'Images/00083.jpg'
                ]
            ];
    
            foreach ($products as $prod) {
                $id = generate_ProductMstrID();
                $shape = rand(1,5);
    
                $sql = "INSERT INTO productMstr
                        (ProductID, CategoryType, ShapeID, BrandID, Model, Material, Price,
                        ProductImage, Avail_FL, Upd_by)
                        VALUES
                        ('$id', '{$prod['category']}', '$shape', '{$prod['brandID']}', 
                        '{$prod['model']}', '{$prod['material']}', '{$prod['price']}', 
                        '{$prod['image']}', 'Available', 'System')";
    
                mysqli_query($conn, $sql);
            }
        } else {
            echo "<br>There is an error in creating the table: " . $conn->error;
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
            $employees = [
                [
                    'name' => 'Bien Ven P. Santos',
                    'email' => 'BVPSantosOptical@gmail.com',
                    'number' => '09864571325',
                    'role' => 1,
                    'login' => 'BVSantos1',
                    'password' => 'JPSantos123',
                    'branch' => 2025160000,
                    'status' => 'Active'
                ],
                [
                    'name' => 'Sean Genesis V. Morse',
                    'email' => 'SeanGenesis@gmail.com',
                    'number' => '09438945698',
                    'role' => 2,
                    'login' => 'SGMorse1',
                    'password' => 'Morse123',
                    'branch' => 2025160001,
                    'status' => 'Active'
                ],
                [
                    'name' => 'Maria Cristina L. Reyes',
                    'email' => 'MCReyes@gmail.com',
                    'number' => '09123456789',
                    'role' => 2,
                    'login' => 'MCReyes1',
                    'password' => 'Reyes123',
                    'branch' => 2025160002,
                    'status' => 'Active'
                ],
                [
                    'name' => 'Juan Dela Cruz',
                    'email' => 'JDCruz@gmail.com',
                    'number' => '09234567890',
                    'role' => 2,
                    'login' => 'JDCruz1',
                    'password' => 'Cruz123',
                    'branch' => 2025160003,
                    'status' => 'Active'
                ],
                [
                    'name' => 'Ana Marie S. Lopez',
                    'email' => 'AMLopez@gmail.com',
                    'number' => '09345678901',
                    'role' => 2,
                    'login' => 'AMLopez1',
                    'password' => 'Lopez123',
                    'branch' => 2025160000,
                    'status' => 'Active'
                ],
                [
                    'name' => 'Carlos Miguel G. Tan',
                    'email' => 'CMTan@gmail.com',
                    'number' => '09456789012',
                    'role' => 2,
                    'login' => 'CMTan1',
                    'password' => 'Tan123',
                    'branch' => 2025160001,
                    'status' => 'Active'
                ],
                [
                    'name' => 'Lourdes F. Mendoza',
                    'email' => 'LFMendoza@gmail.com',
                    'number' => '09567890123',
                    'role' => 2,
                    'login' => 'LFMendoza1',
                    'password' => 'Mendoza123',
                    'branch' => 2025160002,
                    'status' => 'Active'
                ],
                [
                    'name' => 'Ricardo B. Gonzales',
                    'email' => 'RBGonzales@gmail.com',
                    'number' => '09678901234',
                    'role' => 2,
                    'login' => 'RBGonzales1',
                    'password' => 'Gonzales123',
                    'branch' => 2025160003,
                    'status' => 'Active'
                ],
                [
                    'name' => 'Patricia Ann Q. Santos',
                    'email' => 'PAQSantos@gmail.com',
                    'number' => '09789012345',
                    'role' => 2,
                    'login' => 'PAQSantos1',
                    'password' => 'Santos123',
                    'branch' => 2025160000,
                    'status' => 'Active'
                ],
                [
                    'name' => 'Francisco M. Lim',
                    'email' => 'FMLim@gmail.com',
                    'number' => '09890123456',
                    'role' => 2,
                    'login' => 'FMLim1',
                    'password' => 'Lim123',
                    'branch' => 2025160001,
                    'status' => 'Active'
                ]
            ];

            $img_path = "Images/default.jpg";
            
            foreach ($employees as $emp) {
                $id = generate_EmployeeID();
                $hashed_pw = password_hash($emp['password'], PASSWORD_DEFAULT);

                $sql = "INSERT INTO employee
                        (EmployeeID, EmployeeName, EmployeePicture, EmployeeEmail,
                        EmployeeNumber, RoleID, LoginName, Password, BranchCode, Status,
                        Upd_by)
                        VALUES
                        ($id, '{$emp['name']}', '$img_path', '{$emp['email']}', 
                        '{$emp['number']}', '{$emp['role']}', '{$emp['login']}', '$hashed_pw', 
                        '{$emp['branch']}', '{$emp['status']}', 'Admin')";

                mysqli_query($conn, $sql);
            }
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
            $customers = [
                [
                    'name' => 'Sean Genesis',
                    'address' => '231 Visayas Street, Malabon City',
                    'contact' => '09864325874',
                    'info' => '60 Years old \n185cm \nMale',
                    'notes' => 'Round Face Shape'
                ],
                [
                    'name' => 'Maria Teresa Cruz',
                    'address' => '123 Main Street, Quezon City',
                    'contact' => '09123456789',
                    'info' => '45 Years old \n160cm \nFemale',
                    'notes' => 'Oval Face Shape'
                ],
                [
                    'name' => 'Juan Dela Peña',
                    'address' => '456 Oak Avenue, Makati City',
                    'contact' => '09234567890',
                    'info' => '35 Years old \n175cm \nMale',
                    'notes' => 'Square Face Shape'
                ],
                [
                    'name' => 'Ana Marie Santos',
                    'address' => '789 Pine Road, Manila',
                    'contact' => '09345678901',
                    'info' => '28 Years old \n165cm \nFemale',
                    'notes' => 'Heart Face Shape'
                ],
                [
                    'name' => 'Carlos Miguel Reyes',
                    'address' => '321 Elm Street, Pasig City',
                    'contact' => '09456789012',
                    'info' => '50 Years old \n170cm \nMale',
                    'notes' => 'Oval Face Shape'
                ],
                [
                    'name' => 'Lourdes Fernandez',
                    'address' => '654 Maple Lane, Mandaluyong',
                    'contact' => '09567890123',
                    'info' => '55 Years old \n158cm \nFemale',
                    'notes' => 'Round Face Shape'
                ],
                [
                    'name' => 'Ricardo Gonzales',
                    'address' => '987 Cedar Blvd, Taguig',
                    'contact' => '09678901234',
                    'info' => '40 Years old \n180cm \nMale',
                    'notes' => 'Square Face Shape'
                ],
                [
                    'name' => 'Patricia Ann Lim',
                    'address' => '135 Walnut Street, Paranaque',
                    'contact' => '09789012345',
                    'info' => '30 Years old \n162cm \nFemale',
                    'notes' => 'Oval Face Shape'
                ],
                [
                    'name' => 'Francisco Martinez',
                    'address' => '246 Birch Road, Las Piñas',
                    'contact' => '09890123456',
                    'info' => '65 Years old \n172cm \nMale',
                    'notes' => 'Round Face Shape'
                ],
                [
                    'name' => 'Elena Rodriguez',
                    'address' => '369 Spruce Avenue, Muntinlupa',
                    'contact' => '09901234567',
                    'info' => '42 Years old \n166cm \nFemale',
                    'notes' => 'Heart Face Shape'
                ]
            ];
            
            foreach ($customers as $cust) {
                $id = generate_CustomerID();
                
                $sql = "INSERT INTO customer 
                        (CustomerID, CustomerName, CustomerAddress, CustomerContact,
                        CustomerInfo, Notes, Upd_by) 
                        VALUES
                        ('$id', '{$cust['name']}', '{$cust['address']}', 
                        '{$cust['contact']}', '{$cust['info']}', 
                        '{$cust['notes']}', 'Bien Ven P. Santos')";     
                        
                mysqli_query($conn, $sql);
            }
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
    
?>