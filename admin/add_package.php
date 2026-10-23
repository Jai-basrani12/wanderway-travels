<?php
// Include site configuration
require_once __DIR__ . '/../config/config.php';

// Protect admin access
require_once __DIR__ . '/../includes/admin_check.php';

// Include upload helper
require_once __DIR__ . '/../includes/upload.php';

$errors = [];
$title = '';
$destination = '';
$price = '';
$days = '';
$description = '';
$is_active = 1; // Default to active

// Handle form submission
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $days = trim($_POST['days'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Server-side validation
    if ($title === '') {
        $errors[] = 'Package title is required.';
    } elseif (mb_strlen($title) > 255) {
        $errors[] = 'Package title must not exceed 255 characters.';
    }

    if ($destination === '') {
        $errors[] = 'Destination is required.';
    } elseif (mb_strlen($destination) > 255) {
        $errors[] = 'Destination must not exceed 255 characters.';
    }

    if ($price === '' || !is_numeric($price) || (float)$price <= 0) {
        $errors[] = 'Please enter a valid price greater than zero.';
    }

    if ($days === '' || !ctype_digit($days) || (int)$days < 1) {
        $errors[] = 'Please enter a valid duration (minimum 1 day).';
    }

    if ($description === '') {
        $errors[] = 'Package description is required.';
    }

    // Process image upload if a file was selected
    $image_filename = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $image_filename = uploadImage($_FILES['image'], $errors);
    }

    // If no errors, insert into database using prepared statement
    if (empty($errors)) {
        $float_price = (float)$price;
        $int_days = (int)$days;

        $stmt = $conn->prepare("INSERT INTO packages (title, destination, price, days, description, image, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdissi", $title, $destination, $float_price, $int_days, $description, $image_filename, $is_active);

        if ($stmt->execute()) {
            $stmt->close();
            $_SESSION['success_message'] = 'Package created successfully.';
            header("Location: " . BASE_URL . "admin/packages.php");
            exit;
        } else {
            $errors[] = 'Database error: ' . $stmt->error;
            $stmt->close();
            // If DB insert failed, clean up the newly uploaded image
            if ($image_filename) {
                deleteLocalImage($image_filename);
            }
        }
    }
}

$page_title = 'Add New Package - WanderWay Admin';
$admin_name = $_SESSION['admin_username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Theme CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body class="bg-light">

    <!-- Admin Navigation Bar -->
    <nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>admin/dashboard.php">
                <i class="bi bi-compass text-accent me-2 fs-4"></i>
                <span>WanderWay</span>&nbsp;<span class="badge bg-accent-light text-accent ms-1 fs-6">Admin</span>
            </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <li class="nav-item">
                        <a class="nav-link text-secondary" href="<?= BASE_URL ?>admin/dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active fw-bold text-accent" href="<?= BASE_URL ?>admin/packages.php">
                            <i class="bi bi-box-seam me-1"></i> Packages
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-secondary" href="<?= BASE_URL ?>admin/enquiries.php">
                            <i class="bi bi-envelope me-1"></i> Enquiries
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <a href="<?= BASE_URL ?>" target="_blank" class="btn btn-outline-secondary btn-sm" title="View live customer site">
                        <i class="bi bi-box-arrow-up-right me-1"></i> View Website
                    </a>

                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle border d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle text-accent"></i>
                            <span><?= htmlspecialchars($admin_name) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><h6 class="dropdown-header">Signed in as <strong><?= htmlspecialchars($admin_name) ?></strong></h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger d-flex align-items-center gap-2" href="<?= BASE_URL ?>admin/logout.php">
                                    <i class="bi bi-box-arrow-right"></i> Sign Out
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="py-5">
        <div class="container">

            <div class="row justify-content-center">
                <div class="col-lg-9">

                    <!-- Header with Breadcrumb / Back button -->
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h2 class="fw-bold mb-1">Add Tour Package</h2>
                            <p class="text-muted mb-0">Fill in the details below to create a new holiday package.</p>
                        </div>
                        <a href="<?= BASE_URL ?>admin/packages.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Back to Packages
                        </a>
                    </div>

                    <!-- Error Alert -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger rounded-3 shadow-sm mb-4" role="alert">
                            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i> Please fix the following errors:</div>
                            <ul class="mb-0 ps-3 small">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Form Card -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 p-md-5">
                            <form method="POST" action="" enctype="multipart/form-data">

                                <!-- Package Title -->
                                <div class="mb-3">
                                    <label for="title" class="form-label fw-semibold">Package Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($title) ?>" placeholder="e.g. Majestic Kashmir & Dal Lake Tour" required>
                                </div>

                                <div class="row g-3 mb-3">
                                    <!-- Destination -->
                                    <div class="col-md-6">
                                        <label for="destination" class="form-label fw-semibold">Destination <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="destination" name="destination" value="<?= htmlspecialchars($destination) ?>" placeholder="e.g. Srinagar & Gulmarg, India" required>
                                    </div>

                                    <!-- Price -->
                                    <div class="col-md-3">
                                        <label for="price" class="form-label fw-semibold">Price (INR) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">₹</span>
                                            <input type="number" step="0.01" min="1" class="form-control" id="price" name="price" value="<?= htmlspecialchars($price) ?>" placeholder="24999.00" required>
                                        </div>
                                    </div>

                                    <!-- Duration (Days) -->
                                    <div class="col-md-3">
                                        <label for="days" class="form-label fw-semibold">Duration (Days) <span class="text-danger">*</span></label>
                                        <input type="number" min="1" class="form-control" id="days" name="days" value="<?= htmlspecialchars($days) ?>" placeholder="5" required>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="description" name="description" rows="5" placeholder="Detailed itinerary, highlights, and inclusions..." required><?= htmlspecialchars($description) ?></textarea>
                                </div>

                                <!-- Image Upload -->
                                <div class="mb-4">
                                    <label for="image" class="form-label fw-semibold">Package Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept=".jpg,.jpeg,.png">
                                    <div class="form-text small text-muted">
                                        Supported formats: JPG, JPEG, PNG. Maximum file size: 2MB. Optional (a placeholder will be used if omitted).
                                    </div>
                                </div>

                                <!-- Is Active Checkbox -->
                                <div class="form-check form-switch mb-4">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" <?= ($is_active == 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="is_active">
                                        Mark as Active (Visible to website visitors)
                                    </label>
                                </div>

                                <hr class="my-4">

                                <!-- Submit Actions -->
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-accent px-4 py-2 fw-semibold shadow-sm">
                                        <i class="bi bi-check-lg me-1"></i> Save Package
                                    </button>
                                    <a href="<?= BASE_URL ?>admin/packages.php" class="btn btn-outline-secondary px-4 py-2">
                                        Cancel
                                    </a>
                                </div>

                            </form>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <!-- Bootstrap 5 Bundle JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
