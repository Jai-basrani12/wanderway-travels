    </main>

    <!-- Footer -->
    <footer class="pt-5 pb-4">
        <div class="container">
            <div class="row g-4">
                
                <!-- About Column -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-brand mb-2">
                        <i class="bi bi-compass text-accent me-1"></i>
                        <span>WanderWay</span> Travels
                    </div>
                    <p class="small text-secondary mb-3">
                        Your trusted travel partner for unforgettable vacations, custom holiday packages, and seamless travel booking experiences worldwide.
                    </p>
                    <div class="d-flex gap-2">
                        <span class="badge bg-dark border border-secondary text-secondary">Trusted & Certified</span>
                        <span class="badge bg-dark border border-secondary text-secondary">24/7 Support</span>
                    </div>
                </div>

                <!-- Quick Links Column -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="text-white fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= BASE_URL ?>">Home</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>packages.php">Tour Packages</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>contact.php">Contact Us</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>enquiry.php">Enquiry Form</a></li>
                    </ul>
                </div>

                <!-- Popular Categories / Info -->
                <div class="col-lg-3 col-md-6 col-6">
                    <h6 class="text-white fw-bold mb-3">Destinations</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2 text-secondary">Beach & Island Escapes</li>
                        <li class="mb-2 text-secondary">Mountain Treks & Nature</li>
                        <li class="mb-2 text-secondary">Cultural Heritage Tours</li>
                        <li class="mb-2 text-secondary">Luxury Weekend Getaways</li>
                    </ul>
                </div>

                <!-- Contact Info Column -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="text-white fw-bold mb-3">Get In Touch</h6>
                    <ul class="list-unstyled small text-secondary">
                        <li class="mb-2 d-flex align-items-start">
                            <i class="bi bi-geo-alt text-accent me-2 mt-1"></i>
                            <span>102 Sunshine Arcade, MG Road, New Delhi, India</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <i class="bi bi-telephone text-accent me-2"></i>
                            <span>+91 98765 43210</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <i class="bi bi-envelope text-accent me-2"></i>
                            <span>info@wanderwaytravels.com</span>
                        </li>
                    </ul>
                </div>

            </div>

            <hr class="border-secondary my-4">

            <!-- Copyright Notice -->
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start small text-secondary">
                    &copy; <?= date('Y') ?> WanderWay Travels. All rights reserved.
                </div>
                <div class="col-md-6 text-center text-md-end small text-secondary mt-2 mt-md-0">
                    Designed for memorable journeys.
                </div>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Floating Button (Placeholder number: 919876543210) -->
    <a href="https://wa.me/919876543210?text=Hello%20WanderWay%20Travels%2C%20I%20would%20like%20to%20enquire%20about%20travel%20packages" 
       class="whatsapp-float" 
       target="_blank" 
       rel="noopener noreferrer" 
       aria-label="Chat with us on WhatsApp" 
       title="Chat with us on WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>

    <!-- Bootstrap 5 Bundle JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
