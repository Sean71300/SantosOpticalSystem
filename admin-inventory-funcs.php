<?php
include 'setup.php'; // Include the setup.php file
require_once 'connect.php'; //Connect to the database

function getBranches() { // For inventory show
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

function getInventory() { // Show inventory of the selected branch    
    $link = connect();      
    $branchName = $_POST['chooseBranch'] ?? '';
    if (empty($branchName)) {
        echo '<div class="modal fade" id="errorSearchModal" tabindex="-1" aria-labelledby="errorSearchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content bg-danger p-3 text-white">
                    <div class="modal-header">
                        <h5 class="modal-title" id="errorSearchModalLabel">Error <i class="fa-solid fa-exclamation"></i></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Please choose a branch before proceeding.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>';
        return;
    }

    $sql = "SELECT bm.BranchCode, pbm.*, pm.*, sm.Description AS ShapeDescription
            FROM branchmaster bm
            JOIN productbranchmaster pbm ON bm.BranchCode = pbm.BranchCode
            JOIN productmstr pm ON pbm.productID = pm.productID
            JOIN shapemaster sm ON pm.ShapeID = sm.ShapeID
            WHERE bm.BranchName = ?"; //bm is branchmaster, pbm is productbranchmaster, pm is productmstr
    
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $branchName);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // display table
    echo "<table class='table table-bordered table-fixed mt-3' border='1'>
            <thead class = 'text-center'>
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
            </thead>
            
            <tbody class='table-group-divider'>";

    // fetch and display results
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr class='text-center'>
                <td>" . htmlspecialchars($row['ProductID']) . "</td>
                <td>" . htmlspecialchars($row['CategoryType']) . "</td>
                <td>" . htmlspecialchars($row['ShapeDescription']) . "</td>
                <td>" . htmlspecialchars($row['BrandID']) . "</td>
                <td>" . htmlspecialchars($row['Model']) . "</td>
                <td>" . htmlspecialchars($row['Remarks']) . "</td>
                <td><img src='" . htmlspecialchars($row['ProductImage']) . "' alt='Product Image' style='width:100px; height:auto;'/></td>
                <td>" . htmlspecialchars($row['Count']) . "</td>
                <td>" . htmlspecialchars($row['Upd_by']) . "</td>
                <td>" . htmlspecialchars($row['Upd_dt']) . "</td>
                <td>
                    <form method='post'>
                        <input type='hidden' name='chooseBranch' value='" . htmlspecialchars($branchName) . "' />
                        <input type='hidden' name='productID' value='" . htmlspecialchars($row['ProductID']) . "' />
                        <input type='hidden' name='categoryType' value='" . htmlspecialchars($row['CategoryType']) . "' />
                        <input type='hidden' name='shape' value='" . htmlspecialchars($row['ShapeDescription']) . "' />
                        <input type='hidden' name='brandID' value='" . htmlspecialchars($row['BrandID']) . "' />
                        <input type='hidden' name='model' value='" . htmlspecialchars($row['Model']) . "' />
                        <input type='hidden' name='remarks' value='" . htmlspecialchars($row['Remarks']) . "' />
                        <input type='hidden' name='count' value='" . htmlspecialchars($row['Count']) . "' />
                        <button type='submit' class='btn btn-success' name='editProductBtn' value='editProductBtn' style='font-size:12px'><i class='fa-solid fa-pen'></i></button>
                    </form>
                </td>
                <td>
                    <form method='post'>
                        <input type='hidden' name='chooseBranch' value='" . htmlspecialchars($branchName) . "' />
                        <input type='hidden' name='productID' value='" . htmlspecialchars($row['ProductID']) . "' />
                        <button type='submit' class='btn btn-danger' name='deleteProductBtn' value='deleteProductBtn' style='font-size:12px'><i class='fa-solid fa-trash'></i></button>
                    </form>
                </td>
            </tr>";
    }

    echo " </tbody>
        </table>";

    // Close the statement
    mysqli_stmt_close($stmt);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['editProductBtn'])) {
            editProduct(); // Call the edit function
        }
    }
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
    $sql = "SELECT * FROM shapemaster";
    $result = mysqli_query($link, $sql);
    while ($row = mysqli_fetch_array($result)) {
        $selected = ($row['Description'] === $currentShapeDescription) ? 'selected' : '';
        echo "<option class='form-select-sm' value='" . htmlspecialchars($row['ShapeID']) . "' $selected>" . htmlspecialchars($row['Description']) . "</option>";
    }
}

function editProduct(){ //Edit function to edit an existing product in the database
    $link = connect();
    global $employeeName;
    getEmployeeName(); 

    $productID = $_POST['productID'] ?? '';
    $categoryType = $_POST['categoryType'] ?? '';
    $ShapeDescription = $_POST['shape'] ?? '';
    $brandID = $_POST['brandID'] ?? '';
    $model = $_POST['model'] ?? '';
    $remarks = $_POST['remarks'] ?? '';
    $count = $_POST['count'] ?? '';

    echo '<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content bg-secondary-subtle">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>      
                    <div class="modal-body" style="margin-top: -1.5rem;">
                    <hr>
                        <form method="post">
                            <input type="hidden" name="productID" value="' . htmlspecialchars($productID) . '" />
                            <div class="mb-3">
                                <label for="categoryType" class="form-label">Category Type</label>
                                <input type="text" class="form-control" id="categoryType" name="categoryType" value="' . htmlspecialchars($categoryType) . '" required>
                            </div>
                            <div class="mb-3">
                                <label for="ShapeDescription" class="form-label">Shape</label>
                                <select class="form-select" id="ShapeDescription" name="ShapeDescription" required>';
                                    editShape($ShapeDescription);
                                    echo '
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="brandID" class="form-label">Brand ID</label>
                                <input type="text" class="form-control" id="brandID" name="brandID" value="' . htmlspecialchars($brandID) . '" required>
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
                                <input type="number" class="form-control" id="count" name="count" value="' . htmlspecialchars($count) . '" required>
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

function deleteProduct() { //Delete function to delete a product from the database
    $link = connect();
    $productID = $_POST['productID'] ?? '';

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
        mysqli_close($link);
}
?>