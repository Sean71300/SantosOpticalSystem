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
