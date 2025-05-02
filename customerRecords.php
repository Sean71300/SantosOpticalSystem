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

        <!-- Add Medical Record Modal -->
        <div class="modal fade" id="addMedicalRecordModal" tabindex="-1" aria-labelledby="addMedicalRecordModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="addMedicalRecordModalLabel">Add Medical Record</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="medicalRecordForm">
                            <input type="hidden" name="customerID" id="medicalRecordCustomerID">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="visit_date" class="form-label">Visit Date</label>
                                    <input type="date" class="form-control" id="visit_date" name="visit_date" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="eye_condition" class="form-label">Eye Condition</label>
                                    <input type="text" class="form-control" id="eye_condition" name="eye_condition">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="current_medications" class="form-label">Current Medications</label>
                                    <textarea class="form-control" id="current_medications" name="current_medications" rows="2"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="allergies" class="form-label">Allergies</label>
                                    <textarea class="form-control" id="allergies" name="allergies" rows="2"></textarea>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="family_eye_history" class="form-label">Family Eye History</label>
                                    <textarea class="form-control" id="family_eye_history" name="family_eye_history" rows="2"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="previous_eye_surgeries" class="form-label">Previous Eye Surgeries</label>
                                    <textarea class="form-control" id="previous_eye_surgeries" name="previous_eye_surgeries" rows="2"></textarea>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="systemic_diseases" class="form-label">Systemic Diseases (e.g., diabetes, hypertension)</label>
                                    <input type="text" class="form-control" id="systemic_diseases" name="systemic_diseases">
                                </div>
                            </div>
                            
                            <h5 class="mt-4 mb-3 border-bottom pb-2">Visual Acuity</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="visual_acuity_right" class="form-label">Right Eye</label>
                                    <input type="text" class="form-control" id="visual_acuity_right" name="visual_acuity_right" placeholder="e.g., 20/20">
                                </div>
                                <div class="col-md-6">
                                    <label for="visual_acuity_left" class="form-label">Left Eye</label>
                                    <input type="text" class="form-control" id="visual_acuity_left" name="visual_acuity_left" placeholder="e.g., 20/20">
                                </div>
                            </div>
                            
                            <h5 class="mt-4 mb-3 border-bottom pb-2">Intraocular Pressure (mmHg)</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="intraocular_pressure_right" class="form-label">Right Eye</label>
                                    <input type="number" step="0.1" class="form-control" id="intraocular_pressure_right" name="intraocular_pressure_right">
                                </div>
                                <div class="col-md-6">
                                    <label for="intraocular_pressure_left" class="form-label">Left Eye</label>
                                    <input type="number" step="0.1" class="form-control" id="intraocular_pressure_left" name="intraocular_pressure_left">
                                </div>
                            </div>
                            
                            <h5 class="mt-4 mb-3 border-bottom pb-2">Refraction</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="refraction_right" class="form-label">Right Eye</label>
                                    <input type="text" class="form-control" id="refraction_right" name="refraction_right" placeholder="e.g., -1.50 DS">
                                </div>
                                <div class="col-md-6">
                                    <label for="refraction_left" class="form-label">Left Eye</label>
                                    <input type="text" class="form-control" id="refraction_left" name="refraction_left" placeholder="e.g., -1.25 DS">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="pupillary_distance" class="form-label">Pupillary Distance (mm)</label>
                                    <input type="number" class="form-control" id="pupillary_distance" name="pupillary_distance">
                                </div>
                            </div>
                            
                            <h5 class="mt-4 mb-3 border-bottom pb-2">Examinations</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="corneal_topography" class="form-label">Corneal Topography</label>
                                    <textarea class="form-control" id="corneal_topography" name="corneal_topography" rows="3"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="fundus_examination" class="form-label">Fundus Examination</label>
                                    <textarea class="form-control" id="fundus_examination" name="fundus_examination" rows="3"></textarea>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="additional_notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="additional_notes" name="additional_notes" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveMedicalRecord">Save Record</button>
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

        <!-- Load jQuery first -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- Then load Bootstrap bundle (which includes Popper.js) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <!-- Then load other scripts -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="customCodes/custom.js"></script>
    </body>
</html>