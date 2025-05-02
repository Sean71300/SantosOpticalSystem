<?php
include_once 'setup.php';
include 'ActivityTracker.php';
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Santos Optical - Services</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="stylesheet" href="customCodes/s1.css">
        <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
        <style>
            body {
                background-color: #f0ebea;
                font-family: Arial, sans-serif;
            }
            
            /* Navigation styles */
            .navbar {
                padding: 10px 20px;
                position: sticky;
                top: 0;
                background-color: white;
                width: 100%;
                z-index: 1000;
                box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            }
            
            /* Button container */
            .button-container {
                display: flex;
                justify-content: center;
                background-color: #444;
                width: 100%;
                margin-top: 20px;
            }
            
            .nav-button {
                flex: 1;
                background-color: #444;
                color: white;
                padding: 15px 0;
                text-align: center;
                transition: 0.3s;
                text-decoration: none;
                border: none;
                font-size: 16px;
            }
            
            /* Service sections */
            .service-wrapper {
                min-height: 100vh;
                display: flex;
                align-items: center;
                padding: 40px 0;
            }
            
            .service-content {
                display: flex;
                width: 80%;
                max-width: 1200px;
                margin: 0 auto;
                align-items: center;
                gap: 40px;
            }
            
            .service-text {
                flex: 1;
                padding: 20px;
                color: #333;
            }
            
            .service-image {
                flex: 1;
                text-align: center;
            }
            
            .service-image img {
                max-width: 100%;
                height: auto;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            
            /* Responsive adjustments */
            @media (max-width: 768px) {
                .service-content {
                    flex-direction: column;
                    width: 95%;
                }
                
                .service-text {
                    order: 1;
                    padding: 15px 0;
                }
                
                .service-image {
                    order: 2;
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

        <!-- Service Section 1 -->
        <div class="service-wrapper">
            <div class="service-content">
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

        <!-- Service Section 2 -->
        <div class="service-wrapper" style="background-color: #f8f9fa;">
            <div class="service-content">
                <div class="service-text">
                    <h2>B2T1</h2>
                    <p>Don't miss out on our exclusive Buy 2, Take 1 promo! When 
                        you purchase any two pairs of eyewear, you'll receive a third 
                        pair absolutely free. Whether you're looking for stylish frames, 
                        prescription glasses, or trendy sunglasses, now is the perfect time 
                        to upgrade your eyewear collection while enjoying great savings!</p>
                </div>
                <div class="service-image">
                    <img src="Images/os2.png" alt="B2T1 Promo">
                </div>
            </div>
        </div>

        <!-- Continue with other sections following the same pattern -->

    </body>
</html>
