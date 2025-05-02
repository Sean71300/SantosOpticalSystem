<?php
include_once 'connect.php';
include_once 'setup.php';
include 'ActivityTracker.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("Refresh: 0; url=login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Customer Dashboard</title>
        <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>       
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="stylesheet" href="customCodes/s1.css">
        <link rel="stylesheet" href="customCodes/custom.css">
    </head>

    <header>
        <?php include 'Navigation.php'; ?>
    </header>

    <body>
        <div class="container mt-5 mb-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <h2 class="text-center"> Hello there <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
                    <hr>
                    <i class="fa-solid fa-file-medical me-2"></i><h2 class="text-center">Your Medical History</h2>
                    <?php
                        $conn = connect();
                        $customerID = $_SESSION['CustomerID'];
    
                        // Fetch customer basic info
                        $customerInfoQuery = "SELECT CustomerName, CustomerInfo, Upd_by, Upd_dt FROM customer WHERE CustomerID = ?";
                        $stmt = $conn->prepare($customerInfoQuery);
                        $stmt->bind_param("i", $customerID);
                        $stmt->execute();
                        $customerResult = $stmt->get_result();
                        $customer = $customerResult->fetch_assoc();
                        
                        // Fetch medical history
                        $medicalHistoryQuery = "SELECT visit_date, eye_condition, visual_acuity_right, visual_acuity_left, 
                                                intraocular_pressure_right, intraocular_pressure_left, refraction_right, 
                                                refraction_left, pupillary_distance, additional_notes 
                                                FROM customerMedicalHistory 
                                                WHERE CustomerID = ? 
                                                ORDER BY visit_date DESC";
                        $stmtHistory = $conn->prepare($medicalHistoryQuery);
                        $stmtHistory->bind_param("i", $customerID);
                        $stmtHistory->execute();
                        $historyResult = $stmtHistory->get_result();
                    ?>

                    <div class="container mt-3">
                        <!-- Basic Customer Info -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0">Customer Information</h4>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-3">Name:</dt>
                                    <dd class="col-sm-9"><?php echo htmlspecialchars($customer['CustomerName']); ?></dd>

                                    <dt class="col-sm-3">Details:</dt>
                                    <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars($customer['CustomerInfo'])); ?></dd>

                                    <dt class="col-sm-3">Last Updated By:</dt>
                                    <dd class="col-sm-9"><?php echo htmlspecialchars($customer['Upd_by']); ?></dd>

                                    <dt class="col-sm-3">Last Updated On:</dt>
                                    <dd class="col-sm-9"><?php echo htmlspecialchars($customer['Upd_dt']); ?></dd>
                                </dl>
                            </div>
                        </div>

                        <!-- Medical History -->
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h4 class="mb-0">Medical Records</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($historyResult->num_rows > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Visit Date</th>
                                                    <th>Condition</th>
                                                    <th>Visual Acuity</th>
                                                    <th>Eye Pressure</th>
                                                    <th>Refraction</th>
                                                    <th>PD (mm)</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($history = $historyResult->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($history['visit_date']); ?></td>
                                                        <td><?php echo htmlspecialchars($history['eye_condition']); ?></td>
                                                        <td>
                                                            R: <?php echo htmlspecialchars($history['visual_acuity_right']); ?><br>
                                                            L: <?php echo htmlspecialchars($history['visual_acuity_left']); ?>
                                                        </td>
                                                        <td>
                                                            R: <?php echo htmlspecialchars($history['intraocular_pressure_right']); ?> mmHg<br>
                                                            L: <?php echo htmlspecialchars($history['intraocular_pressure_left']); ?> mmHg
                                                        </td>
                                                        <td>
                                                            R: <?php echo htmlspecialchars($history['refraction_right']); ?><br>
                                                            L: <?php echo htmlspecialchars($history['refraction_left']); ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($history['pupillary_distance']); ?></td>
                                                        <td><?php echo htmlspecialchars($history['additional_notes']); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info" role="alert">
                                        No medical history records found.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php
                        // Close connections
                        $stmt->close();
                        $stmtHistory->close();
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
                        <h6 class="fw-bold">About</h6>
                        <ul class="list-unstyled">
                            <li><a href="aboutus.php" class="text-dark text-decoration-none">About Us</a></li>
                            <li><a href="ourservices.php" class="text-dark text-decoration-none">Services</a></li>
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
                <div class="container-fluid text-center py-3" style="background-color: white">
                    <p class="m-0">COPYRIGHT &copy; SANTOS OPTICAL co., ltd. ALL RIGHTS RESERVED.</p>
                </div>
            </div>
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>