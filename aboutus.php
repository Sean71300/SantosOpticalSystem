<?php
  include_once 'setup.php'; // Include the setup.php file
  include 'ActivityTracker.php';
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>About Us</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="stylesheet" href="customCodes/s1.css">
        <link rel="stylesheet" href="customCodes/s2.css">

        <style>
        
            .aboutus2-section {
                animation: fadeIn 2s ease-in-out;
            }

            /* Fade-In Effect */
            @keyframes fadeIn {
                0% {
                    opacity: 0;
                    transform: translateY(30px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

           
            .branch-image {
                transition: transform 0.3s ease;
            }

            .branch-image:hover {
                transform: scale(1.1);
            }

          
            .branch-card {
                transition: transform 0.3s ease-in-out, box-shadow 0.3s ease;
            }

            .branch-card:hover {
                transform: translateY(-10px);
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            }

            .nav-button {
                transition: transform 0.3s ease-in-out;
            }

            .nav-button:hover {
                transform: scale(1.1);
            }

            .doctor-section {
                animation: slideInFromLeft 2s ease-in-out;
            }

            @keyframes slideInFromLeft {
                0% {
                    transform: translateX(-100%);
                }
                100% {
                    transform: translateX(0);
                }
            }

            .aboutus-h2 {
                text-align: center;
                font-size: 2rem;
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <?php include "Navigation.php"?>
        <div class="button-container">
            <a href="aboutus.php" class="nav-button" data-aos="zoom-in">About Us</a>
            <a href="ourservices.php" class="nav-button" data-aos="zoom-in" data-aos-delay="200">Our Services</a>
        </div>
        
        <div class="container-fluid position-relative">
            <div class="aboutus-section" data-aos="fade-up">
                <img src="Images/au1.png" alt="Services Image" class="services-img">
            </div>
        </div><br><br>

        <div class="d-flex justify-content-center align-items-center">
            <div class="aboutus2-section">
                <h1>ABOUT US</h1>
                <h5>Santos Optical Clinic emphasizes personalized customer service. 
                    Services they offer include eye examinations and prescription glasses.</h5>
            </div>
        </div><br><br><br>

        <div class="container my-6 doctor-section" style="background-color: aliceblue;" data-aos="slide-left">
            <div class="row align-items-center text-center text-md-start">
              <div class="col-md-4 mb-3 mb-md-2">
                <img src="Images/owner.png" alt="Doctor" class="img-fluid rounded shadow">
              </div>
              <div class="col-md-7">
                <p class="fs-4">
                  Santos Optical Clinic, owned by <b> Bien Ven P. Santos,</b> is a prominent provider of optical services,
                  and has been serving its community since its establishment as a sole proprietorship in 2001.
                </p>
              </div>
            </div>
        </div><br><br> 

        <div class="container" data-aos="fade-up">
          <h2 class="aboutus-h2">SO BRANCHES</h2>
          <div class="row justify-content-center">
              <div class="col-sm-6 col-md-4 col-lg-3 branch-card" data-aos="fade-up" data-aos-delay="100">
                <div class="image-container">
                  <img src="Images/pascual st.jpg" alt="Pascual St, Malabon" class="branch-image">
                  <img src="Images/loc.png" alt="Map Icon" class="map-icon">
                </div>
                <div class="branch-info">
                  Pascual St, Malabon<br>
                  02 88183480
                </div>
              </div>

              <div class="col-sm-6 col-md-4 col-lg-3 branch-card" data-aos="fade-up" data-aos-delay="200">
                <div class="image-container">
                  <img src="Images/quiapo.jpg" alt="Quiapo, Manila" class="branch-image">
                  <img src="Images/loc.png" alt="Map Icon" class="map-icon">
                </div>
                <div class="branch-info">
                  Quiapo, Manila<br>
                  09328447068
                </div>
              </div>

              <div class="col-sm-6 col-md-4 col-lg-3 branch-card" data-aos="fade-up" data-aos-delay="300">
                <div class="image-container">
                  <img src="Images/bayan.jpg" alt="Bayan, Malabon" class="branch-image">
                  <img src="Images/loc.png" alt="Map Icon" class="map-icon">
                </div>
                <div class="branch-info">
                  Bayan, Malabon<br>
                  02 86321972
                </div>
              </div>

              <div class="col-sm-6 col-md-4 col-lg-3 branch-card" data-aos="fade-up" data-aos-delay="400">
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

        <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
        <script>
            AOS.init({
                duration: 1000, 
                easing: 'ease-in-out',
                once: true 
            });
        </script>

    </body>
</html>
