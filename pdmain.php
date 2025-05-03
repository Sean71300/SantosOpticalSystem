<?php
    include 'ActivityTracker.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BVP Optical Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="customCodes/custom.css">
    <link rel="stylesheet" href="customCodes/s1.css">
    <link rel="stylesheet" href="customCodes/s2.css">
    <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png">
    
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
        background-color: #f5f5f5;
    }
    
    .carousel-item img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 20px;
    }
    
    /* Hide carousel controls */
    .carousel-control-prev,
    .carousel-control-next {
        display: none !important;
    }
    
    /* Improved Product section */
    .product-section {
        padding: 3rem 0;
        background-color: #fdfdfd;
    }
    
    .category-btn {
        padding: 10px 25px;
        margin: 0 10px 20px;
        border: none;
        border-radius: 30px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .best-sellers {
        background-color: #2c3e50;
        color: white;
    }
    
    .new-arrivals {
        background-color: #e74c3c;
        color: white;
    }
    
    .product-img {
        max-width: 90%;
        height: auto;
        transition: transform 0.3s ease;
        margin-bottom: 20px;
    }
    
    .product-img:hover {
        transform: scale(1.05);
    }
    
    .see-more-btn {
        display: inline-block;
        padding: 10px 30px;
        background-color: #3498db;
        color: white;
        text-decoration: none;
        border-radius: 30px;
        font-weight: 600;
        transition: background-color 0.3s;
    }
    
    .see-more-btn:hover {
        background-color: #2980b9;
        color: white;
    }
    
    /* Services section improvements */
    .service-section {
        padding: 4rem 0;
        background-color: #f0f0f0;
    }
    
    .service-img {
        width: 100%;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .service-title {
        font-size: 2rem;
        color: #2c3e50;
        margin-bottom: 1.5rem;
        font-weight: 700;
    }
    
    .service-text {
        font-size: 1.1rem;
        line-height: 1.6;
        color: #555;
        margin-bottom: 2rem;
    }
    
    .nav-button {
        padding: 10px 25px;
        background-color: #2c3e50;
        color: white;
        text-decoration: none;
        border-radius: 30px;
        font-weight: 600;
        transition: background-color 0.3s;
    }
    
    .nav-button:hover {
        background-color: #1a252f;
        color: white;
    }
    
    /* About section improvements */
    .about-section {
        padding: 4rem 0;
    }
    
    .about-section h2 {
        color: #2c3e50;
        font-size: 2rem;
        margin-bottom: 1.5rem;
    }
    
    .about-text {
        font-size: 1.1rem;
        line-height: 1.6;
        color: #555;
        margin-bottom: 2rem;
    }
    
    .btn-see-all {
        display: inline-block;
        padding: 10px 25px;
        background-color: #3498db;
        color: white;
        text-decoration: none;
        border-radius: 30px;
        font-weight: 600;
        transition: background-color 0.3s;
    }
    
    .btn-see-all:hover {
        background-color: #2980b9;
        color: white;
    }
    
    .btn-see-all span {
        margin-left: 5px;
    }
    
    /* Location section improvements */
    .location-section {
        padding: 4rem 0;
        background-color: #f8f5f2;
    }
    
    .location-section h2 {
        color: #2c3e50;
        font-size: 2rem;
        margin-bottom: 2rem;
    }
    
    .map-container {
        width: 90%;
        margin: 0 auto;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    /* Footer improvements */
    footer {
        padding: 3rem 0 0;
        background-color: #ffffff;
    }
    
    footer h6 {
        font-size: 1.1rem;
        color: #2c3e50;
        margin-bottom: 1.2rem;
    }
    
    footer ul li {
        margin-bottom: 0.8rem;
    }
    
    footer a {
        transition: color 0.3s;
    }
    
    footer a:hover {
        color: #3498db !important;
    }
    
    .copyright {
        padding: 1.5rem 0;
        background-color: white;
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
            min-height: 250px;
        }
        
        .service-section, 
        .about-section,
        .location-section {
            padding: 3rem 0;
        }
    }
    
    @media (max-width: 576px) {
        .carousel-item {
            height: 30vh;
        }
        
        .product-img {
            max-width: 80%;
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
                    <img src="Images/cp3.png" class="d-block w-100" alt="Eyeglass Sale 1">
                </div>
                <div class="carousel-item">
                    <img src="Images/cp2.png" class="d-block w-100" alt="Eyeglass Sale 2">
                </div>
                <div class="carousel-item">
                    <img src="Images/cp1.png" class="d-block w-100" alt="Eyeglass Sale 3">
                </div>
                <div class="carousel-item">
                    <img src="Images/cp4.png" class="d-block w-100" alt="Eyeglass Sale 4">
                </div>
            </div>
        </div>
    </header>

    <section class="product-section" data-aos="fade-up">
        <div class="container">
            <div class="row justify-content-center mb-4">
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
                    <img src="Images/imgm2.png" alt="Paul Hueman" class="product-img" data-aos="fade-up" data-aos-delay="200">
                </div>
                <div class="col-md-4 col-sm-6 text-center">
                    <img src="Images/imgm3.png" alt="Paul Hueman PHF" class="product-img" data-aos="fade-up" data-aos-delay="400">
                </div>
            </div>
            <div class="text-center mt-3" data-aos="fade-up">
                <a href="product-gallery.php" class="see-more-btn">SEE MORE</a>
            </div>
        </div>
    </section>

    <section class="service-section" data-aos="fade-up">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                    <img src="Images/imgs.jpg" alt="Our Services" class="service-img">
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <h2 class="service-title">OUR SERVICES</h2>
                    <p class="service-text">
                        We provide high-quality eyewear with expert consultations, premium lenses, 
                        and stylish frames to match your personality. Explore our wide range of services today!
                    </p>
                    <div class="text-lg-start text-center">
                        <a href="ourservices.php" class="nav-button">KNOW MORE</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="about-section" data-aos="fade-up">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 class="fw-bold">ABOUT US</h2>
                    <p class="about-text">
                        Know and learn more about PBV Santos Optical!
                    </p>
                    <a href="aboutus.php" class="btn-see-all">SEE ALL <span>></span></a>
                </div>
                <div class="col-lg-6 text-center" data-aos="fade-left">
                    <img src="Images/imgabt.jpg" alt="About Us Image" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <section class="location-section" data-aos="fade-up">
        <div class="container text-center">
            <h2 class="fw-bold mb-5">OUR LOCATION</h2>
            <div class="ratio ratio-16x9 map-container">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d768.9767236191218!2d120.95072416949931!3d14.6581210991147!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b452e915147b%3A0x910e6ce82d8b5bd7!2sSantos%20Optical!5e1!3m2!1sen!2sph!4v1741535090106!5m2!1sen!2sph" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="row text-center text-md-start">
                <div class="col-md-3 mb-4 mb-md-0 text-center">
                    <img src="Images/logo.png" alt="Logo" width="180" class="mb-3">
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
                    <p class="mb-2">Address: #6 Rizal Avenue Extension, Brgy. San Agustin, Malabon City</p>
                    <p class="mb-2">Phone: 027-508-4792</p>
                    <p class="mb-2">Cell: 0932-844-7068</p>
                    <p class="mb-0">Email: <a href="mailto:Santosoptical@gmail.com" class="text-dark">Santosoptical@gmail.com</a></p>
                </div>
            </div>
            <div class="copyright">
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
            
            // Auto-rotating carousel
            var myCarousel = document.querySelector('#eyeglassCarousel');
            var carousel = new bootstrap.Carousel(myCarousel, {
                interval: 3000,
                wrap: true,
                pause: false
            });
        });
    </script>
</body>
</html>
