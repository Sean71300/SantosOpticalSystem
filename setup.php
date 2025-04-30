<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection
function connect() {
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

// Create database if not exists
function createDB() {
    $conn = new mysqli('localhost', 'u809407821_santosopticals', '8Bt?Q0]=w');
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "CREATE DATABASE IF NOT EXISTS u809407821_santosopticals";
    
    if (!$conn->query($sql)) {
        echo "Error creating database: " . $conn->error;
    }
    
    $conn->close();
}

// ID Generation Functions
function generate_ID($prefix, $table, $padding = 4) {
    $conn = connect();
    $query = "SELECT COUNT(*) as count FROM $table";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $rowCount = $row["count"];
    $currentYear = date("Y");
    $genID = (int)($currentYear . str_pad($prefix, 2, "0", STR_PAD_LEFT) . str_pad($rowCount, $padding, "0", STR_PAD_LEFT));
    $conn->close();
    return $genID;
}

function checkDuplication($id, $checkQuery) {
    $conn = connect();
    while (true) {
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows == 0) break;
        $id++;
        $stmt->close();
    }
    $stmt->close();
    $conn->close();
    return $id;
}

// Logging Function
function addLog($employeeID, $targetID, $targetType, $activityCode, $conn = null) {
    $shouldClose = false;
    if ($conn === null) {
        $conn = connect();
        $shouldClose = true;
    }
    
    try {
        $logID = generate_ID(0, 'Logs');
        $sql = "INSERT INTO Logs (LogsID, EmployeeID, TargetID, TargetType, ActivityCode) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisi", $logID, $employeeID, $targetID, $targetType, $activityCode);
        $stmt->execute();
        return true;
    } catch (Exception $e) {
        error_log("Logging error: " . $e->getMessage());
        return false;
    } finally {
        if ($shouldClose) $conn->close();
    }
}

// Table Creation Functions
function create_RoleMasterTable() {
    $conn = connect();
    $sql = "CREATE TABLE IF NOT EXISTS roleMaster (
            RoleID INT(10) PRIMARY KEY,
            Description VARCHAR(30))";

    if (mysqli_query($conn, $sql)) {
        $roles = [
            ['1','Admin'],
            ['2','Employee']
        ];
        foreach ($roles as $role) {
            $sql = "INSERT IGNORE INTO roleMaster (RoleID, Description) VALUES ('$role[0]', '$role[1]')";
            mysqli_query($conn, $sql);
        }
        addLog(1, 1, 'employee', 2, $conn);
    } else {
        echo "Error creating roleMaster: " . $conn->error;
    }
    $conn->close();
}

function create_BranchMasterTable() {
    $conn = connect();
    $sql = "CREATE TABLE IF NOT EXISTS BranchMaster (
            BranchCode INT(10) PRIMARY KEY,
            BranchName VARCHAR(100),
            BranchLocation VARCHAR(500),
            ContactNo VARCHAR(11))";

    if (mysqli_query($conn, $sql)) {
        $branches = [
            ['Malabon Branch - Pascual St.', 'Pascual St, Malabon', '0288183480'],
            ['Malabon Branch - Bayan', 'Bayan, Malabon', '0286321972'],
            ['Manila Branch', 'Quiapo, Manila', '9328447068'],
            ['Navotas Branch', 'Tangos, Navotas', '9658798565']
        ];
        
        $baseID = generate_ID(6, 'BranchMaster');
        foreach ($branches as $i => $branch) {
            $id = $baseID + $i;
            $sql = "INSERT IGNORE INTO BranchMaster VALUES ('$id', '$branch[0]', '$branch[1]', '$branch[2]')";
            if (mysqli_query($conn, $sql)) {
                addLog(1, $id, 'employee', 2, $conn);
            }
        }
    } else {
        echo "Error creating BranchMaster: " . $conn->error;
    }
    $conn->close();
}

function create_ActivityhMstrTable() {
    $conn = connect();
    $sql = "CREATE TABLE IF NOT EXISTS activityMaster (
            ActivityCode INT(10) PRIMARY KEY,
            Description VARCHAR(30))";

    if (mysqli_query($conn, $sql)) {
        $activities = [
            ['1','Purchased'],
            ['2','Added'],
            ['3','Archived'],
            ['4','Edited']
        ];
        foreach ($activities as $activity) {
            $sql = "INSERT IGNORE INTO activityMaster VALUES ('$activity[0]', '$activity[1]')";
            mysqli_query($conn, $sql);
        }
    } else {
        echo "Error creating activityMaster: " . $conn->error;
    }
    $conn->close();
}

function create_CategoryTypeTable() {
    $conn = connect();
    $sql = "CREATE TABLE IF NOT EXISTS categoryType (
            CategoryType VARCHAR(50) PRIMARY KEY,
            Description VARCHAR(500))";

    if (mysqli_query($conn, $sql)) {
        $categories = [
            ['Frame', "Frames that will be used for the customer's Glasses"],
            ['Contact Lenses', 'Contact lenses are thin lenses placed directly on the surface of the eye.'],
            ['Sunglasses', 'Sunglasses are eyewear designed to protect the eyes from sunlight and high-energy visible light.'],
            ['Convex Lens', 'Convex lenses are thicker in the center than at the edges and are used to correct hyperopia (farsightedness).'],
            ['Concave Lens', 'Concave lenses are thinner in the center than at the edges and are used to correct myopia (nearsightedness).'],
            ['Bifocal Lens', 'Bifocal lenses have two distinct optical powers, one for distance and one for near vision.'],
            ['Trifocal Lens', 'Trifocal lenses have three distinct optical powers for distance, intermediate, and near vision.'],
            ['Progressive Lens', 'Progressive lenses provide a smooth transition between multiple lens powers without visible lines.'],
            ['Photochromic Lens', 'Photochromic lenses darken in response to sunlight and clear up indoors.'],
            ['Polarized Lens', 'Polarized lenses reduce glare from reflective surfaces, improving visual comfort and clarity.']
        ];
        foreach ($categories as $category) {
            $escapedDesc = mysqli_real_escape_string($conn, $category[1]);
            $sql = "INSERT IGNORE INTO categoryType VALUES ('$category[0]', '$escapedDesc')";
            mysqli_query($conn, $sql);
        }
    } else {
        echo "Error creating categoryType: " . $conn->error;
    }
    $conn->close();
}

function create_ShapeMasterTable() {
    $conn = connect();
    $sql = "CREATE TABLE IF NOT EXISTS shapeMaster (
            ShapeID INT(1) PRIMARY KEY,
            Description VARCHAR(30))";

    if (mysqli_query($conn, $sql)) {
        $shapes = [
            ['1','Oval'],
            ['2','Triangle'],
            ['3','Diamond'],
            ['4','Round'],
            ['5','Square']
        ];
        foreach ($shapes as $shape) {
            $sql = "INSERT IGNORE INTO shapeMaster VALUES ('$shape[0]', '$shape[1]')";
            mysqli_query($conn, $sql);
        }
    } else {
        echo "Error creating shapeMaster: " . $conn->error;
    }
    $conn->close();
}

function create_BrandMasterTable() {
    $conn = connect();
    $sql = "CREATE TABLE IF NOT EXISTS brandMaster (
            BrandID INT(10) PRIMARY KEY,
            BrandName VARCHAR(500))";

    if (mysqli_query($conn, $sql)) {
        $brands = [
            'Minima', 'IMAX', 'Paul Hueman', 'Caradin', 'Lee Cooper',
            'Bobby Jones', 'Light Tech', 'Ray-Ban', 'Oakley', 'Persol',
            'Acuvue', 'Air Optix', 'Biofinity', 'EyeMo', 'Essilor',
            'Hoya', 'Zeiss', 'Bausch + Lomb', 'Rodenstock', 'Maui Jim', 'Nikon'
        ];
        
        foreach ($brands as $i => $brand) {
            $id = generate_ID(5, 'brandMaster') + $i;
            $sql = "INSERT IGNORE INTO brandMaster VALUES ('$id', '$brand')";
            if (mysqli_query($conn, $sql)) {
                addLog(1, $id, 'product', 2, $conn);
            }
        }
    } else {
        echo "Error creating brandMaster: " . $conn->error;
    }
    $conn->close();
}

function create_EmployeesTable() {
    $conn = connect();
    $sql = "CREATE TABLE IF NOT EXISTS employee (
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
            FOREIGN KEY (BranchCode) REFERENCES BranchMaster(BranchCode))";

    if (mysqli_query($conn, $sql)) {
        $employees = [
            ['Bien Ven P. Santos', 'BVPSantosOptical@gmail.com', '09864571325', 1, 'BVSantos1', 'JPSantos123', 2025160000, 'Active'],
            ['Sean Genesis V. Morse', 'SeanGenesis@gmail.com', '09438945698', 2, 'SGMorse1', 'Morse123', 2025160001, 'Active'],
            ['Maria Cristina L. Reyes', 'MCReyes@gmail.com', '09123456789', 2, 'MCReyes1', 'Reyes123', 2025160002, 'Active'],
            ['Juan Dela Cruz', 'JDCruz@gmail.com', '09234567890', 2, 'JDCruz1', 'Cruz123', 2025160003, 'Active'],
            ['Ana Marie S. Lopez', 'AMLopez@gmail.com', '09345678901', 2, 'AMLopez1', 'Lopez123', 2025160000, 'Active']
        ];
        
        $img_path = "Images/default.jpg";
        $adminID = 1;
        
        foreach ($employees as $emp) {
            $id = generate_ID(3, 'employee');
            $hashed_pw = password_hash($emp[5], PASSWORD_DEFAULT);
            $sql = "INSERT IGNORE INTO employee VALUES (
                $id, '{$emp[0]}', '$img_path', '{$emp[1]}', 
                '{$emp[2]}', '{$emp[3]}', '{$emp[4]}', '$hashed_pw', 
                '{$emp[6]}', '{$emp[7]}', 'Admin', CURRENT_TIMESTAMP)";
            
            if (mysqli_query($conn, $sql)) {
                addLog($adminID, $id, 'employee', 2, $conn);
            }
        }
    } else {
        echo "Error creating employee table: " . $conn->error;
    }
    $conn->close();
}

function create_CustomersTable() {
    $conn = connect();
    $sql = "CREATE TABLE IF NOT EXISTS customer (
            CustomerID INT(10) PRIMARY KEY,
            CustomerName VARCHAR(100),
            CustomerAddress VARCHAR(100),
            CustomerContact VARCHAR(11),
            CustomerInfo VARCHAR(500),
            Notes VARCHAR(500),
            Upd_by VARCHAR(50),
            Upd_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";

    if (mysqli_query($conn, $sql)) {
        $customers = [
            ['Sean Genesis', '231 Visayas Street, Malabon City', '09864325874', '60 Years old \n185cm \nMale', 'Round Face Shape'],
            ['Maria Teresa Cruz', '123 Main Street, Quezon City', '09123456789', '45 Years old \n160cm \nFemale', 'Oval Face Shape'],
            ['Juan Dela Peña', '456 Oak Avenue, Makati City', '09234567890', '35 Years old \n175cm \nMale', 'Square Face Shape'],
            ['Ana Marie Santos', '789 Pine Road, Manila', '09345678901', '28 Years old \n165cm \nFemale', 'Heart Face Shape'],
            ['Carlos Miguel Reyes', '321 Elm Street, Pasig City', '09456789012', '50 Years old \n170cm \nMale', 'Oval Face Shape']
        ];
        
        $adminID = 1;
        $currentYear = date("Y");
        $currentMonth = date("m");
        
        foreach ($customers as $i => $cust) {
            $id = (int)($currentYear . $currentMonth . str_pad($i+1, 4, "0", STR_PAD_LEFT));
            $sql = "INSERT IGNORE INTO customer VALUES (
                '$id', '{$cust[0]}', '{$cust[1]}', '{$cust[2]}', 
                '{$cust[3]}', '{$cust[4]}', 'Bien Ven P. Santos', CURRENT_TIMESTAMP)";
            
            if (mysqli_query($conn, $sql)) {
                addLog($adminID, $id, 'customer', 2, $conn);
            }
        }
    } else {
        echo "Error creating customer table: " . $conn->error;
    }
    $conn->close();
}

function create_ProductMstrTable() {
    $conn = connect();
    $sql = "CREATE TABLE IF NOT EXISTS productMstr (
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
            FOREIGN KEY (BrandID) REFERENCES brandMaster(BrandID))";

    if (mysqli_query($conn, $sql)) {
        $products = [
            ['Frame', 'Minima M-508C _144 867', 'Magnesium', '₱3500', 'Images/00069.jpg', 2025150000],
            ['Frame', 'IMAX 5565 54-17-140', 'Beryllium', '₱4200', 'Images/00070.jpg', 2025150001],
            ['Sunglasses', 'Ray-Ban RB2140', 'Plastic', '₱5200', 'Images/00076.jpg', 2025150007],
            ['Contact Lenses', 'Acuvue Oasys', 'Silicone hydrogel', '₱3200', 'Images/00079.jpg', 2025150010],
            ['Progressive Lens', 'Essilor Varilux', 'Plastic', '₱7800', 'Images/00082.jpg', 2025150013]
        ];
        
        $adminID = 1;
        
        foreach ($products as $prod) {
            $id = generate_ID(4, 'productMstr');
            $shape = rand(1,5);
            $sql = "INSERT IGNORE INTO productMstr VALUES (
                '$id', '{$prod[0]}', '$shape', '{$prod[5]}', 
                '{$prod[1]}', '{$prod[2]}', '{$prod[3]}', 
                '{$prod[4]}', 'Available', 'System', CURRENT_TIMESTAMP)";
            
            if (mysqli_query($conn, $sql)) {
                addLog($adminID, $id, 'product', 2, $conn);
            }
        }
    } else {
        echo "Error creating productMstr: " . $conn->error;
    }
    $conn->close();
}

function create_ProductBrnchMstrTable() {
    $conn = connect();
    $sql = "CREATE TABLE IF NOT EXISTS ProductBranchMaster (
            ProductBranchID INT(10) PRIMARY KEY,
            ProductID INT(10),
            BranchCode INT(10),
            Count INT(100),
            Avail_FL VARCHAR(50), 
            Upd_by VARCHAR(50),
            Upd_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ProductID) REFERENCES productMstr(ProductID),
            FOREIGN KEY (BranchCode) REFERENCES BranchMaster(BranchCode))";

    if (mysqli_query($conn, $sql)) {
        $products = $conn->query("SELECT ProductID FROM productMstr LIMIT 5");
        $branches = $conn->query("SELECT BranchCode FROM BranchMaster LIMIT 4");
        
        $adminID = 1;
        $productIDs = [];
        $branchCodes = [];
        
        while ($row = $products->fetch_assoc()) $productIDs[] = $row['ProductID'];
        while ($row = $branches->fetch_assoc()) $branchCodes[] = $row['BranchCode'];
        
        foreach ($productIDs as $i => $productID) {
            $id = generate_ID(9, 'ProductBranchMaster');
            $branchCode = $branchCodes[$i % count($branchCodes)];
            $count = rand(3, 50);
            $sql = "INSERT IGNORE INTO ProductBranchMaster VALUES (
                '$id', '$productID', '$branchCode', '$count', 
                'Available', 'System', CURRENT_TIMESTAMP)";
            
            if (mysqli_query($conn, $sql)) {
                addLog($adminID, $id, 'product', 2, $conn);
            }
        }
    } else {
        echo "Error creating ProductBranchMaster: " . $conn->error;
    }
    $conn->close();
}

function create_Order_hdrTable() {
    $conn = connect();
    $sql = "CREATE TABLE IF NOT EXISTS Order_hdr (
            Orderhdr_id INT(10) PRIMARY KEY,
            CustomerID INT(10),
            BranchCode INT(10),
            Created_by VARCHAR(50),
            Created_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (CustomerID) REFERENCES customer(CustomerID))";

    if (mysqli_query($conn, $sql)) {
        $customers = $conn->query("SELECT CustomerID FROM customer LIMIT 3");
        $branches = $conn->query("SELECT BranchCode FROM BranchMaster LIMIT 2");
        $employees = $conn->query("SELECT EmployeeID, EmployeeName FROM employee LIMIT 1")->fetch_assoc();
        
        $adminID = 1;
        
        while ($customer = $customers->fetch_assoc() && $branch = $branches->fetch_assoc()) {
            $id = generate_ID(1, 'Order_hdr');
            $sql = "INSERT IGNORE INTO Order_hdr VALUES (
                '$id', '{$customer['CustomerID']}', '{$branch['BranchCode']}', 
                '{$employees['EmployeeName']}', CURRENT_TIMESTAMP)";
            
            if (mysqli_query($conn, $sql)) {
                addLog($adminID, $id, 'order', 1, $conn);
                create_orderDetailsTable($id, $conn);
            }
        }
    } else {
        echo "Error creating Order_hdr: " . $conn->error;
    }
    $conn->close();
}

function create_orderDetailsTable($orderID = null, $existingConn = null) {
    $conn = $existingConn ?: connect();
    
    $sql = "CREATE TABLE IF NOT EXISTS orderDetails (
            OrderDtlID INT(10) PRIMARY KEY, 
            OrderHdr_id INT(10),
            ProductBranchID INT(10),
            Quantity INT(100),
            ActivityCode INT(10),
            Status VARCHAR(10),
            FOREIGN KEY (OrderHdr_id) REFERENCES Order_hdr(OrderHdr_id),
            FOREIGN KEY (ProductBranchID) REFERENCES ProductBranchMaster(ProductBranchID),
            FOREIGN KEY (ActivityCode) REFERENCES activityMaster(ActivityCode))";

    if (mysqli_query($conn, $sql) && $orderID) {
        $productBranches = $conn->query("SELECT ProductBranchID FROM ProductBranchMaster LIMIT 3");
        $adminID = 1;
        
        while ($pb = $productBranches->fetch_assoc()) {
            $id = generate_ID(8, 'orderDetails');
            $quantity = rand(1, 5);
            $sql = "INSERT IGNORE INTO orderDetails VALUES (
                '$id', '$orderID', '{$pb['ProductBranchID']}', 
                '$quantity', '1', 'Available')";
            
            if (mysqli_query($conn, $sql)) {
                addLog($adminID, $id, 'order', 1, $conn);
            }
        }
    } else if (!$existingConn) {
        echo "Error creating orderDetails: " . $conn->error;
    }
    
    if (!$existingConn) $conn->close();
}

function create_LogsTable() {
    $conn = connect();
    $sql = "CREATE TABLE IF NOT EXISTS Logs (
            LogsID INT(10) PRIMARY KEY,
            EmployeeID INT(10),
            TargetID INT(10),
            TargetType ENUM('customer', 'employee', 'product', 'order') NOT NULL,
            ActivityCode INT(10),
            Upd_dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (EmployeeID) REFERENCES employee(EmployeeID),                
            FOREIGN KEY (ActivityCode) REFERENCES activityMaster(ActivityCode))";
    
    if (!mysqli_query($conn, $sql)) {
        echo "Error creating Logs table: " . $conn->error;
    }
    $conn->close();
}

// Main Execution
createDB();

// Create tables in proper order with dependencies
$tables = [
    'roleMaster' => 'create_RoleMasterTable',
    'BranchMaster' => 'create_BranchMasterTable',
    'activityMaster' => 'create_ActivityhMstrTable',
    'categoryType' => 'create_CategoryTypeTable',
    'shapeMaster' => 'create_ShapeMasterTable',
    'brandMaster' => 'create_BrandMasterTable',
    'employee' => 'create_EmployeesTable',
    'customer' => 'create_CustomersTable',
    'productMstr' => 'create_ProductMstrTable',
    'ProductBranchMaster' => 'create_ProductBrnchMstrTable',
    'Order_hdr' => 'create_Order_hdrTable',
    'orderDetails' => 'create_orderDetailsTable',
    'Logs' => 'create_LogsTable'
];

$conn = connect();
foreach ($tables as $table => $function) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows == 0) {
        $function();
    }
}
$conn->close();

echo "Database setup completed successfully!";
?>