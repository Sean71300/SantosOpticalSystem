<?php
  include_once 'setup.php'; // Include the setup.php file
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
                    <img src="Images/pascual st.jpg" alt="Pascual St, Malabon" class="branch-image">
                    <img src="Images/loc.png" alt="Map Icon" class="map-icon">
                  </div>
                  <div class="branch-info">
                    Pascual St, Malabon<br>
                    02 88183480
                  </div>
                </div>
              </div>

              <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="branch-card" data-aos="fade-up" data-aos-delay="200">
                  <div class="image-container">
                    <img src="Images/quiapo.jpg" alt="Quiapo, Manila" class="branch-image">
                    <img src="Images/loc.png" alt="Map Icon" class="map-icon">
                  </div>
                  <div class="branch-info">
                    Quiapo, Manila<br>
                    09328447068
                  </div>
                </div>
              </div>

              <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="branch-card" data-aos="fade-up" data-aos-delay="300">
                  <div class="image-container">
                    <img src="Images/bayan.jpg" alt="Bayan, Malabon" class="branch-image">
                    <img src="Images/loc.png" alt="Map Icon" class="map-icon">
                  </div>
                  <div class="branch-info">
                    Bayan, Malabon<br>
                    02 86321972
                  </div>
                </div>
              </div>

              <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="branch-card" data-aos="fade-up" data-aos-delay="400">
                  <div class="image-container">
                    <img src="Images/tangos.png" alt="Tangos, Navotas" class="branch-image">
                    <img src="Images/loc.png" alt="Map Icon" class="map-icon">
                  </div>
                  <div class="branch-info">
                    Tangos, Navotas<br>
                    09328447068
                  </div>
                </div>
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
        </div>

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
