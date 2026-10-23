<?php
// Include site configuration
require_once __DIR__ . '/config/config.php';

// SEO Meta Tags
$page_title = 'Contact Us - ' . ($site_contact['name'] ?? 'WanderWay Travels');
$meta_description = 'Get in touch with WanderWay Travels. Reach us by phone, email, WhatsApp, or visit our office for customized travel planning.';

// Include common header
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner -->
<section class="bg-accent-light py-5 border-bottom">
    <div class="container py-2 text-center">
        <span class="badge bg-white text-accent border border-accent px-3 py-1.5 rounded-pill fw-semibold mb-2">
            <i class="bi bi-headset me-1"></i> We are here to help
        </span>
        <h1 class="fw-bold mb-2">Get In Touch With Us</h1>
        <p class="text-muted mx-auto mb-0" style="max-width: 600px;">
            Have questions about our packages or need a custom travel itinerary? Connect with our dedicated holiday consultants today.
        </p>
    </div>
</section>

<!-- Contact Info & Map Section -->
<section class="py-5">
    <div class="container py-3">

        <!-- Contact Cards Row -->
        <div class="row g-4 mb-5">
            
            <!-- Address Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 text-center p-4">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center bg-accent-light text-accent rounded-circle" style="width: 60px; height: 60px;">
                            <i class="bi bi-geo-alt-fill fs-3"></i>
                        </span>
                    </div>
                    <h5 class="fw-bold mb-2">Visit Our Office</h5>
                    <p class="text-muted small mb-0 lh-base">
                        <?= htmlspecialchars($site_contact['address']) ?>
                    </p>
                </div>
            </div>

            <!-- Phone Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 text-center p-4">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center bg-accent-light text-accent rounded-circle" style="width: 60px; height: 60px;">
                            <i class="bi bi-telephone-fill fs-3"></i>
                        </span>
                    </div>
                    <h5 class="fw-bold mb-2">Call Us Directly</h5>
                    <p class="text-muted small mb-3">Speak with a travel consultant</p>
                    <a href="tel:<?= htmlspecialchars($site_contact['phone_clean']) ?>" class="btn btn-outline-accent btn-sm px-3 fw-semibold">
                        <?= htmlspecialchars($site_contact['phone']) ?>
                    </a>
                </div>
            </div>

            <!-- Email Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 text-center p-4">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center bg-accent-light text-accent rounded-circle" style="width: 60px; height: 60px;">
                            <i class="bi bi-envelope-fill fs-3"></i>
                        </span>
                    </div>
                    <h5 class="fw-bold mb-2">Send An Email</h5>
                    <p class="text-muted small mb-3">For enquiries and quotes</p>
                    <a href="mailto:<?= htmlspecialchars($site_contact['email']) ?>" class="btn btn-outline-accent btn-sm px-3 fw-semibold text-truncate d-inline-block mw-100">
                        <?= htmlspecialchars($site_contact['email']) ?>
                    </a>
                </div>
            </div>

            <!-- Working Hours Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 text-center p-4">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center bg-accent-light text-accent rounded-circle" style="width: 60px; height: 60px;">
                            <i class="bi bi-clock-fill fs-3"></i>
                        </span>
                    </div>
                    <h5 class="fw-bold mb-2">Working Hours</h5>
                    <p class="text-muted small mb-0 lh-base">
                        <?= htmlspecialchars($site_contact['working_hours']) ?>
                    </p>
                </div>
            </div>

        </div>

        <!-- Google Maps & WhatsApp Action Row -->
        <div class="row g-4 align-items-center">
            
            <!-- Google Maps Embed Placeholder -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-map text-accent me-2"></i> Our Location on Google Maps
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <!-- Responsive Google Maps Embed -->
                        <div class="ratio ratio-16x9" style="min-height: 380px;">
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14008.114885822606!2d77.2090212!3d28.6139391!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390cfd5b347eb62d%3A0x37205b715389640!2sConnaught%20Place%2C%20New%20Delhi%2C%20Delhi%20110001!5e0!3m2!1sen!2sin!4v1700000000000!5m2!1sen!2sin" 
                                style="border:0;" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade" 
                                title="WanderWay Travels Office Location">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WhatsApp & Direct Chat Column -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 bg-white">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 72px; height: 72px; background-color: #25d366; color: #ffffff;">
                            <i class="bi bi-whatsapp fs-1"></i>
                        </span>
                    </div>
                    <h4 class="fw-bold mb-2">Instant WhatsApp Chat</h4>
                    <p class="text-muted small mb-4">
                        Chat directly with our holiday planning team for rapid answers to your travel queries, package quotes, and itinerary customizations.
                    </p>
                    <a href="https://wa.me/<?= htmlspecialchars($site_contact['whatsapp']) ?>?text=<?= urlencode('Hello ' . $site_contact['name'] . ', I would like to enquire about travel packages') ?>" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="btn btn-success btn-lg w-100 py-3 fw-bold shadow-sm mb-3" 
                       style="background-color: #25d366; border-color: #25d366;">
                        <i class="bi bi-whatsapp me-2"></i> Start Chat on WhatsApp
                    </a>

                    <hr class="my-3 text-secondary opacity-25">

                    <div class="text-start small text-muted">
                        <div class="fw-bold text-dark mb-1">Looking for a tailored tour?</div>
                        <p class="mb-2">Send us your exact requirements with our detailed form.</p>
                        <a href="<?= BASE_URL ?>enquiry.php" class="text-accent fw-semibold text-decoration-none">
                            Go to Custom Enquiry Form <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>

<?php
// Include common footer
require_once __DIR__ . '/includes/footer.php';
?>
