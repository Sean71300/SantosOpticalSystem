<?php
    include 'ActivityTracker.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Santos Optical - Premium Eyewear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="customCodes/custom.css">
    <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
    
    <style>
    /* Improved Carousel styling */
    #eyeglassCarousel {
        width: 100%;
        margin: 0 auto;
    }
    
    .carousel-inner {
        width: 100%;
        height: 100%;
    }
    
    .carousel-item {
        height: 60vh;
        min-height: 350px;
    }
    
    .carousel-item img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background-color: #f8f9fa;
    }
    
    /* Product section improvements */
    .product-section {
        padding: 3rem 0;
    }
    
    .category-buttons {
        margin-bottom: 2rem;
    }
    
    .category-btn {
        padding: 0.75rem 2rem;
        font-weight: 600;
        border-radius: 0;
        margin: 0 0.5rem;
    }
    
    .product-img {
        max-width: 100%;
        height: auto;
        transition: transform 0.3s ease;
        margin-bottom: 1.5rem;
    }
    
    .product-img:hover {
        transform: scale(1.05);
    }
    
    .see-more-btn {
        display: inline-block;
        padding: 0.75rem 2rem;
        background-color: #000;
        color: #fff;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .see-more-btn:hover {
        background-color: #333;
        color: #fff;
    }
    
    /* Services section improvements */
    .service-section {
        padding: 4rem 0;
    }
    
    .service-img {
        max-width: 100%;
        height: auto;
        border-radius: 4px;
    }
    
    .service-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
    }
    
    .service-text {
        margin-bottom: 2rem;
        line-height: 1.6;
    }
    
    .nav-button {
        padding: 0.75rem 2rem;
        background-color: #000;
        color: #fff;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
    }
    
    .nav-button:hover {
        background-color: #333;
        color: #fff;
    }
    
    /* About section improvements */
    .about-section {
        padding: 4rem 0;
    }
    
    .about-text {
        margin: 1.5rem 0;
        line-height: 1.6;
    }
    
    .btn-see-all {
        display: inline-block;
        padding: 0.5rem 1.5rem;
        background-color: #000;
        color: #fff;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-see-all:hover {
        background-color: #333;
        color: #fff;
    }
    
    /* Location section improvements */
    .location-section {
        padding: 4rem 0;
        background-color: #f8f5f2;
    }
    
    /* Footer improvements */
    footer {
        padding: 3rem 0 0;
    }
    
    footer h6 {
        font-weight: 700;
        margin-bottom: 1rem;
    }
    
    footer ul li {
        margin-bottom: 0.5rem;
    }
    
    .copyright {
        padding: 1rem 0;
        margin-top: 2rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 992px) {
        .carousel-item {
            height: 50vh;
        }
    }
    
    @media (max-width: 768px) {
        .carousel-item {
            height: 40vh;
            min-height: 300px;
        }
        
        .text-content {
            text-align: center;
            margin-top: 2rem;
        }
        
        .d-flex.justify-content-end {
            justify-content: center !important;
        }
        
        .about-section .col-lg-6:first-child {
            margin-bottom: 2rem;
        }
    }
</style>
</head>
<body>
    <header>
        <?php include "Navigation.php"?>

        <div id="eyeglassCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="Images/cp3.png" class="d-block w-100" alt="Eyeglass Collection">
                </div>
                <div class="carousel-item">
                    <img src="Images/cp2.png" class="d-block w-100" alt="Premium Frames">
                </div>
                <div class="carousel-item">
                    <img src="Images/cp1.png" class="d-block w-100" alt="New Arrivals">
                </div>
                <div class="carousel-item">
                    <img src="Images/cp4.png" class="d-block w-100" alt="Special Offers">
                </div>
            </div>
        </div>
    </header>

    <section class="product-section" style="background-color: #fdfdfd;" data-aos="fade-up">
        <div class="container">
            <div class="row justify-content-center category-buttons">
                <div class="col-auto">
                    <button class="category-btn best-sellers">BEST SELLER</button>
                </div>
                <div class="col-auto">
                    <button class="category-btn new-arrivals">NEW ARRIVALS</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 col-sm-6 text-center">
                    <img src="Images/imgm1.png" alt="Minima M608" class="product-img" data-aos="fade-up">
                </div>
                <div class="col-md-4 col-sm-6 text-center">
                    <img src="Images/imgm2.png" alt="Paul Hueman" class="product-img" data-aos="fade-up" data-aos-delay="100">
                </div>
                <div class="col-md-4 col-sm-6 text-center">
                    <img src="Images/imgm3.png" alt="Paul Hueman PHF" class="product-img" data-aos="fade-up" data-aos-delay="200">
                </div>
            </div>
            <div class="text-center mt-3" data-aos="fade-up">
                <a href="product-gallery.php" class="see-more-btn">SEE MORE</a>
            </div>
        </div>
    </section>

    <section class="service-section" style="background-color: #e0e0e0;" data-aos="fade-up">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <img src="Images/imgs.jpg" alt="Our Services" class="service-img">
                </div>
                <div class="col-lg-6 text-content" data-aos="fade-left">
                    <h2 class="service-title">OUR SERVICES</h2>
                    <p class="service-text">
                        We provide high-quality eyewear with expert consultations, premium lenses, 
                        and stylish frames to match your personality. Explore our wide range of services today!
                    </p>
                    <div class="d-flex justify-content-end">
                        <a href="ourservices.php" class="nav-button">KNOW MORE</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="about-section" style="background-color: white;" data-aos="fade-up">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="fw-bold">ABOUT US</h2>
                    <p class="about-text">
                        Know and learn more about PBV Santos Optical!
                    </p>
                    <a href="aboutus.php" class="btn-see-all">SEE ALL <span>></span></a>
                </div>
                <div class="col-lg-6 text-center" data-aos="fade-left">
                    <img src="Images/imgabt.jpg" alt="About Us Image" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </section>

    <section class="location-section" data-aos="fade-up">
        <div class="container">
            <h2 class="fw-bold text-center mb-4">OUR LOCATION</h2>
            <div class="ratio ratio-16x9">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d768.9767236191218!2d120.95072416949931!3d14.6581210991147!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b452e915147b%3A0x910e6ce82d8b5bd7!2sSantos%20Optical!5e1!3m2!1sen!2sph!4v1741535090106!5m2!1sen!2sph" 
                    class="border-0" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </section>

    <footer class="border-top" style="border-color: #ffffff !important;">
        <div class="container">
            <div class="row text-center text-md-start">
                <div class="col-md-3 mb-4 mb-md-0 text-center">
                    <img src="Images/logo.png" alt="Santos Optical Logo" width="200">
                </div>

                <div class="col-md-3 mb-4 mb-md-0">
                    <h6 class="fw-bold">PRODUCTS</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-dark text-decoration-none">Frames</a></li>
                        <li><a href="#" class="text-dark text-decoration-none">Sunglasses</a></li>
                    </ul>
                </div>

                <div class="col-md-3 mb-4 mb-md-0">
                    <h6 class="fw-bold">ABOUT</h6>
                    <ul class="list-unstyled">
                        <li><a href="aboutus.php" class="text-dark text-decoration-none">About Us</a></li>
                        <li><a href="ourservices.php" class="text-dark text-decoration-none">Services</a></li>
                    </ul>
                </div>

                <div class="col-md-3">
                    <h6 class="fw-bold">CONTACT US</h6>
                    <p class="mb-1">#6 Rizal Avenue Extension, Brgy. San Agustin, Malabon City</p>
                    <p class="mb-1">Phone: 027-508-4792</p>
                    <p class="mb-1">Cell: 0932-844-7068</p>
                    <p>Email: <a href="mailto:Santosoptical@gmail.com" class="text-dark">Santosoptical@gmail.com</a></p>
                </div>
            </div>
            <div class="copyright text-center">
                <p class="m-0">COPYRIGHT &copy; SANTOS OPTICAL co., ltd. ALL RIGHTS RESERVED.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true
            });
            
            var myCarousel = document.querySelector('#eyeglassCarousel');
            var carousel = new bootstrap.Carousel(myCarousel, {
                interval: 3000,
                wrap: true,
                ride: 'carousel',
                pause: false
            });
            
            myCarousel.addEventListener('slid.bs.carousel', function() {
                carousel.cycle();
            });
        });
    </script>
</body>
</html>
