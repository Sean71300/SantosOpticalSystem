<?php
/**
 * ActivityTracker helper
 *
 * This file no longer outputs any HTML or JavaScript on include. It provides:
 * - enforce_session_timeout(): checks session timeout and redirects if expired
 * - render_activity_tracker_script(): outputs the client-side inactivity script (call from pages that render HTML)
 * - log_action(): helper to insert application logs into Logs table
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Enforce session timeout. Call this early on pages to expire idle sessions.
 * If expired, this will destroy the session and redirect to login.php.
 */
function enforce_session_timeout($timeout_seconds = 900) {
    if (isset($_SESSION['LAST_ACTIVITY'])) {
        if (time() - $_SESSION['LAST_ACTIVITY'] > (int)$timeout_seconds) {
            session_unset();
            session_destroy();
            // Safe redirect: nothing has been output by this file
            header("Location: login.php");
            exit();
        }
    }
    $_SESSION['LAST_ACTIVITY'] = time();
}

/**
 * Render the client-side inactivity script. Call this from templates that output HTML
 * (for example, just before </body> or in the header) when you want automatic logout.
 */
function render_activity_tracker_script($timeout_ms = 900000) {
    $timeout_ms = (int)$timeout_ms;
    // Output the script
    echo "<script>\n";
    echo "(function(){\n";
    echo "  var timeout;\n";
    echo "  function logout(){ window.location.href = 'logout.php'; }\n";
    echo "  function resetTimer(){ clearTimeout(timeout); timeout = setTimeout(logout, {$timeout_ms}); }\n";
    echo "  window.addEventListener('load', resetTimer); window.addEventListener('mousemove', resetTimer); window.addEventListener('keypress', resetTimer);\n";
    echo "})();\n";
    echo "</script>\n";
}

/**
 * Reusable server-side log helper
 */
function log_action($employeeID, $targetID, $targetType, $activityCode, $description) {
    $activityCode = (int)$activityCode;
    $conn = null;
    try {
        $conn = connect();
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
                    $errno = $stmt->errno;
                }
                $stmt->close();
            }
        }

        if (isset($errno) && $errno == 1366 && $targetType) {
            try {
                $alter = "ALTER TABLE Logs MODIFY COLUMN TargetType ENUM('customer','employee','product','order','branch') NOT NULL";
                $conn->query($alter);
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
        // swallow errors to avoid failing the primary action
    } finally {
        if ($conn) $conn->close();
    }
}

// Enforce session timeout by default when included
enforce_session_timeout();

?>
