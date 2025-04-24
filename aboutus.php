<?php
include_once 'setup.php'; // Include the setup.php file
include 'ActivityTracker.php';
include 'loginChecker.php';
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>About Us</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
        <link rel="stylesheet" href="customCodes/s1.css">
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="shortcut icon" type="image/x-icon" href="images/logo.png"/>
        <link rel="stylesheet" href="customCodes/s2.css">
    </head>
    <body>
      <?php include "Navigation.php"?>
        <div class="button-container">
            <a href="aboutus.php" class="nav-button">About Us</a>
            <a href="ourservices.php" class="nav-button">Our Services</a>
        </div>
        
        <div class="container-fluid position-relative">
            <div class="aboutus-section">
                <img src="Images/au1.png" alt="Services Image" class="services-img">
            </div>
        </div><br><br>

        <div class="d-flex justify-content-center align-items-center">
            <div class="aboutus2-section">
                <h1>ABOUT US</h1>
                <h5>BVP Santos Optical Clinic emphasizes personalized customer service. 
                    Services they offer include eye examinations and prescription glasses.</h5>
            </div>
        </div><br><br><br>
        

        <div class="container my-6 doctor-section" style="background-color: aliceblue;">
            <div class="row align-items-center text-center text-md-start">
              <div class="col-md-4 mb-3 mb-md-2">
                <img src="Images/owner.png" alt="Doctor" class="img-fluid rounded shadow">
              </div>
              <div class="col-md-7">
                <p class="fs-4">
                  BVP Santos Optical Clinic, owned by <b> Bien Ven P. Santos,</b> is a prominent provider of optical services,
                  has been serving its community since its establishment as a sole proprietorship in 2001.
                </p>
              </div>
            </div>
        </div><br><br> 

        w<div class="container">
          <h2 class="aboutus-h2">SO BRANCHES</h2>
          <div class="row justify-content-center">
      
      
            <div class="col-sm-6 col-md-4 col-lg-3 branch-card">
              <div class="image-container">
                <img src="Images/pascual st.jpg" alt="Pascual St, Malabon" class="branch-image">
                <img src="Images/loc.png" alt="Map Icon" class="map-icon">
              </div>
              <div class="branch-info">
                Pascual St, Malabon<br>
                02 88183480
              </div>
            </div>
      
      
            <div class="col-sm-6 col-md-4 col-lg-3 branch-card">
              <div class="image-container">
                <img src="Images/quiapo.jpg" alt="Quiapo, Manila" class="branch-image">
                <img src="Images/loc.png" alt="Map Icon" class="map-icon">
              </div>
              <div class="branch-info">
                Quiapo, Manila<br>
                09328447068
              </div>
            </div>
      
      
            <div class="col-sm-6 col-md-4 col-lg-3 branch-card">
              <div class="image-container">
                <img src="Images/bayan.jpg" alt="Bayan, Malabon" class="branch-image">
                <img src="Images/loc.png" alt="Map Icon" class="map-icon">
              </div>
              <div class="branch-info">
                Bayan, Malabon<br>
                02 86321972
              </div>
            </div>
      
      
            <div class="col-sm-6 col-md-4 col-lg-3 branch-card">
              <div class="image-container">
                <img src="Images/tangos.png" alt="Tangos, Navotas" class="branch-image">
                <img src="Images/loc.png" alt="Map Icon" class="map-icon">
              </div>
              <div class="branch-info">
                Tangos, Navotas<br>
                09328447068
              </div>
            </div>
    </body>
</html>