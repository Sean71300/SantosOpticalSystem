<?php
include 'setup.php'; // Include the setup.php file
require_once 'connect.php'; //Connect to the database
include 'ActivityTracker.php';
include 'loginChecker.php';

function getEmployeeID() {
    $link = connect();
    $sql = "SELECT EmployeeID FROM employee WHERE LoginName = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['username']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        return $row['EmployeeID'];
    }
    mysqli_stmt_close($stmt);
    mysqli_close($link);
}

function getBranches() { // For inventory show
    $link = connect();
    $sql = "SELECT BranchName from branchmaster";
    $result = mysqli_query($link, $sql);
    echo "<option class='form-select-sm' value='' selected>View All Branches</option>";
    while($row = mysqli_fetch_array($result)) {
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

function getEmployeeName() 
    { // Function to get employee names from the database
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

function getInventory($sort = 'ProductID', $order = 'ASC') 
    {
        $link = connect();      
        $branchName = $_SESSION['current_branch'] ?? '';
        
        // Validate sort column and order
        $validSortColumns = ['ProductID', 'CategoryType', 'ShapeDescription', 'BrandName', 'Model', 'TotalCount', 'LastUpdated', 'Count', 'Upd_dt'];
        $sort = in_array($sort, $validSortColumns) ? $sort : 'ProductID';
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        
        // If no branch selected, show all products with branch distribution
        if (empty($branchName)) {
            $sql = "SELECT 
                        pm.ProductID, 
                        pm.CategoryType, 
                        sm.Description AS ShapeDescription,
                        bm.BrandName,
                        pm.Model, 
                        pm.Remarks, 
                        pm.ProductImage,
                        GROUP_CONCAT(CONCAT(b.BranchName, ': ', pbm.Count) SEPARATOR '<br>') AS BranchDistribution,
                        SUM(pbm.Count) AS TotalCount,
                        pm.Upd_by,
                        MAX(pbm.Upd_dt) AS LastUpdated
                    FROM productmstr pm
                    JOIN shapemaster sm ON pm.ShapeID = sm.ShapeID
                    JOIN brandmaster bm ON pm.BrandID = bm.BrandID
                    JOIN productbranchmaster pbm ON pm.ProductID = pbm.ProductID
                    JOIN branchmaster b ON pbm.BranchCode = b.BranchCode
                    GROUP BY pm.ProductID, pm.CategoryType, sm.Description, bm.BrandName, 
                                pm.Model, pm.Remarks, pm.ProductImage, pm.Upd_by";
            
            // Add proper table prefixes for sorting
            switch($sort) {
                case 'ProductID': $sql .= " ORDER BY pm.ProductID"; break;
                case 'CategoryType': $sql .= " ORDER BY pm.CategoryType"; break;
                case 'ShapeDescription': $sql .= " ORDER BY sm.Description"; break;
                case 'BrandName': $sql .= " ORDER BY bm.BrandName"; break;
                case 'Model': $sql .= " ORDER BY pm.Model"; break;
                case 'TotalCount': $sql .= " ORDER BY TotalCount"; break;
                case 'LastUpdated': $sql .= " ORDER BY LastUpdated"; break;
                case 'Upd_dt': $sql .= " ORDER BY pbm.Upd_dt"; break;
                default: $sql .= " ORDER BY pm.ProductID";
            }
            
            $sql .= " $order";
            
            $result = mysqli_query($link, $sql);
            
            if (!$result) {
                die("Query failed: " . mysqli_error($link));
            }
            
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>".htmlspecialchars($row['ProductID'])."</td>
                        <td>".htmlspecialchars($row['CategoryType'])."</td>
                        <td>".htmlspecialchars($row['ShapeDescription'])."</td>
                        <td>".htmlspecialchars($row['BrandName'])."</td>
                        <td>".htmlspecialchars($row['Model'])."</td>
                        <td>".htmlspecialchars($row['Remarks'])."</td>
                        <td><img src='".htmlspecialchars($row['ProductImage'])."' class='product-img'></td>
                        <td>".htmlspecialchars($row['TotalCount'])."</td>                        
                    </tr>";
            }
    
            mysqli_free_result($result);
            mysqli_close($link);
            return;
        }
    
        // Branch-specific display
        $sql = "SELECT bm.BranchCode, pbm.*, pm.*, sm.Description AS ShapeDescription, b.BrandName
                FROM branchmaster bm
                JOIN productbranchmaster pbm ON bm.BranchCode = pbm.BranchCode
                JOIN productmstr pm ON pbm.productID = pm.productID
                JOIN shapemaster sm ON pm.ShapeID = sm.ShapeID
                JOIN brandmaster b ON pm.BrandID = b.BrandID
                WHERE bm.BranchName = ?";
        
        // Add sorting
        switch($sort) {
            case 'ProductID': $sql .= " ORDER BY pm.ProductID"; break;
            case 'CategoryType': $sql .= " ORDER BY pm.CategoryType"; break;
            case 'ShapeDescription': $sql .= " ORDER BY sm.Description"; break;
            case 'BrandName': $sql .= " ORDER BY b.BrandName"; break;
            case 'Model': $sql .= " ORDER BY pm.Model"; break;
            case 'Count': $sql .= " ORDER BY pbm.Count"; break;
            case 'Upd_dt': $sql .= " ORDER BY pbm.Upd_dt"; break;
            default: $sql .= " ORDER BY pm.ProductID";
        }
        
        $sql .= " $order";
        
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $branchName);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td>".htmlspecialchars($row['ProductID'])."</td>
                    <td>".htmlspecialchars($row['CategoryType'])."</td>
                    <td>".htmlspecialchars($row['ShapeDescription'])."</td>
                    <td>".htmlspecialchars($row['BrandName'])."</td>
                    <td>".htmlspecialchars($row['Model'])."</td>
                    <td>".htmlspecialchars($row['Remarks'])."</td>
                    <td><img src='".htmlspecialchars($row['ProductImage'])."' class='product-img'></td>
                    <td>".htmlspecialchars($row['Count'])."</td>

                    <td>
                        <form method='post'>
                            <input type='hidden' name='chooseBranch' value='" . htmlspecialchars($branchName) . "' />
                            <input type='hidden' name='productBranchID' value='" . htmlspecialchars($row['ProductBranchID']) . "' />
                            <input type='hidden' name='productID' value='" . htmlspecialchars($row['ProductID']) . "' />
                            <input type='hidden' name='categoryType' value='" . htmlspecialchars($row['CategoryType']) . "' />
                            <input type='hidden' name='shape' value='" . htmlspecialchars($row['ShapeDescription']) . "' />
                            <input type='hidden' name='brandID' value='" . htmlspecialchars($row['BrandID']) . "' />
                            <input type='hidden' name='model' value='" . htmlspecialchars($row['Model']) . "' />
                            <input type='hidden' name='remarks' value='" . htmlspecialchars($row['Remarks']) . "' />
                            <input type='hidden' name='count' value='" . htmlspecialchars($row['Count']) . "' />
                            <input type='hidden' name='productImg' value='" . htmlspecialchars($row['ProductImage']) . "' />
                            <button type='submit' class='btn btn-success' name='editProductBtn' value='editProductBtn' style='font-size:12px'><i class='fa-solid fa-pen'></i></button>
                            </button>
                        </form>
                    </td>
                    <td>
                        <form method='post'>
                            <input type='hidden' name='chooseBranch' value='" . htmlspecialchars($branchName) . "' />
                            <input type='hidden' name='productBranchID' value='" . htmlspecialchars($row['ProductBranchID']) . "' />
                            <input type='hidden' name='productID' value='" . htmlspecialchars($row['ProductID']) . "' />
                            <button type='submit' class='btn btn-danger' name='deleteProductBtn' value='deleteProductBtn' style='font-size:12px'><i class='fa-solid fa-trash'></i></button>
                            </button>
                        </form>
                    </td>
                </tr>";
        }
    
        mysqli_stmt_close($stmt);
        mysqli_close($link);
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
    if ($newProductQty > 0) {
        $avail_FL = 'Available';
    } else {
        $avail_FL = 'Not Available';
    }
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
        if ($uploadOk) {
            $sourceImage = null;
            switch ($imageFileType) {
                case 'jpg':
                case 'jpeg':
                    $sourceImage = imagecreatefromjpeg($newProductImg["tmp_name"]);
                    break;
                case 'png':
                    $sourceImage = imagecreatefrompng($newProductImg["tmp_name"]);
                    break;
                case 'gif':
                    $sourceImage = imagecreatefromgif($newProductImg["tmp_name"]);
                    break;
                default:
                    echo "Unsupported image format.";
                    $uploadOk = 0;
            }

            if ($sourceImage) {
                $resizedImage = imagecreatetruecolor(600, 600);
                $originalWidth = $check[0];
                $originalHeight = $check[1];
                imagecopyresampled(
                    $resizedImage,
                    $sourceImage,
                    0, 0, 0, 0,
                    600, 600,
                    $originalWidth,
                    $originalHeight
                );

                switch ($imageFileType) {
                    case 'jpg':
                    case 'jpeg':
                        imagejpeg($resizedImage, $targetFile);
                        break;
                    case 'png':
                        imagepng($resizedImage, $targetFile);
                        break;
                    case 'gif':
                        imagegif($resizedImage, $targetFile);
                        break;
                }

                // Free memory
                imagedestroy($sourceImage);
                imagedestroy($resizedImage);
            }
        }

        if ($uploadOk && file_exists($targetFile)) {
            // Insert product details into the product master database
            $sql = "INSERT INTO productmstr (ProductID, CategoryType, ShapeID, BrandID, Model, Remarks, ProductImage, Avail_FL, Upd_by, Upd_dt) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssssss", $newProductID, $newProductCategory, $newProductShape, $newProductBrand, $newProductName, $newProductRemarks, $targetFile, $avail_FL, $upd_by, $upd_dt);            
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            // Insert product-branch mapping into the product branch master database
            $sql = "INSERT INTO productbranchmaster (ProductBranchID, ProductID, BranchCode, Count, Avail_FL, Upd_by, Upd_dt)
                    VALUES (?, ?, ?, ?, ?, ?, ?)"; 
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "sssssss", $newProductBranchID, $newProductID, $newProductBranchCode, $newProductQty, $avail_FL, $upd_by, $upd_dt);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            //Insert Logs into logs database

            echo 
                '<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="successModalLabel">Success</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                The product has been added to the database successfully!
                            </div>
                            <div class="modal-footer">
                                <a href="admin-inventory.php" class="btn btn-secondary" data-bs-dismiss="modal">Close</a>
                            </div>
                        </div>
                    </div>
                </div>';

            $logSQL = "INSERT INTO logs (LogsID, EmployeeID, ProductBranchID, ActivityCode, Count, Upd_dt) VALUES (?, ?, ?, ?, ?, ?)";
            $logStmt = mysqli_prepare($link, $logSQL);
            $logID = generate_LogsID();
            $logEmployeeID = getEmployeeID();
            $logActivityCode = '2'; // Activity code for adding a product
            $logCount = $newProductQty; // Assuming count is 1 for adding a product
            $logUpdDT = date('Y-m-d H:i:s');
            mysqli_stmt_bind_param($logStmt, "ssssss", $logID, $logEmployeeID, $newProductBranchID, $logActivityCode, $logCount, $logUpdDT);
            mysqli_stmt_execute($logStmt);
            mysqli_stmt_close($logStmt);
        } else {
            echo 
            '<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="successModalLabel">Error</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                The product has not been added to the database, please try again or contact tech support.
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
            </div>';
        }
    }
}

function editShape($currentShapeDescription = '') { // Function to edit shape
    $link = connect();
    $sql = "SELECT * FROM shapemaster WHERE Description = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $currentShapeDescription);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt); 
    while ($row = mysqli_fetch_array($result)) {
        $selected = ($row['ShapeID'] === $currentShapeDescription) ? 'selected' : '';
        echo "<option class='form-select-sm' value='" . htmlspecialchars($row['ShapeID']) . "' $selected>" . htmlspecialchars($row['Description']) . "</option>";
    }
    mysqli_close($link);
}

function editBrand($currentBrand = '') {
    $link = connect();
    $sql = "SELECT * FROM brandmaster WHERE BrandID = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $currentBrand);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $selected = ($row['BrandID'] === $currentBrand) ? 'selected' : '';
        echo "<option class='form-select-sm' value='" . htmlspecialchars($row['BrandID']) . "' $selected>" . htmlspecialchars($row['BrandName']) . "</option>";
    }
    mysqli_stmt_close($stmt);
    mysqli_close($link);
}

function editCategory($currentCategory = '') {
    $link = connect();
    $sql = "SELECT * FROM categorytype";
    $result = mysqli_query($link, $sql);
    
    while ($row = mysqli_fetch_array($result)) {
        $selected = ($row['CategoryType'] === $currentCategory) ? 'selected' : '';
        echo "<option class='form-select-sm' value='" . htmlspecialchars($row['CategoryType']) . "' $selected>" . 
             htmlspecialchars($row['CategoryType']) . "</option>";
    }
    
    mysqli_close($link);
}

function getBranchCode($currentBranch = '') {
    $link = connect();
    $sql = "SELECT BranchCode FROM branchmaster WHERE BranchName = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $currentBranch);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        return $row['BranchCode'];
    }
    mysqli_stmt_close($stmt);
    mysqli_close($link);
}

function editProduct(){ //Edit function to edit an existing product in the database 
    $productID = $_POST['productID'] ?? '';
    $categoryType = $_POST['categoryType'] ?? '';
    $ShapeDescription = $_POST['shape'] ?? '';
    $brandID = $_POST['brandID'] ?? '';
    $model = $_POST['model'] ?? '';
    $remarks = $_POST['remarks'] ?? '';
    $count = $_POST['count'] ?? '';
    $productImg = $_POST['productImg'] ?? '';
    $branchName = $_POST['chooseBranch'] ?? '';
    $productBranchID = $_POST['productBranchID'] ?? '';

    echo '<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content bg-secondary-subtle">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>      
                    <div class="modal-body" style="margin-top: -1.5rem;">
                    <hr>
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="productID" value="' . htmlspecialchars($productID) . '" />
                            <input type="hidden" name="chooseBranch" value="' . htmlspecialchars($branchName) . '" />
                            <input type="hidden" name="productBranchID" value="' . htmlspecialchars($productBranchID) . '" />
                            <input type="hidden" name="productImg" value="' . htmlspecialchars($productImg) . '" />
                            <div class="mb-3">
                                <label for="categoryType" class="form-label">Category Type</label>
                                <select class="form-select" id="categoryType" name="categoryType" required>';
                                    editCategory($categoryType);
                                    echo '
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="ShapeDescription" class="form-label">Shape</label>
                                <select class="form-select" id="ShapeDescription" name="ShapeDescription" required>';
                                    editShape($ShapeDescription);
                                    echo '
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="col-auto" style="width: 5rem"> 
                                    <label for="brandID" class="col-form-label">Brand:</label>
                                </div>
                                <select name="brandID" id="brandID" class="form-select">';
                                    editBrand($brandID);
                                    echo '
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="model" class="form-label">Model</label>
                                <input type="text" class="form-control" id="model" name="model" value="' . htmlspecialchars($model) . '" required>
                            </div>
                            <div class="mb-3">
                                <label for="remarks" class="form-label">Remarks</label>
                                <input type="text" class="form-control" id="remarks" name="remarks" value="' . htmlspecialchars($remarks) . '" required>
                            </div>
                            <div class="mb-3">
                                <label for="count" class="form-label">Count</label>
                                <input type="number" class="form-control" id="count" name="count" value="' . htmlspecialchars($count) . '" min="0" required>
                            </div>
                            <div class="mb-3">
                                <label for="productImg" class="form-label">Product Image</label>
                                <div class="container d-flex align-items-center justify-content-center">
                                    <img src="' . htmlspecialchars($productImg) . '" alt="Product Image" class="img-thumbnail"/>
                                </div>
                                <input type="file" class="form-control mt-3" id="newProductImg" name="newProductImg" accept="image/*">
                            </div>

                            <hr>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success w-25" name="saveProductBtn">Save</button>
                                <button type="button" class="btn btn-danger w-25" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>';
}

function confirmDeleteProduct() {
    echo 
        '<div class="modal fade" id="confirmDeleteProductModal" tabindex="-1" aria-labelledby="confirmDeleteProductModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmDeleteProductModalLabel">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post">
                        <input type="hidden" name="productID" value="' . htmlspecialchars($_POST['productID']) . '" />
                        <input type="hidden" name="productBranchID" value="' . htmlspecialchars($_POST['productBranchID']) . '" />
                        <input type="hidden" name="chooseBranch" value="' . htmlspecialchars($_POST['chooseBranch']) . '" />                      
                        <div class="modal-body">
                            Are you sure you want to delete this product?
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger" id="confirmDeleteBtn" name="confirmDeleteBtn">Confirm</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>                        
                        </div>
                    </form>
                </div>
            </div>
        </div>';
}

function confirmEditProduct() {
    $link = connect();
    global $employeeName;
    getEmployeeName();

    $productID = $_POST['productID'] ?? '';
    $categoryType = $_POST['categoryType'] ?? '';
    $shape = $_POST['ShapeDescription'] ?? '';
    $brandID = $_POST['brandID'] ?? '';
    $model = $_POST['model'] ?? '';
    $remarks = $_POST['remarks'] ?? '';
    $count = $_POST['count'] ?? '';
    $avail_FL = ($count > 0) ? 'Available' : 'Not Available';
    $NewProductImg = $_FILES['newProductImg'];
    $productImg = $_POST['productImg'] ?? '';
    $branchName = $_POST['chooseBranch'] ?? '';
    $branchCode = getBranchCode($branchName);
    $productBranchID = $_POST['productBranchID'] ?? '';
    $date = new DateTime();
    $upd_dt = $date->format('Y-m-d H:i:s');

    $targetDir = "uploads/";
    $targetFile = $productImg; // Default to existing image
    $uploadOk = 1;

    // Check if a new file is uploaded
    if ($NewProductImg && isset($NewProductImg["tmp_name"]) && is_uploaded_file($NewProductImg["tmp_name"])) {
        $targetFile = $targetDir . basename($NewProductImg["name"]);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Validate the uploaded file
        $check = getimagesize($NewProductImg["tmp_name"]);
        if ($check === false) {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size (limit to 2MB)
        if ($NewProductImg["size"] > 2000000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Resize and upload the file if valid
        if ($uploadOk) {
            $sourceImage = null;
            switch ($imageFileType) {
                case 'jpg':
                case 'jpeg':
                    $sourceImage = imagecreatefromjpeg($NewProductImg["tmp_name"]);
                    break;
                case 'png':
                    $sourceImage = imagecreatefrompng($NewProductImg["tmp_name"]);
                    break;
                case 'gif':
                    $sourceImage = imagecreatefromgif($NewProductImg["tmp_name"]);
                    break;
                default:
                    echo "Unsupported image format.";
                    $uploadOk = 0;
            }

            if ($sourceImage) {
                $resizedImage = imagecreatetruecolor(600, 600);
                $originalWidth = $check[0];
                $originalHeight = $check[1];
                imagecopyresampled(
                    $resizedImage,
                    $sourceImage,
                    0, 0, 0, 0,
                    600, 600,
                    $originalWidth,
                    $originalHeight
                );

                switch ($imageFileType) {
                    case 'jpg':
                    case 'jpeg':
                        imagejpeg($resizedImage, $targetFile);
                        break;
                    case 'png':
                        imagepng($resizedImage, $targetFile);
                        break;
                    case 'gif':
                        imagegif($resizedImage, $targetFile);
                        break;
                }

                // Free memory
                imagedestroy($sourceImage);
                imagedestroy($resizedImage);
            }
        }
    }

    // Proceed with database updates if the file upload is successful
    if ($uploadOk) {
        $success = true;

        // Update productmstr table
        $sql1 = "UPDATE productmstr SET CategoryType = ?, ShapeID = ?, BrandID = ?, Model = ?, Remarks = ?, ProductImage = ?, Upd_by = ?, Upd_dt = ? WHERE ProductID = ?";
        $stmt1 = mysqli_prepare($link, $sql1);
        mysqli_stmt_bind_param($stmt1, "sssssssss", $categoryType, $shape, $brandID, $model, $remarks, $targetFile, $employeeName, $upd_dt, $productID);
        if (!mysqli_stmt_execute($stmt1)) {
            $success = false;
        }
        mysqli_stmt_close($stmt1);

        // Update productbranchmaster table
        $sql2 = "UPDATE productbranchmaster SET ProductBranchID = ?, BranchCode = ?, Count = ?, Avail_FL = ?, Upd_by = ?, Upd_dt = ? WHERE ProductID = ?";
        $stmt2 = mysqli_prepare($link, $sql2);
        mysqli_stmt_bind_param($stmt2, "sssssss", $productBranchID, $branchCode, $count, $avail_FL, $employeeName, $upd_dt, $productID);
        if (!mysqli_stmt_execute($stmt2)) {
            $success = false;
        }
        mysqli_stmt_close($stmt2);

        // Show success or error modal
        if ($success) {
            echo '<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editProductModalLabel">Success</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                The product has been updated successfully!
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>';
            $logSQL = "INSERT INTO logs (LogsID, EmployeeID, ProductBranchID, ActivityCode, Count, Upd_dt) VALUES (?, ?, ?, ?, ?, ?)";
            $logStmt = mysqli_prepare($link, $logSQL);
            $logID = generate_LogsID();
            $logEmployeeID = getEmployeeID();
            $logActivityCode = '4';
            $logCount = $count;
            $logUpdDT = date('Y-m-d H:i:s');
            mysqli_stmt_bind_param($logStmt, "ssssss", $logID, $logEmployeeID, $productBranchID, $logActivityCode, $logCount, $logUpdDT);
            mysqli_stmt_execute($logStmt);
            mysqli_stmt_close($logStmt);
        } else {
            echo '<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editProductModalLabel">Error</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                An error occurred while updating the product. Please try again.
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>';
        }
    }

    mysqli_close($link);
}

function deleteProduct() { //Delete function to delete a product from the database
    $link = connect();
    $productID = $_POST['productID'] ?? '';
    $logBranchID = $_POST['productBranchID'] ?? '';

    $logID = generate_LogsID();
    $logEmployeeID = getEmployeeID();
    $logActivityCode = '3';
    $logCount = 1;
    $logUpdDT = date('Y-m-d H:i:s');
    $logSQL = "INSERT INTO logs (LogsID, EmployeeID, ProductBranchID, ActivityCode, Count, Upd_dt) VALUES (?, ?, ?, ?, ?, ?)";
    $logStmt = mysqli_prepare($link, $logSQL);
    mysqli_stmt_bind_param($logStmt, "ssssss", $logID, $logEmployeeID, $logBranchID, $logActivityCode, $logCount, $logUpdDT);
    $logSuccess = mysqli_stmt_execute($logStmt);
    mysqli_stmt_close($logStmt);

    if ($logSuccess) {
        $sql = "DELETE FROM productbranchmaster WHERE ProductID = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $productID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $sql = "DELETE FROM productmstr WHERE ProductID = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $productID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        echo '<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteProductModalLabel">Success</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            The product has been deleted from the database successfully!
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>';
    } else {
        echo '<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteProductModalLabel">Error</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            An error occurred while logging the deletion. Please try again.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>';
    }
    mysqli_close($link);
}
?>