<?php
/**
 * ActivityTracker
 *
 * - Manages session inactivity timeout on the server side.
 * - Provides a helper to render the client-side inactivity JS.
 *
 * Usage:
 * include 'ActivityTracker.php';
 * // inside HTML page where you want the client-side timeout script to run:
 * echo renderActivityTrackerScript();
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session timeout in seconds (15 minutes)
$timeout_duration = 900;

// If user has been inactive for longer than timeout, destroy session and redirect to login.
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration)) {
    session_unset();
    session_destroy();

    // If headers are not sent, perform a PHP redirect. If headers already sent, emit a JS redirect.
    if (!headers_sent()) {
        header('Location: login.php');
        exit();
    }

    echo '<script>window.location.href = "login.php";</script>';
    exit();
}

// Update last activity timestamp
$_SESSION['LAST_ACTIVITY'] = time();

/**
 * Returns the client-side inactivity script. Call this from within the page HTML.
 * @return string
 */
function renderActivityTrackerScript()
{
    // Use the same timeout value (in milliseconds) on the client as the server uses (to keep UX consistent)
    $timeout = (defined('ACTIVITY_TIMEOUT_SECONDS') ? ACTIVITY_TIMEOUT_SECONDS : 900);
    $timeout_ms = ((int)$timeout) * 1000;

    $script = <<<JS
<script>
    (function() {
        var timeoutId;
        function logout() {
            window.location.href = 'logout.php';
        }
        function resetTimer() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(logout, $timeout_ms);
        }
        window.addEventListener('load', resetTimer, false);
        window.addEventListener('mousemove', resetTimer, false);
        window.addEventListener('keydown', resetTimer, false);
        window.addEventListener('touchstart', resetTimer, false);
    })();
</script>
JS;

    return $script;
}
