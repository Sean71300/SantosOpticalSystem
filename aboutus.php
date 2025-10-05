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
        <style>
            /* Additional styles for new sections */
            .mission-vision-section {
                padding: 60px 0;
                background-color: #fff9e6;
            }
            
            .values-section {
                padding: 60px 0;
                background-color: #fff;
            }
            
            .value-card {
                text-align: center;
                padding: 30px 20px;
                border-radius: 10px;
                transition: transform 0.3s ease;
                height: 100%;
                background-color: white;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05);
                border-top: 4px solid #dc3545;
            }
            
            .value-card:hover {
                transform: translateY(-10px);
                box-shadow: 0 10px 25px rgba(220, 53, 69, 0.15);
            }
            
            .value-icon {
                width: 70px;
                height: 70px;
                margin: 0 auto 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background-color: #ffc107;
            }
            
            .timeline-section {
                padding: 60px 0;
                background-color: #fff9e6;
            }
            
            .timeline-item {
                position: relative;
                padding: 20px 0;
            }
            
            .timeline-content {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 3px 10px rgba(0,0,0,0.08);
                border-left: 4px solid #dc3545;
            }
            
            .timeline-item:before {
                content: '';
                position: absolute;
                left: 50%;
                top: 0;
                bottom: 0;
                width: 4px;
                background: #dc3545;
                transform: translateX(-50%);
            }
            
            .timeline-item:nth-child(odd) .timeline-content {
                margin-left: 50%;
                margin-right: 20px;
            }
            
            .timeline-item:nth-child(even) .timeline-content {
                margin-right: 50%;
                margin-left: 20px;
            }
            
            .timeline-year {
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
                background: #dc3545;
                color: white;
                padding: 5px 15px;
                border-radius: 20px;
                font-weight: bold;
                z-index: 1;
            }
            
            .innovation-section {
                padding: 60px 0;
                background: linear-gradient(135deg, #dc3545 0%, #ff6b7a 100%);
                color: white;
            }
            
            .innovation-card {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border-radius: 10px;
                padding: 30px;
                height: 100%;
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
            
            .branch-card {
                border: 2px solid #ffc107;
                border-radius: 10px;
                overflow: hidden;
                transition: transform 0.3s ease;
                background: white;
            }
            
            .branch-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(220, 53, 69, 0.15);
            }
            
            .aboutus-h2 {
                color: #dc3545;
                border-bottom: 3px solid #ffc107;
                padding-bottom: 10px;
                display: inline-block;
            }
            
            .nav-button {
                background: linear-gradient(45deg, #dc3545, #ff6b7a);
                color: white;
                border: none;
                padding: 12px 30px;
                border-radius: 25px;
                text-decoration: none;
                transition: all 0.3s ease;
            }
            
            .nav-button:hover {
                transform: scale(1.05);
                box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
                color: white;
            }
            
            @media (max-width: 768px) {
                .timeline-item:before {
                    left: 30px;
                }
                
                .timeline-item:nth-child(odd) .timeline-content,
                .timeline-item:nth-child(even) .timeline-content {
                    margin-left: 60px;
                    margin-right: 0;
                }
                
                .timeline-year {
                    left: 30px;
                }
            }
        </style>
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
                <h1 style="color: #dc3545;">ABOUT US</h1>
                <h5>Santos Optical Clinic, founded by Dr. Bien Ven P. Santos in 2001 as a sole proprietorship, has established itself as a reliable provider of quality optical services. Due to growing customer demand, the business transitioned into a corporation in 2013.</h5>
            </div>
        </div><br><br><br>

        <!-- Mission & Vision Section -->
        <div class="mission-vision-section" data-aos="fade-up">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 mb-4" data-aos="fade-right">
                        <div class="card h-100 border-0 shadow-sm" style="border-top: 4px solid #dc3545 !important;">
                            <div class="card-body p-4">
                                <div class="text-center mb-4">
                                    <div class="value-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="#dc3545" class="bi bi-eye" viewBox="0 0 16 16">
                                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="card-title text-center mb-3" style="color: #dc3545;">Our Mission</h3>
                                <p class="card-text text-center">
                                    To provide exceptional eye care services through personalized attention, 
                                    quality products, and professional expertise that enhances our patients' 
                                    vision and quality of life across all our branches.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4" data-aos="fade-left">
                        <div class="card h-100 border-0 shadow-sm" style="border-top: 4px solid #ffc107 !important;">
                            <div class="card-body p-4">
                                <div class="text-center mb-4">
                                    <div class="value-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="#dc3545" class="bi bi-bullseye" viewBox="0 0 16 16">
                                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                            <path d="M8 13A5 5 0 1 1 8 3a5 5 0 0 1 0 10zm0 1A6 6 0 1 0 8 2a6 6 0 0 0 0 12z"/>
                                            <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>
                                            <path d="M9.5 8a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="card-title text-center mb-3" style="color: #dc3545;">Our Vision</h3>
                                <p class="card-text text-center">
                                    To be the leading optical clinic recognized for excellence in eye care, 
                                    commitment to community eye health, and continuous innovation in serving 
                                    the vision needs of our growing customer base.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Our Values Section -->
        <div class="values-section" data-aos="fade-up">
            <div class="container">
                <h2 class="text-center mb-5" style="color: #dc3545;">Our Core Values</h2>
                <div class="row g-4">
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="value-card">
                            <div class="value-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="#dc3545" class="bi bi-heart" viewBox="0 0 16 16">
                                    <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z"/>
                                </svg>
                            </div>
                            <h4 style="color: #dc3545;">Compassionate Care</h4>
                            <p>We treat every patient with empathy, respect, and understanding, ensuring a comfortable and supportive experience in all our branches.</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="value-card">
                            <div class="value-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="#dc3545" class="bi bi-award" viewBox="0 0 16 16">
                                    <path d="M9.669.864 8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68L9.669.864zm1.196 1.193.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702 1.509.229z"/>
                                    <path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1 4 11.794z"/>
                                </svg>
                            </div>
                            <h4 style="color: #dc3545;">Quality Excellence</h4>
                            <p>We maintain the highest standards in eye care, providing quality optical products and professional services to ensure customer satisfaction.</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                        <div class="value-card">
                            <div class="value-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="#dc3545" class="bi bi-people" viewBox="0 0 16 16">
                                    <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                                </svg>
                            </div>
                            <h4 style="color: #dc3545;">Community Focus</h4>
                            <p>We are committed to serving our local communities with accessible, affordable eye care for all through our strategically located branches.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container my-6 doctor-section" style="background-color: #fff9e6;" data-aos="slide-left">
            <div class="row align-items-center text-center text-md-start">
              <div class="col-md-4 mb-3 mb-md-2 d-flex justify-content-center">
                <img src="Images/owner.png" alt="Doctor" class="img-fluid rounded shadow">
              </div>
              <div class="col-md-8">
                <h3 style="color: #dc3545;">Our Founder</h3>
                <p class="fs-4">
                  Santos Optical Clinic, owned by <b> Dr. Bien Ven P. Santos,</b> is a prominent provider of optical services,
                  and has been serving its community since its establishment as a sole proprietorship in 2001.
                </p>
                <p class="fs-5 mt-3">
                  Starting from a single location in Tangos, Navotas, Dr. Santos' dedication to providing quality vision care 
                  has led to the expansion of Santos Optical to four branches across Malabon, Navotas, and Manila, 
                  serving thousands of satisfied customers.
                </p>
              </div>
            </div>
        </div><br><br> 

        <!-- Our Story / Timeline Section -->
        <div class="timeline-section" data-aos="fade-up">
            <div class="container">
                <h2 class="text-center mb-5" style="color: #dc3545;">Our Journey</h2>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-year">2001</div>
                        <div class="timeline-content">
                            <h4 style="color: #dc3545;">Foundation</h4>
                            <p>Santos Optical Clinic was established as a sole proprietorship by Dr. Bien Ven P. Santos, beginning operations in Tangos, Navotas.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-year">2013</div>
                        <div class="timeline-content">
                            <h4 style="color: #dc3545;">Business Transformation</h4>
                            <p>Due to growing customer demand, the business transitioned into a corporation, enabling further expansion and improved services.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-year">2015</div>
                        <div class="timeline-content">
                            <h4 style="color: #dc3545;">Branch Expansion</h4>
                            <p>Expanded to San Agustin, Malabon and Rizal Extension Avenue, Malabon, bringing quality eye care services to more communities.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-year">2018</div>
                        <div class="timeline-content">
                            <h4 style="color: #dc3545;">Manila Presence</h4>
                            <p>Opened our Quiapo, Manila branch, extending our services to the heart of the city and reaching a wider customer base.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-year">Present</div>
                        <div class="timeline-content">
                            <h4 style="color: #dc3545;">Digital Transformation</h4>
                            <p>Implementing a centralized website system to streamline inventory monitoring, customer record management, and enhance service delivery across all four branches.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Innovation Section -->
        <div class="innovation-section" data-aos="fade-up">
            <div class="container">
                <h2 class="text-center mb-5">Digital Innovation</h2>
                <div class="row g-4">
                    <div class="col-md-6" data-aos="fade-right">
                        <div class="innovation-card">
                            <h4>Centralized Management System</h4>
                            <p>We are developing a comprehensive website system to streamline our operations across all branches. This digital transformation will enhance inventory monitoring and customer record management, reducing delays and minimizing human error.</p>
                        </div>
                    </div>
                    <div class="col-md-6" data-aos="fade-left">
                        <div class="innovation-card">
                            <h4>AI-Powered Frame Selection</h4>
                            <p>Our upcoming system will feature a facial shape-scanning tool that assists customers in selecting the perfect eyeglass frames. This innovative technology supports our commitment to personalized service while optimizing internal processes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container" data-aos="fade-up">
          <h2 class="aboutus-h2">SO BRANCHES</h2>
          <div class="row justify-content-center g-4">
              <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="branch-card" data-aos="fade-up" data-aos-delay="100">
                  <div class="image-container">
                    <a href="https://maps.app.goo.gl/8gff1kz1FyvZ6FCK8" target="_blank">
                      <img src="Images/pascual st.jpg" alt="San Agustin, Malabon" class="branch-image">
                      <img src="Images/loc.png" alt="Map Icon" class="map-icon">
                    </a>
                  </div>
                  <div class="branch-info">
                    <strong>SO Santos Optical San Agustin</strong><br>
                    San Agustin, Malabon<br>
                    ☎️ 02 88183480<br>
                    <small>Conveniently located in San Agustin</small>
                  </div>
                </div>
              </div>

              <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="branch-card" data-aos="fade-up" data-aos-delay="200">
                  <div class="image-container">
                    <a href="https://maps.app.goo.gl/FCzggs1hSusNxnMq7" target="_blank">
                      <img src="Images/bayan.jpg" alt="Rizal Extension Avenue, Malabon" class="branch-image">
                      <img src="Images/loc.png" alt="Map Icon" class="map-icon">
                    </a>
                  </div>
                  <div class="branch-info">
                    <strong>SO Santos Optical Rizal Avenue</strong><br>
                    Rizal Extension Avenue, Malabon<br>
                    ☎️ 02 86321972<br>
                    <small>Along the main avenue of Malabon</small>
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
                    Quiapo, Manila<br>
                    ☎️ 09328447068<br>
                    <small>In the heart of Quiapo, Manila</small>
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
                    Tangos, Navotas<br>
                    ☎️ 09328447068<br>
                    <small>Our original branch in Tangos</small>
                  </div>
                </div>
              </div>
          </div>
        </div>  

        <?php include 'footer.php'; ?>

        <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
        <script>
            AOS.init({
                duration: 1000, 
                easing: 'ease-in-out',
                once: true,
                disable: window.innerWidth < 768 // Disable animations on mobile for better performance
            });
            
            // Re-init AOS when window is resized
            window.addEventListener('resize', function() {
                AOS.refresh();
            });
        </script>

    </body>
</html>
