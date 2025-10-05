<?php
$current_page = basename($_SERVER['PHP_SELF']);

// If the current user is an employee/admin/super-admin, don't render the public navigation.
// This prevents employees and admins from viewing or clicking the public site navigation.
// By default show Track Order link; we'll disable it for staff/admins
$hideNav = false;
$showTrackOrder = true;
if (session_status() === PHP_SESSION_NONE) {
    // session should already be started by the including page, but be defensive
    @session_start();
}
if (isset($_SESSION['user_type'])) {
    $ut = strtolower((string)$_SESSION['user_type']);
    if ($ut === 'employee' || $ut === 'admin') {
        $hideNav = true;
        $showTrackOrder = false;
    }
}
// roleid 1 -> admin, 2 -> employee in this app; additionally check any role name
if (isset($_SESSION['roleid'])) {
    $rid = (int)$_SESSION['roleid'];
    if (in_array($rid, [1,2], true)) {
        $hideNav = true;
        $showTrackOrder = false;
    }
}
if (isset($_SESSION['role'])) {
    $rname = strtolower((string)$_SESSION['role']);
    if (in_array($rname, ['admin', 'super admin', 'superadmin'], true)) {
        $hideNav = true;
        $showTrackOrder = false;
    }
}

// Don't completely return; instead conditionally hide specific links (Track Order)
// We'll still render the rest of the navigation for consistency.
// (This keeps the brand and other links visible on admin pages.)
?>

<style>
.navbar .nav-link.active {
    position: relative;
    font-weight: bold;
}

.navbar .nav-link.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 50%;
    height: 3px; 
    background-color: #FFD700; 
    border-radius: 2px;
}

/* Responsive adjustments for dropdown */
@media (max-width: 992px) {
    .navbar-nav {
        align-items: flex-start;
    }
    
    .dropdown {
        margin-left: 0;
        padding: 0.5rem 1rem;
    }
    
    .dropdown-toggle::after {
        margin-left: 0.5em;
    }
}

.logo {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
    margin-left: 5px;
}
/* Slightly lower the username text in the navbar so it lines up better with the bar */
#userDropdown {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transform: translateY(10px);
}
/* Ensure dropdown appears above other elements */
.dropdown-menu { z-index: 2000; }
/* Force visible when JS adds .show (defensive against conflicting CSS) */
.dropdown-menu.show { display: block !important; }
.dropdown-menu { display: none; }
/* Anchor dropdown to its parent nav item and ensure it isn't clipped */
.nav-item.dropdown { position: relative; }
.nav-item.dropdown .dropdown-menu { position: absolute; top: 100%; right: 0; left: auto; min-width: 10rem; pointer-events: auto; }

/* Custom user dropdown (used when logged in). We keep it simple and deterministic. */
.user-dropdown { display: none; position: absolute; right: 0; top: 100%; background: white; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 6px 20px rgba(0,0,0,0.08); padding: 0.25rem 0; z-index: 2500; min-width: 12rem; }
.user-dropdown .dropdown-link { display: block; padding: 0.5rem 1rem; color: #333; text-decoration: none; }
.user-dropdown .dropdown-link:hover { background: #f8f9fa; }
.user-dropdown.show { display: block; }

.user-toggle { cursor: pointer; }
</style>

<div class="forNavigationbar sticky-top">
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold mx-3" href="index.php">
                <img src="Images/logo.png" alt="Logo" width="60" height="80"> 
                Santos Optical
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ps-5 fs-5 fw-bold ms-2 mb-lg-0 col d-flex justify-content-end">
                    <li class="nav-item">
                        <a class="nav-link m-2 <?php echo ($current_page == 'face-shape-detector.php') ? 'active' : ''; ?>" href="face-shape-detector.php">DISCOVER YOUR BEST LOOK</a>
                    </li>                
                    <li class="nav-item">
                        <a class="nav-link m-2 <?php echo ($current_page == 'product-gallery.php') ? 'active' : ''; ?>" href="product-gallery.php">PRODUCTS</a>
                    </li>
                    <li class="nav-item m-2">
                        <a class="nav-link <?php echo ($current_page == 'aboutus.php') ? 'active' : ''; ?>" href="aboutus.php">ABOUT</a>
                    </li> 
                    <?php if ($showTrackOrder): ?>
                    <li class="nav-item">
                        <a class="nav-link m-2 <?php echo ($current_page == 'trackorder.php') ? 'active' : ''; ?>" href="trackorder.php">TRACK ORDER</a>
                    </li>
                    <?php endif; ?> 
                    <?php  
                    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
                        echo '<li class="nav-item m-2">';
                        echo '<a class="nav-link ' . ($current_page == 'login.php' ? 'active' : '') . '" href="login.php">|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Login</a>';
                        echo '</li>';
                    }
                    else {
                        // Deterministic user menu: compute primary link and render a custom toggle/menu
                        $fullNameHtml = htmlspecialchars($_SESSION['full_name'] ?? '');
                        // compute primary label/href similar to before
                        $firstLabel = '';
                        $firstHref = '#';
                        if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'employee') {
                            $rIdLocal = isset($_SESSION['roleid']) ? (int)$_SESSION['roleid'] : 0;
                            switch ($rIdLocal) {
                                case 4: $firstLabel = 'Super Admin Page'; $firstHref = 'Dashboard.php'; break;
                                case 1: $firstLabel = 'Admin Page'; $firstHref = 'Dashboard.php'; break;
                                case 2: $firstLabel = 'Employee Page'; $firstHref = 'Dashboard.php'; break;
                                case 3: $firstLabel = 'Optometrist Page'; $firstHref = 'Dashboard.php'; break;
                                default: $firstLabel = 'Dashboard'; $firstHref = 'Dashboard.php'; break;
                            }
                        } elseif (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer') {
                            $firstLabel = 'Customer Dashboard'; $firstHref = 'customer_dashboard.php';
                        } elseif (isset($_SESSION['role'])) {
                            $rnameLocal = strtolower((string)$_SESSION['role']);
                            if (in_array($rnameLocal, ['super admin', 'superadmin', 'admin'], true)) { $firstLabel = 'Admin Panel'; $firstHref = 'Dashboard.php'; }
                        }

                        echo '<li class="nav-item dropdown">';
                        echo '<a href="#" id="userDropdown" class="nav-link user-toggle" aria-haspopup="true" aria-expanded="false">';
                        echo '|&nbsp;&nbsp;&nbsp;' . $fullNameHtml;
                        if (isset($_SESSION['img']) && $_SESSION['user_type'] !== 'customer') { echo ' <img src="' . htmlspecialchars($_SESSION['img']) . '" class="logo">'; }
                        echo '</a>';

                        // Render a simple menu container (we toggle visibility with JS)
                        echo '<div id="userDropdownMenu" class="user-dropdown">';
                        if ($firstLabel !== '') { echo '<a class="dropdown-link" href="' . htmlspecialchars($firstHref) . '">' . htmlspecialchars($firstLabel) . '</a>'; }
                        echo '<a class="dropdown-link" href="logout.php">Log Out</a>';
                        echo '</div>';
                        echo '</li>';
                    }
                    ?>                               
                </ul>        
            </div>                    
        </div>                   
    </nav>
</div>
        <script>
        // Ensure the Bootstrap dropdown is instantiated and ready to toggle on click.
        document.addEventListener('DOMContentLoaded', function() {
            var userToggle = document.getElementById('userDropdown');
            if (userToggle && typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                try { bootstrap.Dropdown.getOrCreateInstance(userToggle); } catch (e) { console.error('Dropdown init error', e); }
            }
        });
        </script>
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
        <script>
        // DEBUG: print server-side session values to console to help diagnose missing menu items
        try {
            console.groupCollapsed('Navigation debug');
            console.log('session_full_name:', <?php echo json_encode($_SESSION['full_name'] ?? null); ?>);
            console.log('session_user_type:', <?php echo json_encode($_SESSION['user_type'] ?? null); ?>);
            console.log('session_roleid:', <?php echo json_encode(isset($_SESSION['roleid']) ? (int)$_SESSION['roleid'] : null); ?>);
            console.log('session_role:', <?php echo json_encode($_SESSION['role'] ?? null); ?>);
            console.groupEnd();
        } catch(e) { console.error('Nav debug error', e); }
        </script>
        <?php endif; ?>
        <script>
        // Toggle our custom user dropdown
        document.addEventListener('DOMContentLoaded', function(){
            var toggle = document.querySelector('.user-toggle');
            var menu = document.getElementById('userDropdownMenu');
            if (!toggle || !menu) return;
            toggle.addEventListener('click', function(ev){ ev.preventDefault(); ev.stopPropagation(); menu.classList.toggle('show'); });
            // close on outside click
            document.addEventListener('click', function(ev){ if (!toggle.contains(ev.target) && !menu.contains(ev.target)) { menu.classList.remove('show'); } });
        });
        </script>
