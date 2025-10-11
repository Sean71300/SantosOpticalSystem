<?php
if (session_status() === PHP_SESSION_NONE) {
    // Session has not started; start it
    session_start();
}


// Set session timeout duration (e.g., 5 minutes)
$timeout_duration = 900; // 15 minutes

// Check if the session is set and if the timeout has expired
if (isset($_SESSION['LAST_ACTIVITY'])) {
    if (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration) {
        // Last request was more than 15 minutes ago
        session_unset();     // Unset session variables
        session_destroy();   // Destroy session
        header("Location: login.php"); // Redirect to login page
        exit();
    }
}

// Update last activity time stamp
$_SESSION['LAST_ACTIVITY'] = time();


?>

<?php
// If this is a normal page load, output the small client-side activity script.
// If it's an AJAX request (X-Requested-With: XMLHttpRequest), don't output the script
// because AJAX endpoints should return clean JSON only.
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
    ?>
    <script>
        let timeout; // Variable to track the timeout

        // Function to log out the user
        function logout() {

<?php
// Reusable server-side log helper
function log_action($employeeID, $targetID, $targetType, $activityCode, $description) {
    // defensive: ensure numeric activityCode
    $activityCode = (int)$activityCode;
    $conn = null;
    try {
        $conn = connect();
        // Prefer explicit LogsID generation if helper exists, otherwise omit
        if (function_exists('generate_LogsID')) {
            $logsId = generate_LogsID();
            $sql = "INSERT INTO Logs (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description, Upd_dt) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('iiisis', $logsId, $employeeID, $targetID, $targetType, $activityCode, $description);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            $sql = "INSERT INTO Logs (EmployeeID, TargetID, TargetType, ActivityCode, Description, Upd_dt) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('iisis', $employeeID, $targetID, $targetType, $activityCode, $description);
                $stmt->execute();
                if ($stmt->errno) {
                    // If insertion failed due to enum restriction (unknown TargetType), try to add the value to the enum
                    $errno = $stmt->errno;
                }
                $stmt->close();
            }
        }
        // If we detected an enum error (unknown value), attempt to alter the Logs table to include the new type then retry once
        if (isset($errno) && $errno == 1366 && $targetType) {
            try {
                // Read current enum definition and attempt to append the new value 'branch'
                $alter = "ALTER TABLE Logs MODIFY COLUMN TargetType ENUM('customer','employee','product','order','branch') NOT NULL";
                $conn->query($alter);
                // retry insert
                $sql2 = "INSERT INTO Logs (EmployeeID, TargetID, TargetType, ActivityCode, Description, Upd_dt) VALUES (?, ?, ?, ?, ?, NOW())";
                $stmt2 = $conn->prepare($sql2);
                if ($stmt2) {
                    $stmt2->bind_param('iisis', $employeeID, $targetID, $targetType, $activityCode, $description);
                    $stmt2->execute();
                    $stmt2->close();
                }
            } catch (Exception $e) {
                // ignore
            }
        }
    } catch (Exception $e) {
        // swallow errors to avoid failing the primary action; consider logging to file if desired
    } finally {
        if ($conn) $conn->close();
    }
}

            window.location.href = 'logout.php'; // Redirect to logout script
        }

        // Function to reset the inactivity timer
        function resetTimer() {
            clearTimeout(timeout);
            timeout = setTimeout(logout, 900000); // 900000 ms = 15 minutes
        }

        // Event listeners for user activity
        window.onload = resetTimer; // Reset timer on page load
        window.onmousemove = resetTimer; // Reset timer on mouse movement
        window.onkeypress = resetTimer; // Reset timer on key press
    </script>
    <?php
}
