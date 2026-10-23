<?php
// Include site configuration
require_once __DIR__ . '/../config/config.php';

// Protect admin access
require_once __DIR__ . '/../includes/admin_check.php';

// Handle POST actions (Toggle status and Delete)
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Security verification failed. Please try again.';
        header("Location: " . BASE_URL . "admin/packages.php");
        exit;
    }

    $action = $_POST['action'] ?? '';
    $package_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($package_id > 0) {
        // Action: Toggle Active / Inactive
        if ($action === 'toggle') {
            $stmt = $conn->prepare("SELECT is_active FROM packages WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $package_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $new_status = ($row['is_active'] == 1) ? 0 : 1;
                $update_stmt = $conn->prepare("UPDATE packages SET is_active = ? WHERE id = ?");
                $update_stmt->bind_param("ii", $new_status, $package_id);
                $update_stmt->execute();
                $update_stmt->close();
                $_SESSION['success_message'] = 'Package status changed to ' . ($new_status ? 'Active' : 'Inactive') . '.';
            } else {
                $_SESSION['error_message'] = 'Package not found.';
            }
            $stmt->close();

            header("Location: " . BASE_URL . "admin/packages.php");
            exit;
        }

        // Action: Delete Package
        if ($action === 'delete') {
            // Check if customer enquiries exist for this package
            $enq_stmt = $conn->prepare("SELECT COUNT(*) as enq_count FROM enquiries WHERE package_id = ?");
            $enq_stmt->bind_param("i", $package_id);
            $enq_stmt->execute();
            $enq_res = $enq_stmt->get_result();
            $enquiry_count = (int)($enq_res->fetch_assoc()['enq_count'] ?? 0);
            $enq_stmt->close();

            if ($enquiry_count > 0) {
                // Refuse to delete if enquiries exist
                $_SESSION['error_message'] = 'Cannot delete package: ' . $enquiry_count . ' customer enquiry(ies) are linked to this package.';
            } else {
                // Fetch package details to check for local image
                $pkg_stmt = $conn->prepare("SELECT image FROM packages WHERE id = ? LIMIT 1");
                $pkg_stmt->bind_param("i", $package_id);
                $pkg_stmt->execute();
                $pkg_res = $pkg_stmt->get_result();

                if ($pkg_data = $pkg_res->fetch_assoc()) {
                    // Only delete file if it is a local upload, never for URLs
                    deleteLocalImage($pkg_data['image']);

                    // Delete record from database
                    $del_stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
                    $del_stmt->bind_param("i", $package_id);
                    $del_stmt->execute();
                    $del_stmt->close();

                    $_SESSION['success_message'] = 'Package deleted successfully.';
                } else {
                    $_SESSION['error_message'] = 'Package not found.';
                }
                $pkg_stmt->close();
            }

            header("Location: " . BASE_URL . "admin/packages.php");
            exit;
        }
    }
}

// Retrieve flash messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Fetch all packages ordered by latest first
$query = "SELECT id, title, destination, price, days, image, is_active, created_at FROM packages ORDER BY id DESC";
$packages_result = $conn->query($query);

$page_title = 'Manage Packages - WanderWay Admin';
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

            <!-- Page Header -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                <div>
                    <h2 class="fw-bold mb-1">Tour Packages</h2>
                    <p class="text-muted mb-0">View, manage, edit, and publish holiday tour packages.</p>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>admin/add_package.php" class="btn btn-accent shadow-sm">
                        <i class="bi bi-plus-lg me-1"></i> Add New Package
                    </a>
                </div>
            </div>

            <!-- Flash Alerts -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center rounded-3 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div><?= htmlspecialchars($success_message) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center rounded-3 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div><?= htmlspecialchars($error_message) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Packages Table Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4" style="width: 60px;">#</th>
                                    <th scope="col" style="width: 100px;">Image</th>
                                    <th scope="col">Title</th>
                                    <th scope="col">Destination</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">Days</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-end pe-4" style="width: 220px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($packages_result && $packages_result->num_rows > 0): ?>
                                    <?php $serial = 1; ?>
                                    <?php while ($row = $packages_result->fetch_assoc()): ?>
                                        <tr>
                                            <!-- Serial Number -->
                                            <td class="ps-4 fw-semibold text-muted"><?= $serial++ ?></td>

                                            <!-- Thumbnail using imageSrc helper -->
                                            <td>
                                                <img src="<?= htmlspecialchars(imageSrc($row['image'])) ?>" 
                                                     alt="<?= htmlspecialchars($row['title']) ?>" 
                                                     class="rounded-3 border" 
                                                     style="width: 70px; height: 50px; object-fit: cover;"
                                                     loading="lazy"
                                                     onerror="this.src='<?= BASE_URL ?>assets/img/placeholder.svg';">
                                            </td>

                                            <!-- Title -->
                                            <td>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['title']) ?></div>
                                            </td>

                                            <!-- Destination -->
                                            <td>
                                                <span class="text-secondary small">
                                                    <i class="bi bi-geo-alt me-1 text-accent"></i><?= htmlspecialchars($row['destination']) ?>
                                                </span>
                                            </td>

                                            <!-- Price -->
                                            <td>
                                                <span class="fw-bold text-accent">₹<?= number_format((float)$row['price'], 2) ?></span>
                                            </td>

                                            <!-- Days -->
                                            <td>
                                                <span class="badge bg-light text-dark border"><?= (int)$row['days'] ?> Days</span>
                                            </td>

                                            <!-- Active / Inactive Badge -->
                                            <td>
                                                <?php if ($row['is_active'] == 1): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                        <i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i> Active
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                                        <i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i> Inactive
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Actions -->
                                            <td class="text-end pe-4">
                                                <div class="d-inline-flex gap-1">
                                                    <!-- Edit Button -->
                                                    <a href="<?= BASE_URL ?>admin/edit_package.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit Package">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>

                                                    <!-- Toggle Active/Inactive Form (POST) -->
                                                    <form method="POST" action="" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                                        <input type="hidden" name="action" value="toggle">
                                                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                                        <button type="submit" class="btn btn-sm <?= ($row['is_active'] == 1) ? 'btn-outline-warning' : 'btn-outline-success' ?>" title="<?= ($row['is_active'] == 1) ? 'Deactivate Package' : 'Activate Package' ?>">
                                                            <i class="bi <?= ($row['is_active'] == 1) ? 'bi-eye-slash' : 'bi-eye' ?>"></i>
                                                        </button>
                                                    </form>

                                                    <!-- Delete Form (POST) -->
                                                    <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this package? This action cannot be undone.');">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Package">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                            <p class="mb-2">No tour packages found.</p>
                                            <a href="<?= BASE_URL ?>admin/add_package.php" class="btn btn-accent btn-sm">
                                                <i class="bi bi-plus-lg me-1"></i> Add Your First Package
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Bootstrap 5 Bundle JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
