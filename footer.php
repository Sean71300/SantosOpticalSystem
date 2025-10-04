<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Santos Optical - Navigation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, #1a6bb3 0%, #0d4d8c 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 0 0 15px 15px;
            margin-bottom: 30px;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .logo {
            font-size: 2.5rem;
            margin-right: 15px;
            color: #fff;
        }
        
        .logo-text {
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .tagline {
            text-align: center;
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        
        .nav-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
        }
        
        .nav-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            flex: 1;
            min-width: 250px;
            max-width: 350px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .nav-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }
        
        .section-title {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #eaeaea;
            color: #1a6bb3;
        }
        
        .section-title i {
            font-size: 1.5rem;
            margin-right: 12px;
            width: 30px;
            text-align: center;
        }
        
        .section-title h2 {
            font-size: 1.4rem;
            font-weight: 600;
        }
        
        .nav-links {
            list-style: none;
        }
        
        .nav-links li {
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }
        
        .nav-links li a {
            text-decoration: none;
            color: #555;
            font-weight: 500;
            transition: color 0.2s ease, transform 0.2s ease;
            display: flex;
            align-items: center;
            width: 100%;
            padding: 8px 5px;
            border-radius: 6px;
        }
        
        .nav-links li a:hover {
            color: #1a6bb3;
            background-color: #f0f7ff;
            transform: translateX(5px);
        }
        
        .nav-links li i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            color: #1a6bb3;
        }
        
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
        }
        
        .contact-item i {
            margin-right: 15px;
            margin-top: 3px;
            color: #1a6bb3;
            font-size: 1.2rem;
            width: 20px;
            text-align: center;
        }
        
        .contact-text {
            flex: 1;
        }
        
        .contact-text p {
            margin-bottom: 3px;
        }
        
        .contact-label {
            font-weight: 600;
            color: #444;
        }
        
        footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: #666;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                align-items: center;
            }
            
            .nav-section {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo-container">
                <i class="fas fa-glasses logo"></i>
                <h1 class="logo-text">SANTOS OPTICAL</h1>
            </div>
            <p class="tagline">Clear Vision for a Better Life</p>
        </div>
    </header>
    
    <div class="container">
        <div class="nav-container">
            <!-- Products Section -->
            <div class="nav-section">
                <div class="section-title">
                    <i class="fas fa-shopping-bag"></i>
                    <h2>OUR PRODUCTS</h2>
                </div>
                <ul class="nav-links">
                    <li>
                        <a href="#">
                            <i class="fas fa-glasses"></i>
                            Eyeglass Frames
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-sun"></i>
                            Sunglasses
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-eye"></i>
                            Contact Lenses
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-th-large"></i>
                            All Products
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Quick Links Section -->
            <div class="nav-section">
                <div class="section-title">
                    <i class="fas fa-link"></i>
                    <h2>QUICK LINKS</h2>
                </div>
                <ul class="nav-links">
                    <li>
                        <a href="#">
                            <i class="fas fa-info-circle"></i>
                            About Us
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-concierge-bell"></i>
                            Our Services
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-user-circle"></i>
                            Face Shape Detector
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-shipping-fast"></i>
                            Track Your Order
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Contact Information Section -->
            <div class="nav-section">
                <div class="section-title">
                    <i class="fas fa-address-card"></i>
                    <h2>CONTACT INFORMATION</h2>
                </div>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div class="contact-text">
                            <p class="contact-label">Main Address:</p>
                            <p>#6 Rizai Avenue Extension, Bray. San Agustin, Malabon City</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone-alt"></i>
                        <div class="contact-text">
                            <p class="contact-label">Landline:</p>
                            <p>(02) 7508-4792</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-mobile-alt"></i>
                        <div class="contact-text">
                            <p class="contact-label">Mobile:</p>
                            <p>0932-844-7068</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div class="contact-text">
                            <p class="contact-label">Email:</p>
                            <p>Santosoptical@gmail.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; 2023 Santos Optical. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
