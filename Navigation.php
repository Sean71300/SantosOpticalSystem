<div class="forNavigationbar sticky-top">
    <nav class="navbar navbar-expand-lg bg-body-tertiary ">
        <div class="container-fluid ">
        <a href="index.php"><img src="Images/Logo.jpg" class="logo ms-4 ms-lg-5 "></a>
        <a class="navbar-brand fs-3 " href="index.php">&nbsp;<b>Santos Optical</b></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ps-5 fs-5 fw-bold ms-2 mb-lg-0 col d-flex justify-content-end">                  
                <li class="nav-item m-2">
                <a class="nav-link" href="index.php">Home</a>
                </li>                 
                <li class="nav-item m-2">
                <a class="nav-link" href="About.php">About</a>
                </li>                 
                <li class="nav-item m-2">
                <a class="nav-link"  href="Menu.php">Menu</a>
                </li>                 
                <li class="nav-item m-2">
                <a class="nav-link"  href="Pages.php">Personnel</a>
                </li>                 
                <li class="nav-item m-2">
                <a class="nav-link" href="Contact.php">Contact</a>
                </li>                 
                <li class="nav-item m-2">
                <a class="nav-link" href="posted-rate.php">Rating</a>
                </li>                         
                <li class="nav-item m-2">
                <a class="nav-link" href="Client_Gallery.php">Gallery</a>
                </li>                             
                <li class="nav-item m-2">
                <a class="nav-link" href="#">&nbsp;|&nbsp;</a>
                </li>                  
                <?php  
                if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
                echo '<li class="nav-item m-2">';
                echo '<a class="nav-link " href="Login.php">Login</a>';
                echo '</li>';
                }
                else{     
                $image = base64_encode($_SESSION["img"]);
                echo  '<div class="dropdown">';
                echo  '<button class="btn dropdown-toggle fs-5 fw-bold" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">';
                echo  htmlspecialchars($_SESSION["full_name"]) . '  ';   
                echo  '<img src="data:image/jpeg;base64,' . $image . '"class="logo">';
                echo  '</button>';
                echo  '<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                echo  '<li><a class="dropdown-item" href="edit.php">Account Settings</a></li>';
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