<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Santos Optical - Quality Eyewear Since 2001</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header Styles */
        header {
            background: linear-gradient(135deg, #1a5276 0%, #3498db 100%);
            color: white;
            padding: 30px 0;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .logo-icon {
            font-size: 2.5rem;
            margin-right: 15px;
            color: #f1c40f;
        }
        
        .logo-text {
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .tagline {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-top: 10px;
        }
        
        /* Main Content Styles */
        .main-content {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            padding: 40px 0;
        }
        
        .section {
            flex: 1;
            min-width: 300px;
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        
        .section:hover {
            transform: translateY(-5px);
        }
        
        .section-title {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eaeaea;
            color: #2c3e50;
        }
        
        .section-icon {
            font-size: 1.5rem;
            margin-right: 10px;
            color: #3498db;
        }
        
        .section h2 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .section ul {
            list-style: none;
        }
        
        .section li {
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }
        
        .section li i {
            margin-right: 10px;
            color: #3498db;
            width: 20px;
            text-align: center;
        }
        
        .section a {
            color: #3498db;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .section a:hover {
            color: #2c3e50;
            text-decoration: underline;
        }
        
        /* Contact Section Specific Styles */
        .contact-info li {
            margin-bottom: 15px;
        }
        
        .contact-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        /* Footer Styles */
        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 30px;
        }
        
        .copyright {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                flex-direction: column;
            }
            
            .logo-text {
                font-size: 2rem;
            }
            
            .section {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <i class="fas fa-glasses logo-icon"></i>
                <h1 class="logo-text">Santos Optical</h1>
            </div>
            <p class="tagline">Quality eyewear and professional eye care services since 2001</p>
        </div>
    </header>
    
    <div class="container">
        <div class="main-content">
            <section class="section">
                <div class="section-title">
                    <i class="fas fa-shopping-bag section-icon"></i>
                    <h2>OUR PRODUCTS</h2>
                </div>
                <ul>
                    <li><i class="fas fa-glasses"></i> Eyeglass Frames</li>
                    <li><i class="fas fa-sun"></i> Sunglasses</li>
                    <li><i class="fas fa-eye"></i> Contact Lenses</li>
                    <li><i class="fas fa-box-open"></i> All Products</li>
                </ul>
            </section>
            
            <section class="section">
                <div class="section-title">
                    <i class="fas fa-link section-icon"></i>
                    <h2>QUICK LINKS</h2>
                </div>
                <ul>
                    <li><i class="fas fa-info-circle"></i> <a href="#">About Us</a></li>
                    <li><i class="fas fa-concierge-bell"></i> <a href="#">Our Services</a></li>
                    <li><i class="fas fa-user-circle"></i> <a href="#">Face Shape Detector</a></li>
                    <li><i class="fas fa-shipping-fast"></i> <a href="#">Track Your Order</a></li>
                </ul>
            </section>
            
            <section class="section contact-info">
                <div class="section-title">
                    <i class="fas fa-address-card section-icon"></i>
                    <h2>CONTACT INFORMATION</h2>
                </div>
                <ul>
                    <li>
                        <div class="contact-label"><i class="fas fa-map-marker-alt"></i> Main Address:</div>
                        <div>Air Road Avenue Extension,<br>Diggy San Agustin, Mashon City</div>
                    </li>
                    <li>
                        <div class="contact-label"><i class="fas fa-phone"></i> Landline:</div>
                        <div>(02) 7508-4792</div>
                    </li>
                    <li>
                        <div class="contact-label"><i class="fas fa-mobile-alt"></i> Mobile:</div>
                        <div>0592-844-7066</div>
                    </li>
                    <li>
                        <div class="contact-label"><i class="fas fa-envelope"></i> Email:</div>
                        <div>Santosoptical@gmail.com</div>
                    </li>
                </ul>
            </section>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p class="copyright">© 2023 Santos Optical. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
