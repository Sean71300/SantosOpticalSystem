<?php
include 'setup.php'; // Include the setup.php file

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

function displayBranchesWithCheckboxes() {
    $link = connect();
    $sql = "SELECT BranchCode, BranchName FROM BranchMaster";
    $result = mysqli_query($link, $sql);
    
    echo '<div class="branch-selection-container">';
    while($row = mysqli_fetch_array($result)) {
        echo '<div class="branch-item row align-items-center mb-3">';
        echo '  <div class="col-6" style="height: 2rem:">';
        echo '    <div class="form-check">';
        echo '      <input class="form-check-input branch-checkbox" type="checkbox" ';
        echo '             id="branch_'.$row['BranchCode'].'" value="'.$row['BranchCode'].'">';
        echo '      <label class="form-check-label" for="branch_'.$row['BranchCode'].'">';
        echo          htmlspecialchars($row['BranchName']);
        echo '      </label>';
        echo '    </div>';
        echo '  </div>';
        echo '  <div class="col-6" style="height: 2rem:">';
        echo '    <input type="number" name="qtys['.$row['BranchCode'].']" ';
        echo '           class="form-control quantity-input" placeholder="Qty" ';
        echo '           min="0" disabled style="display: none;">';
        echo '  </div>';
        echo '</div>';
    }
    echo '</div>';
    mysqli_close($link);
}

function getBranches() { // For inventory show
    $link = connect();
    $sql = "SELECT BranchName from BranchMaster";
    $result = mysqli_query($link, $sql);
    echo "<option class='form-select-sm' value='' selected>View All Branches</option>";
    while($row = mysqli_fetch_array($result)) {
        echo "<option class='form-select-sm' value='".$row['BranchName']."'>".$row['BranchName']."</option>";
    }
}

function getBranch() { // Function to get branches from the database
    $link = connect();
    $sql = "SELECT * from BranchMaster";
    $result = mysqli_query($link, $sql);
    while($row = mysqli_fetch_array($result)){
        echo "<option class='form-select-sm' value='".$row['BranchCode']."'>".$row['BranchName']."</option>";
    }
}

function getShapes() { // Function to get shapes from the database
    $link = connect();
    $sql = "SELECT * from shapeMaster";
    $result = mysqli_query($link, $sql);
    while($row = mysqli_fetch_array($result)){
        echo "<option class='form-select-sm' value='".$row['ShapeID']."'>".$row['Description']."</option>";
    }
}

function getBrands() {
    $link = connect();
    $sql = "SELECT * from brandMaster";
    $result = mysqli_query($link, $sql);
    while($row = mysqli_fetch_array($result)){
        echo "<option class='form-select-sm' value='".$row['BrandID']."'>".$row['BrandName']."</option>";
    }
}

function getCategory() {
    $link = connect();
    $sql = "SELECT * from categoryType";
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

function getInventory($sort = 'ProductID', $order = 'ASC') 
{
    $link = connect();      
    if (!$link) {
        die("Database connection failed: " . mysqli_connect_error());
    }
    
    $branchName = $_SESSION['current_branch'] ?? '';
    
    $validSortColumns = ['ProductID', 'CategoryType', 'ShapeDescription', 'BrandName', 'Model', 'TotalCount', 'LastUpdated', 'Stocks', 'Upd_dt'];
    $sort = in_array($sort, $validSortColumns) ? $sort : 'ProductID';
    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
    
    if (empty($branchName)) {
        $sql = "SELECT 
                    pm.ProductID, 
                    pm.CategoryType, 
                    sm.Description AS ShapeDescription,
                    bm.BrandName,
                    pm.Model, 
                    pm.Material,
                    pm.Price, 
                    pm.ProductImage,
                    GROUP_CONCAT(CONCAT(b.BranchName, ': ', pbm.Stocks) SEPARATOR '<br>') AS BranchDistribution,
                    SUM(pbm.Stocks) AS TotalCount,
                    pm.Upd_by,
                    MAX(pbm.Upd_dt) AS LastUpdated
                FROM productMstr pm
                JOIN shapeMaster sm ON pm.ShapeID = sm.ShapeID
                JOIN brandMaster bm ON pm.BrandID = bm.BrandID
                JOIN ProductBranchMaster pbm ON pm.ProductID = pbm.ProductID
                JOIN BranchMaster b ON pbm.BranchCode = b.BranchCode
                WHERE pbm.Avail_FL = 'Available' AND pm.Avail_FL = 'Available'
                GROUP BY pm.ProductID, pm.CategoryType, sm.Description, bm.BrandName, 
                            pm.Model, pm.Material, pm.Price, pm.ProductImage, pm.Upd_by";
        
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
                    <td class='align-middle'>".htmlspecialchars($row['ProductID'])."</td>
                    <td class='align-middle'>".htmlspecialchars($row['CategoryType'])."</td>
                    <td class='align-middle'>".htmlspecialchars($row['ShapeDescription'])."</td>
                    <td class='align-middle'>".htmlspecialchars($row['BrandName'])."</td>
                    <td class='align-middle'>".htmlspecialchars($row['Model'])."</td>
                    <td class='align-middle'>".htmlspecialchars($row['Material'])."</td>
                    <td class='align-middle'><img src='".htmlspecialchars($row['ProductImage'])."' class='product-img'></td>
                    <td class='align-middle'>".htmlspecialchars($row['TotalCount'])."</td>                        
                </tr>";
        }

        mysqli_free_result($result);
    } else {
        $sql = "SELECT b.BranchCode, pbm.*, pm.*, sm.Description AS ShapeDescription, bm.BrandName
                FROM BranchMaster b
                JOIN ProductBranchMaster pbm ON b.BranchCode = pbm.BranchCode
                JOIN productMstr pm ON pbm.ProductID = pm.ProductID
                JOIN shapeMaster sm ON pm.ShapeID = sm.ShapeID
                JOIN brandMaster bm ON pm.BrandID = bm.BrandID
                WHERE b.BranchName = ? AND pm.Avail_FL = 'Available' AND pbm.Avail_FL = 'Available'";
        
        switch($sort) {
            case 'ProductID': $sql .= " ORDER BY pm.ProductID"; break;
            case 'CategoryType': $sql .= " ORDER BY pm.CategoryType"; break;
            case 'ShapeDescription': $sql .= " ORDER BY sm.Description"; break;
            case 'BrandName': $sql .= " ORDER BY bm.BrandName"; break;
            case 'Model': $sql .= " ORDER BY pm.Model"; break;
            case 'Stocks': $sql .= " ORDER BY pbm.Stocks"; break;
            case 'Upd_dt': $sql .= " ORDER BY pbm.Upd_dt"; break;
            default: $sql .= " ORDER BY pm.ProductID";
        }
        
        $sql .= " $order";
        
        $stmt = mysqli_prepare($link, $sql);
        if (!$stmt) {
            die("Prepare failed: " . mysqli_error($link));
        }
        
        mysqli_stmt_bind_param($stmt, "s", $branchName);
        if (!mysqli_stmt_execute($stmt)) {
            die("Execute failed: " . mysqli_stmt_error($stmt));
        }
        
        $result = mysqli_stmt_get_result($stmt);
        if (!$result) {
            die("Get result failed: " . mysqli_stmt_error($stmt));
        }

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td class='align-middle'>".htmlspecialchars($row['ProductID'])."</td>
                    <td class='align-middle'>".htmlspecialchars($row['CategoryType'])."</td>
                    <td class='align-middle'>".htmlspecialchars($row['ShapeDescription'])."</td>
                    <td class='align-middle'>".htmlspecialchars($row['BrandName'])."</td>
                    <td class='align-middle'>".htmlspecialchars($row['Model'])."</td>
                    <td class='align-middle'>".htmlspecialchars($row['Material'])."</td>
                    <td class='align-middle'>".htmlspecialchars($row['Price'])."</td>
                    <td class='align-middle'><img src='".htmlspecialchars($row['ProductImage'])."' class='product-img'></td>
                    <td class='align-middle'>".htmlspecialchars($row['Stocks'])."</td>
                    <td class='align-middle'>
                        <form method='post'>
                            <input type='hidden' name='chooseBranch' value='" . htmlspecialchars($branchName) . "' />
                            <input type='hidden' name='productBranchID' value='" . htmlspecialchars($row['ProductBranchID']) . "' />
                            <input type='hidden' name='productID' value='" . htmlspecialchars($row['ProductID']) . "' />
                            <input type='hidden' name='categoryType' value='" . htmlspecialchars($row['CategoryType']) . "' />
                            <input type='hidden' name='shape' value='" . htmlspecialchars($row['ShapeDescription']) . "' />
                            <input type='hidden' name='brandID' value='" . htmlspecialchars($row['BrandID']) . "' />
                            <input type='hidden' name='model' value='" . htmlspecialchars($row['Model']) . "' />
                            <input type='hidden' name='material' value='" . htmlspecialchars($row['Material']) . "' />
                            <input type='hidden' name='price' value='" . htmlspecialchars($row['Price']) . "' />
                            <input type='hidden' name='count' value='" . htmlspecialchars($row['Stocks']) . "' />
                            <input type='hidden' name='productImg' value='" . htmlspecialchars($row['ProductImage']) . "' />
                            <button type='submit' class='btn btn-success mt-2' name='editProductBtn' style='font-size:18px'><i class='fa-solid fa-pen'></i></button>
                        </form>
                    </td>
                    <td class='align-middle'>
                        <form method='post'>
                            <input type='hidden' name='chooseBranch' value='" . htmlspecialchars($branchName) . "' />
                            <input type='hidden' name='productBranchID' value='" . htmlspecialchars($row['ProductBranchID']) . "' />
                            <input type='hidden' name='productID' value='" . htmlspecialchars($row['ProductID']) . "' />
                            <button type='submit' class='btn btn-danger mt-2' name='deleteProductBtn' value='deleteProductBtn' style='font-size:18px'><i class='fa-solid fa-trash'></i></button>
                        </form>
                    </td>
                </tr>";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($link);
}

function addProduct(){ //Add function to add a new product to the database
    $link = connect();
    $newProductID = generate_ProductMstrID();
    $newProductName = $_POST['productName'];
    $newProductBrand = $_POST['productBrand'];
    $newProductShape = $_POST['productShape'];
    $newProductCategory = $_POST['productCategory'];
    $newProductMaterial = $_POST['productMaterial'];
    $newProductPrice = "₱". $_POST['productPrice'];
    $newProductImg = $_FILES['productImg'];
    $upd_by = $_SESSION['full_name'];
    $date = new DateTime();
    $upd_dt = $date->format('Y-m-d H:i:s');
    
    if (isset($_POST['addProduct'])) {
        // Validate and upload the product image
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($newProductImg["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if image file is a valid image
        $check = getimagesize($newProductImg["tmp_name"]);
        if (empty($check)) {
            echo "Error: Unable to process the image. Please upload a valid image file.";
            $uploadOk = 0;
        }

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
            $anyAvailable = false;
            if (!empty($_POST['qtys'])) {
                foreach ($_POST['qtys'] as $qty) {
                    if ($qty > 0) {
                        $anyAvailable = true;
                        break;
                    }
                }
            }
            $avail_FL = $anyAvailable ? 'Available' : 'Not Available';

            // Insert product details into the product master database
            $sql = "INSERT INTO productMstr (ProductID, CategoryType, ShapeID, BrandID, Model, Material, Price, ProductImage, Avail_FL, Upd_by, Upd_dt) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "sssssssssss", $newProductID, $newProductCategory, $newProductShape, $newProductBrand, $newProductName, $newProductMaterial, $newProductPrice, $targetFile, $avail_FL, $upd_by, $upd_dt);          
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            // Insert product-branch mapping into the product branch master database
            if (!empty($_POST['qtys'])) {
                foreach ($_POST['qtys'] as $branchCode => $qty) {
                    $branchAvailFL = ($qty > 0) ? 'Available' : 'Not Available';
                    $newProductBranchID = generate_ProductBrnchMstrID();
                    $sql = "INSERT INTO ProductBranchMaster (ProductBranchID, ProductID, BranchCode, Stocks, Avail_FL, Upd_by, Upd_dt)
                            VALUES (?, ?, ?, ?, ?, ?, ?)"; 
                            $stmt = mysqli_prepare($link, $sql);
                            mysqli_stmt_bind_param($stmt, "sssisds", $newProductBranchID, $newProductID, $branchCode, 
                            $qty, $branchAvailFL, $upd_by, $upd_dt);
                            mysqli_stmt_execute($stmt);
                            mysqli_stmt_close($stmt);
                }
            }

            //Insert Logs into logs database
            $code = '3';
            GenerateLogs($newProductID,$newProductName,$code);
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
    $sql = "SELECT * FROM shapeMaster";
    $result = mysqli_query($link, $sql);

    while ($row = mysqli_fetch_array($result)) {
        $selected = ($row['Description'] === $currentShapeDescription) ? 'selected' : '';
        echo "<option class='form-select-sm' value='" . htmlspecialchars($row['ShapeID']) . "' $selected>" . 
             htmlspecialchars($row['Description']) . "</option>";
    }

    mysqli_close($link);
}

function editBrand($currentBrand = '') {
    $link = connect();
    $sql = "SELECT * FROM brandMaster";
    $result = mysqli_query($link, $sql);

    while ($row = mysqli_fetch_array($result)) {
        $selected = ($row['BrandID'] === $currentBrand) ? 'selected' : '';
        echo "<option class='form-select-sm' value='" . htmlspecialchars($row['BrandID']) . "' $selected>" . 
             htmlspecialchars($row['BrandName']) . "</option>";
    }

    mysqli_close($link);
}

function editCategory($currentCategory = '') {
    $link = connect();
    $sql = "SELECT * FROM categoryType";
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
    $sql = "SELECT BranchCode FROM BranchMaster WHERE BranchName = ?";
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
    $material = $_POST['material'] ?? '';
    $price = $_POST['price'] ?? '';
    $price = ltrim($price, '₱');
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
                                <label for="material" class="form-label">Material</label>
                                <input type="text" class="form-control" id="material" name="material" value="' . htmlspecialchars($material) . '" required>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="text" class="form-control" id="price" name="price" value="' . htmlspecialchars($price) . '" required>
                            </div>
                            <div class="mb-3">
                                <label for="count" class="form-label">Stocks</label>
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
    $material = $_POST['material'] ?? '';
    $price = '₱' . $_POST['price'] ?? '';
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
        $sql1 = "UPDATE productMstr SET CategoryType = ?, ShapeID = ?, BrandID = ?, Model = ?, Material = ?, Price = ?, ProductImage = ?, Upd_by = ?, Upd_dt = ? WHERE ProductID = ?";
        $stmt1 = mysqli_prepare($link, $sql1);
        mysqli_stmt_bind_param($stmt1, "ssssssssss", $categoryType, $shape, $brandID, $model, $material, $price, $targetFile, $employeeName, $upd_dt, $productID);
        if (!mysqli_stmt_execute($stmt1)) {
            $success = false;
        }
        mysqli_stmt_close($stmt1);
        
        // Update productbranchmaster table
        $sql2 = "UPDATE ProductBranchMaster SET ProductBranchID = ?, BranchCode = ?, Stocks = ?, Avail_FL = ?, Upd_by = ?, Upd_dt = ? WHERE ProductID = ?";
        $stmt2 = mysqli_prepare($link, $sql2);
        mysqli_stmt_bind_param($stmt2, "sssssss", $productBranchID, $branchCode, $count, $avail_FL, $employeeName, $upd_dt, $productID);
        if (!mysqli_stmt_execute($stmt2)) {
            $success = false;
        }
        mysqli_stmt_close($stmt2);
        $code = '4';
        GenerateLogs($productID,$model,$code);
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
                                <a href="admin-inventory.php?branch='.urlencode($_POST['chooseBranch']).'&sort='.urlencode($_GET['sort'] ?? 'ProductID').'&order='.urlencode($_GET['order'] ?? 'ASC').'" class="btn btn-secondary">Close</a>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const modal = new bootstrap.Modal(document.getElementById("editProductModal"));
                        modal.show();
                        
                        setTimeout(function() {
                            const urlParams = new URLSearchParams(window.location.search);
                            window.location.href = window.location.pathname + "?" + urlParams.toString();
                        }, 2000); // Redirect after 2 seconds
                    });
                </script>';
            // $logSQL = "INSERT INTO logs (LogsID, EmployeeID, ProductBranchID, ActivityCode, Count, Upd_dt) VALUES (?, ?, ?, ?, ?, ?)";
            // $logStmt = mysqli_prepare($link, $logSQL);
            // $logID = generate_LogsID();
            // $logEmployeeID = getEmployeeID();
            // $logActivityCode = '4';
            // $logCount = $count;
            // $logUpdDT = date('Y-m-d H:i:s');
            // mysqli_stmt_bind_param($logStmt, "ssssss", $logID, $logEmployeeID, $productBranchID, $logActivityCode, $logCount, $logUpdDT);
            // mysqli_stmt_execute($logStmt);
            // mysqli_stmt_close($logStmt);
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

function GenerateLogs($productID,$model,$code)
    {
        $conn = connect(); 
        $Logsid = generate_LogsID();        
        $employee_id = $_SESSION["id"];
        $stmt = $conn->prepare("INSERT INTO Logs 
                            (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description, Upd_dt)
                            VALUES
                            (?, ?, ?, 'product', ?, ? , NOW())");
        $stmt->bind_param("sssss", $Logsid, $employee_id,$productID, $code, $model);
        $stmt->execute();
        $stmt->close();
    }
    
    function setStatus($productID){
        $conn = connect(); 
        $sql = "UPDATE productMstr 
            SET Avail_FL = 'Unavailable' WHERE ProductID = $productID";
        $result = $conn->query($sql);
    }

    function Archive($productID){
        $conn = connect(); 
        $Aid = generate_ArchiveID();
        $Eid = $_SESSION["id"];
        
        // Archive the employee
        $sqlProduct = "INSERT INTO archives (ArchiveID, TargetID, EmployeeID, TargetType) VALUES (?, ?, ?, 'product')";
        $stmt = $conn->prepare($sqlProduct);
        $stmt->bind_param("iii", $Aid, $productID, $Eid);
        $stmt->execute();
        $stmt->close();        
    }
function deleteProduct() 
    { //Delete function to delete a product from the database
        $productID = $_POST['productID'] ?? '';

        $conn = connect();
        $stmt = $conn->prepare("SELECT ProductBranchID FROM ProductBranchMaster WHERE productID = ? ");
        $stmt->bind_param("s", $productID );
        $stmt->execute();
        $PBID = $stmt->get_result()->fetch_assoc()['ProductBranchID'];

        $stmt = $conn->prepare("SELECT OrderDtlID FROM orderDetails WHERE ProductBranchID = ? ");
        $stmt->bind_param("s", $PBID);
        $stmt->execute();
        $exists = (bool)$stmt->get_result()->fetch_assoc();

        if ($exists) {
            echo 
            '<div class="modal fade" id="deleteErrorModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Delete Product Error</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p><i class="fas fa-exclamation-triangle me-2"></i> Product could not be deleted because it has an active order related to it.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    var myModal = new bootstrap.Modal(document.getElementById("deleteErrorModal"));
                    myModal.show();
                });
            </script>';
        exit();         
            }
        
        Archive($productID);
        setStatus($productID);
        $link = connect();
        
        $code = '5';
        $sql = "SELECT * FROM productMstr WHERE ProductID = $productID"  ;
        $result = mysqli_query($link, $sql);

        while ($row = mysqli_fetch_array($result)) {
            $model= $row['Model'];
        }
        
        GenerateLogs($productID,$model,$code);
        /*

            Old Delete

            $sql = "DELETE FROM ProductBranchMaster WHERE ProductID = ?";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "s", $productID);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $sql = "DELETE FROM productMstr WHERE ProductID = ?";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "s", $productID);
            mysqli_stmt_execute($stmt);
        */
        if (mysqli_stmt_close($stmt))
            {
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

    function getLowInventoryProducts($threshold = 10) {
        $link = connect();
        $sql = "SELECT pbm.ProductID, pm.Model, bm.BranchName, pbm.Stocks 
                FROM ProductBranchMaster pbm
                JOIN productMstr pm ON pbm.ProductID = pm.ProductID
                JOIN BranchMaster bm ON pbm.BranchCode = bm.BranchCode
                WHERE pbm.Stocks <= ? AND pbm.Avail_FL = 'Available' AND pm.Avail_FL = 'Available'";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "i", $threshold);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $lowInventory = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $lowInventory[] = $row;
        }
        mysqli_stmt_close($stmt);
        mysqli_close($link);
        return $lowInventory;
    }
?>          