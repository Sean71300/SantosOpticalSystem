<?php
?>
<footer class="footer-section py-5 mt-5">
    <div class="container">
        <div class="row g-4">
            <!-- Logo Section -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="footer-brand text-center text-md-start">
                    <img src="Images/logo.png" alt="Santos Optical Logo" width="180" class="mb-3">
                    <h5 class="fw-bold text-warning mb-2">Santos Optical</h5>
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
                                <i class="fas fa-glasses me-2 text-warning"></i>Eyeglass Frames
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="product-gallery.php?category=Sunglasses" class="footer-link text-decoration-none">
                                <i class="fas fa-sun me-2 text-warning"></i>Sunglasses
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="product-gallery.php?category=Contact+Lenses" class="footer-link text-decoration-none">
                                <i class="fas fa-eye me-2 text-warning"></i>Contact Lenses
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="product-gallery.php" class="footer-link text-decoration-none">
                                <i class="fas fa-search me-2 text-warning"></i>All Products
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
                                <i class="fas fa-building me-2 text-warning"></i>About Us
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="ourservices.php" class="footer-link text-decoration-none">
                                <i class="fas fa-concierge-bell me-2 text-warning"></i>Our Services
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="face-shape-detector.php" class="footer-link text-decoration-none">
                                <i class="fas fa-user-circle me-2 text-warning"></i>Face Shape Detector
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="trackorder.php" class="footer-link text-decoration-none">
                                <i class="fas fa-shipping-fast me-2 text-warning"></i>Track Your Order
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
                                <i class="fas fa-map-marker-alt text-danger"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Main Address:</strong><br>
                                #6 Rizal Avenue Extension,<br>
                                Brgy. San Agustin, Malabon City
                            </div>
                        </div>
                        
                        <div class="contact-item mb-3">
                            <div class="contact-icon">
                                <i class="fas fa-phone text-danger"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Landline:</strong><br>
                                (02) 7508-4792
                            </div>
                        </div>
                        
                        <div class="contact-item mb-3">
                            <div class="contact-icon">
                                <i class="fas fa-mobile-alt text-danger"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Mobile:</strong><br>
                                0932-844-7068
                            </div>
                        </div>
                        
                        <div class="contact-item mb-3">
                            <div class="contact-icon">
                                <i class="fas fa-envelope text-danger"></i>
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
                            <span class="badge bg-warning text-dark me-2">Monday - Saturday</span>
                            <span class="text-muted">8:00 AM - 7:00 PM</span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-danger me-2">Sunday</span>
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
                    <p class="m-0 text-white">
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
/* Footer Styles - Yellow, Red & White Theme */
.footer-section {
    background: linear-gradient(135deg, #ffffff 0%, #fff9e6 50%, #ffffff 100%);
    border-top: 4px solid #ffc107;
    position: relative;
}

.footer-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #ffc107, #dc3545, #ffc107);
}

.footer-title {
    color: #dc3545;
    font-weight: 700;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #ffc107;
    padding-bottom: 0.5rem;
    display: inline-block;
}

.footer-brand h5 {
    color: #dc3545;
    font-size: 1.3rem;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

.footer-list li {
    transition: transform 0.3s ease;
}

.footer-list li:hover {
    transform: translateX(5px);
}

.footer-link {
    color: #666 !important;
    transition: all 0.3s ease;
    font-size: 0.95rem;
    display: block;
    padding: 0.25rem 0;
    border-left: 2px solid transparent;
    padding-left: 8px;
}

.footer-link:hover {
    color: #dc3545 !important;
    transform: translateX(3px);
    border-left-color: #ffc107;
    background: linear-gradient(90deg, rgba(255,193,7,0.1), transparent);
}

.contact-info {
    margin-top: 0.5rem;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,193,7,0.2);
}

.contact-item:last-child {
    border-bottom: none;
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
    color: #666;
}

.contact-text strong {
    color: #dc3545;
    font-size: 0.85rem;
}

.business-hours {
    padding: 1.5rem;
    background: linear-gradient(135deg, #fff3cd 0%, #fff9e6 100%);
    border-radius: 15px;
    border: 2px solid #ffc107;
    box-shadow: 0 4px 15px rgba(255,193,7,0.1);
}

.business-hours .badge {
    font-size: 0.8rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
}

.copyright-section {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    border-radius: 10px;
    margin-top: 1rem;
    border: 2px solid #ffc107;
    box-shadow: 0 4px 15px rgba(220,53,69,0.2);
}

.copyright-section p {
    font-size: 0.9rem;
    letter-spacing: 0.5px;
    font-weight: 500;
}

/* Hover effects */
.footer-link:hover i {
    transform: scale(1.2);
    transition: transform 0.3s ease;
}

.contact-item:hover .contact-icon {
    transform: scale(1.1);
    transition: transform 0.3s ease;
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
    
    .footer-link {
        border-left: none;
        border-bottom: 2px solid transparent;
        padding-left: 0;
        padding-bottom: 4px;
    }
    
    .footer-link:hover {
        border-left-color: transparent;
        border-bottom-color: #ffc107;
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
    
    .business-hours {
        padding: 1rem;
    }
}
</style>
