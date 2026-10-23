<?php
// Include site configuration
require_once __DIR__ . '/config/config.php';

// Check if redirected after successful submission (Post/Redirect/Get)
$is_sent = isset($_GET['sent']) && $_GET['sent'] === '1';

// Fetch all active packages for dropdown
$pkg_stmt = $conn->prepare("SELECT id, title FROM packages WHERE is_active = 1 ORDER BY title ASC");
$pkg_stmt->execute();
$pkg_result = $pkg_stmt->get_result();
$active_packages = [];
while ($row = $pkg_result->fetch_assoc()) {
    $active_packages[] = $row;
}
$pkg_stmt->close();

// Initial form field values
$selected_package_id = isset($_GET['package_id']) ? (int)$_GET['package_id'] : 0;
$name = '';
$phone = '';
$email = '';
$travel_date = '';
$persons = '';
$message = '';
$errors = [];

// Handle form submission
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !$is_sent) {
    // 1. Honeypot spam check (silently pretend success if filled)
    if (!empty($_POST['website_url'])) {
        header("Location: " . BASE_URL . "enquiry.php?sent=1");
        exit;
    }

    // 2. CSRF Token Verification
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token verification failed. Please refresh and try again.';
    }

    // 3. Retrieve and sanitize input values
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $selected_package_id = isset($_POST['package_id']) ? (int)$_POST['package_id'] : 0;
    $travel_date = trim($_POST['travel_date'] ?? '');
    $persons = trim($_POST['persons'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // 4. Server-side validation
    // Name validation
    if ($name === '') {
        $errors[] = 'Your full name is required.';
    } elseif (mb_strlen($name) > 100) {
        $errors[] = 'Name cannot exceed 100 characters.';
    }

    // Phone validation (10 digit Indian mobile number, digits only)
    if ($phone === '') {
        $errors[] = 'Mobile phone number is required.';
    } elseif (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        $errors[] = 'Please enter a valid 10-digit Indian mobile number (e.g. 9876543210).';
    }

    // Email validation (optional, validated if provided)
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }

    // Package validation (optional, verify existence if selected)
    $valid_package_id = null;
    if ($selected_package_id > 0) {
        $valid_pkg = false;
        foreach ($active_packages as $pkg) {
            if ((int)$pkg['id'] === $selected_package_id) {
                $valid_pkg = true;
                $valid_package_id = $selected_package_id;
                break;
            }
        }
        if (!$valid_pkg) {
            $errors[] = 'Selected holiday package is invalid or currently unavailable.';
        }
    }

    // Travel Date validation (optional, must be today or later)
    $valid_travel_date = null;
    if ($travel_date !== '') {
        $today = date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $travel_date) || $travel_date < $today) {
            $errors[] = 'Travel date must be today or a future date.';
        } else {
            $valid_travel_date = $travel_date;
        }
    }

    // Number of persons validation (optional, 1 to 50)
    $valid_persons = null;
    if ($persons !== '') {
        if (!ctype_digit($persons) || (int)$persons < 1 || (int)$persons > 50) {
            $errors[] = 'Number of persons must be an integer between 1 and 50.';
        } else {
            $valid_persons = (int)$persons;
        }
    }

    // Message validation (optional, max 1000 characters)
    if (mb_strlen($message) > 1000) {
        $errors[] = 'Message cannot exceed 1000 characters.';
    }

    // 5. If no errors, insert into database using prepared statement
    if (empty($errors)) {
        $insert_stmt = $conn->prepare("INSERT INTO enquiries (name, phone, email, package_id, travel_date, persons, message) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $email_param = ($email !== '') ? $email : null;
        $message_param = ($message !== '') ? $message : null;

        $insert_stmt->bind_param("sssisss", $name, $phone, $email_param, $valid_package_id, $valid_travel_date, $valid_persons, $message_param);

        if ($insert_stmt->execute()) {
            $insert_stmt->close();
            // Redirect using Post/Redirect/Get pattern
            header("Location: " . BASE_URL . "enquiry.php?sent=1");
            exit;
        } else {
            $errors[] = 'Database error: ' . $insert_stmt->error;
            $insert_stmt->close();
        }
    }
}

// Page Title and SEO Meta Description
$page_title = 'Send An Enquiry - ' . ($site_contact['name'] ?? 'WanderWay Travels');
$meta_description = 'Request a customized vacation quote, plan your holiday itinerary, and get personal assistance from WanderWay Travels.';

// Include common header
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Banner -->
<section class="bg-accent-light py-5 border-bottom">
    <div class="container py-2 text-center">
        <span class="badge bg-white text-accent border border-accent px-3 py-1.5 rounded-pill fw-semibold mb-2">
            <i class="bi bi-chat-heart me-1"></i> Personalized Holiday Planning
        </span>
        <h1 class="fw-bold mb-2">Plan Your Custom Journey</h1>
        <p class="text-muted mx-auto mb-0" style="max-width: 600px;">
            Tell us about your dream destination, travel dates, and preferences. Our holiday consultants will curate the perfect itinerary for you.
        </p>
    </div>
</section>

<!-- Main Enquiry Section -->
<section class="py-5">
    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <?php if ($is_sent): ?>
                    <!-- Thank You Message (After Post/Redirect/Get) -->
                    <div class="card border-0 shadow-sm rounded-4 text-center p-4 p-md-5">
                        <div class="mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle" style="width: 84px; height: 84px;">
                                <i class="bi bi-check2-circle fs-1"></i>
                            </span>
                        </div>
                        <h2 class="fw-bold text-dark mb-2">Thank You For Your Enquiry!</h2>
                        <p class="text-muted mx-auto mb-4" style="max-width: 520px;">
                            We have received your travel request. One of our dedicated holiday specialists will review your requirements and reach out to you within 24 hours.
                        </p>
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="<?= BASE_URL ?>packages.php" class="btn btn-accent px-4 py-2 fw-semibold">
                                <i class="bi bi-compass me-1"></i> Explore More Packages
                            </a>
                            <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary px-4 py-2">
                                <i class="bi bi-house me-1"></i> Return Home
                            </a>
                        </div>
                    </div>

                <?php else: ?>

                    <!-- Form Card -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 p-md-5">

                            <!-- Validation Error Summary -->
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger rounded-3 shadow-sm mb-4" role="alert">
                                    <div class="fw-bold mb-1">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Please correct the following issues:
                                    </div>
                                    <ul class="mb-0 ps-3 small">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?= htmlspecialchars($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="<?= BASE_URL ?>enquiry.php" novalidate>
                                
                                <!-- CSRF Token -->
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">

                                <!-- Honeypot Anti-Spam Field (hidden from genuine users) -->
                                <div style="display: none;" aria-hidden="true">
                                    <label for="website_url">Leave this empty</label>
                                    <input type="text" id="website_url" name="website_url" tabindex="-1" autocomplete="off">
                                </div>

                                <div class="row g-3 mb-3">
                                    <!-- Full Name (Required) -->
                                    <div class="col-md-6">
                                        <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted"><i class="bi bi-person"></i></span>
                                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="e.g. Rahul Sharma" required>
                                        </div>
                                    </div>

                                    <!-- Mobile Phone (Required, 10 Digits) -->
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label fw-semibold">Mobile Phone (India) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted">+91</span>
                                            <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="9876543210" maxlength="10" pattern="[6-9][0-9]{9}" required>
                                        </div>
                                        <div class="form-text small text-muted">10-digit mobile number only.</div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <!-- Email Address (Optional) -->
                                    <div class="col-md-6">
                                        <label for="email" class="form-label fw-semibold">Email Address <span class="text-muted small fw-normal">(Optional)</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted"><i class="bi bi-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="name@example.com">
                                        </div>
                                    </div>

                                    <!-- Package Selection Dropdown -->
                                    <div class="col-md-6">
                                        <label for="package_id" class="form-label fw-semibold">Interested Package <span class="text-muted small fw-normal">(Optional)</span></label>
                                        <select class="form-select" id="package_id" name="package_id">
                                            <option value="0">General Enquiry (Custom Trip / No Package)</option>
                                            <?php foreach ($active_packages as $pkg): ?>
                                                <option value="<?= (int)$pkg['id'] ?>" <?= ((int)$pkg['id'] === $selected_package_id) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($pkg['title']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <!-- Travel Date (Optional, Today or Later) -->
                                    <div class="col-md-6">
                                        <label for="travel_date" class="form-label fw-semibold">Expected Travel Date <span class="text-muted small fw-normal">(Optional)</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted"><i class="bi bi-calendar3"></i></span>
                                            <input type="date" class="form-control" id="travel_date" name="travel_date" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($travel_date) ?>">
                                        </div>
                                    </div>

                                    <!-- Number of Persons (Optional, 1 to 50) -->
                                    <div class="col-md-6">
                                        <label for="persons" class="form-label fw-semibold">Number of Travelers <span class="text-muted small fw-normal">(Optional)</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted"><i class="bi bi-people"></i></span>
                                            <input type="number" class="form-control" id="persons" name="persons" min="1" max="50" value="<?= htmlspecialchars($persons) ?>" placeholder="e.g. 2">
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Notes / Message (Optional, Max 1000 characters) -->
                                <div class="mb-4">
                                    <label for="message" class="form-label fw-semibold">Special Requests / Message <span class="text-muted small fw-normal">(Optional, max 1000 chars)</span></label>
                                    <textarea class="form-control" id="message" name="message" rows="4" maxlength="1000" placeholder="Tell us about your budget, hotel preferences, meal plans, or any special requests..."><?= htmlspecialchars($message) ?></textarea>
                                </div>

                                <hr class="my-4">

                                <div class="d-grid d-sm-flex justify-content-sm-between align-items-center gap-3">
                                    <button type="submit" class="btn btn-accent btn-lg px-5 py-2 fw-semibold shadow-sm">
                                        <i class="bi bi-send-fill me-1"></i> Submit Enquiry
                                    </button>
                                    <small class="text-muted">
                                        <i class="bi bi-shield-check text-accent me-1"></i> Your information is kept completely confidential.
                                    </small>
                                </div>

                            </form>

                        </div>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<?php
// Include common footer
require_once __DIR__ . '/includes/footer.php';
?>
