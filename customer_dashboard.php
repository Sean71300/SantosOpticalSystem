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
        <title>About Us</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="stylesheet" href="customCodes/s1.css">
        <link rel="stylesheet" href="customCodes/s2.css">
        <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
        <style>
            /* Additional styles for new sections */
            .mission-vision-section {
                padding: 60px 0;
                background-color: #fff9e6;
            }
            
            .values-section {
                padding: 60px 0;
                background-color: #fff;
            }
            
            .value-card {
                text-align: center;
                padding: 30px 20px;
                border-radius: 10px;
                transition: transform 0.3s ease;
                height: 100%;
                background-color: white;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05);
                border-top: 4px solid #dc3545;
            }
            
            .value-card:hover {
                transform: translateY(-10px);
                box-shadow: 0 10px 25px rgba(220, 53, 69, 0.15);
            }
            
            .value-icon {
                width: 70px;
                height: 70px;
                margin: 0 auto 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background-color: #ffc107;
            }
            
            .timeline-section {
                padding: 60px 0;
                background-color: #fff9e6;
            }
            
            .timeline-item {
                position: relative;
                padding: 20px 0;
            }
            
            .timeline-content {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 3px 10px rgba(0,0,0,0.08);
                border-left: 4px solid #dc3545;
            }
            
            .timeline-item:before {
                content: '';
                position: absolute;
                left: 50%;
                top: 0;
                bottom: 0;
                width: 4px;
                background: #dc3545;
                transform: translateX(-50%);
            }
            
            .timeline-item:nth-child(odd) .timeline-content {
                margin-left: 50%;
                margin-right: 20px;
            }
            
            .timeline-item:nth-child(even) .timeline-content {
                margin-right: 50%;
                margin-left: 20px;
            }
            
            .timeline-year {
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
                background: #dc3545;
                color: white;
                padding: 5px 15px;
                border-radius: 20px;
                font-weight: bold;
                z-index: 1;
            }
            
            .innovation-section {
                padding: 60px 0;
                background: linear-gradient(135deg, #dc3545 0%, #ff6b7a 100%);
                color: white;
            }
            
            .innovation-card {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border-radius: 10px;
                padding: 30px;
                height: 100%;
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
            
            .branch-card {
                border: 2px solid #ffc107;
                border-radius: 10px;
                overflow: hidden;
                transition: transform 0.3s ease;
                background: white;
            }
            
            .branch-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(220, 53, 69, 0.15);
            }
            
            .aboutus-h2 {
                color: #dc3545;
                border-bottom: 3px solid #ffc107;
                padding-bottom: 10px;
                display: inline-block;
            }
            
            @media (max-width: 768px) {
                .timeline-item:before {
                    left: 30px;
                }
                
                .timeline-item:nth-child(odd) .timeline-content,
                .timeline-item:nth-child(even) .timeline-content {
                    margin-left: 60px;
                    margin-right: 0;
                }
                
                .timeline-year {
                    left: 30px;
                }
            }
        </style>
    </head>

    <body>
       
            <?php                
                include 'Navigation.php';
            ?>
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
                                    <dd class="col-sm-9"><?php echo !empty($customer['CustomerName']) ? htmlspecialchars($customer['CustomerName']) : 'No record.'; ?></dd>

                                    <dt class="col-sm-3">Details:</dt>
                                    <dd class="col-sm-9"><?php echo !empty($customer['CustomerInfo']) ? nl2br(htmlspecialchars($customer['CustomerInfo'])) : 'No record.'; ?></dd>

                                    <dt class="col-sm-3">Last Updated By:</dt>
                                    <dd class="col-sm-9"><?php echo !empty($customer['Upd_by']) ? htmlspecialchars($customer['Upd_by']) : 'No record.'; ?></dd>

                                    <dt class="col-sm-3">Last Updated On:</dt>
                                    <dd class="col-sm-9"><?php echo !empty($customer['Upd_dt']) ? htmlspecialchars($customer['Upd_dt']) : 'No record.'; ?></dd>
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
                                                        <td><?php echo !empty($history['visit_date']) ? htmlspecialchars($history['visit_date']) : 'No record.'; ?></td>
                                                        <td><?php echo !empty($history['eye_condition']) ? htmlspecialchars($history['eye_condition']) : 'No record.'; ?></td>
                                                        <td>
                                                            R: <?php echo !empty($history['visual_acuity_right']) ? htmlspecialchars($history['visual_acuity_right']) : 'No record.'; ?><br>
                                                            L: <?php echo !empty($history['visual_acuity_left']) ? htmlspecialchars($history['visual_acuity_left']) : 'No record.'; ?>
                                                        </td>
                                                        <td>
                                                            R: <?php echo !empty($history['intraocular_pressure_right']) ? htmlspecialchars($history['intraocular_pressure_right']).' mmHg' : 'No record.'; ?><br>
                                                            L: <?php echo !empty($history['intraocular_pressure_left']) ? htmlspecialchars($history['intraocular_pressure_left']).' mmHg' : 'No record.'; ?>
                                                        </td>
                                                        <td>
                                                            R: <?php echo !empty($history['refraction_right']) ? htmlspecialchars($history['refraction_right']) : 'No record.'; ?><br>
                                                            L: <?php echo !empty($history['refraction_left']) ? htmlspecialchars($history['refraction_left']) : 'No record.'; ?>
                                                        </td>
                                                        <td><?php echo !empty($history['pupillary_distance']) ? htmlspecialchars($history['pupillary_distance']) : 'No record.'; ?></td>
                                                        <td><?php echo !empty($history['additional_notes']) ? htmlspecialchars($history['additional_notes']) : 'No record.'; ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <span class="text-muted">No record.</span>
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