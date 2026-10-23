<?php
// Set HTTP 404 status code
http_response_code(404);

// Include site configuration
require_once __DIR__ . '/config/config.php';

// SEO Meta Tags
$page_title = '404 - Page Not Found - ' . ($site_contact['name'] ?? 'WanderWay Travels');
$meta_description = 'The page you requested could not be found. Explore our holiday tour packages or return to our homepage.';

// Include common header
require_once __DIR__ . '/includes/header.php';
?>

<!-- 404 Error Section -->
<section class="py-5 my-5">
    <div class="container py-4 text-center">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                    
                    <!-- 404 Badge Icon -->
                    <div class="mb-4">
                        <span class="d-inline-flex align-items-center justify-content-center bg-accent-light text-accent rounded-circle" style="width: 100px; height: 100px;">
                            <i class="bi bi-compass fs-1"></i>
                        </span>
                    </div>

                    <h1 class="display-4 fw-bold text-dark mb-2">404</h1>
                    <h3 class="fw-bold mb-3">Oops! You have wandered off the path</h3>
                    <p class="text-muted mb-4">
                        The page or destination you are searching for does not exist, has been moved, or is temporarily unavailable.
                    </p>

                    <!-- Helpful Navigation Links -->
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="<?= BASE_URL ?>" class="btn btn-accent px-4 py-2 fw-semibold shadow-sm">
                            <i class="bi bi-house-door me-1"></i> Return Home
                        </a>
                        <a href="<?= BASE_URL ?>packages.php" class="btn btn-outline-accent px-4 py-2 fw-semibold">
                            <i class="bi bi-grid-fill me-1"></i> Browse Packages
                        </a>
                        <a href="<?= BASE_URL ?>contact.php" class="btn btn-outline-secondary px-4 py-2">
                            <i class="bi bi-telephone me-1"></i> Contact Support
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
