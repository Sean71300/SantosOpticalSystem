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
                                <i class="fas fa-glasses me-2"></i>Eyeglass Frames
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="product-gallery.php?category=Sunglasses" class="footer-link text-decoration-none">
                                <i class="fas fa-sun me-2"></i>Sunglasses
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="product-gallery.php?category=Contact+Lenses" class="footer-link text-decoration-none">
                                <i class="fas fa-eye me-2"></i>Contact Lenses
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="product-gallery.php" class="footer-link text-decoration-none">
                                <i class="fas fa-search me-2"></i>All Products
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
                                <i class="fas fa-building me-2"></i>About Us
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="ourservices.php" class="footer-link text-decoration-none">
                                <i class="fas fa-concierge-bell me-2"></i>Our Services
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="face-shape-detector.php" class="footer-link text-decoration-none">
                                <i class="fas fa-user-circle me-2"></i>Face Shape Detector
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="trackorder.php" class="footer-link text-decoration-none">
                                <i class="fas fa-shipping-fast me-2"></i>Track Your Order
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
                                <i class="fas fa-map-marker-alt me-2"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Main Address:</strong><br>
                                #6 Rizal Avenue Extension,<br>
                                Brgy. San Agustin, Malabon City
                            </div>
                        </div>
                        
                        <div class="contact-item mb-3">
                            <div class="contact-icon">
                                <i class="fas fa-phone me-2"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Landline:</strong><br>
                                (02) 7508-4792
                            </div>
                        </div>
                        
                        <div class="contact-item mb-3">
                            <div class="contact-icon">
                                <i class="fas fa-mobile-alt me-2"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Mobile:</strong><br>
                                0932-844-7068
                            </div>
                        </div>
                        
                        <div class="contact-item mb-3">
                            <div class="contact-icon">
                                <i class="fas fa-envelope me-2"></i>
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
/* Footer Styles - Matching Product Gallery Design */
.footer-section {
    background: #ffffff;
    border-top: 1px solid #e9ecef;
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

/* EXACT Product Gallery Icon Styling */
.footer-list li {
    transition: all 0.3s ease;
}

.footer-link {
    color: #6c757d !important;
    transition: all 0.3s ease;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    padding: 0.5rem 0;
    text-decoration: none;
}

.footer-link:hover {
    color: #007bff !important;
    transform: translateX(5px);
    text-decoration: none;
}

.footer-link i {
    color: #6c757d;
    width: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.footer-link:hover i {
    color: #007bff;
}

/* Contact Section Matching Product Gallery */
.contact-info {
    margin-top: 0.5rem;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 8px 0;
    border-bottom: 1px solid #f8f9fa;
    transition: all 0.3s ease;
}

.contact-item:hover {
    background-color: #f8f9fa;
    border-radius: 4px;
}

.contact-item:last-child {
    border-bottom: none;
}

.contact-icon {
    width: 20px;
    text-align: center;
    margin-top: 3px;
    flex-shrink: 0;
    color: #6c757d;
    transition: all 0.3s ease;
}

.contact-item:hover .contact-icon {
    color: #007bff;
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

.contact-item:hover .contact-text {
    color: #495057;
}

.contact-item:hover .contact-text strong {
    color: #007bff;
}

/* Business Hours */
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
    background: #ffffff;
}

/* Copyright Section */
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

.copyright-section i {
    color: #6c757d;
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
        flex-direction: column;
        gap: 8px;
    }
    
    .contact-icon {
        margin-top: 0;
    }
    
    .business-hours .row {
        flex-direction: column;
        gap: 8px;
    }
    
    .footer-link {
        justify-content: center;
        padding: 0.4rem 0;
    }
    
    .footer-link:hover {
        transform: translateY(-2px);
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
