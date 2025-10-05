<?php
    include 'ActivityTracker.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Santos Optical Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="customCodes/custom.css">
    <link rel="stylesheet" href="customCodes/s1.css">
    <link rel="stylesheet" href="customCodes/custom.css">
    <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
    <link rel="stylesheet" href="customCodes/s2.css">

    <style>
    /* Color Variables */
    :root {
        --primary-yellow: #FFD700;
        --dark-yellow: #FFC107;
        --primary-red: #DC3545;
        --dark-red: #C82333;
        --light-red: #FF6B6B;
        --pure-white: #FFFFFF;
        --off-white: #F8F9FA;
        --light-gray: #E9ECEF;
        --text-dark: #2C3E50;
    }
    
    /* Enhanced Carousel with Brand Colors */
    .carousel-container {
        position: relative;
        width: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }
    
    #eyeglassCarousel {
        width: 100%;
        margin: 0;
        position: relative;
    }
    
    .carousel-inner {
        width: 100%;
        height: 100%;
    }
    
    .carousel-item {
        height: 85vh;
        min-height: 500px;
        max-height: 800px;
        transition: transform 0.5s ease-in-out;
    }
    
    .carousel-item img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        transition: all 0.5s ease-out;
    }
    
    /* Enhanced Vision Statement with Brand Colors */
    .vision-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 215, 0, 0.1));
        backdrop-filter: blur(10px);
        padding: 2rem 0;
        text-align: center;
        z-index: 10;
        border-top: 3px solid var(--primary-yellow);
    }
    
    .vision-text {
        font-family: 'Georgia', serif;
        font-size: 2.8rem;
        font-weight: 300;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
        position: relative;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    
    .vision-text::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-red), var(--primary-yellow), var(--primary-red));
        border-radius: 2px;
    }
    
    .vision-subtext {
        font-family: 'Arial', sans-serif;
        font-size: 1.1rem;
        color: var(--primary-red);
        letter-spacing: 3px;
        text-transform: uppercase;
        font-weight: 700;
        margin-top: 1rem;
    }
    
    /* Enhanced Product Sections with Brand Colors */
    .product-showcase {
        position: relative;
        overflow: hidden;
        background: var(--pure-white);
    }
    
    .category-btn {
        background: linear-gradient(135deg, var(--primary-yellow), var(--dark-yellow));
        border: none;
        color: var(--text-dark);
        padding: 1rem 2rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.1rem;
        transition: all 0.4s ease;
        box-shadow: 0 8px 25px rgba(255, 215, 0, 0.3);
        position: relative;
        overflow: hidden;
        border: 2px solid transparent;
    }
    
    .category-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        transition: left 0.5s ease;
    }
    
    .category-btn:hover::before {
        left: 100%;
    }
    
    .category-btn:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(255, 215, 0, 0.4);
        /* Removed border-color change */
    }
    
    .product-img {
        max-width: 100%;
        height: auto;
        transition: all 0.5s ease;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(220, 53, 69, 0.1);
        position: relative;
        overflow: hidden;
        border: 3px solid transparent;
    }
    
    .product-img::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), transparent);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .product-img:hover {
        transform: scale(1.08);
        box-shadow: 0 20px 40px rgba(220, 53, 69, 0.2);
        /* Removed border-color change */
    }
    
    .product-img:hover::before {
        opacity: 1;
    }
    
    .see-more-btn {
        display: inline-block;
        background: linear-gradient(135deg, var(--primary-red), var(--dark-red));
        color: var(--pure-white);
        padding: 1rem 2.5rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 700;
        font-size: 1.1rem;
        transition: all 0.4s ease;
        box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
        position: relative;
        overflow: hidden;
        border: 2px solid transparent;
    }
    
    .see-more-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s ease;
    }
    
    .see-more-btn:hover::before {
        left: 100%;
    }
    
    .see-more-btn:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(220, 53, 69, 0.4);
        /* Removed border-color change */
        color: var(--pure-white);
    }
    
    /* Enhanced Service Section with Brand Colors */
    .service-section {
        background: linear-gradient(135deg, var(--off-white), var(--light-gray));
    }
    
    .service-img {
        width: 100%;
        height: 400px;
        object-fit: cover;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(255, 215, 0, 0.2);
        transition: all 0.5s ease;
        border: 3px solid var(--primary-yellow);
    }
    
    .service-img:hover {
        transform: scale(1.02);
        box-shadow: 0 25px 50px rgba(255, 215, 0, 0.3);
    }
    
    .service-title {
        font-size: 3rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 1.5rem;
        position: relative;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    
    .service-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 100px;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-yellow), var(--primary-red));
        border-radius: 2px;
    }
    
    .service-text {
        font-size: 1.2rem;
        line-height: 1.8;
        color: var(--text-dark);
        margin-bottom: 2rem;
    }
    
    .nav-button {
        display: inline-block;
        background: linear-gradient(135deg, var(--primary-yellow), var(--dark-yellow));
        color: var(--text-dark);
        padding: 1rem 2rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 700;
        transition: all 0.4s ease;
        box-shadow: 0 8px 25px rgba(255, 215, 0, 0.3);
        border: 2px solid transparent;
    }
    
    .nav-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(255, 215, 0, 0.4);
        /* Removed border-color change */
        color: var(--text-dark);
    }
    
    /* Enhanced About Section */
    .about-section {
        background: var(--pure-white);
    }
    
    .about-text {
        font-size: 1.3rem;
        color: var(--text-dark);
        margin-bottom: 2rem;
        line-height: 1.6;
    }
    
    .btn-see-all {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(135deg, var(--primary-red), var(--dark-red));
        color: var(--pure-white);
        padding: 1rem 2rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 700;
        transition: all 0.4s ease;
        box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
        border: 2px solid transparent;
    }
    
    .btn-see-all span {
        margin-left: 0.5rem;
        transition: transform 0.3s ease;
    }
    
    .btn-see-all:hover span {
        transform: translateX(5px);
    }
    
    .btn-see-all:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(220, 53, 69, 0.4);
        /* Removed border-color change */
        color: var(--pure-white);
    }
    
    /* Enhanced Location Section */
    .location-section {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, var(--off-white), var(--light-gray));
    }
    
    .location-title {
        font-size: 3.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-red), var(--primary-yellow));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    
    .ratio-16x9 {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(220, 53, 69, 0.15);
        transition: all 0.5s ease;
        border: 3px solid var(--primary-yellow);
    }
    
    .ratio-16x9:hover {
        transform: scale(1.02);
        box-shadow: 0 30px 60px rgba(220, 53, 69, 0.25);
    }
    
    /* Floating Elements */
    .floating-element {
        animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-20px);
        }
    }
    
    /* Particle Background with Brand Colors */
    .particles {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
    }
    
    .particle {
        position: absolute;
        border-radius: 50%;
        opacity: 0.3;
        animation: float 8s ease-in-out infinite;
    }
    
    .particle.yellow {
        background: radial-gradient(circle, var(--primary-yellow), var(--dark-yellow));
    }
    
    .particle.red {
        background: radial-gradient(circle, var(--primary-red), var(--dark-red));
    }
    
    /* Section Dividers */
    .section-divider {
        height: 4px;
        background: linear-gradient(90deg, var(--primary-yellow), var(--primary-red), var(--primary-yellow));
        margin: 3rem 0;
        border-radius: 2px;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .vision-text {
            font-size: 2rem;
        }
        
        .service-title {
            font-size: 2.2rem;
        }
        
        .location-title {
            font-size: 2.5rem;
        }
        
        .category-btn, .see-more-btn, .nav-button, .btn-see-all {
            padding: 0.8rem 1.5rem;
            font-size: 1rem;
        }
        
        .product-img:hover {
            transform: scale(1.05);
        }
    }
    
    @media (max-width: 576px) {
        .vision-text {
            font-size: 1.6rem;
        }
        
        .vision-subtext {
            font-size: 0.9rem;
            letter-spacing: 2px;
        }
        
        .service-title {
            font-size: 1.8rem;
        }
        
        .location-title {
            font-size: 2rem;
        }
    }
    </style>
</head>
<body>
    <header>
    <?php include "Navigation.php"?>

        <div class="carousel-container">
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
            
            <!-- Enhanced Vision Statement -->
            <div class="vision-overlay" data-aos="fade-up">
                <div class="container">
                    <div class="vision-text floating-element">We value your sight. We care.</div>
                    <div class="vision-subtext">QUALITY VISION FOR QUALITY LIFE</div>
                </div>
            </div>
        </div>
    </header>

    <!-- Enhanced Product Showcase -->
    <div class="container-fluid py-5 product-showcase" data-aos="fade-up">
        <div class="particles" id="particles-1"></div>
        <div class="container text-center my-4">
            <div class="row justify-content-center mb-5">
                <div class="col-md-3 mb-3">
                    <button class="category-btn best-sellers floating-element">
                        <i class="fas fa-crown me-2"></i>BEST SELLER
                    </button>
                </div>
                <div class="col-md-3 mb-3">
                    <button class="category-btn new-arrivals floating-element" style="animation-delay: 0.5s;">
                        <i class="fas fa-star me-2"></i>NEW ARRIVALS
                    </button>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-4 col-sm-6 text-center mb-4">
                    <img src="Images/imgm1.png" alt="Minima M608" class="product-img" data-aos="fade-up">
                </div>
                <div class="col-md-4 col-sm-6 text-center mb-4">
                    <img src="Images/imgm2.png" alt="Paul Hueman" class="product-img" data-aos="fade-up" data-aos-delay="200">
                </div>
                <div class="col-md-4 col-sm-6 text-center mb-4">
                    <img src="Images/imgm3.png" alt="Paul Hueman PHF" class="product-img" data-aos="fade-up" data-aos-delay="400">
                </div>
            </div>
            <div class="text-center my-4" data-aos="fade-up">
                <a href="product-gallery.php" class="see-more-btn">
                    <i class="fas fa-eye me-2"></i>SEE MORE
                </a>
            </div>
        </div>
    </div>

    <div class="section-divider"></div>

    <!-- Enhanced Services Section -->
    <div class="container-fluid py-5 service-section" data-aos="fade-up">
        <div class="particles" id="particles-2"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <img src="Images/imgs.jpg" alt="Our Services" class="service-img floating-element">
                </div>
                <div class="col-lg-6 text-content" data-aos="fade-left">
                    <h2 class="service-title">OUR SERVICES</h2>
                    <p class="service-text">
                        We provide high-quality eyewear with expert consultations, premium lenses, 
                        and stylish frames to match your personality. Explore our wide range of services today!
                    </p>
                    <div class="d-flex justify-content-end">
                        <a href="ourservices.php" class="nav-button">
                            <i class="fas fa-arrow-right me-2"></i>KNOW MORE
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-divider"></div>

    <!-- Enhanced About Section -->
    <div class="container-fluid py-5 about-section" data-aos="fade-up">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="service-title mb-4">ABOUT US</h2>
                    <p class="about-text">
                        Know and learn more about PBV Santos Optical! Discover our commitment to quality vision care and exceptional customer service since 2001.
                    </p>
                    <a href="aboutus.php" class="btn-see-all">
                        EXPLORE OUR STORY <span>></span>
                    </a>
                </div>
                <div class="col-lg-6 text-center" data-aos="fade-left" data-aos-delay="200">
                    <img src="Images/imgabt.jpg" alt="About Us Image" class="img-fluid rounded floating-element" style="border: 3px solid var(--primary-yellow); animation-delay: 1s;">
                </div>
            </div>
        </div>
    </div>

    <div class="section-divider"></div>

    <!-- Enhanced Location Section -->
    <div class="container-fluid py-5 location-section vh-100 d-flex flex-column justify-content-center" data-aos="fade-up">
        <h2 class="location-title pb-3 pt-5 text-center">OUR LOCATION</h2>
        <div class="ratio ratio-16x9 w-75 mx-auto mt-4">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d768.9767236191218!2d120.95072416949931!3d14.6581210991147!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b452e915147b%3A0x910e6ce82d8b5bd7!2sSantos%20Optical!5e1!3m2!1sen!2sph!4v1741535090106!5m2!1sen!2sph" 
                class="w-100 h-100 border-0" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({
                duration: 1000,
                easing: 'ease-in-out',
                once: true,
                mirror: false
            });
            
            // Enhanced Carousel
            var myCarousel = document.querySelector('#eyeglassCarousel');
            var carousel = new bootstrap.Carousel(myCarousel, {
                interval: 4000,
                wrap: true,
                ride: 'carousel',
                pause: 'hover'
            });
            
            // Create floating particles with brand colors
            function createParticles(containerId, count) {
                const container = document.getElementById(containerId);
                if (!container) return;
                
                for (let i = 0; i < count; i++) {
                    const particle = document.createElement('div');
                    const isYellow = Math.random() > 0.5;
                    particle.className = `particle ${isYellow ? 'yellow' : 'red'}`;
                    particle.style.width = Math.random() * 20 + 5 + 'px';
                    particle.style.height = particle.style.width;
                    particle.style.left = Math.random() * 100 + '%';
                    particle.style.top = Math.random() * 100 + '%';
                    particle.style.animationDelay = Math.random() * 5 + 's';
                    particle.style.animationDuration = Math.random() * 10 + 5 + 's';
                    container.appendChild(particle);
                }
            }
            
            createParticles('particles-1', 12);
            createParticles('particles-2', 8);
            
            // Add hover effects to carousel items
            const carouselItems = document.querySelectorAll('.carousel-item');
            carouselItems.forEach(item => {
                item.addEventListener('mouseenter', () => {
                    item.style.transform = 'scale(1.02)';
                });
                
                item.addEventListener('mouseleave', () => {
                    item.style.transform = 'scale(1)';
                });
            });
            
            // Add scroll-triggered animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);
            
            // Observe all animated elements
            document.querySelectorAll('[data-aos]').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'all 0.8s ease-out';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>
