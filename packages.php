<?php
// Include site configuration
require_once __DIR__ . '/config/config.php';

// Get search query from GET
$search_term = trim($_GET['q'] ?? '');

// Fetch packages using prepared statement
if ($search_term !== '') {
    $like_query = '%' . $search_term . '%';
    $stmt = $conn->prepare("SELECT id, title, destination, price, days, description, image FROM packages WHERE is_active = 1 AND (title LIKE ? OR destination LIKE ?) ORDER BY id DESC");
    $stmt->bind_param("ss", $like_query, $like_query);
} else {
    $stmt = $conn->prepare("SELECT id, title, destination, price, days, description, image FROM packages WHERE is_active = 1 ORDER BY id DESC");
}

$stmt->execute();
$result = $stmt->get_result();

$packages = [];
while ($row = $result->fetch_assoc()) {
    $packages[] = $row;
}
$stmt->close();

// Page Title and SEO Meta Description
$page_title = ($search_term !== '') 
    ? 'Search: ' . htmlspecialchars($search_term) . ' - Tour Packages - WanderWay Travels' 
    : 'All Tour Packages - WanderWay Travels';
$meta_description = ($search_term !== '') 
    ? 'Browse holiday tour packages matching "' . htmlspecialchars($search_term) . '" with WanderWay Travels.' 
    : 'Browse all active holiday tour packages, vacation getaways, and scenic itineraries with WanderWay Travels.';

// Include common header
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header / Hero Banner -->
<section class="bg-accent-light py-5 border-bottom">
    <div class="container py-2">
        <div class="row align-items-center justify-content-between">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <span class="badge bg-white text-accent border border-accent px-3 py-1.5 rounded-pill fw-semibold mb-2">
                    <i class="bi bi-compass me-1"></i> Wanderlust Awaits
                </span>
                <h1 class="fw-bold mb-2">Explore Holiday Packages</h1>
                <p class="text-muted mb-0">
                    Discover unforgettable vacations, thrilling excursions, and peaceful getaways designed for every traveler.
                </p>
            </div>

            <!-- Search Form -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-2 bg-white">
                    <form method="GET" action="<?= BASE_URL ?>packages.php" class="d-flex align-items-center gap-2">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0 text-muted ps-3 pe-2">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   name="q" 
                                   class="form-control border-0 shadow-none ps-0" 
                                   placeholder="Search by destination or package..." 
                                   value="<?= htmlspecialchars($search_term) ?>" 
                                   aria-label="Search tour packages">
                        </div>
                        <button type="submit" class="btn btn-accent px-4 py-2 fw-semibold rounded-3 text-nowrap">
                            Search
                        </button>
                        <?php if ($search_term !== ''): ?>
                            <a href="<?= BASE_URL ?>packages.php" class="btn btn-light px-3 py-2 text-secondary rounded-3" title="Clear Search">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Packages Listing Section -->
<section class="py-5">
    <div class="container py-3">

        <!-- Search Results Count Info -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <?php if ($search_term !== ''): ?>
                    <h5 class="fw-bold mb-1">
                        Search Results for <span class="text-accent">"<?= htmlspecialchars($search_term) ?>"</span>
                    </h5>
                    <p class="text-muted small mb-0">Showing <?= count($packages) ?> matching tour package(s)</p>
                <?php else: ?>
                    <h5 class="fw-bold mb-1">Available Holiday Packages</h5>
                    <p class="text-muted small mb-0">Showing <?= count($packages) ?> active vacation packages</p>
                <?php endif; ?>
            </div>
            
            <?php if ($search_term !== ''): ?>
                <a href="<?= BASE_URL ?>packages.php" class="btn btn-outline-secondary btn-sm mt-2 mt-md-0">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset All Filters
                </a>
            <?php endif; ?>
        </div>

        <!-- Packages Grid -->
        <div class="row g-4">
            <?php if (!empty($packages)): ?>
                <?php foreach ($packages as $pkg): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="package-card">
                            <!-- Package Image -->
                            <div class="package-card-img-wrapper">
                                <img src="<?= htmlspecialchars(imageSrc($pkg['image'])) ?>" 
                                     alt="<?= htmlspecialchars($pkg['title']) ?>" 
                                     loading="lazy"
                                     onerror="this.src='<?= BASE_URL ?>assets/img/placeholder.svg';">
                                <span class="package-badge-days">
                                    <i class="bi bi-clock me-1"></i> <?= (int)$pkg['days'] ?> Days
                                </span>
                            </div>

                            <!-- Package Body -->
                            <div class="card-body">
                                <div class="destination-tag">
                                    <i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($pkg['destination']) ?>
                                </div>
                                <h5 class="card-title"><?= htmlspecialchars($pkg['title']) ?></h5>
                                <p class="card-text">
                                    <?php 
                                        $desc = strip_tags($pkg['description']);
                                        echo htmlspecialchars(mb_substr($desc, 0, 100)) . (mb_strlen($desc) > 100 ? '...' : ''); 
                                    ?>
                                </p>

                                <hr class="my-3 text-secondary opacity-25">

                                <!-- Price and Action Link -->
                                <div class="d-flex align-items-center justify-content-between mt-auto">
                                    <div>
                                        <div class="package-price">
                                            ₹<?= number_format((float)$pkg['price']) ?>
                                            <span>/ person</span>
                                        </div>
                                    </div>
                                    <a href="<?= BASE_URL ?>package.php?id=<?= (int)$pkg['id'] ?>" class="btn btn-sm btn-accent px-3 shadow-sm">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- No Packages Found Friendly Message -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 text-center py-5 px-3">
                        <div class="py-4">
                            <div class="mb-3">
                                <span class="d-inline-flex align-items-center justify-content-center bg-accent-light text-accent rounded-circle" style="width: 80px; height: 80px;">
                                    <i class="bi bi-geo-alt-fill fs-1"></i>
                                </span>
                            </div>
                            <h4 class="fw-bold mb-2">No Packages Found</h4>
                            <?php if ($search_term !== ''): ?>
                                <p class="text-muted mx-auto mb-4" style="max-width: 500px;">
                                    We couldn't find any packages matching "<strong><?= htmlspecialchars($search_term) ?></strong>". Try checking for spelling errors or searching with a different destination keyword.
                                </p>
                                <a href="<?= BASE_URL ?>packages.php" class="btn btn-accent px-4 py-2 fw-semibold">
                                    <i class="bi bi-arrow-left me-1"></i> View All Packages
                                </a>
                            <?php else: ?>
                                <p class="text-muted mx-auto mb-4" style="max-width: 500px;">
                                    There are currently no active holiday packages available. Please check back later or get in touch with our team for a personalized itinerary.
                                </p>
                                <a href="<?= BASE_URL ?>enquiry.php" class="btn btn-accent px-4 py-2 fw-semibold">
                                    <i class="bi bi-chat-square-text me-1"></i> Send Us An Enquiry
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php
// Include common footer
require_once __DIR__ . '/includes/footer.php';
?>
