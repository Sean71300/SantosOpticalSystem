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
        <style>
            /* Additional styles for side-by-side layout with text inside images */
            .service-row {
                display: flex;
                align-items: center;
                min-height: 100vh;
                padding: 50px 0;
                position: relative;
            }
            
            .service-image {
                flex: 1;
                position: relative;
                height: 80vh;
                overflow: hidden;
                border-radius: 10px;
                margin: 0 20px;
            }
            
            .service-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            .service-text {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                width: 45%;
                padding: 30px;
                color: #333;
                z-index: 2;
            }
            
            /* Position text on left side for odd sections */
            .service-row:nth-child(odd) .service-text {
                left: 5%;
            }
            
            /* Position text on right side for even sections */
            .service-row:nth-child(even) .service-text {
                right: 5%;
                text-align: right;
            }
            
            /* Add overlay to improve text readability if needed */
            .service-image::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.1);
                z-index: 1;
            }
            
            @media (max-width: 768px) {
                .service-row {
                    flex-direction: column;
                    height: auto;
                    min-height: auto;
                }
                
                .service-image {
                    width: 100%;
                    height: 50vh;
                    margin: 20px 0;
                }
                
                .service-text {
                    position: relative;
                    width: 90%;
                    top: auto;
                    transform: none;
                    left: auto;
                    right: auto;
                    text-align: center;
                    padding: 20px;
                    margin: 0 auto;
                }
                
                .service-row:nth-child(even) .service-text {
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
        <div class="container-fluid">
            <div class="service-row">
                <div class="service-image">
                    <img src="Images/os1.png" alt="Services Image">
                    <div class="service-text">
                        <h2>SERVICES</h2>
                        <p>At BVP Santos Optical, we are committed to delivering exceptional eye care and 
                            customer service. Guided by our core standards, we ensure a consistent and excellent 
                            experience across all our branches, providing quality eyewear and professional optical 
                            services you can trust.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Services Section 2 -->
        <div class="container-fluid">
            <div class="service-row">
                <div class="service-image">
                    <img src="Images/os2.png" alt="Services Image">
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
        </div>

        <!-- Services Section 3 -->
        <div class="container-fluid">
            <div class="service-row">
                <div class="service-image">
                    <img src="Images/os3.png" alt="Services Image">
                    <div class="service-text">
                        <h2>Less than 30 minutes</h2>
                        <p>Get your glasses ready in less than 30 minutes! We understand the value 
                            of your time, which is why our skilled professionals work efficiently 
                            to have your eyewear prepared as quickly as possible. With expertise 
                            and precision, we ensure that your glasses are ready for you in no time 
                            after purchase.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Section 4 -->
        <div class="container-fluid">
            <div class="service-row">
                <div class="service-image">
                    <img src="Images/os4.png" alt="Services Image">
                    <div class="service-text">
                        <h2>2 Years Frame Warranty!</h2>
                        <p>We know how important peace of mind is when it comes to your eyewear. 
                            That's why we offer a 2-year frame warranty to protect your glasses from 
                            manufacturing defects and frame issues. If something goes wrong, 
                            our team is here to fix it — fast, easy, and completely free. 
                            With quality you can count on, you can wear your glasses with 
                            confidence every day.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Section 5 -->
        <div class="container-fluid">
            <div class="service-row">
                <div class="service-image">
                    <img src="Images/os5.png" alt="Services Image">
                    <div class="service-text">
                        <h2>10-D Lens Guarantee</h2>
                        <p>We know how important clear vision and peace of mind are when it comes to your eyewear. 
                            That's why we offer a 10-day lens guarantee — giving you time to make sure your lenses 
                            are just right. If you notice any issues, our team will make it right — quickly, easily, 
                            and at no extra cost. With quality you can trust and support you can count on, you can 
                            see the world with confidence.</p>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'footer.php'; ?>
    </body>
</html>
