<?php
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    include_once 'setup.php'; 

    //read all row from database table
    function customerData($sort = 'CustomerID', $order = 'ASC')
        {
            $isAdmin = false;
            $isOptometrist = false;
            $roleId = isset($_SESSION['roleid']) ? (int)$_SESSION['roleid'] : 0;
            $isAdmin = ($roleId === 1);
            $isOptometrist = ($roleId === 3);
            $isSuperAdmin = ($roleId === 4);
            
            $customerData = "";
            $connection = connect();

            // Validate sort column to prevent SQL injection
            $validColumns = ['CustomerID', 'CustomerName', 'CustomerAddress', 'CustomerContact'];
            $sort = in_array($sort, $validColumns) ? $sort : 'CustomerID';
            $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

            $sql = "SELECT * FROM customer WHERE Status = 'Active'
            ORDER BY $sort $order";
            $result = $connection->query($sql);

            if(!$result) {
                die ("Invalid query: " . $connection->error);
            }

            // read data of each row
            while ($row = $result->fetch_assoc()){
                echo
                "<tr>
                    <td>$row[CustomerID]</td>
                    <td>$row[CustomerName]</td>
                    <td>$row[CustomerAddress]</td>
                    <td>$row[CustomerContact]</td>
                    <td>";
                        
                    if ($isAdmin || $isSuperAdmin)
                        {
                            echo 
                            "
                                <button class='btn btn-primary btn-sm profile-btn' data-customer-id='{$row['CustomerID']}'>Profile</button>
                                <button class='btn btn-danger btn-sm delete-btn' data-customer-id='{$row['CustomerID']}' data-customer-name='".htmlspecialchars($row['CustomerName'], ENT_QUOTES)."'>Remove</button>

                            ";
                        }
                    else if ($isOptometrist) {
                        echo 
                            "
                                <a class='btn btn-primary btn-sm' href='optometrist-medicalhistory.php?CustomerID={$row['CustomerID']}'>Medical History</a>
                            ";
                    } else {
                        echo "
                            <button class='btn btn-info btn-sm view-orders' data-customer-id='$row[CustomerID]'>Orders</button>
                    </td>
                </tr>";
                }
            }            
        }
        

    // New function to get ordered products by customer
    function getCustomerOrders($customerID) {
        $connection = connect();
        $orders = array();

        // Determine if current user is restricted to a branch (Admin=1, Employee=2)
        $roleId = isset($_SESSION['roleid']) ? (int)$_SESSION['roleid'] : 0;
        $sessionBranch = isset($_SESSION['branchcode']) ? (string)$_SESSION['branchcode'] : '';
        $username = isset($_SESSION['username']) ? (string)$_SESSION['username'] : '';
        $isRestrictedRole = in_array($roleId, [1,2], true);

        if ($isRestrictedRole && $sessionBranch === '' && $username !== '') {
            if ($st = $connection->prepare("SELECT BranchCode FROM employee WHERE LoginName = ? LIMIT 1")) {
                $st->bind_param('s', $username);
                if ($st->execute()) {
                    $rs = $st->get_result();
                    if ($rs && ($r = $rs->fetch_assoc())) {
                        $_SESSION['branchcode'] = (string)$r['BranchCode'];
                        $sessionBranch = $_SESSION['branchcode'];
                    }
                }
                $st->close();
            }
        }

        $sql = "SELECT p.Model, b.BrandName, od.Quantity, oh.Created_dt 
                FROM orderDetails od
                JOIN Order_hdr oh ON od.OrderHdr_id = oh.Orderhdr_id
                JOIN ProductBranchMaster pbm ON od.ProductBranchID = pbm.ProductBranchID
                JOIN productMstr p ON pbm.ProductID = p.ProductID
                JOIN brandMaster b ON p.BrandID = b.BrandID
                WHERE oh.CustomerID = ?";

        $types = 's';
        $params = [$customerID];
        if ($isRestrictedRole && $sessionBranch !== '') {
            $sql .= " AND oh.BranchCode = ?";
            $types .= 's';
            $params[] = $sessionBranch;
        }
        
        $stmt = $connection->prepare($sql);
        if ($stmt === false) {
            return [];
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        $stmt->close();
        $connection->close();
        
        return $orders;
    }

    // Handle AJAX request for customer orders
    if (isset($_GET['action']) && $_GET['action'] === 'getCustomerOrders' && isset($_GET['customerID'])) {
        header('Content-Type: application/json');
        $customerID = $_GET['customerID'];
        $orders = getCustomerOrders($customerID);
        echo json_encode($orders);
        exit();
    }

    // Return HTML snippet for profile modal (edit form + orders list; include medical history for Super Admin)
    if (isset($_GET['action']) && $_GET['action'] === 'getCustomerProfile' && isset($_GET['customerID'])) {
        $customerID = $_GET['customerID'];
        $conn = connect();
        $stmt = $conn->prepare("SELECT * FROM customer WHERE CustomerID = ? LIMIT 1");
        $stmt->bind_param('s', $customerID);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        header('Content-Type: text/html; charset=utf-8');
        if (!$row) {
            echo '<div class="alert alert-danger">Customer not found.</div>';
            exit();
        }

    $roleId = isset($_SESSION['roleid']) ? (int)$_SESSION['roleid'] : 0;
    $isSuperAdmin = ($roleId === 4);

        // Layout: two columns when Super Admin, otherwise simple single column
        echo '<div class="container-fluid">';
        if ($isSuperAdmin) {
            echo '<div class="row">';
            // LEFT: Customer info + Orders (wider 2:3 ratio)
            echo '<div class="col-lg-5 col-md-5">';
            echo '<form id="profileForm">';
            echo '<input type="hidden" name="CustomerID" value="'.htmlspecialchars($row['CustomerID']).'">';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="mb-3"><label class="form-label">Name</label><input type="text" name="CustomerName" class="form-control" value="'.htmlspecialchars($row['CustomerName']).'"></div>';
            echo '<div class="mb-3"><label class="form-label">Address</label><input type="text" name="CustomerAddress" class="form-control" value="'.htmlspecialchars($row['CustomerAddress']).'"></div>';
            echo '<div class="mb-3"><label class="form-label">Contact</label><input type="text" name="CustomerContact" class="form-control" value="'.htmlspecialchars($row['CustomerContact']).'"></div>';
            echo '</div>';
            echo '<div class="col-md-6">';
            echo '<div class="mb-3"><label class="form-label">Info</label><textarea name="CustomerInfo" class="form-control">'.htmlspecialchars($row['CustomerInfo']).'</textarea></div>';
            echo '<div class="mb-3"><label class="form-label">Notes</label><textarea name="Notes" class="form-control">'.htmlspecialchars($row['Notes']).'</textarea></div>';
            echo '</div>';
            echo '</div>';
            echo '</form>';
            // Orders below the form
            echo '<hr>';
            echo '<div class="mt-2">';
            echo '<h5><i class="fas fa-receipt me-2"></i>Orders</h5>';
            echo '<div class="table-responsive" style="max-height:40vh; overflow-y:auto;">';
            echo '<table class="table table-sm align-middle mb-0">';
            echo '<thead class="table-light"><tr><th>Product</th><th>Brand</th><th>Qty</th><th>Ordered At</th></tr></thead>';
            echo '<tbody id="ordersTableBodyProfile"><tr><td colspan="4" class="text-center">Loading orders...</td></tr></tbody>';
            echo '</table>';
            echo '</div>';
            echo '</div>';
            echo '</div>'; // end LEFT col

            // RIGHT: Medical history within a scrollable card
            echo '<div class="col-lg-7 col-md-7">';
            echo '<div class="card h-100">';
            echo '<div class="card-header d-flex justify-content-between align-items-center">';
            echo '<h5 class="mb-0"><i class="fas fa-notes-medical me-2"></i>Medical History</h5>';
            echo '<button type="button" id="newMedicalBtn" data-customer-id="'.htmlspecialchars($customerID).'" class="btn btn-sm btn-primary">New Medical History</button>';
            echo '</div>';
            echo '<div class="card-body p-3" id="medicalRecordsArea" style="max-height:65vh; overflow-y:auto;">';
            // Render detailed medical records (embedded style without its own header)
            getMedicalRecords($customerID, true);
            echo '</div>'; // card-body / medicalRecordsArea
            echo '</div>'; // card
            echo '</div>'; // end RIGHT col
            echo '</div>'; // end row
        } else {
            // Non-Super Admin: original single-column layout (form + orders stacked)
            echo '<form id="profileForm">';
            echo '<input type="hidden" name="CustomerID" value="'.htmlspecialchars($row['CustomerID']).'">';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="mb-3"><label class="form-label">Name</label><input type="text" name="CustomerName" class="form-control" value="'.htmlspecialchars($row['CustomerName']).'"></div>';
            echo '<div class="mb-3"><label class="form-label">Address</label><input type="text" name="CustomerAddress" class="form-control" value="'.htmlspecialchars($row['CustomerAddress']).'"></div>';
            echo '<div class="mb-3"><label class="form-label">Contact</label><input type="text" name="CustomerContact" class="form-control" value="'.htmlspecialchars($row['CustomerContact']).'"></div>';
            echo '</div>';
            echo '<div class="col-md-6">';
            echo '<div class="mb-3"><label class="form-label">Info</label><textarea name="CustomerInfo" class="form-control">'.htmlspecialchars($row['CustomerInfo']).'</textarea></div>';
            echo '<div class="mb-3"><label class="form-label">Notes</label><textarea name="Notes" class="form-control">'.htmlspecialchars($row['Notes']).'</textarea></div>';
            echo '</div>';
            echo '</div>';
            echo '</form>';
            echo '<hr>';
            echo '<div class="mt-2">';
            echo '<h5><i class="fas fa-receipt me-2"></i>Orders</h5>';
            echo '<div class="table-responsive" style="max-height:40vh; overflow-y:auto;">';
            echo '<table class="table table-sm align-middle mb-0">';
            echo '<thead class="table-light"><tr><th>Product</th><th>Brand</th><th>Qty</th><th>Ordered At</th></tr></thead>';
            echo '<tbody id="ordersTableBodyProfile"><tr><td colspan="4" class="text-center">Loading orders...</td></tr></tbody>';
            echo '</table>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        exit();
    }

    // Endpoint to return rendered medical records (so profile modal can load them)
    if (isset($_GET['action']) && $_GET['action'] === 'getCustomerMedicalRecords' && isset($_GET['customerID'])) {
        header('Content-Type: text/html; charset=utf-8');
        $embed = isset($_GET['embed']) && $_GET['embed'] == '1';
        getMedicalRecords($_GET['customerID'], $embed);
        exit();
    }

    function handleCustomerForm() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST["name"];
            $address = $_POST["address"];
            $phone = $_POST["phone"];
            $info = $_POST["info"];
            $notes = $_POST["notes"];
    
            // Initialize messages
            $errorMessage = "";
            $successMessage = "";
    
            // Validate inputs
            if (empty($name) || empty($address) || empty($phone) || empty($info) || empty($notes)) {
                $errorMessage = 'All the fields are required';
            } else {
                // Call the function to insert data
                insertData($name, $address, $phone, $info, $notes);
                $successMessage = "Customer added successfully"; 
    
                // Clear the form fields after submission
                $name = "";
                $address = "";
                $phone = "";
                $info = "";
                $notes = "";
            }
    
            // Return messages for further handling (e.g., displaying in the original page)
            return [$errorMessage, $successMessage];
        }
    }
    
    function insertData($name,$address,$phone,$info,$notes)
    {
        $conn = connect(); 
        $id = generate_CustomerID();   
        $upd_by = $_SESSION["full_name"];
        $employee_id = $_SESSION["id"];
        $sql = "INSERT INTO customer 
                (CustomerID,CustomerName,CustomerAddress,CustomerContact,
                CustomerInfo,Notes,Upd_by,Status) 
                VALUES
                ('$id','$name','$address','$phone','$info','$notes','$upd_by','Active')";
        
        mysqli_query($conn, $sql);
        GenerateLogs($employee_id,$id,$name);
    }

    function setStatus($id){
        $conn = connect(); 
        $sql = "UPDATE customer 
            SET Status = 'Inactive' WHERE CustomerID = $id";
        $result = $conn->query($sql);
    }

    function GenerateLogs($employee_id,$id,$name)
    {
        $conn = connect(); 
        $Logsid = generate_LogsID();
        
        $stmt = $conn->prepare("INSERT INTO Logs 
                            (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description, Upd_dt)
                            VALUES
                            (?, ?, ?, 'customer', '3', ?, NOW())");
        $stmt->bind_param("ssss", $Logsid, $employee_id, $id, $name);
        $stmt->execute();
        $stmt->close();
    }
    
    function handleCancellation() {
        if (isset($_POST['confirm_cancel'])) {
            // Execute your cancellation logic here
            // For example, you might want to remove a record from the database
    
            // Redirect to another page
            header('Location: customerRecords.php');
            exit();
        }
    }
    
    //Medical Records

    function getMedicalRecords($customerID, $embed = false) {
        $connection = connect();
    
        $sql = "SELECT * FROM customerMedicalHistory WHERE CustomerID = ? ORDER BY visit_date DESC";
        
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $customerID);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            if (!$embed) {
                echo '<div class="form-container">';
                echo '<div class="d-flex justify-content-between align-items-center mb-4">';
                echo '<h3><i class="fas fa-calendar-check me-2"></i> Medical History Records</h3>';
                echo '<button class="btn btn-primary me-2" data-customer-id="'.$customerID.'">';
                echo '<i class="fas fa-plus me-2"></i> Add Record</button>';
                echo '</div>';
            }
            
            while ($row = $result->fetch_assoc()) {
                echo '<div class="medical-record-card mb-4 p-4 border rounded">';
                echo '<h5 class="mb-4"><i class="fas fa-calendar-day me-2"></i> '.htmlspecialchars($row['visit_date']).'</h5>';
                
                // Basic Information
                echo '<div class="row mb-3">';
                echo '<div class="col-md-6">';
                echo '<label class="form-label">Eye Condition</label>';
                echo '<p class="form-control-static">'.(!empty($row['eye_condition']) ? htmlspecialchars($row['eye_condition']) : 'No record.').'</p>';
                echo '</div>';
                echo '<div class="col-md-6">';
                echo '<label class="form-label">Systemic Diseases</label>';
                echo '<p class="form-control-static">'.(!empty($row['systemic_diseases']) ? htmlspecialchars($row['systemic_diseases']) : 'No record.').'</p>';
                echo '</div>';
                echo '</div>';
                
                // Visual Acuity
                echo '<div class="row mb-3">';
                echo '<div class="col-md-4">';
                echo '<label class="form-label">Visual Acuity (Right)</label>';
                echo '<p class="form-control-static">'.(!empty($row['visual_acuity_right']) ? htmlspecialchars($row['visual_acuity_right']) : 'No record.').'</p>';
                echo '</div>';
                echo '<div class="col-md-4">';
                echo '<label class="form-label">Visual Acuity (Left)</label>';
                echo '<p class="form-control-static">'.(!empty($row['visual_acuity_left']) ? htmlspecialchars($row['visual_acuity_left']) : 'No record.').'</p>';
                echo '</div>';
                echo '<div class="col-md-4">';
                echo '<label class="form-label">Pupillary Distance (mm)</label>';
                echo '<p class="form-control-static">'.(!empty($row['pupillary_distance']) ? htmlspecialchars($row['pupillary_distance']) : 'No record.').'</p>';
                echo '</div>';
                echo '</div>';
                
                // Intraocular Pressure
                echo '<div class="row mb-3">';
                echo '<div class="col-md-6">';
                echo '<label class="form-label">Intraocular Pressure - Right (mmHg)</label>';
                echo '<p class="form-control-static">'.(!empty($row['intraocular_pressure_right']) ? htmlspecialchars($row['intraocular_pressure_right']) : 'No record.').'</p>';
                echo '</div>';
                echo '<div class="col-md-6">';
                echo '<label class="form-label">Intraocular Pressure - Left (mmHg)</label>';
                echo '<p class="form-control-static">'.(!empty($row['intraocular_pressure_left']) ? htmlspecialchars($row['intraocular_pressure_left']) : 'No record.').'</p>';
                echo '</div>';
                echo '</div>';
                
                // Refraction
                echo '<div class="row mb-3">';
                echo '<div class="col-md-6">';
                echo '<label class="form-label">Refraction (Right)</label>';
                echo '<p class="form-control-static">'.(!empty($row['refraction_right']) ? htmlspecialchars($row['refraction_right']) : 'No record.').'</p>';
                echo '</div>';
                echo '<div class="col-md-6">';
                echo '<label class="form-label">Refraction (Left)</label>';
                echo '<p class="form-control-static">'.(!empty($row['refraction_left']) ? htmlspecialchars($row['refraction_left']) : 'No record.').'</p>';
                echo '</div>';
                echo '</div>';
                
                // Medications and Allergies
                echo '<div class="row mb-3">';
                echo '<div class="col-md-6">';
                echo '<label class="form-label">Current Medications</label>';
                echo '<p class="form-control-static">'.(!empty($row['current_medications']) ? nl2br(htmlspecialchars($row['current_medications'])) : 'No record.').'</p>';
                echo '</div>';
                echo '<div class="col-md-6">';
                echo '<label class="form-label">Allergies</label>';
                echo '<p class="form-control-static">'.(!empty($row['allergies']) ? nl2br(htmlspecialchars($row['allergies'])) : 'No record.').'</p>';
                echo '</div>';
                echo '</div>';
                
                // Family History and Previous Surgeries
                echo '<div class="row mb-3">';
                echo '<div class="col-md-6">';
                echo '<label class="form-label">Family Eye History</label>';
                echo '<p class="form-control-static">'.(!empty($row['family_eye_history']) ? nl2br(htmlspecialchars($row['family_eye_history'])) : 'No record.').'</p>';
                echo '</div>';
                echo '<div class="col-md-6">';
                echo '<label class="form-label">Previous Eye Surgeries</label>';
                echo '<p class="form-control-static">'.(!empty($row['previous_eye_surgeries']) ? nl2br(htmlspecialchars($row['previous_eye_surgeries'])) : 'No record.').'</p>';
                echo '</div>';
                echo '</div>';
                
                // Examinations
                echo '<div class="row mb-3">';
                echo '<div class="col-md-6">';
                echo '<label class="form-label">Corneal Topography</label>';
                echo '<p class="form-control-static">'.(!empty($row['corneal_topography']) ? nl2br(htmlspecialchars($row['corneal_topography'])) : 'No record.').'</p>';
                echo '</div>';
                echo '<div class="col-md-6">';
                echo '<label class="form-label">Fundus Examination</label>';
                echo '<p class="form-control-static">'.(!empty($row['fundus_examination']) ? nl2br(htmlspecialchars($row['fundus_examination'])) : 'No record.').'</p>';
                echo '</div>';
                echo '</div>';
                
                // Additional Notes
                if (!empty($row['additional_notes'])) {
                    echo '<div class="row mb-3">';
                    echo '<div class="col-12">';
                    echo '<label class="form-label">Additional Notes</label>';
                    echo '<p class="form-control-static">'.nl2br(htmlspecialchars($row['additional_notes'])).'</p>';
                    echo '</div>';
                    echo '</div>';
                }
                
                echo '</div>'; // Close medical-record-card
            }
            if (!$embed) { echo '</div>'; } // Close form-container only when not embedded
        } else {
            if (!$embed) {
                echo '<div class="d-flex justify-content-between align-items-center mb-4">';
                echo '<h3><i class="fas fa-calendar-check me-2"></i> Medical History Records</h3>';
                    echo '<button class="btn btn-primary me-2" data-customer-id="'.$customerID.'">';
                echo '<i class="fas fa-plus me-2"></i> Add Record</button>';
                echo '</div>';
                echo '<div class="alert alert-info">No medical records found for this customer.</div>';
            } else {
                echo '<div class="alert alert-info">No medical records found for this customer.</div>';
            }
        }
        $stmt->close();
        $connection->close();
    }

    function addMedicalRecords($customerID) {
        $conn = connect();
        
    }
?>