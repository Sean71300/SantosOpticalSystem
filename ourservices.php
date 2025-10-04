<?php
include_once 'setup.php'; // Include the setup.php file
include 'ActivityTracker.php';
?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Navbar Example</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="stylesheet" href="customCodes/s1.css">
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
        <link rel="stylesheet" href="customCodes/s2.css">
    </head>
    <body>
        <?php include "Navigation.php"?>        

        <div class="button-container">
            <a href="aboutus.php" class="nav-button">About Us</a>
            <a href="ourservices.php" class="nav-button active">Our Services</a>
        </div>        
        
        <!-- Centered Content Sections -->
        <div class="centered-section">
            <h2>SERVICES</h2>
            <p>At BVP Santos Optical, we are committed to delivering exceptional eye care and customer service. Guided by our core standards, we ensure a consistent and excellent experience across all our branches, providing quality eyewear and professional optical services you can trust. Our team of experienced optometrists and opticians are dedicated to helping you find the perfect vision solution tailored to your needs.</p>
        </div>
        
        <div class="divider"></div>
        
        <div class="centered-section">
            <h2>OUR SERVICES</h2>
            <p>We offer a comprehensive range of optical services including comprehensive eye examinations, prescription glasses, contact lenses fitting, vision therapy, and a wide selection of stylish frames and sunglasses to meet all your vision needs. Our state-of-the-art equipment and modern facilities ensure accurate prescriptions and comfortable fittings for every patient.</p>
        </div>
            
        <!-- Image Sections -->
        <div class="container-fluid">
            <div class="services-section">
                <img src="Images/os1.png" alt="Services Image" class="services-img">
            </div>
        </div>
        
        <!-- Second Image with B2T1 Content -->
        <div class="container-fluid">
            <div class="services2-section">
                <div class="b2t1-overlay">
                    <h2>B2T1</h2>
                    <p>Don't miss out on our exclusive Buy 2, Take 1 promo! When you purchase any two pairs of eyewear, you'll receive a third pair absolutely free. Whether you're looking for stylish frames, prescription glasses, or trendy sunglasses, now is the perfect time to upgrade your eyewear collection while enjoying great savings!</p>
                </div>
                <img src="Images/os2.png" alt="B2T1 Promotion" class="services-img">
            </div>
        </div>

        <div class="container-fluid">
            <div class="services3-section">
                <img src="Images/os3.png" alt="Fast Service" class="services-img">
            </div>
        </div>

        <div class="container-fluid">
            <div class="services4-section">
                <img src="Images/os4.png" alt="Warranty" class="services-img">
            </div>
        </div>

        <div class="container-fluid">
            <div class="services5-section">
                <img src="Images/os5.png" alt="Lens Guarantee" class="services-img">
            </div>
        </div>

        <footer class="py-5 border-top mt-5 pt-4" style="background-color: #ffffff; margin-top: 50px; border-color: #ffffff;">
            <div class="container">
                <div class="row text-center text-md-start">
                    <div class="col-md-3 mb-3 mb-md-0 text-center">
                        <img src="Images/logo.png" alt="Logo" width="200">
                    </div>

                    <div class="col-md-3 mb-3 mb-md-0">
                        <h6 class="fw-bold">PRODUCTS</h6>
                        <ul class="list-unstyled">
                            <li><a href="#" class="text-dark text-decoration-none">Frames</a></li>
                            <li><a href="#" class="text-dark text-decoration-none">Sunglasses</a></li>
                        </ul>
                    </div>

                    <div class="col-md-3 mb-3 mb-md-0">
                        <h6 class="fw-bold">About</h6>
                        <ul class="list-unstyled">
                            <li><a href="aboutus.php" class="text-dark text-decoration-none">About Us</a></li>
                            <li><a href="ourservices.php" class="text-dark text-decoration-none">Services</a></li>
                        </ul>
                    </div>

                    <div class="col-md-3">
                        <h6 class="fw-bold">CONTACT US!</h6>
                        <p class="mb-1">Address: #6 Rizal Avenue Extension, Brgy. San Agustin, Malabon City</p>
                        <p class="mb-1">Phone: 027-508-4792</p>
                        <p class="mb-1">Cell: 0932-844-7068</p>
                        <p>Email: <a href="mailto:Santosoptical@gmail.com" class="text-dark">Santosoptical@gmail.com</a></p>
                    </div>
                </div>
                <div class="container-fluid text-center py-3" style="background-color: white">
                    <p class="m-0">COPYRIGHT &copy; SANTOS OPTICAL co., ltd. ALL RIGHTS RESERVED.</p>
                </div>
            </div>
        </footer>
       
    </body>

</html>
