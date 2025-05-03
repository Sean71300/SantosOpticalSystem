<?php
include 'ActivityTracker.php';
include 'customerFunctions.php'; 
include 'loginChecker.php';
include_once 'setup.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Medical History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Customer Medical Records</h2>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Customer ID</th>
                        <th>Customer Name</th>
                        <th>Visit Date</th>
                        <th>Eye Condition</th>
                        <th>Visual Acuity</th>
                        <th>Additional Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $conn = connect();
                    $query = "SELECT c.CustomerID, c.CustomerName, 
                              m.visit_date, m.eye_condition, 
                              CONCAT(m.visual_acuity_right, '/', m.visual_acuity_left) AS visual_acuity,
                              m.additional_notes
                              FROM customerMedicalHistory m
                              JOIN customer c ON m.CustomerID = c.CustomerID
                              ORDER BY m.visit_date DESC";
                    
                    $result = mysqli_query($conn, $query);

                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['CustomerID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['CustomerName']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['visit_date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['eye_condition']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['visual_acuity']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['additional_notes']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>No medical records found</td></tr>";
                    }
                    
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>