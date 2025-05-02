<?php
    include 'ActivityTracker.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BVP Optical Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="customCodes/custom.css">
    <link rel="stylesheet" href="customCodes/s1.css">
    <link rel="stylesheet" href="customCodes/custom.css">
    <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
    <link rel="stylesheet" href="customCodes/s2.css">
    
    <style>
    /* Carousel full-width styling */
    #eyeglassCarousel {
        width: 100%;
        margin: 0 auto;
        margin-top: 20px; /* Added margin to move it down */
    }
    
    .carousel-inner {
        width: 100%;
        height: 100%;
    }
    
    .carousel-item {
        height: 70vh; /* Adjust this value based on your needs */
        min-height: 400px;
    }
    
    .carousel-item img {
        width: 100%;
        height: 100%;
        object-fit: contain; /* Changed from 'cover' to 'contain' to prevent cropping */
        background-color: #f8f9fa; /* Added background color for any empty space */
    }
    
    /* Hide carousel controls */
    .carousel-control-prev,
    .carousel-control-next {
        display: none !important;
    }
    
    /* Product image styling */
    .product-img {
        max-width: 100%;
        height: auto;
        transition: transform 0.3s ease;
    }
    
    .product-img:hover {
        transform: scale(1.05);
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

            <button class="carousel-control-prev" type="button" data-bs-target="#eyeglassCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#eyeglassCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </header>

    <div class="container-fluid py-5" style="background-color: #fdfdfd;" data-aos="fade-up">
        <div class="container text-center my-4">
            <div class="row justify-content-center">
                <div class="col-md-3">
                    <button class="category-btn best-sellers">BEST SELLER</button>
                </div>
                <div class="col-md-3">
                    <button class="category-btn new-arrivals">NEW ARRIVALS</button>
                </div>
            </div>
            <div class="row justify-content-center">
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
            <div class="text-center my-4" data-aos="fade-up">
              <a href="product-gallery.php" button class="see-more-btn">SEE MORE</button>
            </div>
        </div>
    </div>

    <div class="container-fluid py-5" style="background-color: #e0e0e0;" data-aos="fade-up">
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
    </div>

    <div class="container-fluid py-5" style="background-color: white;" data-aos="fade-up">
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
    </div>

    <div class="container-fluid text-center bg-light vh-100 d-flex flex-column justify-content-center" style="background-color: #f8f5f2; height: 100vh;" data-aos="fade-up">
        <h2 class="fw-bold pb-3 pt-5">OUR LOCATION</h2>
        <div class="ratio ratio-16x9 w-75 mx-auto">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d768.9767236191218!2d120.95072416949931!3d14.6581210991147!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b452e915147b%3A0x910e6ce82d8b5bd7!2sSantos%20Optical!5e1!3m2!1sen!2sph!4v1741535090106!5m2!1sen!2sph" 
                class="w-100 h-100 border-0" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
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
                    <h6 class="fw-bold">POLICY</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-dark text-decoration-none">FAQ</a></li>
                        <li><a href="#" class="text-dark text-decoration-none">Store Policy</a></li>
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
            
            // paulit ulit like the way I miss her
            var myCarousel = document.querySelector('#eyeglassCarousel');
            var carousel = new bootstrap.Carousel(myCarousel, {
                interval: 3000,
                wrap: true
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        
        var myCarousel = document.querySelector('#eyeglassCarousel');
        var carousel = new bootstrap.Carousel(myCarousel, {
            interval: 3000,  // pang adjust sa time nang pag slide
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
