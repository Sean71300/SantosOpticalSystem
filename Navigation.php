<?php
$current_page = basename($_SERVER['PHP_SELF']);

// If the current user is an employee/admin/super-admin, don't render the public navigation.
$hideNav = false;
$showTrackOrder = true;
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
if (isset($_SESSION['user_type'])) {
    $ut = strtolower((string)$_SESSION['user_type']);
    if ($ut === 'employee' || $ut === 'admin') {
        $hideNav = true;
        $showTrackOrder = false;
    }
}
if (isset($_SESSION['roleid'])) {
    $rid = (int)$_SESSION['roleid'];
    if (in_array($rid, [1,2], true)) {
        $hideNav = true;
        $showTrackOrder = false;
    }
}
if (isset($_SESSION['role'])) {
    $rname = strtolower((string)$_SESSION['role']);
    if (in_array($rname, ['admin', 'super admin', 'superadmin', 'owner'], true)) {
        $hideNav = true;
        $showTrackOrder = false;
    }
}
?>

<style>
/* Reset and consistent header styling */
.forNavigationbar {
    position: sticky;
    top: 0;
    z-index: 1030;
    background-color: #ffffff;
}

.navbar {
    background-color: #ffffff !important;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
    box-shadow: none !important;
}

.navbar-brand {
    font-weight: 700;
    color: #2c3e50 !important;
    font-size: 1.25rem;
}

.navbar-brand img {
    width: 60px;
    height: 80px;
    object-fit: contain;
}

.navbar-nav .nav-link {
    color: #2c3e50 !important;
    font-weight: 500;
    font-size: 1rem;
    padding: 0.5rem 1rem;
    transition: color 0.3s ease;
}

.navbar-nav .nav-link:hover {
    color: #007bff !important;
}

.navbar-nav .nav-link.active {
    position: relative;
    font-weight: 600;
}

.navbar-nav .nav-link.active::after {
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

.navbar-toggler {
    border: none;
    padding: 0.25rem 0.5rem;
}

.navbar-toggler:focus {
    box-shadow: none;
    outline: none;
}

.dropdown-toggle {
    background: none;
    border: none;
    color: #2c3e50 !important;
    font-weight: 500;
    font-size: 1rem;
    padding: 0.5rem 1rem;
}

.dropdown-toggle:hover {
    color: #007bff !important;
}

.dropdown-menu {
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.dropdown-item {
    padding: 0.5rem 1rem;
    color: #2c3e50;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    color: #007bff;
}

.logo {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
    margin-left: 5px;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .navbar-nav {
        align-items: flex-start;
        padding: 1rem 0;
    }
    
    .nav-item {
        width: 100%;
    }
    
    .nav-link {
        padding: 0.75rem 1rem !important;
        border-bottom: 1px solid #f8f9fa;
    }
    
    .dropdown {
        width: 100%;
        margin-left: 0;
        padding: 0;
    }
    
    .dropdown-toggle {
        width: 100%;
        text-align: left;
        padding: 0.75rem 1rem !important;
        border-bottom: 1px solid #f8f9fa;
    }
    
    .dropdown-menu {
        position: static !important;
        transform: none !important;
        width: 100%;
        border: none;
        box-shadow: none;
    }
}

@media (max-width: 768px) {
    .navbar-brand {
        font-size: 1.1rem;
    }
    
    .navbar-brand img {
        width: 50px;
        height: 70px;
    }
    
    .nav-link, .dropdown-toggle {
        font-size: 0.9rem;
    }
}
</style>

<div class="forNavigationbar sticky-top">
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <img src="Images/logo.png" alt="Logo"> 
                Santos Optical
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'face-shape-detector.php') ? 'active' : ''; ?>" href="face-shape-detector.php">DISCOVER YOUR BEST LOOK</a>
                    </li>                
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'product-gallery.php') ? 'active' : ''; ?>" href="product-gallery.php">PRODUCTS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'aboutus.php') ? 'active' : ''; ?>" href="aboutus.php">ABOUT</a>
                    </li> 
                    <?php if ($showTrackOrder): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'trackorder.php') ? 'active' : ''; ?>" href="trackorder.php">TRACK ORDER</a>
                    </li>
                    <?php endif; ?> 
                    <?php  
                    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
                        echo '<li class="nav-item">';
                        echo '<a class="nav-link ' . ($current_page == 'login.php' ? 'active' : '') . '" href="login.php">| Login</a>';
                        echo '</li>';
                    }
                    else {
                        echo '<li class="nav-item dropdown">';
                        echo '<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
                        echo '| ' . htmlspecialchars($_SESSION["full_name"]);
                        
                        if (isset($_SESSION["img"]) && $_SESSION["user_type"] !== 'customer') {
                            echo '<img src="' . $_SESSION["img"] . '" class="logo ms-2">';
                        }
                        
                        echo '</a>';
                        echo '<ul class="dropdown-menu" aria-labelledby="navbarDropdown">';
                        
                        if (isset($_SESSION["user_type"]) && $_SESSION["user_type"] == 'employee') {
                            if (isset($_SESSION["roleid"])) {
                                $rIdLocal = (int)$_SESSION["roleid"];
                                if ($rIdLocal === 4) {
                                    echo '<li><a class="dropdown-item" href="Dashboard.php">Admin Panel</a></li>';
                                } elseif ($rIdLocal === 1) {
                                    echo '<li><a class="dropdown-item" href="Dashboard.php">Admin page</a></li>';
                                } elseif ($rIdLocal === 2) {
                                    echo '<li><a class="dropdown-item" href="Dashboard.php">Employee page</a></li>';
                                }
                            }
                        }
                        else if (isset($_SESSION["role"])) {
                            $rnameLocal = strtolower((string)$_SESSION["role"]);
                            if (in_array($rnameLocal, ['super admin', 'superadmin', 'admin', 'owner'], true)) {
                                echo '<li><a class="dropdown-item" href="Dashboard.php">Admin Panel</a></li>';
                            }
                        }
                        else if (isset($_SESSION["user_type"]) && $_SESSION["user_type"] == 'customer') {
                            echo '<li><a class="dropdown-item" href="customer_dashboard.php">Medical History</a></li>';
                            echo '<li><a class="dropdown-item" href="trackorder.php">Track Order</a></li>';
                        }
                        
                        echo '<li><hr class="dropdown-divider"></li>';
                        echo '<li><a class="dropdown-item" href="logout.php">Log Out</a></li>';
                        echo '</ul>';
                        echo '</li>';
                    }
                    ?>                               
                </ul>        
            </div>                    
        </div>                   
    </nav>
</div>
