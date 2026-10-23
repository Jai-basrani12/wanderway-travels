<?php
// Include site configuration
require_once __DIR__ . '/config/config.php';

// Validate package ID from GET
$package_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$package = null;

if ($package_id > 0) {
    // Fetch active package details using prepared statement
    $stmt = $conn->prepare("SELECT id, title, destination, price, days, description, image, is_active FROM packages WHERE id = ? AND is_active = 1 LIMIT 1");
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $package = $result->fetch_assoc();
    $stmt->close();
}

// Handle 404 Not Found if id is missing, invalid, or package is inactive
if (!$package) {
    http_response_code(404);
    $page_title = 'Package Not Found - WanderWay Travels';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <section class="py-5">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-7 col-lg-6 text-center">
                    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                        <div class="mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle" style="width: 80px; height: 80px;">
                                <i class="bi bi-geo-alt-fill fs-1"></i>
                            </span>
                        </div>
                        <h2 class="fw-bold mb-2">Package Not Found</h2>
                        <p class="text-muted mb-4">
                            The vacation package you are looking for does not exist, has been removed, or is currently unavailable.
                        </p>
                        <div>
                            <a href="<?= BASE_URL ?>packages.php" class="btn btn-accent px-4 py-2 fw-semibold">
                                <i class="bi bi-arrow-left me-1"></i> Browse All Packages
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Set dynamic page title
$page_title = htmlspecialchars($package['title']) . ' - WanderWay Travels';

// Include common header
require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumb Navigation -->
<div class="bg-white border-bottom py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-decoration-none text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>packages.php" class="text-decoration-none text-muted">Packages</a></li>
                <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page"><?= htmlspecialchars($package['title']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Package Details Section -->
<section class="py-5">
    <div class="container py-2">
        <div class="row g-4 lg:g-5">

            <!-- Left Column: Image & Description -->
            <div class="col-lg-8">
                
                <!-- Package Hero Image -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="position-relative" style="height: 420px; background-color: #e2e8f0;">
                        <img src="<?= htmlspecialchars(imageSrc($package['image'])) ?>" 
                             alt="<?= htmlspecialchars($package['title']) ?>" 
                             class="w-100 h-100 object-fit-cover" 
                             onerror="this.src='<?= BASE_URL ?>assets/img/placeholder.svg';">
                        
                        <span class="position-absolute top-0 end-0 m-3 badge bg-dark bg-opacity-75 fs-6 px-3 py-2 rounded-pill backdrop-blur">
                            <i class="bi bi-clock me-1"></i> <?= (int)$package['days'] ?> Days Duration
                        </span>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        
                        <!-- Destination Badge -->
                        <div class="destination-tag mb-2">
                            <i class="bi bi-geo-alt-fill me-1"></i> <?= htmlspecialchars($package['destination']) ?>
                        </div>

                        <!-- Package Title -->
                        <h1 class="fw-bold mb-3"><?= htmlspecialchars($package['title']) ?></h1>

                        <!-- Quick Badges -->
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <span class="badge bg-light text-secondary border px-3 py-2">
                                <i class="bi bi-calendar-check me-1 text-accent"></i> Flexible Dates
                            </span>
                            <span class="badge bg-light text-secondary border px-3 py-2">
                                <i class="bi bi-person-check me-1 text-accent"></i> Guided Tour
                            </span>
                            <span class="badge bg-light text-secondary border px-3 py-2">
                                <i class="bi bi-shield-check me-1 text-accent"></i> Verified Operator
                            </span>
                        </div>

                        <hr class="my-4 text-secondary opacity-25">

                        <!-- Description -->
                        <h4 class="fw-bold mb-3">Package Overview & Itinerary</h4>
                        <div class="package-description text-secondary lh-lg" style="font-size: 1.05rem;">
                            <?= nl2br(htmlspecialchars($package['description'])) ?>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Right Column: Booking & Enquiry Card -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 sticky-top p-4" style="top: 100px;">
                    
                    <div class="mb-3">
                        <span class="text-muted small text-uppercase fw-bold letter-spacing-1">Starting From</span>
                        <div class="d-flex align-items-baseline gap-2 mt-1">
                            <h2 class="fw-bold text-accent mb-0">₹<?= number_format((float)$package['price']) ?></h2>
                            <span class="text-muted small">/ person</span>
                        </div>
                    </div>

                    <hr class="my-3 text-secondary opacity-25">

                    <!-- Key Details List -->
                    <ul class="list-unstyled mb-4 small">
                        <li class="d-flex justify-content-between py-2 border-bottom border-light">
                            <span class="text-muted"><i class="bi bi-geo-alt me-2 text-accent"></i>Destination</span>
                            <span class="fw-semibold text-end"><?= htmlspecialchars($package['destination']) ?></span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom border-light">
                            <span class="text-muted"><i class="bi bi-clock me-2 text-accent"></i>Duration</span>
                            <span class="fw-semibold text-end"><?= (int)$package['days'] ?> Days</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom border-light">
                            <span class="text-muted"><i class="bi bi-patch-check me-2 text-accent"></i>Status</span>
                            <span class="badge bg-success-subtle text-success">Available to Book</span>
                        </li>
                    </ul>

                    <!-- Call To Action Button -->
                    <a href="<?= BASE_URL ?>enquiry.php?package_id=<?= (int)$package['id'] ?>" class="btn btn-accent btn-lg w-100 py-3 fw-bold shadow-sm mb-3">
                        <i class="bi bi-send-fill me-2"></i> Enquire Now
                    </a>

                    <!-- Assistance Note -->
                    <div class="bg-light p-3 rounded-3 text-center small text-muted">
                        <i class="bi bi-chat-dots me-1 text-accent"></i>
                        Need assistance or custom changes? Contact our travel experts anytime.
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
