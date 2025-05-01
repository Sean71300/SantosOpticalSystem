<?php
include_once 'customerFunctions.php';  
include 'ActivityTracker.php';
include 'loginChecker.php';

// Start with database connection
$conn = connect();

if (isset($_GET["CustomerID"])) {
    $id = $_GET["CustomerID"];
    $Aid = generate_ArchiveID();
    $Eid = $_SESSION["id"];
    
    // Archive the customer
    $sqlCustomer = "INSERT INTO archives (ArchiveID, TargetID, EmployeeID, TargetType) VALUES (?, ?, ?, 'customer')";
    $stmt = $conn->prepare($sqlCustomer);
    $stmt->bind_param("iii", $Aid, $id, $Eid);
    $stmt->execute();
    $stmt->close();
    
    // Get customer name for logs
    $query = "SELECT CustomerName FROM customer WHERE CustomerID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $name = $row['CustomerName'];
    $stmt->close();
    
    // Create log entry
    $Logsid = generate_LogsID();
    $stmt = $conn->prepare("INSERT INTO Logs 
                        (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description, Upd_dt)
                        VALUES
                        (?, ?, ?, 'customer', '5', ?, NOW())");
    $stmt->bind_param("ssss", $Logsid, $Eid, $id, $name);
    $stmt->execute();
    $stmt->close();
    
    // Close connection
    mysqli_close($conn);
    
    // Show modal and refresh
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Customer Deleted</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            .modal {
                display: flex !important;
                align-items: center;
                justify-content: center;
            }
            .modal-dialog {
                margin: 0;
                width: auto;
                max-width: 400px;
            }
        </style>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Show modal
                var myModal = new bootstrap.Modal(document.getElementById("successModal"), {
                    backdrop: "static",
                    keyboard: false
                });
                myModal.show();
                
                // Redirect after 1 second
                setTimeout(function() {
                    window.location.href = "customerRecords.php";
                }, 3000);
            });
        </script>
    </head>
    <body>
        <!-- Success Modal -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Customer Removed Succefully</h5>
                    </div>
                    <div class="modal-body text-center">
                        <p><i class="fas fa-check-circle fa-4x text-success mb-3"></i></p>
                        <p>Customer successfully archived.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    </body>
    </html>';
    exit;
}

// If no CustomerID provided, redirect immediately
header("Location: customerRecords.php");
exit;
?>