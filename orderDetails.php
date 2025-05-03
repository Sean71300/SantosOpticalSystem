<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once 'setup.php';

if (isset($_GET['id'])) {
    $orderId = (int)$_GET['id'];
    $conn = connect();
    
    // Get order header information
    $headerQuery = "SELECT oh.Orderhdr_id, oh.Created_dt, oh.Created_by, 
                   c.CustomerName, c.CustomerContact, c.CustomerAddress,
                   b.BranchName, b.BranchLocation, b.ContactNo as BranchContact,
                   e.EmployeeName
                   FROM Order_hdr oh
                   JOIN customer c ON oh.CustomerID = c.CustomerID
                   JOIN BranchMaster b ON oh.BranchCode = b.BranchCode
                   JOIN employee e ON oh.Created_by = e.LoginName
                   WHERE oh.Orderhdr_id = ?";
    $stmt = $conn->prepare($headerQuery);
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $header = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($header) {
        // Get order details
        $detailsQuery = "SELECT od.OrderDtlID, od.Quantity, od.Status,
                        p.ProductID, p.Model, p.Price, p.CategoryType,
                        pb.Stocks, b.BrandName
                        FROM orderDetails od
                        JOIN ProductBranchMaster pb ON od.ProductBranchID = pb.ProductBranchID
                        JOIN productMstr p ON pb.ProductID = p.ProductID
                        JOIN brandMaster b ON p.BrandID = b.BrandID
                        WHERE od.OrderHdr_id = ?";
        $stmt = $conn->prepare($detailsQuery);
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $details = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Calculate total
        $total = 0;
        foreach ($details as $detail) {
            $price = (float)str_replace('₱', '', $detail['Price']);
            $total += $price * $detail['Quantity'];
        }
        
        // Display order details
        echo '<div class="order-details-container">';
        echo '<div class="row mb-4">';
        echo '<div class="col-md-6">';
        echo '<h5>Order Information</h5>';
        echo '<p><strong>Order ID:</strong> ' . htmlspecialchars($header['Orderhdr_id']) . '</p>';
        echo '<p><strong>Date Created:</strong> ' . date('M j, Y h:i A', strtotime($header['Created_dt'])) . '</p>';
        echo '<p><strong>Created By:</strong> ' . htmlspecialchars($header['EmployeeName']) . '</p>';
        echo '</div>';
        echo '<div class="col-md-6">';
        echo '<h5>Branch Information</h5>';
        echo '<p><strong>Branch:</strong> ' . htmlspecialchars($header['BranchName']) . '</p>';
        echo '<p><strong>Location:</strong> ' . htmlspecialchars($header['BranchLocation']) . '</p>';
        echo '<p><strong>Contact:</strong> ' . htmlspecialchars($header['BranchContact']) . '</p>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="row mb-4">';
        echo '<div class="col-md-6">';
        echo '<h5>Customer Information</h5>';
        echo '<p><strong>Name:</strong> ' . htmlspecialchars($header['CustomerName']) . '</p>';
        echo '<p><strong>Contact:</strong> ' . htmlspecialchars($header['CustomerContact']) . '</p>';
        echo '<p><strong>Address:</strong> ' . htmlspecialchars($header['CustomerAddress']) . '</p>';
        echo '</div>';
        echo '</div>';
        
        echo '<h5 class="mb-3">Order Items</h5>';
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered order-details-table">';
        echo '<thead class="table-light">';
        echo '<tr>';
        echo '<th>Product</th>';
        echo '<th>Brand</th>';
        echo '<th>Category</th>';
        echo '<th>Price</th>';
        echo '<th>Qty</th>';
        echo '<th>Subtotal</th>';
        echo '<th>Status</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($details as $detail) {
            $price = (float)str_replace('₱', '', $detail['Price']);
            $subtotal = $price * $detail['Quantity'];
            
            echo '<tr>';
            echo '<td>' . htmlspecialchars($detail['Model']) . '</td>';
            echo '<td>' . htmlspecialchars($detail['BrandName']) . '</td>';
            echo '<td>' . htmlspecialchars($detail['CategoryType']) . '</td>';
            echo '<td>₱' . number_format($price, 2) . '</td>';
            echo '<td>' . htmlspecialchars($detail['Quantity']) . '</td>';
            echo '<td>₱' . number_format($subtotal, 2) . '</td>';
            echo '<td>';
            echo '<span class="badge ' . match($detail['Status']) {
                'Completed' => 'bg-success',
                'Cancelled' => 'bg-danger',
                default => 'bg-warning text-dark'
            } . '">' . htmlspecialchars($detail['Status']) . '</span>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        
        echo '<div class="text-end mt-3">';
        echo '<h4>Total: ₱' . number_format($total, 2) . '</h4>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-danger">Order not found.</div>';
    }
    
    $conn->close();
} else {
    echo '<div class="alert alert-danger">Invalid request. No order ID specified.</div>';
}
?>