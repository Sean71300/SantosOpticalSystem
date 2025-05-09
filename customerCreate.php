<?php
include 'ActivityTracker.php';
include 'loginChecker.php';
include 'customerFunctions.php'; 
$name = "";
$address = "";
$phone = "";
$info = "";
$notes = "";   

$errorMessage = "";
$successMessage = "";

[$errorMessage, $successMessage] = handleCustomerForm();
handleCancellation();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>New Customer | Santos Optical</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="customCodes/custom.css">
    <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
    
    <style>
        body {
            background-color: #f5f7fa;
            display: flex;
        }
        .sidebar {
            background-color: white;
            height: 100vh;
            padding: 20px 0;
            color: #2c3e50;
            position: fixed;
            width: 250px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
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
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }
        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .form-label {
            font-weight: 500;
        }
        .btn-action {
            min-width: 120px;
        }
    </style>
</head>

<body>
    <?php include "sidebar.php"?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="form-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-user-plus me-2"></i> New Customer</h1>
                <a class="btn btn-outline-secondary" href="customerRecords.php" role="button" data-bs-toggle="modal" 
                data-bs-target="#cancelModal">
                    <i class="fas fa-arrow-left me-2"></i> Back to List
                </a>            
            </div>
            
            <?php if (!empty($errorMessage)): ?>
                <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                    <strong><?php echo $errorMessage; ?></strong>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($successMessage)): ?>
                <div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <strong><?php echo $successMessage; ?></strong>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>
            <?php endif; ?>
            
            <form method="post" id="customerCreate">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control form-control-lg" name="name" 
                               value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control form-control-lg" name="phone" 
                               value="<?php echo htmlspecialchars($phone); ?>" required>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control form-control-lg" name="address" 
                               value="<?php echo htmlspecialchars($address); ?>" required>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Customer Information</label>
                        <textarea class="form-control form-control-lg" name="info" rows="3" required><?php 
                            echo htmlspecialchars($info); 
                        ?></textarea>
                        <div class="form-text">Age, height, gender, etc.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control form-control-lg" name="notes" rows="3" required><?php 
                            echo htmlspecialchars($notes); 
                        ?></textarea>
                        <div class="form-text">Face shape, special requirements, etc.</div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-3 mt-5">
                    
                    <button type="submit" class="btn btn-primary btn-action" name="submit">
                        <i class="fas fa-save me-2"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">Confirm Cancellation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to retrun to the records? Any unsaved changes will be lost.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="customerRecords.php" class="btn btn-danger">Yes, Cancel</a>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.min.js"></script>
</body>
</html>