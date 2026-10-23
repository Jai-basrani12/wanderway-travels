<?php
// Include site configuration
require_once __DIR__ . '/../config/config.php';

// Protect admin access
require_once __DIR__ . '/../includes/admin_check.php';

// Include upload helper
require_once __DIR__ . '/../includes/upload.php';

// Get and validate package ID
$package_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($package_id <= 0) {
    $_SESSION['error_message'] = 'Invalid package ID specified.';
    header("Location: " . BASE_URL . "admin/packages.php");
    exit;
}

// Fetch existing package from database using prepared statement
$stmt = $conn->prepare("SELECT * FROM packages WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$result = $stmt->get_result();
$package = $result->fetch_assoc();
$stmt->close();

if (!$package) {
    $_SESSION['error_message'] = 'Package not found.';
    header("Location: " . BASE_URL . "admin/packages.php");
    exit;
}

$errors = [];
$title = $package['title'];
$destination = $package['destination'];
$price = $package['price'];
$days = $package['days'];
$description = $package['description'];
$is_active = (int)$package['is_active'];
$current_image = $package['image'];

// Handle form submission
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token verification failed. Please try again.';
    }

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

    // Process image replacement if a new file was uploaded
    $image_to_save = $current_image;
    $new_uploaded_image = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $new_uploaded_image = uploadImage($_FILES['image'], $errors);
        if ($new_uploaded_image) {
            $image_to_save = $new_uploaded_image;
        }
    }

    // Update package if no validation errors
    if (empty($errors)) {
        $float_price = (float)$price;
        $int_days = (int)$days;

        $update_stmt = $conn->prepare("UPDATE packages SET title = ?, destination = ?, price = ?, days = ?, description = ?, image = ?, is_active = ? WHERE id = ?");
        $update_stmt->bind_param("ssdissii", $title, $destination, $float_price, $int_days, $description, $image_to_save, $is_active, $package_id);

        if ($update_stmt->execute()) {
            $update_stmt->close();

            // If a new image was successfully saved, delete the old local image
            if ($new_uploaded_image && $current_image) {
                deleteLocalImage($current_image);
            }

            $_SESSION['success_message'] = 'Package updated successfully.';
            header("Location: " . BASE_URL . "admin/packages.php");
            exit;
        } else {
            $errors[] = 'Database error: ' . $update_stmt->error;
            $update_stmt->close();

            // If query failed, remove the newly uploaded file to avoid orphan files
            if ($new_uploaded_image) {
                deleteLocalImage($new_uploaded_image);
            }
        }
    }
}

$page_title = 'Edit Package - WanderWay Admin';
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
                            <h2 class="fw-bold mb-1">Edit Tour Package</h2>
                            <p class="text-muted mb-0">Update package details, price, duration, or image.</p>
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
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">

                                <!-- Package Title -->
                                <div class="mb-3">
                                    <label for="title" class="form-label fw-semibold">Package Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($title) ?>" required>
                                </div>

                                <div class="row g-3 mb-3">
                                    <!-- Destination -->
                                    <div class="col-md-6">
                                        <label for="destination" class="form-label fw-semibold">Destination <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="destination" name="destination" value="<?= htmlspecialchars($destination) ?>" required>
                                    </div>

                                    <!-- Price -->
                                    <div class="col-md-3">
                                        <label for="price" class="form-label fw-semibold">Price (INR) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">₹</span>
                                            <input type="number" step="0.01" min="1" class="form-control" id="price" name="price" value="<?= htmlspecialchars($price) ?>" required>
                                        </div>
                                    </div>

                                    <!-- Duration (Days) -->
                                    <div class="col-md-3">
                                        <label for="days" class="form-label fw-semibold">Duration (Days) <span class="text-danger">*</span></label>
                                        <input type="number" min="1" class="form-control" id="days" name="days" value="<?= htmlspecialchars($days) ?>" required>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="description" name="description" rows="5" required><?= htmlspecialchars($description) ?></textarea>
                                </div>

                                <!-- Current Image Preview & Replacement -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold d-block">Current Package Image</label>
                                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 border mb-3">
                                        <img src="<?= htmlspecialchars(imageSrc($current_image)) ?>" 
                                             alt="<?= htmlspecialchars($title) ?>" 
                                             class="rounded-3 border" 
                                             style="width: 120px; height: 80px; object-fit: cover;"
                                             onerror="this.src='<?= BASE_URL ?>assets/img/placeholder.svg';">
                                        <div>
                                            <span class="badge bg-secondary-subtle text-secondary mb-1">
                                                <?= (strpos((string)$current_image, 'http') === 0) ? 'External URL' : (!empty($current_image) ? 'Local Upload' : 'No Image (Placeholder)') ?>
                                            </span>
                                            <div class="small text-muted font-monospace text-break">
                                                <?= htmlspecialchars($current_image ?: 'None') ?>
                                            </div>
                                        </div>
                                    </div>

                                    <label for="image" class="form-label fw-semibold">Upload New Image (Optional)</label>
                                    <input type="file" class="form-control" id="image" name="image" accept=".jpg,.jpeg,.png">
                                    <div class="form-text small text-muted">
                                        Leave empty to keep the existing image. Allowed: JPG, JPEG, PNG. Max: 2MB.
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
                                        <i class="bi bi-check-lg me-1"></i> Update Package
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
