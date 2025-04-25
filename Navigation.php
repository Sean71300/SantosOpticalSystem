<div class="forNavigationbar sticky-top">
    <nav class="navbar navbar-expand-lg bg-body-tertiary ">
        <div class="container-fluid ">
        <a class="navbar-brand fw-bold mx-3" href="index.php">
            <img src="images/logo.png" alt="Logo" width="64" height="64"> 
            BVP Santos Optical
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ps-5 fs-5 fw-bold ms-2 mb-lg-0 col d-flex justify-content-end">                  
                <li class ="nav-item">
                    <a class="nav-link m-2" href="order.php">ORDER</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link m-2" href="product-gallery.php">PRODUCTS</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link m-2" href="#">COLLECTIONS</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link m-2" href="#">PACKAGE</a>
                </li>
                <li class="nav-item m-2">
                    <a class="nav-link" href="aboutus.php">ABOUT</a>
                </li>                                  
                <?php  
                if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
                    echo '<li class="nav-item m-2">';
                    echo '<a class="nav-link " href="Login.php">|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Login</a>';
                    echo '</li>';
                }
                else{     
                $image = $_SESSION["img"];
                echo  '<div class="dropdown">';
                echo  '<button class="btn dropdown-toggle fs-5 fw-bold" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">';
                echo  '|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'. htmlspecialchars($_SESSION["full_name"]) . '  ';   
                echo  '<img src="' . $image . '"class="logo">';
                echo  '</button>';
                echo  '<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
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