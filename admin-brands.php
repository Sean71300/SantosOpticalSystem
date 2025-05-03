<?php
include 'admin-inventory-funcs.php';
include 'ActivityTracker.php';
include 'loginChecker.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_brand'])) {
        $brandName = trim($_POST['brand_name']);
        if (!empty($brandName)) {
            addBrand($brandName);
        }
    } elseif (isset($_POST['edit_brand'])) {
        $brandID = $_POST['brand_id'];
        $newName = trim($_POST['brand_name']);
        if (!empty($newName)) {
            updateBrand($brandID, $newName);
        }
    } elseif (isset($_POST['delete_brand'])) {
        $brandID = $_POST['brand_id'];
        deleteBrand($brandID);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="customCodes/custom.css">
    <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
    <title>Brands</title>

    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 80px;
            --mobile-breakpoint: 992px;
        }
        
        body {
            background-color: #f5f7fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            background-color: white;
            height: 100vh;
            padding: 20px 0;
            color: #2c3e50;
            position: fixed;
            width: var(--sidebar-width);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .sidebar-item {
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 0;
            display: flex;
            align-items: center;
            color: #2c3e50;
            transition: all 0.3s;
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
        }
        
        .sidebar-item:hover {
            background-color: #f8f9fa;
            color: #2c3e50;
        }
        
        .sidebar-item.active {
            background-color: #e9ecef;
            color: #2c3e50;
            font-weight: 500;
        }   
        
        .sidebar-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }
        
        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            width: calc(100% - var(--sidebar-width));
            transition: all 0.3s ease;
        }
        
        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            overflow-x: auto;
        }
        
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .action-btn {
            padding: 5px 8px;
            margin: 2px;
            font-size: 0.85rem;
        }
        
        .filter-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .sortable {
            cursor: pointer;
            position: relative;
            padding-right: 25px;
            white-space: nowrap;
        }
        
        .sortable:hover {
            background-color: #f8f9fa;
        }
        
        .sort-icon {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            display: none;
        }
        
        .sortable.active .sort-icon {
            display: inline-block;                
        }
        
        /* Mobile Menu Toggle */
        .menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1100;
            background: white;
            border: none;
            border-radius: 5px;
            padding: 8px 12px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        /* Responsive Styles */
        @media (max-width: 1200px) {
            .main-content {
                padding: 15px;
            }
            
            .table-container, .filter-container {
                padding: 15px;
            }
        }
        
        @media (max-width: 992px) {
            :root {
                --sidebar-width: 80px;
            }
            
            .sidebar-item span {
                display: none;
            }
            
            .sidebar-item i {
                margin-right: 0;
                font-size: 1.2rem;
            }
            
            .sidebar-header {
                padding: 0 10px 20px;
            }
            
            .sidebar-header h3 {
                display: none;
            }
            
            .sidebar-header img {
                width: 40px;
                height: 40px;
            }
        }
        
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                display: none;
            }
            
            .sidebar.active {
                display: block;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 15px 10px;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 15px;
            }
            
            .filter-container .row > div {
                margin-bottom: 10px;
            }
            
            .filter-container .row > div:last-child {
                margin-bottom: 0;
            }
            
            .action-btn {
                padding: 3px 6px;
                font-size: 0.75rem;
            }
        }
        
        @media (max-width: 576px) {
            .product-img {
                width: 40px;
                height: 40px;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            .btn {
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
            }
        }
        
        /* Print Styles */
        @media print {
            .sidebar, .menu-toggle, .btn {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 0;
            }
            
            .table-container {
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="form-container">
        <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Brand Management</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBrandModal">
                    <i class="fas fa-plus"></i> Add Brand
                </button>
            </div>

            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Brand ID</th>
                            <th>Brand Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $link = connect();
                        $sql = "SELECT * FROM brandMaster ORDER BY BrandName";
                        $result = mysqli_query($link, $sql);
                        while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['BrandID']) ?></td>
                                <td><?= htmlspecialchars($row['BrandName']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editBrandModal"
                                            data-id="<?= $row['BrandID'] ?>"
                                            data-name="<?= htmlspecialchars($row['BrandName']) ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteBrandModal"
                                            data-id="<?= $row['BrandID'] ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; 
                        mysqli_close($link); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Brand Modal -->
    <div class="modal fade" id="addBrandModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Brand</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Brand Name</label>
                            <input type="text" class="form-control" name="brand_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_brand" class="btn btn-primary">Add Brand</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Brand Modal -->
    <div class="modal fade" id="editBrandModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Brand</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="brand_id" id="editBrandId">
                        <div class="mb-3">
                            <label class="form-label">Brand Name</label>
                            <input type="text" class="form-control" name="brand_name" id="editBrandName" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit_brand" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteBrandModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="brand_id" id="deleteBrandId">
                        <p>Are you sure you want to delete this brand?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_brand" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Handle Edit Button Click
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('editBrandId').value = btn.dataset.id;
                document.getElementById('editBrandName').value = btn.dataset.name;
            });
        });

        // Handle Delete Button Click
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('deleteBrandId').value = btn.dataset.id;
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.min.js"></script>
</body>
</html>