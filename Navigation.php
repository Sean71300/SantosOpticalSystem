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
                        <a class="nav-link m-2 <?php echo ($current_page == 'face-shape-detector.php') ? 'active' : ''; ?>" href="face-shape-detector.php">FACE ANALYZER</a>
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
                    else{     
                        $image = $_SESSION["img"];
                        echo  '<div class="dropdown">';
                        echo  '<button class="btn dropdown-toggle fs-5 fw-bold" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">';
                        echo  '|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'. htmlspecialchars($_SESSION["full_name"]) . '  ';   
                        echo  '<img src="' . $image . '" class="logo">';
                        echo  '</button>';
                        echo  '<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                        if ($_SESSION["roleid"] == 1) {
                            echo '<li><a class="dropdown-item" href="Dashboard.php">Admin page</a></li>';
                        }
                        else if ($_SESSION["roleid"] == 2) {
                            echo '<li><a class="dropdown-item" href="Dashboard.php">Employee page</a></li>';
                        }
                        echo  '<li><a class="dropdown-item" href="logout.php">Log Out</a></li>';
                        echo  '</ul>';
                        echo  '</div>';
                    }
                    ?>                               
                </ul>        
            </div>                    
        </div>                   
    </nav>
</div>
