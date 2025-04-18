<?php
include 'setup.php'; // Include the setup.php file
require_once 'connect.php'; //Connect to the database


function getBranches() { // Function to get branches from the database
    $link = connect();
    $sql = "SELECT BranchName from branchmaster";
    $result = mysqli_query($link, $sql);
    while($row = mysqli_fetch_array($result)){
        echo "<option class='form-select-sm' value='".$row['BranchName']."'>".$row['BranchName']."</option>";
    }
}

function getBranch() { // Function to get branches from the database
    $link = connect();
    $sql = "SELECT * from branchmaster";
    $result = mysqli_query($link, $sql);
    while($row = mysqli_fetch_array($result)){
        echo "<option class='form-select-sm' value='".$row['BranchCode']."'>".$row['BranchName']."</option>";
    }
}

function getShapes() { // Function to get shapes from the database
    $link = connect();
    $sql = "SELECT * from shapemaster";
    $result = mysqli_query($link, $sql);
    while($row = mysqli_fetch_array($result)){
        echo "<option class='form-select-sm' value='".$row['ShapeID']."'>".$row['Description']."</option>";
    }
}

function getBrands() {
    $link = connect();
    $sql = "SELECT * from brandmaster";
    $result = mysqli_query($link, $sql);
    while($row = mysqli_fetch_array($result)){
        echo "<option class='form-select-sm' value='".$row['BrandID']."'>".$row['BrandName']."</option>";
    }
}

function getCategory() {
    $link = connect();
    $sql = "SELECT * from categorytype";
    $result = mysqli_query($link, $sql);
    while($row = mysqli_fetch_array($result)){
        echo "<option class='form-select-sm' value='".$row['CategoryType']."'>".$row['CategoryType']."</option>";
    }
}

function getEmployeeName() { // Function to get employee names from the database
    global $employeeName;
    $link = connect();
    $sql = "SELECT EmployeeName from employee WHERE LoginName = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['username']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $employeeName = $row['EmployeeName'];
    }
    mysqli_stmt_close($stmt);
}

function getInventory($branchName) { // Function to get inventory based on the selected branch
    $link = connect();
    $branchName = $_POST['chooseBranch'];
        $sql = "SELECT bm.BranchCode, pbm.*, pm.* 
                FROM branchmaster bm
                JOIN productbranchmaster pbm ON bm.BranchCode = pbm.BranchCode
                JOIN productmstr pm ON pbm.productID = pm.productID 
                WHERE bm.BranchName = ?"; //bm is branchmaster, pbm is productbranchmaster, pm is productmstr
        
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $branchName);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // display table
        echo "<table class='table table-bordered table-fixed mt-3' border='1'>
                <tr class = 'text-center'>
                    <th>Product ID</th>
                    <th>Category Type</th>
                    <th>Shape ID</th>
                    <th>Brand ID</th>
                    <th>Model</th>
                    <th>Remarks</th>
                    <th>Product Image</th>
                    <th>Count</th>
                    <th>Updated by</th>
                    <th>Updated Date</th>
                    <th colspan=2>Action</th>
                </tr>
                
                <tbody class='table-group-divider'>";

        // fetch and display results
        while ($row = mysqli_fetch_assoc($result)) {
            echo 
                "<tr class='text-center'>
                    <td>" . htmlspecialchars($row['ProductID']) . "</td>
                    <td>" . htmlspecialchars($row['CategoryType']) . "</td>
                    <td>" . htmlspecialchars($row['ShapeID']) . "</td>
                    <td>" . htmlspecialchars($row['BrandID']) . "</td>
                    <td>" . htmlspecialchars($row['Model']) . "</td>
                    <td>" . htmlspecialchars($row['Remarks']) . "</td>
                    <td><img src='" . htmlspecialchars($row['ProductImage']) . "' alt='Product Image' style='width:100px; height:auto;'/></td>
                    <td>" . htmlspecialchars($row['Count']) . "</td>
                    <td>" . htmlspecialchars($row['Upd_by']) . "</td>
                    <td>" . htmlspecialchars($row['Upd_dt']) . "</td>
                    <td>
                        <button type='submit' class='btn btn-success' style='font-size:12px'><i class='fa-solid fa-pen'></i></button>                        
                    </td>
                    <td>
                        <button type='submit' class='btn btn-danger' style='font-size:12px'><i class='fa-solid fa-trash'></i></button>                        
                    </td>
                </tr>";
        }

        echo " </tbody>
            </table>";

        // Close the statement
        mysqli_stmt_close($stmt);
}

function addProduct(){ //Add function to add a new product to the database
    $link = connect();
    global $employeeName;
    getEmployeeName();

    $newProductID = generate_ProductMstrID();
    $newProductBranchCode = $_POST['productBranch'];
    $newProductName = $_POST['productName'];
    $newProductQty = $_POST['productQty'];
    $newProductShape = $_POST['productShape'];
    $newProductCategory = $_POST['productCategory'];
    $newProductRemarks = $_POST['productRemarks'];
    $newProductImg = $_FILES['productImg'];
    $avail_FL = 'Available';
    $upd_by = $employeeName;
    $date = new DateTime();
    $upd_dt = $date->format('Y-m-d H:i:s');
    $newProductBrand = $_POST['productBrand'];
    $newProductBranchID = generate_ProductBrnchMstrID();
    

    
    if (isset($_POST['addProduct'])) {
        // Validate and upload the product image
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($newProductImg["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if image file is a valid image
        $check = getimagesize($newProductImg["tmp_name"]);
        if ($check === false) {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size (limit to 2MB)
        if ($newProductImg["size"] > 2000000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Attempt to upload file
        if ($uploadOk && move_uploaded_file($newProductImg["tmp_name"], $targetFile)) {
            // Insert product details into the database
            $sql = "INSERT INTO productmstr (ProductID, CategoryType, ShapeID, BrandID, Model, Remarks, ProductImage, Avail_FL, Upd_by, Upd_dt) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssssss", $newProductID, $newProductCategory, $newProductShape, $newProductBrand, $newProductName, $newProductRemarks, $targetFile, $avail_FL, $upd_by, $upd_dt);            
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            // Insert product-branch mapping into the database
            $sql = "INSERT INTO productbranchmaster (ProductBranchID, ProductID, BranchCode, Count, Avail_FL, Upd_by, Upd_dt)
                    VALUES (?, ?, ?, ?, ?, ?, ?)"; 
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "sssssss", $newProductBranchID, $newProductID, $newProductBranchCode, $newProductQty, $avail_FL, $upd_by, $upd_dt);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            echo '<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="successModalLabel">Success</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        The product has been added to the database successfully!
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>';
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
?>