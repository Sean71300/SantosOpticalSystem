<div class="forNavigationbar sticky-start">
    <nav class="navbar navbar-expand-lg bg-body-tertiary flex-column align-items-start h-100">
        <div class="container-fluid flex-column h-100">
            <a class="navbar-brand fw-bold my-3 d-flex align-items-center" href="index.php">
                <img src="images/logo.png" alt="Logo" width="64" height="64"> 
                <span class="ms-2">Santos Optical</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse w-100" id="navbarSupportedContent">
                <ul class="navbar-nav flex-column w-100 fs-5 fw-bold">                  
                    <li class="nav-item w-100">
                        <a class="nav-link my-2" href="order.php">ORDER</a>
                    </li>
                    <li class="nav-item w-100">
                        <a class="nav-link my-2" href="product-gallery.php">PRODUCTS</a>
                    </li>
                    <li class="nav-item w-100">
                        <a class="nav-link my-2" href="#">COLLECTIONS</a>
                    </li>
                    <li class="nav-item w-100">
                        <a class="nav-link my-2" href="#">PACKAGE</a>
                    </li>
                    <li class="nav-item w-100">
                        <a class="nav-link my-2" href="aboutus.php">ABOUT</a>
                    </li>                                  
                    <?php  
                    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
                        echo '<li class="nav-item w-100 my-2">';
                        echo '<a class="nav-link" href="Login.php">Login</a>';
                        echo '</li>';
                    }
                    else{     
                    $image = $_SESSION["img"];
                    echo  '<div class="dropdown w-100 my-2">';
                    echo  '<button class="btn dropdown-toggle fs-5 fw-bold w-100 text-start" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">';
                    echo  htmlspecialchars($_SESSION["full_name"]) . '  ';   
                    echo  '<img src="' . $image . '"class="logo">';
                    echo  '</button>';
                    echo  '<ul class="dropdown-menu w-100" aria-labelledby="dropdownMenuButton">';
                    if ($_SESSION["roleid"] == 1)
                    {
                        echo '<li><a class="dropdown-item" href="admin.php">Admin page</a></li>';
                    }
                    else if ($_SESSION["roleid"] == 2)
                    {
                        echo '<li><a class="dropdown-item" href="employee.php">Employee page</a></li>';
                    }
                    echo  '<li><a class="dropdown-item" href="logout.php">Sign Out</a></li>';
                    echo  '</ul>';
                    echo  '</div>';
                    }
                    ?>                               
                </ul>        
            </div>                    
        </div>                   
    </nav>
</div>

<style>
    .forNavigationbar {
        width: 250px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
    }
    
    .logo {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin-left: 10px;
    }
    
    .navbar {
        height: 100%;
    }
    
    .dropdown-menu {
        position: relative !important;
    }
</style>