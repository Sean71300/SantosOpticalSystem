<?php
include_once 'setup.php';
include_once 'connect.php';
include 'ActivityTracker.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("Refresh: 0; url=login.php");
    exit;
}

// Pagination variables
$ordersPerPage = 3;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOM8b+z4+2e5c7e5a5e5c7e5a5e5c7e5a5e5c7e5" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="customCodes/custom.css">
    <link rel="stylesheet" href="customCodes/s1.css">
    <link rel="stylesheet" href="customCodes/custom.css">
    <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
</head>

<header>
    <?php include 'Navigation.php'; ?>
</header>

<body>
    <div class="container" style="min-height: 30rem;">
        <div class="row justify-content-center mt-5 mb-5">
            <div class="col-md-8">
                <i class="fa-solid fa-file-invoice-dollar me-2"></i><h1 class="text-center">Track Your Order</h1>

                <?php
                    $conn = connect();
                    $customer_id = $_SESSION['CustomerID'];

                    // Get total number of orders
                    $countSql = "SELECT COUNT(DISTINCT oH.Orderhdr_id) AS total 
                                 FROM Order_hdr oH
                                 INNER JOIN orderDetails oD ON oH.Orderhdr_id = oD.OrderHdr_id
                                 WHERE oH.CustomerID = ?";
                    $countStmt = $conn->prepare($countSql);
                    $countStmt->bind_param("i", $customer_id);
                    $countStmt->execute();
                    $countResult = $countStmt->get_result();
                    $totalOrders = $countResult->fetch_assoc()['total'];
                    $countStmt->close();

                    // Calculate total pages
                    $totalPages = ceil($totalOrders / $ordersPerPage);
                    if ($currentPage > $totalPages && $totalPages > 0) {
                        $currentPage = $totalPages;
                    }

                    $offset = ($currentPage - 1) * $ordersPerPage;

                    $sql = "SELECT 
                        oH.Orderhdr_id AS OrderID,
                        oH.Created_dt AS OrderDate,
                        oH.Created_by AS EmployeeName,
                        oD.Status
                    FROM Order_hdr oH
                    INNER JOIN orderDetails oD ON oH.Orderhdr_id = oD.OrderHdr_id
                    WHERE oH.CustomerID = ?
                    GROUP BY oH.Orderhdr_id
                    ORDER BY oH.Created_dt DESC
                    LIMIT ?, ?";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iii", $customer_id, $offset, $ordersPerPage);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        echo "<div class='alert alert-info mt-4'>";
                        echo "<h5>Orders for Customer ID: " . htmlspecialchars($customer_id) . "</h5>";
                        while ($row = $result->fetch_assoc()) {
                            echo "<hr><strong>Order ID:</strong> " . $row['OrderID'] . "<br>";
                            echo "<strong>Date:</strong> " . $row['OrderDate'] . "<br>";
                            echo "<strong>Handled by:</strong> " . $row['EmployeeName'] . "<br>";
                            echo "<strong>Status:</strong> " . $row['Status'];
                        }
                        echo "</div>";

                        // Pagination controls
                        if ($totalPages > 1) {
                            echo '<nav aria-label="Order pagination">';
                            echo '<ul class="pagination justify-content-center">';
                            
                            // Previous button
                            if ($currentPage > 1) {
                                echo '<li class="page-item">';
                                echo '<a class="page-link" href="?page='.($currentPage - 1).'" aria-label="Previous">';
                                echo '<span aria-hidden="true">&laquo;</span>';
                                echo '</a>';
                                echo '</li>';
                            } else {
                                echo '<li class="page-item disabled">';
                                echo '<a class="page-link" href="#" tabindex="-1" aria-disabled="true">&laquo;</a>';
                                echo '</li>';
                            }
                            
                            // Page numbers
                            for ($i = 1; $i <= $totalPages; $i++) {
                                if ($i == $currentPage) {
                                    echo '<li class="page-item active"><a class="page-link" href="#">'.$i.'</a></li>';
                                } else {
                                    echo '<li class="page-item"><a class="page-link" href="?page='.$i.'">'.$i.'</a></li>';
                                }
                            }
                            
                            // Next button
                            if ($currentPage < $totalPages) {
                                echo '<li class="page-item">';
                                echo '<a class="page-link" href="?page='.($currentPage + 1).'" aria-label="Next">';
                                echo '<span aria-hidden="true">&raquo;</span>';
                                echo '</a>';
                                echo '</li>';
                            } else {
                                echo '<li class="page-item disabled">';
                                echo '<a class="page-link" href="#" tabindex="-1" aria-disabled="true">&raquo;</a>';
                                echo '</li>';
                            }
                            
                            echo '</ul>';
                            echo '</nav>';
                        }
                    } else {
                        echo "<div class='alert alert-warning mt-4 text-center'>";
                        echo "<i class='fas fa-exclamation-triangle fa-2x mb-3'></i>";
                        echo "<h5>No Orders Found</h5>";
                        echo "<p class='mb-0'>If you believe this is an error or need assistance,<br>";
                        echo "please contact our support team:<br>";
                        echo "<strong>Phone:</strong> 027-508-4792<br>";
                        echo "<strong>Email:</strong> Santosoptical@gmail.com</p>";
                        echo "</div>";
                    }
                
                    $stmt->close();
                    $conn->close();               
                ?>
            </div>
        </div>
    </div>

    <footer class="py-5 border-top mt-5 pt-4" style="background-color: #ffffff; margin-top: 50px; border-color: #ffffff;">
        <div class="container">
            <div class="row text-center text-md-start">
                <div class="col-md-3 mb-3 mb-md-0 text-center">
                    <img src="Images/logo.png" alt="Logo" width="200">
                </div>

                <div class="col-md-3 mb-3 mb-md-0">
                    <h6 class="fw-bold">PRODUCTS</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-dark text-decoration-none">Frames</a></li>
                        <li><a href="#" class="text-dark text-decoration-none">Sunglasses</a></li>
                    </ul>
                </div>

                <div class="col-md-3 mb-3 mb-md-0">
                    <h6 class="fw-bold">POLICY</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-dark text-decoration-none">FAQ</a></li>
                        <li><a href="#" class="text-dark text-decoration-none">Store Policy</a></li>
                    </ul>
                </div>

                <div class="col-md-3">
                    <h6 class="fw-bold">CONTACT US!</h6>
                    <p class="mb-1">Address: #6 Rizal Avenue Extension, Brgy. San Agustin, Malabon City</p>
                    <p class="mb-1">Phone: 027-508-4792</p>
                    <p class="mb-1">Cell: 0932-844-7068</p>
                    <p>Email: <a href="mailto:Santosoptical@gmail.com" class="text-dark">Santosoptical@gmail.com</a></p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>