<?php
  include_once 'setup.php'; 
  include 'ActivityTracker.php';
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>About Us</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="stylesheet" href="customCodes/s1.css">
        <link rel="stylesheet" href="customCodes/s2.css">
        <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
    </head>
    <body>
        <?php include "Navigation.php"?>
        <div class="button-container">
            <a href="aboutus.php" class="nav-button" data-aos="zoom-in">About Us</a>
            <a href="ourservices.php" class="nav-button" data-aos="zoom-in" data-aos-delay="200">Our Services</a>
        </div>
        
        <div class="container-fluid position-relative px-0">
            <div class="aboutus-section" data-aos="fade-up">
                <img src="Images/au1.png" alt="Services Image" class="services-img">
            </div>
        </div><br><br>

        <div class="container">
            <div class="aboutus2-section">
                <h1>ABOUT US</h1>
                <h5>Santos Optical Clinic emphasizes personalized customer service. 
                    Services they offer include eye examinations and prescription glasses.</h5>
            </div>
        </div><br><br><br>

        <div class="container my-6 doctor-section" style="background-color: aliceblue;" data-aos="slide-left">
            <div class="row align-items-center text-center text-md-start">
              <div class="col-md-4 mb-3 mb-md-2 d-flex justify-content-center">
                <img src="Images/owner.png" alt="Doctor" class="img-fluid rounded shadow">
              </div>
              <div class="col-md-8">
                <p class="fs-4">
                  Santos Optical Clinic, owned by <b> Dr. Bien Ven P. Santos,</b> is a prominent provider of optical services,
                  and has been serving its community since its establishment as a sole proprietorship in 2001.
                </p>
              </div>
            </div>
        </div><br><br> 

        <div class="container" data-aos="fade-up">
          <h2 class="aboutus-h2">SO BRANCHES</h2>
          <div class="row justify-content-center g-4">
              <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="branch-card" data-aos="fade-up" data-aos-delay="100">
                  <div class="image-container">
                    <a href="https://maps.app.goo.gl/8gff1kz1FyvZ6FCK8" target="_blank">
                      <img src="Images/pascual st.jpg" alt="Pascual St, Malabon" class="branch-image">
                      <img src="Images/loc.png" alt="Map Icon" class="map-icon">
                    </a>
                  </div>
                  <div class="branch-info">
                    <strong>SO Santos Optical SBP</strong><br>
                    Near Malabon City Hall<br>
                    Pascual St, Malabon<br>
                    ☎️ 02 88183480<br>
                    <small>Beside 7eleven Malabon City</small>
                  </div>
                </div>
              </div>

              <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="branch-card" data-aos="fade-up" data-aos-delay="200">
                  <div class="image-container">
                    <a href="https://maps.app.goo.gl/FCzggs1hSusNxnMq7" target="_blank">
                      <img src="Images/bayan.jpg" alt="Bayan, Malabon" class="branch-image">
                      <img src="Images/loc.png" alt="Map Icon" class="map-icon">
                    </a>
                  </div>
                  <div class="branch-info">
                    <strong>SO Santos Optical Symaco</strong><br>
                    In front of Mcdonalds<br>
                    Bayan, Malabon<br>
                    ☎️ 02 86321972<br>
                    <small>Infront of MCDO Malabon City</small>
                  </div>
                </div>
              </div>

              <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="branch-card" data-aos="fade-up" data-aos-delay="300">
                  <div class="image-container">
                    <a href="https://maps.app.goo.gl/JGjVQRtRNCQYwkus6" target="_blank">
                      <img src="Images/quiapo.jpg" alt="Quiapo, Manila" class="branch-image">
                      <img src="Images/loc.png" alt="Map Icon" class="map-icon">
                    </a>
                  </div>
                  <div class="branch-info">
                    <strong>SO Santos Optical Quiapo</strong><br>
                    #536 Quiapo Boulevard<br>
                    Manila<br>
                    ☎️ 09328447068<br>
                    <small>Near Reddoorz Hotel opposite side of Quiapo Church</small>
                  </div>
                </div>
              </div>

              <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="branch-card" data-aos="fade-up" data-aos-delay="400">
                  <div class="image-container">
                    <a href="https://maps.app.goo.gl/riu7zr7VhunhW1Tq8" target="_blank">
                      <img src="Images/tangos.png" alt="Tangos, Navotas" class="branch-image">
                      <img src="Images/loc.png" alt="Map Icon" class="map-icon">
                    </a>
                  </div>
                  <div class="branch-info">
                    <strong>SO Santos Optical Tangos</strong><br>
                    Near Tangos Market<br>
                    Tangos, Navotas<br>
                    ☎️ 09328447068<br>
                    <small>Near Tangos Market Navotas City</small>
                  </div>
                </div>
              </div>
          </div>
        </div>  
   <footer class="footer-section py-5 mt-5">
    <div class="container">
        <div class="row g-4">
            <!-- Logo Section -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="footer-brand text-center text-md-start">
                    <img src="Images/logo.png" alt="Santos Optical Logo" width="180" class="mb-3">
                    <h5 class="fw-bold text-primary mb-2">Santos Optical</h5>
                    <p class="text-muted small">Quality eyewear and professional eye care services since 2001</p>
                </div>
            </div>

            <!-- Products Section -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="footer-section">
                    <h6 class="footer-title mb-3">OUR PRODUCTS</h6>
                    <ul class="footer-list list-unstyled">
                        <li class="mb-2">
                            <a href="product-gallery.php?category=Frame" class="footer-link text-decoration-none">
                                <i class="fas fa-glasses me-2 text-primary"></i>Eyeglass Frames
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="product-gallery.php?category=Sunglasses" class="footer-link text-decoration-none">
                                <i class="fas fa-sun me-2 text-primary"></i>Sunglasses
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="product-gallery.php?category=Contact+Lenses" class="footer-link text-decoration-none">
                                <i class="fas fa-eye me-2 text-primary"></i>Contact Lenses
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="product-gallery.php" class="footer-link text-decoration-none">
                                <i class="fas fa-search me-2 text-primary"></i>All Products
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Quick Links Section -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="footer-section">
                    <h6 class="footer-title mb-3">QUICK LINKS</h6>
                    <ul class="footer-list list-unstyled">
                        <li class="mb-2">
                            <a href="aboutus.php" class="footer-link text-decoration-none">
                                <i class="fas fa-building me-2 text-primary"></i>About Us
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="ourservices.php" class="footer-link text-decoration-none">
                                <i class="fas fa-concierge-bell me-2 text-primary"></i>Our Services
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="face-shape-detector.php" class="footer-link text-decoration-none">
                                <i class="fas fa-user-circle me-2 text-primary"></i>Face Shape Detector
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="trackorder.php" class="footer-link text-decoration-none">
                                <i class="fas fa-shipping-fast me-2 text-primary"></i>Track Your Order
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Contact Section -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="footer-section">
                    <h6 class="footer-title mb-3">CONTACT INFORMATION</h6>
                    <div class="contact-info">
                        <div class="contact-item mb-3">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Main Address:</strong><br>
                                #6 Rizal Avenue Extension,<br>
                                Brgy. San Agustin, Malabon City
                            </div>
                        </div>
                        
                        <div class="contact-item mb-3">
                            <div class="contact-icon">
                                <i class="fas fa-phone text-primary"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Landline:</strong><br>
                                (02) 7508-4792
                            </div>
                        </div>
                        
                        <div class="contact-item mb-3">
                            <div class="contact-icon">
                                <i class="fas fa-mobile-alt text-primary"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Mobile:</strong><br>
                                0932-844-7068
                            </div>
                        </div>
                        
                        <div class="contact-item mb-3">
                            <div class="contact-icon">
                                <i class="fas fa-envelope text-primary"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Email:</strong><br>
                                <a href="mailto:Santosoptical@gmail.com" class="footer-link">Santosoptical@gmail.com</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Hours -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="business-hours text-center">
                    <h6 class="footer-title mb-3">BUSINESS HOURS</h6>
                    <div class="row justify-content-center">
                        <div class="col-auto">
                            <span class="badge bg-primary me-2">Monday - Saturday</span>
                            <span class="text-muted">8:00 AM - 7:00 PM</span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-secondary me-2">Sunday</span>
                            <span class="text-muted">9:00 AM - 5:00 PM</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Copyright Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="copyright-section text-center py-3">
                    <p class="m-0 text-muted">
                        <i class="far fa-copyright me-1"></i>
                        2024 Santos Optical Co., Ltd. All Rights Reserved. | 
                        <span class="ms-2">Providing Quality Eyewear Since 2001</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
/* Footer Styles */
.footer-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-top: 3px solid #007bff;
}

.footer-title {
    color: #2c3e50;
    font-weight: 700;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #007bff;
    padding-bottom: 0.5rem;
    display: inline-block;
}

.footer-brand h5 {
    color: #2c3e50;
    font-size: 1.3rem;
}

.footer-list li {
    transition: transform 0.3s ease;
}

.footer-list li:hover {
    transform: translateX(5px);
}

.footer-link {
    color: #555 !important;
    transition: all 0.3s ease;
    font-size: 0.95rem;
    display: block;
    padding: 0.25rem 0;
}

.footer-link:hover {
    color: #007bff !important;
    transform: translateX(3px);
}

.contact-info {
    margin-top: 0.5rem;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.contact-icon {
    width: 20px;
    text-align: center;
    margin-top: 2px;
    flex-shrink: 0;
}

.contact-text {
    flex: 1;
    font-size: 0.9rem;
    line-height: 1.4;
    color: #555;
}

.contact-text strong {
    color: #2c3e50;
    font-size: 0.85rem;
}

.business-hours {
    padding: 1rem;
    background: rgba(0, 123, 255, 0.05);
    border-radius: 10px;
    border-left: 4px solid #007bff;
}

.copyright-section {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    border-radius: 8px;
    margin-top: 1rem;
}

.copyright-section p {
    font-size: 0.9rem;
    letter-spacing: 0.5px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .footer-section {
        text-align: center;
    }
    
    .footer-brand {
        text-align: center !important;
    }
    
    .contact-item {
        justify-content: center;
        text-align: center;
    }
    
    .contact-icon {
        margin-top: 0;
    }
    
    .business-hours .row {
        flex-direction: column;
        gap: 10px;
    }
}

@media (max-width: 576px) {
    .footer-title {
        font-size: 1rem;
    }
    
    .footer-link {
        font-size: 0.9rem;
    }
    
    .contact-text {
        font-size: 0.85rem;
    }
}
</style>
            
            // Re-init AOS when window is resized
            window.addEventListener('resize', function() {
                AOS.refresh();
            });
        </script>

    </body>
</html>
