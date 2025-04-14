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

function addProduct(){
    include 'setup.php';
    $link = connect();

    $newProductID = generate_ProductMstrID();
    $newProductBranchID = generate_ProductBrnchMstrID();
    $newProductBranch = $_POST['productBranch'];
    $newProductName = $_POST['productName'];
    $newProductQty = $_POST['productQty'];
    $newProductCategory = $_POST['productCategory'];
    $newProductRemarks = $_POST['productRemarks'];
    $newProductImg = $_FILES['productImg'];

    
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
            $sql = "INSERT INTO productmstr (ProductID, ProductName, CategoryType, Remarks, ProductImage) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "sssss", $newProductID, $newProductName, $newProductCategory, $newProductRemarks, $targetFile);
            mysqli_stmt_execute($stmt);

            // Insert product-branch mapping into the database
            $sqlBranch = "INSERT INTO productbranchmaster (ProductBranchID, BranchCode, ProductID, Count) 
                          VALUES (?, ?, ?, ?)";
            $stmtBranch = mysqli_prepare($link, $sqlBranch);
            mysqli_stmt_bind_param($stmtBranch, "sssi", $newProductBranchID, $newProductBranch, $newProductID, $newProductQty);
            mysqli_stmt_execute($stmtBranch);

            echo "Product added successfully.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
?>