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
                    <button class="btn btn-primary" id="newCustomerBtn" type="button">
                        <i class="fas fa-plus me-2"></i> New Customer
                    </button>
                </div>
                
                <div class="table-instructions alert alert-info" style="margin-bottom: 20px; padding: 10px 15px; border-radius: 4px;">
                    <strong>Instructions:</strong>
                    <ul style="margin-bottom: 0; padding-left: 20px;">
                        <li>To add a customer, click the button at the top right.</li>
                        <?php 
                        $isAdmin = isset($_SESSION['roleid']) && $_SESSION['roleid'] === 1; 
                        $isSuperAdmin = isset($_SESSION['roleid']) && $_SESSION['roleid'] === 4; 
                        if ($isAdmin || $isSuperAdmin): ?>
                            <li>To view and edit a customer, open the Profile; you can also Remove the customer from the 'Actions' column.</li>
                        <?php else: ?>
                            <li>To check their orders, click the Orders button at the 'Actions' column.</li>
                        <?php endif; ?>
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
        
                <!-- Processing modal (shows while deleting a customer) -->
                <div class="modal fade" id="processingModalCustomer" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body text-center py-4">
                                <div class="spinner-border text-danger me-2" role="status"></div>
                                <span>Removing customer...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Success modal (shown after deletion) -->
                <div class="modal fade" id="deletedSuccessModalCustomer" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">Customer Removed</h5>
                            </div>
                            <div class="modal-body text-center">
                                The customer has been removed. This window will close and the list will refresh shortly.
                            </div>
                        </div>
                    </div>
                </div>
        <!-- Remove confirmation modal -->
        <div class="modal fade" id="confirmRemoveModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Remove</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to remove <strong id="removeCustomerName">this customer</strong>? The record will be removed and the list will refresh shortly.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmRemoveBtn" class="btn btn-danger">Remove</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Profile / Edit modal (centered large dialog) -->
        <div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="profileModalTitle">Customer Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="profileModalBody">
                        <!-- Profile and medical history will be loaded here via AJAX -->
                        <div class="text-center">Loading...</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="saveProfileBtn" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Customer modal -->
        <div class="modal fade" id="newCustomerModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Customer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="newCustomerForm">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact</label>
                                <input type="text" name="phone" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Info</label>
                                <textarea name="info" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="submitNewCustomer" class="btn btn-primary">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Add Medical Record Modal -->
        <div class="modal fade" id="addMedicalRecordModal" tabindex="-1" aria-labelledby="addMedicalRecordModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="addMedicalRecordModalLabel">Add New Medical Record</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="medicalRecordForm" method="post" action="medical-records-funcs.php">
                        <div class="modal-body">
                            <input type="hidden" name="customerID" id="modalCustomerID">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="visit_date" class="form-label">Visit Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="visit_date" name="visit_date" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="eye_condition" class="form-label">Eye Condition</label>
                                    <input type="text" class="form-control" id="eye_condition" name="eye_condition">
                                </div>
                                <div class="col-md-6">
                                    <label for="systemic_diseases" class="form-label">Systemic Diseases</label>
                                    <input type="text" class="form-control" id="systemic_diseases" name="systemic_diseases" placeholder="e.g., diabetes, hypertension">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="visual_acuity_right" class="form-label">Visual Acuity (Right)</label>
                                    <input type="text" class="form-control" id="visual_acuity_right" name="visual_acuity_right" placeholder="e.g., 20/20">
                                </div>
                                <div class="col-md-4">
                                    <label for="visual_acuity_left" class="form-label">Visual Acuity (Left)</label>
                                    <input type="text" class="form-control" id="visual_acuity_left" name="visual_acuity_left" placeholder="e.g., 20/20">
                                </div>
                                <div class="col-md-4">
                                    <label for="pupillary_distance" class="form-label">Pupillary Distance (mm)</label>
                                    <input type="number" class="form-control" id="pupillary_distance" name="pupillary_distance">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="intraocular_pressure_right" class="form-label">Intraocular Pressure - Right (mmHg)</label>
                                    <input type="number" step="0.01" class="form-control" id="intraocular_pressure_right" name="intraocular_pressure_right">
                                </div>
                                <div class="col-md-6">
                                    <label for="intraocular_pressure_left" class="form-label">Intraocular Pressure - Left (mmHg)</label>
                                    <input type="number" step="0.01" class="form-control" id="intraocular_pressure_left" name="intraocular_pressure_left">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="refraction_right" class="form-label">Refraction (Right)</label>
                                    <input type="text" class="form-control" id="refraction_right" name="refraction_right" placeholder="e.g., -1.50 DS">
                                </div>
                                <div class="col-md-6">
                                    <label for="refraction_left" class="form-label">Refraction (Left)</label>
                                    <input type="text" class="form-control" id="refraction_left" name="refraction_left" placeholder="e.g., -1.25 DS">
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
                                    <label for="corneal_topography" class="form-label">Corneal Topography</label>
                                    <textarea class="form-control" id="corneal_topography" name="corneal_topography" rows="2"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="fundus_examination" class="form-label">Fundus Examination</label>
                                    <textarea class="form-control" id="fundus_examination" name="fundus_examination" rows="2"></textarea>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="additional_notes" class="form-label">Additional Notes</label>
                                    <textarea class="form-control" id="additional_notes" name="additional_notes" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Record</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
                    
        

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

            // Remove (soft-delete) functionality
            let removeTargetId = null;
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    removeTargetId = this.getAttribute('data-customer-id');
                    const name = this.getAttribute('data-customer-name') || 'this customer';
                    document.getElementById('removeCustomerName').textContent = name;
                    const mdl = new bootstrap.Modal(document.getElementById('confirmRemoveModal'));
                    mdl.show();
                });
            });

            document.getElementById('confirmRemoveBtn')?.addEventListener('click', function() {
                if (!removeTargetId) return;
                const btn = this; btn.disabled = true; btn.textContent = 'Removing...';
                var confModal = bootstrap.Modal.getInstance(document.getElementById('confirmRemoveModal'));
                if (confModal) confModal.hide();

                // Show a dedicated processing modal (centered spinner)
                var procModal = new bootstrap.Modal(document.getElementById('processingModalCustomer'), { backdrop: 'static', keyboard: false });
                procModal.show();

                const fd = new FormData(); fd.append('CustomerID', removeTargetId);
                fetch('customerDeleteAjax.php', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(async r => { const txt = await r.text(); console.debug('[Remove] HTTP', r.status, txt); try { return JSON.parse(txt); } catch (e) { throw new Error('Invalid JSON response: ' + txt); } })
                .then(json => {
                    if (!json.success) throw new Error(json.message || 'Remove failed');
                    // hide processing and show success modal
                    procModal.hide();
                    var successModal = new bootstrap.Modal(document.getElementById('deletedSuccessModalCustomer'));
                    successModal.show();
                    // refresh after 2 seconds
                    setTimeout(() => { location.reload(); }, 2000);
                })
                .catch(err => { procModal.hide(); alert('Remove failed: ' + err.message); })
                .finally(() => { btn.disabled = false; btn.textContent = 'Remove'; });
            });

            // Profile modal: load edit form + medical history via AJAX
            document.querySelectorAll('.profile-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const customerID = this.getAttribute('data-customer-id');
                    const body = document.getElementById('profileModalBody');
                    body.innerHTML = '<div class="text-center">Loading...</div>';
                    const modal = new bootstrap.Modal(document.getElementById('profileModal'));
                    modal.show();

                    // Fetch profile form (returns edit form + Orders table placeholder; Super Admin also includes right-side medical history)
                    fetch('customerFunctions.php?action=getCustomerProfile&customerID=' + encodeURIComponent(customerID))
                    .then(r => r.text())
                    .then(html => {
                        body.innerHTML = html;
                        // Populate Orders list inside the profile modal (branch-scoped server side)
                        return fetch('customerFunctions.php?action=getCustomerOrders&customerID=' + encodeURIComponent(customerID))
                            .then(r => r.json())
                            .then(orders => {
                                const tbody = document.getElementById('ordersTableBodyProfile');
                                if (!tbody) return;
                                tbody.innerHTML = '';
                                if (!orders || !orders.length) {
                                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No orders found for this customer</td></tr>';
                                    return;
                                }
                                orders.forEach(o => {
                                    const tr = document.createElement('tr');
                                    tr.innerHTML = `<td>${o.Model || 'N/A'}</td><td>${o.BrandName || 'N/A'}</td><td>${o.Quantity || 'N/A'}</td><td>${o.Created_dt || 'N/A'}</td>`;
                                    tbody.appendChild(tr);
                                });
                            });
                    }).then(() => {
                        // Wire up the right-pane New Medical History button to inject inline form
                        const modalEl = document.getElementById('profileModal');
                        const btnNew = modalEl ? modalEl.querySelector('#newMedicalBtn') : null;
                        if (btnNew) {
                            btnNew.addEventListener('click', function(ev){
                                ev.preventDefault();
                                const cid = this.getAttribute('data-customer-id');
                                const area = document.getElementById('medicalRecordsArea');
                                if (!area) return;
                                if (document.getElementById('medicalRecordFormInline')) {
                                    document.getElementById('medicalRecordFormInline').scrollIntoView({behavior:'smooth'});
                                    return;
                                }
                                const formHtml = `
                                    <div class="card mb-3 p-3" id="addRecordInline">
                                        <h5>Add Medical Record</h5>
                                        <form id="medicalRecordFormInline" method="post" action="medical-records-funcs.php">
                                            <input type="hidden" name="customerID" value="${cid}">
                                            <div class="row mb-2">
                                                <div class="col-12">
                                                    <label class="form-label">Visit Date</label>
                                                    <input type="date" name="visit_date" class="form-control" required value="${new Date().toISOString().split('T')[0]}">
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-12">
                                                    <label class="form-label">Eye Condition</label>
                                                    <input type="text" name="eye_condition" class="form-control">
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <label class="form-label">Systemic Diseases</label>
                                                    <input type="text" name="systemic_diseases" class="form-control">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Pupillary Distance (mm)</label>
                                                    <input type="number" step="0.01" name="pupillary_distance" class="form-control">
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-4">
                                                    <label class="form-label">Visual Acuity (Right)</label>
                                                    <input type="text" name="visual_acuity_right" class="form-control">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Visual Acuity (Left)</label>
                                                    <input type="text" name="visual_acuity_left" class="form-control">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Intraocular Pressure - Right (mmHg)</label>
                                                    <input type="number" step="0.01" name="intraocular_pressure_right" class="form-control">
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-4">
                                                    <label class="form-label">Intraocular Pressure - Left (mmHg)</label>
                                                    <input type="number" step="0.01" name="intraocular_pressure_left" class="form-control">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Refraction (Right)</label>
                                                    <input type="text" name="refraction_right" class="form-control">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Refraction (Left)</label>
                                                    <input type="text" name="refraction_left" class="form-control">
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <label class="form-label">Current Medications</label>
                                                    <textarea name="current_medications" class="form-control" rows="2"></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Allergies</label>
                                                    <textarea name="allergies" class="form-control" rows="2"></textarea>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <label class="form-label">Family Eye History</label>
                                                    <textarea name="family_eye_history" class="form-control" rows="2"></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Previous Eye Surgeries</label>
                                                    <textarea name="previous_eye_surgeries" class="form-control" rows="2"></textarea>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <label class="form-label">Corneal Topography</label>
                                                    <textarea name="corneal_topography" class="form-control" rows="2"></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Fundus Examination</label>
                                                    <textarea name="fundus_examination" class="form-control" rows="2"></textarea>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-12">
                                                    <label class="form-label">Additional Notes</label>
                                                    <textarea name="additional_notes" class="form-control" rows="3"></textarea>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2 justify-content-end">
                                                <button type="button" id="cancelAddInline" class="btn btn-secondary">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Save Record</button>
                                            </div>
                                        </form>
                                    </div>`;
                                const container = document.createElement('div');
                                container.innerHTML = formHtml;
                                area.prepend(container);
                            });
                        }
                    })
                    .catch(err => { body.innerHTML = '<div class="alert alert-danger">Error loading profile.</div>'; console.error(err); });
                });
            });

            // ===== New JS: submit profile edit form via AJAX when footer "Save" is clicked =====
            document.getElementById('saveProfileBtn')?.addEventListener('click', function() {
                const btn = this;
                const modalBody = document.getElementById('profileModalBody');
                if (!modalBody) { alert('Profile area not found.'); return; }

                // Look for a form inside the loaded profile HTML
                const form = modalBody.querySelector('form');
                if (!form) { alert('No editable form was found in the profile.'); return; }

                // Determine endpoint: prefer form.action, fallback to a conventional ajax endpoint
                const endpoint = form.getAttribute('action') || 'customerUpdateAjax.php';
                const method = (form.getAttribute('method') || 'POST').toUpperCase();

                btn.disabled = true;
                const prevText = btn.textContent;
                btn.textContent = 'Saving...';

                const fd = new FormData(form);

                fetch(endpoint, { method: method, body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(async r => {
                    const txt = await r.text();
                    try { return JSON.parse(txt); }
                    catch (e) { throw new Error('Invalid JSON response from server: ' + txt); }
                })
                .then(json => {
                    if (!json.success) throw new Error(json.message || 'Save failed');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('profileModal'));
                    if (modal) modal.hide();
                    // quick refresh to reflect updated record
                    setTimeout(() => { location.reload(); }, 800);
                })
                .catch(err => {
                    alert('Save failed: ' + err.message);
                    console.error('Profile save error:', err);
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.textContent = prevText;
                });
            });

            // New Customer button
            document.getElementById('newCustomerBtn')?.addEventListener('click', function() {
                const mdl = new bootstrap.Modal(document.getElementById('newCustomerModal'));
                mdl.show();
            });

            // New Customer form submit via AJAX
            document.getElementById('newCustomerForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this; const btn = document.getElementById('submitNewCustomer');
                btn.disabled = true; btn.textContent = 'Creating...';
                const fd = new FormData(form);
                fetch('customerCreateAjax.php', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(async r => { const txt = await r.text(); console.debug('[CreateCustomer] HTTP', r.status, txt); try { return JSON.parse(txt); } catch (e) { throw new Error('Invalid JSON response: ' + txt); } })
                .then(json => {
                    if (!json.success) throw new Error(json.message || 'Create failed');
                    // close modal and refresh quickly
                    var m = bootstrap.Modal.getInstance(document.getElementById('newCustomerModal'));
                    if (m) m.hide();
                    setTimeout(() => { location.reload(); }, 1000);
                })
                .catch(err => { alert('Create failed: ' + err.message); })
                .finally(() => { btn.disabled = false; btn.textContent = 'Create'; });
            });

            // Medical record form submit via AJAX (works for add modal inside this page)
            const medForm = document.getElementById('medicalRecordForm');
            if (medForm) {
                medForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const btn = medForm.querySelector('button[type="submit"]');
                    if (btn) { btn.disabled = true; btn.textContent = 'Saving...'; }
                    const fd = new FormData(medForm);
                    fetch('medical-records-funcs.php', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(async r => { const txt = await r.text(); console.debug('[AddMed] HTTP', r.status, txt); try { return JSON.parse(txt); } catch (e) { throw new Error('Invalid JSON response: ' + txt); } })
                    .then(json => {
                        if (!json.success) throw new Error(json.message || 'Add record failed');
                        // close modal
                        const addModal = bootstrap.Modal.getInstance(document.getElementById('addMedicalRecordModal'));
                                    if (addModal) {
                                        // Hide the add modal first, then when it's fully hidden re-open the profile modal
                                        const addModalEl = document.getElementById('addMedicalRecordModal');
                                        const addInstance = bootstrap.Modal.getInstance(addModalEl) || bootstrap.Modal.getOrCreateInstance(addModalEl);
                                        const profileModalEl = document.getElementById('profileModal');
                                        const customerID = fd.get('customerID');

                                        const onHidden = function() {
                                            addModalEl.removeEventListener('hidden.bs.modal', onHidden);
                                            // Re-open profile modal and refresh records
                                            if (profileModalEl) {
                                                const profileInstance = bootstrap.Modal.getOrCreateInstance(profileModalEl);
                                                profileInstance.show();
                                                fetch('customerFunctions.php?action=getCustomerMedicalRecords&customerID=' + encodeURIComponent(customerID) + '&embed=1')
                                                    .then(r=>r.text()).then(html=>{ const area = document.getElementById('medicalRecordsArea'); if (area) area.innerHTML = html; })
                                                    .catch(e=>console.error('Failed to refresh medical records', e));
                                            }
                                        };

                                        addModalEl.addEventListener('hidden.bs.modal', onHidden);
                                        try { addInstance.hide(); } catch (e) { /* fallback */ onHidden(); }
                                    } else {
                                        // If add modal instance not found, just re-open profile and refresh
                                        const profileModalEl = document.getElementById('profileModal');
                                        if (profileModalEl) {
                                            const profileInstance = bootstrap.Modal.getOrCreateInstance(profileModalEl);
                                            profileInstance.show();
                                            const customerID = fd.get('customerID');
                                            fetch('customerFunctions.php?action=getCustomerMedicalRecords&customerID=' + encodeURIComponent(customerID) + '&embed=1')
                                                .then(r=>r.text()).then(html=>{ const area = document.getElementById('medicalRecordsArea'); if (area) area.innerHTML = html; })
                                                .catch(e=>console.error('Failed to refresh medical records', e));
                                        }
                                    }
                    })
                    .catch(err => { alert('Add record failed: ' + err.message); })
                    .finally(() => { if (btn) { btn.disabled = false; btn.textContent = 'Save Record'; } });
                });
            }

            // Delegate clicks for Add Record buttons (injected HTML). Instead of
            // opening a nested Bootstrap modal (which causes backdrop/focus errors),
            // inject an inline form into the medical records area.
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('[data-customer-id]');
                if (!btn) return;
                // only handle Add Record buttons (we expect text contains 'Add Record')
                if (!btn.textContent || !btn.textContent.includes('Add Record')) return;

                e.preventDefault();
                const customerID = btn.getAttribute('data-customer-id') || '';
                const area = document.getElementById('medicalRecordsArea');
                if (!area) return;

                // If an inline form already exists, focus it instead
                if (document.getElementById('medicalRecordFormInline')) {
                    document.getElementById('medicalRecordFormInline').querySelector('input[name="visit_date"]').focus();
                    return;
                }

                // Build inline form HTML
                const formHtml = `
                    <div class="card mb-3 p-3" id="addRecordInline">
                        <h5>Add Medical Record</h5>
                        <form id="medicalRecordFormInline" method="post" action="medical-records-funcs.php">
                            <input type="hidden" name="customerID" value="${customerID}">
                            <div class="row mb-2">
                                <div class="col-12">
                                    <label class="form-label">Visit Date</label>
                                    <input type="date" name="visit_date" class="form-control" required value="${new Date().toISOString().split('T')[0]}">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12">
                                    <label class="form-label">Eye Condition</label>
                                    <input type="text" name="eye_condition" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label class="form-label">Systemic Diseases</label>
                                    <input type="text" name="systemic_diseases" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pupillary Distance (mm)</label>
                                    <input type="number" step="0.01" name="pupillary_distance" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <label class="form-label">Visual Acuity (Right)</label>
                                    <input type="text" name="visual_acuity_right" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Visual Acuity (Left)</label>
                                    <input type="text" name="visual_acuity_left" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Intraocular Pressure - Right (mmHg)</label>
                                    <input type="number" step="0.01" name="intraocular_pressure_right" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <label class="form-label">Intraocular Pressure - Left (mmHg)</label>
                                    <input type="number" step="0.01" name="intraocular_pressure_left" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Refraction (Right)</label>
                                    <input type="text" name="refraction_right" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Refraction (Left)</label>
                                    <input type="text" name="refraction_left" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label class="form-label">Current Medications</label>
                                    <textarea name="current_medications" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Allergies</label>
                                    <textarea name="allergies" class="form-control" rows="2"></textarea>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label class="form-label">Family Eye History</label>
                                    <textarea name="family_eye_history" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Previous Eye Surgeries</label>
                                    <textarea name="previous_eye_surgeries" class="form-control" rows="2"></textarea>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label class="form-label">Corneal Topography</label>
                                    <textarea name="corneal_topography" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fundus Examination</label>
                                    <textarea name="fundus_examination" class="form-control" rows="2"></textarea>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-12">
                                    <label class="form-label">Additional Notes</label>
                                    <textarea name="additional_notes" class="form-control" rows="3"></textarea>
                                </div>
                            </div>

                            <div class="d-flex gap-2 justify-content-end">
                                <button type="button" id="cancelAddInline" class="btn btn-secondary">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Record</button>
                            </div>
                        </form>
                    </div>
                `;

                // Insert at the top of the medical records area
                const container = document.createElement('div');
                container.innerHTML = formHtml;
                area.prepend(container);
                // focus first input but delay slightly and avoid focusing while profile modal is aria-hidden
                setTimeout(() => {
                    const firstInput = document.querySelector('#medicalRecordFormInline input[name="visit_date"]');
                    const profileModalEl = document.getElementById('profileModal');
                    const ariaHidden = profileModalEl ? profileModalEl.getAttribute('aria-hidden') : null;
                    if (firstInput && ariaHidden !== 'true') {
                        try { firstInput.focus(); } catch (e) { /* ignore focus errors */ }
                    }
                }, 120);
            });

            // Handle cancel for inline form
            document.addEventListener('click', function(e) {
                if (e.target && e.target.id === 'cancelAddInline') {
                    const el = document.getElementById('addRecordInline');
                    if (el) el.remove();
                }
            });

            // Submit handler for inline form
            document.addEventListener('submit', function(e) {
                if (!e.target || e.target.id !== 'medicalRecordFormInline') return;
                e.preventDefault();
                const form = e.target;
                const btn = form.querySelector('button[type="submit"]');
                if (btn) { btn.disabled = true; btn.textContent = 'Saving...'; }
                const fd = new FormData(form);
                fetch('medical-records-funcs.php', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(async r => { const txt = await r.text(); try { return JSON.parse(txt); } catch (e) { throw new Error('Invalid JSON response: ' + txt); } })
                .then(json => {
                    if (!json.success) throw new Error(json.message || 'Add record failed');
                    // remove inline form
                    const el = document.getElementById('addRecordInline'); if (el) el.remove();
                    // refresh medical records area
                    const customerID = fd.get('customerID');
                    fetch('customerFunctions.php?action=getCustomerMedicalRecords&customerID=' + encodeURIComponent(customerID))
                        .then(r=>r.text()).then(html=>{ const area = document.getElementById('medicalRecordsArea'); if (area) area.innerHTML = html; })
                        .catch(e=>console.error('Failed to refresh medical records', e));
                })
                .catch(err => { alert('Add record failed: ' + err.message); })
                .finally(() => { if (btn) { btn.disabled = false; btn.textContent = 'Save Record'; } });
            });
        });
        </script>

        <script src="customCodes/custom.js"></script>
    </body>
</html>