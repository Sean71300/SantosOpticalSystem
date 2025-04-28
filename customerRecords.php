<?php
include 'ActivityTracker.php';
include_once 'customerFunctions.php'; 
include 'loginChecker.php';
?>
    
<html>
    <title>Customer Records</title>
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="customCodes/custom.css">
        <style>
            body {
                background-color: #f5f7fa;
                display: flex;
            }
            .sidebar {
                background-color: white;  /* Changed from #2c3e50 to white */
                height: 100vh;
                padding: 20px 0;
                color: #2c3e50;  /* Changed text color for better contrast */
                position: fixed;
                width: 250px;
                box-shadow: 2px 0 5px rgba(0,0,0,0.1);  /* Added subtle shadow */
            }

            .sidebar-header {
                padding: 0 20px 20px;
                border-bottom: 1px solid rgba(0,0,0,0.1);  /* Changed border color */
            }

            .sidebar-item {
                padding: 12px 20px;
                margin: 5px 0;
                border-radius: 0;
                display: flex;
                align-items: center;
                color: #2c3e50;  /* Changed text color */
                transition: all 0.3s;
                text-decoration: none;  /* Removed underline */
            }

            .sidebar-item:hover {
                background-color: #f8f9fa;  /* Lighter hover state */
                color: #2c3e50;
            }

            .sidebar-item.active {
                background-color: #e9ecef;  /* Lighter active state */
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
                
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Contact Number</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>                      
                        <?php customerData(); ?>                      
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
            document.addEventListener('DOMContentLoaded', function() {
                // Handle view orders button clicks
                document.querySelectorAll('.view-orders').forEach(button => {
                    button.addEventListener('click', function() {
                        const customerID = this.getAttribute('data-customer-id');
                        
                        // Fetch customer orders via AJAX - now pointing to current file
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
                                
                                // Show the modal
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
    </body>
</html>