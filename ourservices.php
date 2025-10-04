<?php
include_once 'setup.php'; // Include the setup.php file
include 'ActivityTracker.php';
?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Our Services - Santos Optical</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="stylesheet" href="customCodes/s1.css">
        <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
        <link rel="stylesheet" href="customCodes/s2.css">
    </head>
    <body>
        <?php include "Navigation.php"?>        

        <div class="button-container">
            <a href="aboutus.php" class="nav-button">About Us</a>
            <a href="ourservices.php" class="nav-button">Our Services</a>
        </div>        
            
        <div class="container-fluid">
            <div class="services-section">
                <div class="text-overlay">
                    <h2>SERVICES</h2>
                    <p>At BVP Santos Optical, we are committed to delivering exceptional eye care and 
                        customer service. Guided by our core standards, we ensure a consistent and excellent 
                        experience across all our branches, providing quality eyewear and professional optical 
                        services you can trust.</p>
                </div>
                <img src="Images/os1.png" alt="Services Image" class="services-img">
            </div>
        </div>
        
        <div class="container-fluid">
            <div class="services2-section">
                <div class="text2-overlay">
                    <h2>B2T1</h2>
                    <p>Don't miss out on our exclusive Buy 2, Take 1 promo! When 
                        you purchase any two pairs of eyewear, you'll receive a third 
                        pair absolutely free. Whether you're looking for stylish frames, 
                        prescription glasses, or trendy sunglasses, now is the perfect time 
                        to upgrade your eyewear collection while enjoying great savings!</p>
                </div>
                <img src="Images/os2.png" alt="Services Image" class="services-img">
            </div>
        </div>

        <div class="container-fluid">
            <div class="services3-section">
                <div class="text3-overlay">
                    <h2>Less than 30 minutes</h2>
                    <p>Get your glasses ready in less than 30 minutes! We understand the value 
                        of your time, which is why our skilled professionals work efficiently 
                        to have your eyewear prepared as quickly as possible. With expertise 
                        and precision, we ensure that your glasses are ready for you in no time 
                        after purchase.</p>
                </div>
                <img src="Images/os3.png" alt="Services Image" class="services-img">
            </div>
        </div>

        <div class="container-fluid">
            <div class="services4-section">
                <div class="text4-overlay">
                    <h2>2 Years Frame Warranty!</h2>
                    <p>We know how important peace of mind is when it comes to your eyewear. 
                        That's why we offer a 2-year frame warranty to protect your glasses from 
                        manufacturing defects and frame issues. If something goes wrong, 
                        our team is here to fix it — fast, easy, and completely free. 
                        With quality you can count on, you can wear your glasses with 
                        confidence every day.</p>
                </div>
                <img src="Images/os4.png" alt="Services Image" class="services-img">
            </div>
        </div>

        <div class="container-fluid">
            <div class="services5-section">
                <div class="text5-overlay">
                    <h2>10-D Lens Guarantee</h2>
                    <p>We know how important clear vision and peace of mind are when it comes to your eyewear. 
                        That's why we offer a 10-day lens guarantee — giving you time to make sure your lenses 
                        are just right. If you notice any issues, our team will make it right — quickly, easily, 
                        and at no extra cost. With quality you can trust and support you can count on, you can 
                        see the world with confidence.</p>
                </div>
                <img src="Images/os5.png" alt="Services Image" class="services-img">
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
