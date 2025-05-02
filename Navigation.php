<?php
$current_page = basename($_SERVER['PHP_SELF']);
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
                    <li class="nav-item">
                        <a class="nav-link m-2 <?php echo ($current_page == 'trackorder.php') ? 'active' : ''; ?>" href="trackorder.php">TRACK ORDER</a>
                    </li> 
                    <?php  
                    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
                        echo '<li class="nav-item m-2">';
                        echo '<a class="nav-link ' . ($current_page == 'login.php' ? 'active' : '') . '" href="login.php">|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Login</a>';
                        echo '</li>';
                    }
                    else {
                        echo '<div class="dropdown">';
                        echo '<button class="btn dropdown-toggle fs-5 fw-bold" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">';
                        echo '|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'. htmlspecialchars($_SESSION["full_name"]);
                        
                        // Only show image if it exists (for employees)
                        if (isset($_SESSION["img"]) && $_SESSION["user_type"] !== 'customer') {
                            echo '<img src="' . $_SESSION["img"] . '" class="logo">';
                        }
                        
                        echo '</button>';
                        echo '<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                        
                        // Menu items based on user type
                        if (isset($_SESSION["user_type"]) && $_SESSION["user_type"] == 'employee') {
                            if (isset($_SESSION["roleid"]) && $_SESSION["roleid"] == 1) {
                                echo '<li><a class="dropdown-item" href="Dashboard.php">Admin page</a></li>';
                            } else if (isset($_SESSION["roleid"]) && $_SESSION["roleid"] == 2) {
                                echo '<li><a class="dropdown-item" href="Dashboard.php">Employee page</a></li>';
                            }
                        } 
                        else if (isset($_SESSION["user_type"]) && $_SESSION["user_type"] == 'customer') {
                            echo '<li><a class="dropdown-item" href="customer_dashboard.php">Medical History</a></li>';
                            echo '<li><a class="dropdown-item" href="trackorder.php">Track Order</a></li>';
                        }
                        
                        echo '<li><a class="dropdown-item" href="logout.php">Log Out</a></li>';
                        echo '</ul>';
                        echo '</div>';
                    }
                    ?>                               
                </ul>        
            </div>                    
        </div>                   
    </nav>
</div>