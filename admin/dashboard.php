<?php
// Include site configuration
require_once __DIR__ . '/../config/config.php';

// Protect admin access
require_once __DIR__ . '/../includes/admin_check.php';

// Page Title
$page_title = 'Dashboard - WanderWay Admin';

// 1. Get total number of packages using prepared statement
$pkg_stmt = $conn->prepare("SELECT COUNT(*) as total FROM packages");
$pkg_stmt->execute();
$pkg_res = $pkg_stmt->get_result();
$total_packages = (int)($pkg_res->fetch_assoc()['total'] ?? 0);
$pkg_stmt->close();

// 2. Get number of new enquiries using prepared statement
$status_filter = 'new';
$enq_stmt = $conn->prepare("SELECT COUNT(*) as total FROM enquiries WHERE status = ?");
$enq_stmt->bind_param("s", $status_filter);
$enq_stmt->execute();
$enq_res = $enq_stmt->get_result();
$new_enquiries = (int)($enq_res->fetch_assoc()['total'] ?? 0);
$enq_stmt->close();

// Admin username
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
            <!-- Brand -->
            <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>admin/dashboard.php">
                <i class="bi bi-compass text-accent me-2 fs-4"></i>
                <span>WanderWay</span>&nbsp;<span class="badge bg-accent-light text-accent ms-1 fs-6">Admin</span>
            </a>

            <!-- Toggle -->
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <li class="nav-item">
                        <a class="nav-link active fw-bold text-accent" href="<?= BASE_URL ?>admin/dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-secondary" href="<?= BASE_URL ?>admin/packages.php">
                            <i class="bi bi-box-seam me-1"></i> Packages
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-secondary" href="<?= BASE_URL ?>admin/enquiries.php">
                            <i class="bi bi-envelope me-1"></i> Enquiries
                            <?php if ($new_enquiries > 0): ?>
                                <span class="badge bg-danger ms-1"><?= (int)$new_enquiries ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>

                <!-- User & Action Links -->
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

    <!-- Main Dashboard Content -->
    <main class="py-5">
        <div class="container">
            
            <!-- Welcome Header -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Welcome back, <?= htmlspecialchars($admin_name) ?>!</h2>
                    <p class="text-muted mb-0">Here is an overview of your agency packages and customer enquiries.</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="<?= BASE_URL ?>admin/logout.php" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i> Log Out
                    </a>
                </div>
            </div>

            <!-- Stats Overview Cards -->
            <div class="row g-4 mb-5">
                
                <!-- Total Packages Card -->
                <div class="col-md-6 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="bg-accent-light text-accent rounded-3 p-3">
                                    <i class="bi bi-geo-alt-fill fs-2"></i>
                                </div>
                                <span class="badge bg-light text-secondary border">Total Listings</span>
                            </div>
                            <h3 class="fw-bold text-dark mb-1"><?= (int)$total_packages ?></h3>
                            <p class="text-muted small mb-3">Tour packages available in system</p>
                            <a href="<?= BASE_URL ?>admin/packages.php" class="btn btn-outline-accent btn-sm">
                                Manage Packages <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- New Enquiries Card -->
                <div class="col-md-6 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="bg-danger-subtle text-danger rounded-3 p-3">
                                    <i class="bi bi-chat-left-dots-fill fs-2"></i>
                                </div>
                                <?php if ($new_enquiries > 0): ?>
                                    <span class="badge bg-danger">Action Required</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-secondary border">All Caught Up</span>
                                <?php endif; ?>
                            </div>
                            <h3 class="fw-bold text-dark mb-1">
                                <a href="<?= BASE_URL ?>admin/enquiries.php?status=new" class="text-dark text-decoration-none hover-accent">
                                    <?= (int)$new_enquiries ?>
                                </a>
                            </h3>
                            <p class="text-muted small mb-3">New customer enquiries awaiting response</p>
                            <a href="<?= BASE_URL ?>admin/enquiries.php?status=new" class="btn btn-outline-danger btn-sm">
                                View New Enquiries <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Quick Management Shortcuts Card -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Quick Navigation</h5>
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-4">
                            <a href="<?= BASE_URL ?>admin/packages.php" class="card card-body border text-decoration-none h-100 hover-shadow">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-box-seam fs-3 text-accent"></i>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0">Tour Packages</h6>
                                        <small class="text-muted">Create & edit packages</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <a href="<?= BASE_URL ?>admin/enquiries.php" class="card card-body border text-decoration-none h-100 hover-shadow">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-inbox fs-3 text-danger"></i>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0">Customer Enquiries</h6>
                                        <small class="text-muted">Review booking requests</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <a href="<?= BASE_URL ?>" target="_blank" class="card card-body border text-decoration-none h-100 hover-shadow">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-globe2 fs-3 text-primary"></i>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0">Public Website</h6>
                                        <small class="text-muted">Preview live visitor site</small>
                                    </div>
                                </div>
                            </a>
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
