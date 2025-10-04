<?php
?>
<footer class="footer-section py-5 mt-5">
    <div class="container">
        <div class="row g-4">
            <!-- Logo Section -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="footer-brand text-center text-md-start">
                    <img src="Images/logo.png" alt="Santos Optical Logo" width="160" class="mb-3">
                    <h5 class="fw-bold text-dark mb-2">Santos Optical</h5>
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
                                <i class="fas fa-glasses me-2 text-muted"></i>Eyeglass Frames
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="product-gallery.php?category=Sunglasses" class="footer-link text-decoration-none">
                                <i class="fas fa-sun me-2 text-muted"></i>Sunglasses
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="product-gallery.php?category=Contact+Lenses" class="footer-link text-decoration-none">
                                <i class="fas fa-eye me-2 text-muted"></i>Contact Lenses
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="product-gallery.php" class="footer-link text-decoration-none">
                                <i class="fas fa-search me-2 text-muted"></i>All Products
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
                                <i class="fas fa-building me-2 text-muted"></i>About Us
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="ourservices.php" class="footer-link text-decoration-none">
                                <i class="fas fa-concierge-bell me-2 text-muted"></i>Our Services
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="face-shape-detector.php" class="footer-link text-decoration-none">
                                <i class="fas fa-user-circle me-2 text-muted"></i>Face Shape Detector
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="trackorder.php" class="footer-link text-decoration-none">
                                <i class="fas fa-shipping-fast me-2 text-muted"></i>Track Your Order
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
                                <i class="fas fa-map-marker-alt text-dark"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Main Address:</strong><br>
                                #6 Rizal Avenue Extension,<br>
                                Brgy. San Agustin, Malabon City
                            </div>
                        </div>
                        
                        <div class="contact-item mb-3">
                            <div class="contact-icon">
                                <i class="fas fa-phone text-dark"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Landline:</strong><br>
                                (02) 7508-4792
                            </div>
                        </div>
                        
                        <div class="contact-item mb-3">
                            <div class="contact-icon">
                                <i class="fas fa-mobile-alt text-dark"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Mobile:</strong><br>
                                0932-844-7068
                            </div>
                        </div>
                        
                        <div class="contact-item mb-3">
                            <div class="contact-icon">
                                <i class="fas fa-envelope text-dark"></i>
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
                            <span class="badge bg-light text-dark border me-2">Monday - Saturday</span>
                            <span class="text-muted">8:00 AM - 7:00 PM</span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-light text-dark border me-2">Sunday</span>
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
/* Footer Styles - Clean White Theme */
.footer-section {
    background: #ffffff;
    border-top: 1px solid #e9ecef;
    position: relative;
}

.footer-title {
    color: #2c3e50;
    font-weight: 600;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 0.5rem;
    display: inline-block;
}

.footer-brand h5 {
    color: #2c3e50;
    font-size: 1.2rem;
}

.footer-list li {
    transition: transform 0.2s ease;
}

.footer-list li:hover {
    transform: translateX(3px);
}

.footer-link {
    color: #6c757d !important;
    transition: all 0.2s ease;
    font-size: 0.9rem;
    display: block;
    padding: 0.25rem 0;
    border-left: 2px solid transparent;
    padding-left: 8px;
}

.footer-link:hover {
    color: #2c3e50 !important;
    transform: translateX(2px);
    border-left-color: #6c757d;
    background-color: #f8f9fa;
}

.contact-info {
    margin-top: 0.5rem;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 6px 0;
    border-bottom: 1px solid #f8f9fa;
}

.contact-item:last-child {
    border-bottom: none;
}

.contact-icon {
    width: 16px;
    text-align: center;
    margin-top: 3px;
    flex-shrink: 0;
}

.contact-text {
    flex: 1;
    font-size: 0.85rem;
    line-height: 1.4;
    color: #6c757d;
}

.contact-text strong {
    color: #2c3e50;
    font-size: 0.8rem;
}

.business-hours {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.business-hours .badge {
    font-size: 0.75rem;
    padding: 0.4rem 0.8rem;
    border-radius: 15px;
    border: 1px solid #dee2e6;
}

.copyright-section {
    background: #f8f9fa;
    color: #6c757d;
    border-radius: 6px;
    margin-top: 1rem;
    border: 1px solid #e9ecef;
}

.copyright-section p {
    font-size: 0.85rem;
    letter-spacing: 0.3px;
    font-weight: 400;
}

/* Hover effects */
.footer-link:hover i {
    color: #2c3e50;
    transition: color 0.2s ease;
}

.contact-item:hover .contact-icon {
    color: #2c3e50;
    transition: color 0.2s ease;
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
        gap: 8px;
    }
    
    .footer-link {
        border-left: none;
        border-bottom: 1px solid transparent;
        padding-left: 0;
        padding-bottom: 3px;
    }
    
    .footer-link:hover {
        border-left-color: transparent;
        border-bottom-color: #6c757d;
    }
}

@media (max-width: 576px) {
    .footer-title {
        font-size: 0.9rem;
    }
    
    .footer-link {
        font-size: 0.85rem;
    }
    
    .contact-text {
        font-size: 0.8rem;
    }
    
    .business-hours {
        padding: 0.8rem;
    }
    
    .footer-brand img {
        width: 140px;
    }
}
</style>
