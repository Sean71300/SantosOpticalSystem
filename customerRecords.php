<?php
include 'ActivityTracker.php';
include 'customerFunctions.php'; 
include 'loginChecker.php';

// Get sort parameters from URL
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'CustomerID';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
?>
    
<html>
    <title>Customer Records</title>
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            .table-container {
                background-color: white;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                padding: 20px;
            }
            /* Sorting styles */
            .sortable {
                cursor: pointer;
                position: relative;
                padding-right: 25px;
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
        </style>
    </head>
    <body>
        <?php include "sidebar.php"?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-user-plus ms-2"></i> Customer Records</h1>
                    <a class="btn btn-primary" href="customerCreate.php" role="button">
                        <i class="fas fa-plus me-2"></i> New Customer
                    </a>            
                </div>
                
                <div class="table-instructions alert alert-info" style="margin-bottom: 20px; padding: 10px 15px; border-radius: 4px;">
                    <strong>Instructions:</strong>
                    <ul style="margin-bottom: 0; padding-left: 20px;">
                        <li>To add a customer, click the button at the top right.</li>
                        <?php if ($isAdmin): ?>
                            <li>To edit or delete customer, click the Profile or Delete button at the 'Actions' column.</li>
                        <?php endif; ?>
                        <li>To check their order, click the Order button at the 'Actions' column.</li>
                        <li>Click any column header to sort the table in ascending/descending order.</li>
                    </ul>
                </div>
                
                <table class="table table-hover text-center">
                    <thead class="table-light">
                        <tr>
                            <th class="sortable <?php echo $sort == 'CustomerID' ? 'active' : ''; ?>" onclick="sortTable('CustomerID')">
                                ID
                                <span class="sort-icon">
                                    <i class="fas fa-sort-<?php echo $sort == 'CustomerID' ? (strtolower($order)) == 'asc' ? 'up' : 'down' : 'up'; ?>"></i>
                                </span>
                            </th>
                            <th class="sortable <?php echo $sort == 'CustomerName' ? 'active' : ''; ?>" onclick="sortTable('CustomerName')">
                                Name
                                <span class="sort-icon">
                                    <i class="fas fa-sort-<?php echo $sort == 'CustomerName' ? (strtolower($order)) == 'asc' ? 'up' : 'down' : 'up'; ?>"></i>
                                </span>
                            </th>
                            <th>
                                Address                                
                            </th>
                            <th>
                                Contact Number                                
                            </th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>                      
                        <?php customerData($sort, $order); ?>                      
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Orders Modal -->
        <div class="modal fade" id="ordersModal" tabindex="-1" aria-labelledby="ordersModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ordersModalLabel">Customer Orders</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Brand</th>
                                    <th>Quantity</th>
                                    <th>Order Date and Time</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                <!-- Orders will be populated here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
                    
        <script>
            // Sorting function
            function sortTable(column) {
                const urlParams = new URLSearchParams(window.location.search);
                let currentSort = urlParams.get('sort') || 'CustomerID';
                let currentOrder = urlParams.get('order') || 'ASC';
                
                let newOrder = 'ASC';
                if (currentSort === column) {
                    newOrder = currentOrder === 'ASC' ? 'DESC' : 'ASC';
                }
                
                urlParams.set('sort', column);
                urlParams.set('order', newOrder);
                window.location.href = window.location.pathname + '?' + urlParams.toString();
            }

            // View orders functionality
            document.addEventListener('DOMContentLoaded', function() {
                // Orders modal functionality
                document.querySelectorAll('.view-orders').forEach(button => {
                    button.addEventListener('click', function() {
                        const customerID = this.getAttribute('data-customer-id');
                        
                        fetch('customerFunctions.php?action=getCustomerOrders&customerID=' + customerID)
                            .then(response => response.json())
                            .then(orders => {
                                const tableBody = document.getElementById('ordersTableBody');
                                tableBody.innerHTML = '';
                                
                                if (orders.length === 0) {
                                    tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No orders found for this customer</td></tr>';
                                } else {
                                    orders.forEach(order => {
                                        const row = document.createElement('tr');
                                        row.innerHTML = `
                                            <td>${order.Model || 'N/A'}</td>
                                            <td>${order.BrandName || 'N/A'}</td>
                                            <td>${order.Quantity || 'N/A'}</td>
                                            <td>${order.Created_dt || 'N/A'}</td>
                                        `;
                                        tableBody.appendChild(row);
                                    });
                                }
                                
                                const modal = new bootstrap.Modal(document.getElementById('ordersModal'));
                                modal.show();
                            })
                            .catch(error => {
                                console.error('Error fetching orders:', error);
                            });
                    });
                });
            });
        </script>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="customCodes/custom.js"></script>
    </body>
</html>