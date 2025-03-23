<?php
session_start();
include_once 'setup.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BVP Santos Optical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="s1.css">
<<<<<<< HEAD
    <link rel="shortcut icon" type="image/x-icon" href="images/logo.png"/>
=======
    <link rel="stylesheet" href="customCodes/custom.css">


>>>>>>> f03f6b39a4d31cce36bf52fd4de417b19fb869ec
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
    .carousel {
        padding-bottom: 80px;
    }
    .carousel-item img {
        width: 100%;
        height: auto;
    }
    .category-btn {
        border: none;
        padding: 10px 20px;
        font-weight: 20px;
        border-radius: 10px;
        width: 180px;
    }
    .best-sellers {
        background-color: #f8a8a8;
    }
    .new-arrivals {
        background-color: #f8f3a8;
    }
    .product-img {
        width: 100%;
        height: auto;
        border-radius: 5px;
        transition: transform 0.3s ease;
        padding-top: 30px;
        padding-bottom: 30px;
    }
    .product-img:hover {
        transform: scale(1.05);
    }
    .see-more-btn {
        border: 2px solid black;
        padding: 10px 20px;
        font-weight: bold;
        background: none;
        cursor: pointer;
        width: 170px;
    }
    .see-more-btn:hover {
        background: black;
        color: white;
    }
    .services-section {
        padding: 50px 0;
    }
    .service-img {
        width: 90%;
        border-radius: 10px;
    }
    .text-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        height: 100%;
    }
    .service-title {
        font-size: 24px;
        font-weight: bold;
        text-align: right;
    }
    .service-text {
        font-size: 16px;
        color: #333;
        margin-bottom: 20px;
        text-align: right;
        padding-bottom: 50px;
    }
    .btn-know-more {
        border: 2px solid black;
        padding: 10px 20px;
        font-weight: bold;
        background: none;
        transition: 0.3s ease;
        width: 200px;
    }
    .btn-know-more:hover {
        background: black;
        color: white;
    }
    .about-section {
        padding: 60px 0;
        padding-bottom: 40px;
    }
    .about-text {
        font-size: 16px;
        color: #333;
        padding-top: 50px;
        padding-bottom: 50px;
        padding-right: 30px;
        text-align: justify;
    }
    .about img {
        padding-bottom: 200px;
        padding-top: 30px;
    }
    .btn-see-all {
        border: 2px solid black;
        padding: 10px 20px;
        font-weight: bold;
        background: none;
        transition: 0.3s ease;
        display: inline-flex;
        align-items: center;
        text-decoration: none;
        color: black;
    }
    .btn-see-all:hover {
        background: black;
        color: white;
    }
    .btn-see-all span {
        margin-left: 10px;
        font-weight: bold;
    }
        </style>
    </head>
    <body>
<<<<<<< HEAD
<header>
        <nav class="navbar navbar-expand-lg bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="demo1.html">
                    <img src="logo.png" alt="Logo"> BVP Santos Optical
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="#">PRODUCTS</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">COLLECTIONS</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">PACKAGE</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">ABOUT</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div id="eyeglassCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="cp3.png" class="d-block w-90" alt="Eyeglass Sale 1">
                </div>
                <div class="carousel-item">
                    <img src="cp2.png" class="d-block w-90" alt="Eyeglass Sale 2">
                </div>
                <div class="carousel-item">
                    <img src="cp1.png" class="d-block w-90" alt="Eyeglass Sale 3">
                </div>
                <div class="carousel-item">
                    <img src="cp4.png" class="d-block w-90" alt="Eyeglass Sale 3">
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

=======
    <?php include "Navigation.php"?> 
>>>>>>> f03f6b39a4d31cce36bf52fd4de417b19fb869ec

<div class="container-fluid py-5" style="background-color: #fdfdfd;" data-aos="fade-up">
        <div class="container text-center my-4">
            <div class="row justify-content-center">
                <div class="col-md-3">
                    <button class="category-btn best-sellers">BEST SELLERS</button>
                </div>
                <div class="col-md-3">
                    <button class="category-btn new-arrivals">NEW ARRIVALS</button>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-4 col-sm-6 text-center">
                    <img src="imgm1.png" alt="Minima M608" class="product-img" data-aos="fade-up">
                </div>
                <div class="col-md-4 col-sm-6 text-center">
                    <img src="imgm2.png" alt="Paul Hueman" class="product-img" data-aos="fade-up" data-aos-delay="200">
                </div>
                <div class="col-md-4 col-sm-6 text-center">
                    <img src="imgm3.png" alt="Paul Hueman PHF" class="product-img" data-aos="fade-up" data-aos-delay="400">
                </div>
            </div>
            <div class="text-center my-4" data-aos="fade-up">
                <button class="see-more-btn">SEE MORE</button>
            </div>
        </div>
    </div>

    <div class="container-fluid py-5" style="background-color: #e0e0e0;" data-aos="fade-up">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <img src="imgs.jpg" alt="Our Services" class="service-img">
                </div>
                <div class="col-lg-6 text-content" data-aos="fade-left">
                    <h2 class="service-title">OUR SERVICES</h2>
                    <p class="service-text">
                        We provide high-quality eyewear with expert consultations, premium lenses, 
                        and stylish frames to match your personality. Explore our wide range of services today!
                    </p>
                    <div class="d-flex justify-content-end">
                        <button class="btn-know-more">KNOW MORE</button>
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
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam viverra fermentum mi, nec 
                        venenatis elit efficitur et. Duis convallis tincidunt libero. Nulla facilisi. Suspendisse potenti.
                    </p>
                    <a href="#" class="btn-see-all">SEE ALL <span>></span></a>
                </div>
                <div class="col-lg-6 text-center" data-aos="fade-left">
                    <img src="imgabt.jpg" alt="About Us Image" class="img-fluid rounded">
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

    <footer class="py-5 border-top mt-5 pt-4" style="background-color: #ffffff; margin-top: 50px; border-color: #ffffff;" data-aos="fade-up">
        <div class="container">
            <div class="row text-center text-md-start">
                <div class="col-md-3 mb-3 mb-md-0 text-center">
                    <img src="logo.png" alt="Logo" width="200">
                </div>

                <div class="col-md-3 mb-3 mb-md-0">
                    <h6 class="fw-bold">PRODUCTS</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-dark text-decoration-none">Frames</a></li>
                        <li><a href="#" class="text-dark text-decoration-none">Sunglasses</a></li>
                        <li><a href="#" class="text-dark text-decoration-none">Package</a></li>
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
        AOS.init();
    </script>
</body>
</html>