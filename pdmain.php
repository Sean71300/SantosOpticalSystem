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
    /* Enhanced Carousel styling */
    #eyeglassCarousel {
        width: 100%;
        margin: 0;
    }
    
    .carousel-inner {
        width: 100%;
        height: 100%;
    }
    
    .carousel-item {
        height: 85vh;
        min-height: 500px;
        max-height: 800px;
    }
    
    .carousel-item img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background-color: #f8f9fa;
        padding: 0;
    }
    
    .carousel-control-prev,
    .carousel-control-next {
        display: none !important;
    }
    
    .product-img {
        max-width: 100%;
        height: auto;
        transition: transform 0.3s ease;
    }
    
    .product-img:hover {
        transform: scale(1.05);
    }
    
    .carousel-item {
        transition: transform 0.6s ease-in-out;
    }
    
    /* Vision Statement Section - Now Flush with Carousel */
    .vision-statement {
        background-color: #f8f9fa;
        padding: 2rem 0;
        text-align: center;
        margin: 0;
        border-top: 1px solid #e9ecef;
        border-bottom: 1px solid #e9ecef;
    }
    
    .vision-text {
        font-family: 'Georgia', serif;
        font-size: 2.5rem;
        font-weight: 300;
        color: #2c3e50;
        letter-spacing: 1px;
        line-height: 1.3;
        margin-bottom: 0.5rem;
    }
    
    .vision-subtext {
        font-family: 'Arial', sans-serif;
        font-size: 1rem;
        color: #7f8c8d;
        letter-spacing: 2px;
        text-transform: uppercase;
    }
    
    /* Remove all gaps */
    body {
        margin: 0;
        padding: 0;
    }
    
    header {
        margin: 0;
        padding: 0;
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

    <!-- Vision Statement - Now flush with carousel -->
    <div class="vision-statement" data-aos="fade-up">
        <div class="container">
            <div class="vision-text">We value your sight. We care.</div>
            <div class="vision-subtext">QUALITY VISION FOR QUALITY LIFE</div>
        </div>
    </div>

    <!-- Rest of your content remains exactly the same -->
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
            [rest of your existing HTML...]
