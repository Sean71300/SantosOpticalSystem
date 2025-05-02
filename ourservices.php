<?php
include_once 'setup.php'; // Include the setup.php file
include 'ActivityTracker.php';
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Santos Optical - Services</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="stylesheet" href="customCodes/s1.css">
        <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
        <link rel="stylesheet" href="customCodes/s2.css">
        <style>
            body {
                background-color: #f0ebea;
            }
            .navbar {
                padding: 10px 20px;
                position: sticky;
                top: 0;
                background-color: white;
                width: 100%;
                z-index: 1000;
                box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            }
            .navbar-brand {
                display: flex;
                align-items: center;
                font-weight: bold;
                font-size: 1.5rem;
                margin-left: 0;
            }
            .navbar-brand img {
                height: 50px;
                width: 100%;
                margin-right: 10px;
            }
            .navbar-nav {
                margin-right: auto;
            }
            .nav-link {
                font-weight: 400;
                letter-spacing: 1px;
                font-size: 15px;
            }
            .button-container {
                display: flex;
                justify-content: center;
                align-items: center;
                background-color: #444;
                padding: 0;
                width: 100%; 
                margin-top: 20px; 
            }
            .nav-button {
                flex: 1; 
                background-color: #444; 
                color: white;
                font-family: 'Arial', sans-serif;
                font-size: 16px;
                letter-spacing: 1px;
                text-transform: uppercase;
                border: none;
                padding: 15px 0; 
                cursor: pointer;
                text-align: center;
                transition: 0.3s;
                text-decoration: none; 
                display: inline-block; 
            }
            .nav-button + .nav-button { 
                border-left: 2px solid white; 
            }
            .nav-button:hover,
            .nav-button:focus,
            .nav-button.active { 
                background-color: #666;
            }
            
            /* Improved Service Sections */
            .service-container {
                padding: 50px 0;
            }
            .service-section {
                display: flex;
                align-items: center;
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }
            .service-text {
                flex: 1;
                padding: 30px;
            }
            .service-image {
                flex: 1;
                text-align: center;
            }
            .service-image img {
                max-width: 100%;
                height: auto;
                border-radius: 8px;
            }
            
            /* Footer Styles */
            footer {
                font-family: 'Arial', sans-serif;
                color: white;
                line-height: 1.6;
                background-color: #333;
                padding: 40px 0 0;
            }
            footer h5 {
                letter-spacing: 1px;
                font-size: 16px;
                color: #fff;
            }
            footer ul li {
                transition: color 0.3s;
                cursor: pointer;
            }
            footer ul li:hover {
                color: #ccc;
            }
            footer address p {
                margin-bottom: 5px;
            }
            .copyright {
                background-color: #222;
                padding: 15px 0;
                margin-top: 30px;
            }
            
            @media (max-width: 768px) {
                .service-section {
                    flex-direction: column;
                }
                .service-text, 
                .service-image {
                    flex: none;
                    width: 100%;
                }
                footer .col-md-3 {
                    margin-bottom: 20px;
                    text-align: center;
                }
            }
        </style>
    </head>
    <body>
        <?php include "Navigation.php"?>        

        <div class="button-container">
            <a href="aboutus.php" class="nav-button">About Us</a>
            <a href="ourservices.php" class="nav-button">Our Services</a>
        </div>        
        
        <!-- Services Section 1 -->
        <div class="service-container">
            <div class="service-section">
                <div class="service-text">
                    <h2>SERVICES</h2>
                    <p>At BVP Santos Optical, we are committed to delivering exceptional eye care and 
                        customer service. Guided by our core standards, we ensure a consistent and excellent 
                        experience across all our branches, providing quality eyewear and professional optical 
                        services you can trust.</p>
                </div>
                <div class="service-image">
                    <img src="Images/os1.png" alt="Services Image">
                </div>
            </div>
        </div>
        
        <!-- Services Section 2 -->
        <div class="service-container" style="background-color: #f8f9fa;">
            <div class="service-section">
                <div class="service-image">
                    <img src="Images/os2.png" alt="B2T1 Promo">
                </div>
                <div class="service-text">
                    <h2>B2T1</h2>
                    <p>Don't miss out on our exclusive Buy 2, Take 1 promo! When 
                        you purchase any two pairs of eyewear, you'll receive a third 
                        pair absolutely free. Whether you're looking for stylish frames, 
                        prescription glasses, or trendy sunglasses, now is the perfect time 
                        to upgrade your eyewear collection while enjoying great savings!</p>
                </div>
            </div>
        </div>
        
        <!-- Continue with other service sections following the same pattern -->
        
        <!-- Footer -->
        <footer>
            <div class="container">
                <div class="row">
                    <div class="col-md-3 mb-4 text-center">
                        <img src="Images/logo.png" alt="Logo" width="200">
                    </div>
                    <div class="col-md-3 mb-4">
                        <h5>PRODUCTS</h5>
                        <ul class="list-unstyled">
                            <li><a href="#" class="text-white text-decoration-none">Frames</a></li>
                            <li><a href="#" class="text-white text-decoration-none">Sunglasses</a></li>
                        </ul>
                    </div>
                    <div class="col-md-3 mb-4">
                        <h5>ABOUT</h5>
                        <ul class="list-unstyled">
                            <li><a href="aboutus.php" class="text-white text-decoration-none">About Us</a></li>
                            <li><a href="ourservices.php" class="text-white text-decoration-none">Services</a></li>
                        </ul>
                    </div>
                    <div class="col-md-3 mb-4">
                        <h5>CONTACT US</h5>
                        <address>
                            <p>#6 Rizal Avenue Extension, Brgy. San Agustin, Malabon City</p>
                            <p>Phone: 027-508-4792</p>
                            <p>Cell: 0932-844-7068</p>
                            <p>Email: <a href="mailto:Santosoptical@gmail.com" class="text-white">Santosoptical@gmail.com</a></p>
                        </address>
                    </div>
                </div>
            </div>
            <div class="copyright text-center py-3">
                <p class="m-0">COPYRIGHT &copy; SANTOS OPTICAL co., ltd. ALL RIGHTS RESERVED.</p>
            </div>
        </footer>
    </body>
</html>
