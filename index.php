<?php
// Include site configuration and database connection
require_once __DIR__ . '/config/config.php';

// Set page title and SEO meta description
$page_title = 'WanderWay Travels - Discover Beautiful Destinations';
$meta_description = 'Discover extraordinary travel packages, scenic holiday escapes, and custom vacations worldwide with WanderWay Travels.';

// Fetch 3 latest active packages from the database using a prepared statement
$sql = "SELECT id, title, destination, price, days, description, image FROM packages WHERE is_active = ? ORDER BY id DESC LIMIT 3";
$stmt = $conn->prepare($sql);
$active_flag = 1;
$stmt->bind_param("i", $active_flag);
$stmt->execute();
$result = $stmt->get_result();

$packages = [];
while ($row = $result->fetch_assoc()) {
    $packages[] = $row;
}
$stmt->close();

// Include common header
require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Hero Banner Section -->
<section class="hero-section text-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <span class="badge bg-accent-light text-accent px-3 py-2 rounded-pill fw-semibold mb-3">
                    <i class="bi bi-geo-alt-fill me-1"></i> Explore The World With Confidence
                </span>
                <h1 class="mb-3">Find Your Perfect Journey With WanderWay</h1>
                <p class="mb-4">
                    Handcrafted vacation packages, scenic escapes, and memorable travel adventures at honest prices.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="<?= BASE_URL ?>packages.php" class="btn btn-accent btn-lg px-4 shadow">
                        <i class="bi bi-grid-fill me-2"></i> Browse Packages
                    </a>
                    <a href="<?= BASE_URL ?>enquiry.php" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-chat-square-text me-2"></i> Enquire Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. Latest Featured Packages Section -->
<section class="py-5">
    <div class="container py-3">
        <!-- Section Title -->
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <h6 class="text-accent text-uppercase fw-bold mb-1 letter-spacing-1">Top Destinations</h6>
                <h2 class="fw-bold mb-0">Featured Holiday Packages</h2>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="<?= BASE_URL ?>packages.php" class="btn btn-outline-accent">
                    View All Packages <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <!-- Packages Cards Grid -->
        <div class="row g-4">
            <?php if (!empty($packages)): ?>
                <?php foreach ($packages as $pkg): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="package-card">
                            <!-- Package Image -->
                            <div class="package-card-img-wrapper">
                                <img src="<?= htmlspecialchars(imageSrc($pkg['image'])) ?>" alt="<?= htmlspecialchars($pkg['title']) ?>" loading="lazy">
                                <span class="package-badge-days">
                                    <i class="bi bi-clock me-1"></i> <?= (int)$pkg['days'] ?> Days
                                </span>
                            </div>

                            <!-- Package Content -->
                            <div class="card-body">
                                <div class="destination-tag">
                                    <i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($pkg['destination']) ?>
                                </div>
                                <h5 class="card-title"><?= htmlspecialchars($pkg['title']) ?></h5>
                                <p class="card-text">
                                    <?php 
                                        $desc = strip_tags($pkg['description']);
                                        echo htmlspecialchars(mb_substr($desc, 0, 95)) . (mb_strlen($desc) > 95 ? '...' : ''); 
                                    ?>
                                </p>

                                <hr class="my-3 text-secondary opacity-25">

                                <!-- Price & Details Action -->
                                <div class="d-flex align-items-center justify-content-between mt-auto">
                                    <div>
                                        <div class="package-price">
                                            ₹<?= number_format((float)$pkg['price']) ?>
                                            <span>/ person</span>
                                        </div>
                                    </div>
                                    <a href="<?= BASE_URL ?>package.php?id=<?= (int)$pkg['id'] ?>" class="btn btn-sm btn-accent px-3">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center py-4 rounded-3 border-0 shadow-sm" role="alert">
                        <i class="bi bi-info-circle fs-3 d-block mb-2 text-accent"></i>
                        <h5 class="alert-heading fw-bold">No Packages Available Yet</h5>
                        <p class="mb-0 text-muted">We are currently updating our travel offerings. Please check back shortly or send us a custom enquiry!</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 3. Why Choose Us Section -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container py-3">
        <div class="text-center mb-5">
            <h6 class="text-accent text-uppercase fw-bold mb-1">Our Commitment</h6>
            <h2 class="fw-bold">Why Travel With WanderWay?</h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">
                We take care of every detail so you can focus on making unforgettable memories.
            </p>
        </div>

        <div class="row g-4">
            <!-- Feature 1 -->
            <div class="col-lg-3 col-sm-6">
                <div class="feature-box">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Best Price Value</h5>
                    <p class="text-muted small mb-0">Transparent pricing with no hidden charges and the best value for every itinerary.</p>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="col-lg-3 col-sm-6">
                <div class="feature-box">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-map"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Tailored Itineraries</h5>
                    <p class="text-muted small mb-0">Custom tours created by experienced travel specialists to match your travel style.</p>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="col-lg-3 col-sm-6">
                <div class="feature-box">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h5 class="fw-bold mb-2">24/7 Support</h5>
                    <p class="text-muted small mb-0">Dedicated support team standing by to assist you before, during, and after your trip.</p>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="col-lg-3 col-sm-6">
                <div class="feature-box">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-award"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Verified Partners</h5>
                    <p class="text-muted small mb-0">We work only with vetted hotels, certified local guides, and trusted transport operators.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 4. Call To Action (Enquiry) Section -->
<section class="py-5">
    <div class="container py-3">
        <div class="cta-section text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2 class="fw-bold mb-3">Ready to Plan Your Dream Vacation?</h2>
                    <p class="lead mb-4 opacity-90">
                        Have a specific destination or custom budget in mind? Send us your requirements and our holiday specialists will craft a personalized quote for you.
                    </p>
                    <a href="<?= BASE_URL ?>enquiry.php" class="btn btn-light btn-lg px-4 text-accent fw-bold shadow">
                        <i class="bi bi-send-fill me-2"></i> Send An Enquiry
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include common footer
require_once __DIR__ . '/includes/footer.php';
?>
