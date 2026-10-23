<?php
// Include site configuration
require_once __DIR__ . '/../config/config.php';

// Protect admin access
require_once __DIR__ . '/../includes/admin_check.php';

// Handle POST request to update enquiry status
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Verify CSRF
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Security verification failed. Please try again.';
        header("Location: " . BASE_URL . "admin/enquiries.php");
        exit;
    }

    if ($action === 'update_status') {
        $enquiry_id = isset($_POST['enquiry_id']) ? (int)$_POST['enquiry_id'] : 0;
        $new_status = trim($_POST['status'] ?? '');
        $allowed_statuses = ['new', 'contacted', 'closed'];

        if ($enquiry_id > 0 && in_array($new_status, $allowed_statuses, true)) {
            $stmt = $conn->prepare("UPDATE enquiries SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $enquiry_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Enquiry status updated to "' . ucfirst($new_status) . '".';
            } else {
                $_SESSION['error_message'] = 'Failed to update enquiry status: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = 'Invalid enquiry or status provided.';
        }

        // Redirect back preserving current filter and page
        $current_filter = trim($_GET['status'] ?? '');
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $redirect_url = BASE_URL . "admin/enquiries.php?status=" . urlencode($current_filter) . "&page=" . $current_page;
        header("Location: " . $redirect_url);
        exit;
    }
}

// Retrieve flash messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// 1. Status Filter
$filter_status = trim($_GET['status'] ?? 'all');
$valid_filter = in_array($filter_status, ['new', 'contacted', 'closed'], true) ? $filter_status : 'all';

// Count total enquiries per status for filter tabs
$counts = ['all' => 0, 'new' => 0, 'contacted' => 0, 'closed' => 0];
$count_query = "SELECT status, COUNT(*) as cnt FROM enquiries GROUP BY status";
if ($res = $conn->query($count_query)) {
    while ($row = $res->fetch_assoc()) {
        $s = $row['status'];
        if (isset($counts[$s])) {
            $counts[$s] = (int)$row['cnt'];
            $counts['all'] += (int)$row['cnt'];
        }
    }
}

// 2. Pagination Configuration
$per_page = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Get total count for the selected filter
if ($valid_filter !== 'all') {
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM enquiries WHERE status = ?");
    $count_stmt->bind_param("s", $valid_filter);
    $count_stmt->execute();
    $total_enquiries = (int)($count_stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $count_stmt->close();
} else {
    $total_enquiries = $counts['all'];
}

$total_pages = max(1, (int)ceil($total_enquiries / $per_page));
if ($page > $total_pages) {
    $page = $total_pages;
}
$offset = ($page - 1) * $per_page;

// 3. Fetch Enquiries with LEFT JOIN to packages
if ($valid_filter !== 'all') {
    $stmt = $conn->prepare("
        SELECT e.id, e.name, e.phone, e.email, e.package_id, e.travel_date, e.persons, e.message, e.status, e.created_at, p.title AS package_title
        FROM enquiries e
        LEFT JOIN packages p ON e.package_id = p.id
        WHERE e.status = ?
        ORDER BY e.id DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("sii", $valid_filter, $per_page, $offset);
} else {
    $stmt = $conn->prepare("
        SELECT e.id, e.name, e.phone, e.email, e.package_id, e.travel_date, e.persons, e.message, e.status, e.created_at, p.title AS package_title
        FROM enquiries e
        LEFT JOIN packages p ON e.package_id = p.id
        ORDER BY e.id DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("ii", $per_page, $offset);
}

$stmt->execute();
$enquiries_result = $stmt->get_result();
$enquiries = [];
while ($row = $enquiries_result->fetch_assoc()) {
    $enquiries[] = $row;
}
$stmt->close();

$page_title = 'Customer Enquiries - WanderWay Admin';
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
                        <a class="nav-link text-secondary" href="<?= BASE_URL ?>admin/packages.php">
                            <i class="bi bi-box-seam me-1"></i> Packages
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active fw-bold text-accent" href="<?= BASE_URL ?>admin/enquiries.php">
                            <i class="bi bi-envelope me-1"></i> Enquiries
                            <?php if ($counts['new'] > 0): ?>
                                <span class="badge bg-danger ms-1"><?= (int)$counts['new'] ?></span>
                            <?php endif; ?>
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
                    <h2 class="fw-bold mb-1">Customer Enquiries</h2>
                    <p class="text-muted mb-0">Track and respond to vacation bookings and custom trip requests.</p>
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

            <!-- Status Filter Tabs -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 p-2">
                <ul class="nav nav-pills gap-2">
                    <li class="nav-item">
                        <a class="nav-link py-2 px-3 <?= ($valid_filter === 'all') ? 'active bg-accent' : 'text-secondary' ?>" href="<?= BASE_URL ?>admin/enquiries.php?status=all">
                            All Enquiries <span class="badge bg-secondary-subtle text-dark ms-1"><?= $counts['all'] ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 px-3 <?= ($valid_filter === 'new') ? 'active bg-accent' : 'text-secondary' ?>" href="<?= BASE_URL ?>admin/enquiries.php?status=new">
                            New <span class="badge <?= ($counts['new'] > 0) ? 'bg-danger text-white' : 'bg-secondary-subtle text-dark' ?> ms-1"><?= $counts['new'] ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 px-3 <?= ($valid_filter === 'contacted') ? 'active bg-accent' : 'text-secondary' ?>" href="<?= BASE_URL ?>admin/enquiries.php?status=contacted">
                            Contacted <span class="badge bg-secondary-subtle text-dark ms-1"><?= $counts['contacted'] ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 px-3 <?= ($valid_filter === 'closed') ? 'active bg-accent' : 'text-secondary' ?>" href="<?= BASE_URL ?>admin/enquiries.php?status=closed">
                            Closed <span class="badge bg-secondary-subtle text-dark ms-1"><?= $counts['closed'] ?></span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Enquiries Table Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4" style="width: 50px;">#</th>
                                    <th scope="col">Customer</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Package</th>
                                    <th scope="col">Travel Date</th>
                                    <th scope="col">Persons</th>
                                    <th scope="col">Message</th>
                                    <th scope="col">Submitted</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-end pe-4" style="width: 170px;">Change Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($enquiries)): ?>
                                    <?php $serial = $offset + 1; ?>
                                    <?php foreach ($enquiries as $enq): ?>
                                        <tr>
                                            <!-- Serial Number -->
                                            <td class="ps-4 fw-semibold text-muted"><?= $serial++ ?></td>

                                            <!-- Name -->
                                            <td>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($enq['name']) ?></div>
                                            </td>

                                            <!-- Phone (clickable tel: link) -->
                                            <td>
                                                <a href="tel:<?= htmlspecialchars($enq['phone']) ?>" class="text-accent fw-semibold text-decoration-none">
                                                    <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($enq['phone']) ?>
                                                </a>
                                            </td>

                                            <!-- Email -->
                                            <td>
                                                <?php if (!empty($enq['email'])): ?>
                                                    <a href="mailto:<?= htmlspecialchars($enq['email']) ?>" class="text-secondary small text-decoration-none">
                                                        <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($enq['email']) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">N/A</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Package (via LEFT JOIN) -->
                                            <td>
                                                <?php if (!empty($enq['package_title'])): ?>
                                                    <a href="<?= BASE_URL ?>package.php?id=<?= (int)$enq['package_id'] ?>" target="_blank" class="text-dark fw-semibold small text-decoration-none" title="View Package">
                                                        <?= htmlspecialchars($enq['package_title']) ?> <i class="bi bi-box-arrow-up-right ms-1 text-muted" style="font-size: 10px;"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-secondary border">General enquiry</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Travel Date -->
                                            <td class="small text-secondary">
                                                <?= !empty($enq['travel_date']) ? date('M d, Y', strtotime($enq['travel_date'])) : 'Flexible' ?>
                                            </td>

                                            <!-- Persons -->
                                            <td class="small text-center">
                                                <?= !empty($enq['persons']) ? (int)$enq['persons'] : '-' ?>
                                            </td>

                                            <!-- Message (Shortened + Modal Button) -->
                                            <td>
                                                <?php if (!empty($enq['message'])): ?>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <span class="text-truncate text-secondary small" style="max-width: 120px;" title="<?= htmlspecialchars($enq['message']) ?>">
                                                            <?= htmlspecialchars(mb_substr($enq['message'], 0, 30)) . (mb_strlen($enq['message']) > 30 ? '...' : '') ?>
                                                        </span>
                                                        <button type="button" class="btn btn-sm btn-light border text-accent p-1 py-0" data-bs-toggle="modal" data-bs-target="#messageModal<?= (int)$enq['id'] ?>" title="View Full Message">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </div>

                                                    <!-- Modal for Full Message -->
                                                    <div class="modal fade" id="messageModal<?= (int)$enq['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= (int)$enq['id'] ?>" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content rounded-4 border-0 shadow">
                                                                <div class="modal-header border-bottom">
                                                                    <h5 class="modal-title fw-bold" id="modalLabel<?= (int)$enq['id'] ?>">
                                                                        <i class="bi bi-chat-text text-accent me-2"></i> Enquiry Message
                                                                    </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body p-4">
                                                                    <div class="bg-light p-3 rounded-3 mb-3 small">
                                                                        <div><strong>Customer:</strong> <?= htmlspecialchars($enq['name']) ?></div>
                                                                        <div><strong>Phone:</strong> <a href="tel:<?= htmlspecialchars($enq['phone']) ?>"><?= htmlspecialchars($enq['phone']) ?></a></div>
                                                                        <?php if (!empty($enq['email'])): ?>
                                                                            <div><strong>Email:</strong> <?= htmlspecialchars($enq['email']) ?></div>
                                                                        <?php endif; ?>
                                                                        <div><strong>Package:</strong> <?= htmlspecialchars($enq['package_title'] ?? 'General enquiry') ?></div>
                                                                    </div>
                                                                    <h6 class="fw-bold mb-2">Message Content:</h6>
                                                                    <div class="p-3 bg-white border rounded-3 text-secondary lh-base">
                                                                        <?= nl2br(htmlspecialchars($enq['message'])) ?>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer border-top">
                                                                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Close</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small">None</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Submitted Date and Time in Asia/Kolkata -->
                                            <td class="small text-secondary text-nowrap">
                                                <?= date('M d, Y h:i A', strtotime($enq['created_at'])) ?>
                                            </td>

                                            <!-- Status Badge -->
                                            <td>
                                                <?php if ($enq['status'] === 'new'): ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">New</span>
                                                <?php elseif ($enq['status'] === 'contacted'): ?>
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">Contacted</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Closed</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Change Status Form (POST with CSRF) -->
                                            <td class="text-end pe-4">
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="enquiry_id" value="<?= (int)$enq['id'] ?>">
                                                    <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()" aria-label="Change enquiry status">
                                                        <option value="new" <?= ($enq['status'] === 'new') ? 'selected' : '' ?>>New</option>
                                                        <option value="contacted" <?= ($enq['status'] === 'contacted') ? 'selected' : '' ?>>Contacted</option>
                                                        <option value="closed" <?= ($enq['status'] === 'closed') ? 'selected' : '' ?>>Closed</option>
                                                    </select>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="11" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                            <p class="mb-0">No customer enquiries found for this filter.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination (20 items per page) -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Enquiries pagination" class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        Showing page <?= (int)$page ?> of <?= (int)$total_pages ?> (Total: <?= (int)$total_enquiries ?> enquiries)
                    </span>
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Previous Page Link -->
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>admin/enquiries.php?status=<?= urlencode($valid_filter) ?>&page=<?= $page - 1 ?>">
                                Previous
                            </a>
                        </li>

                        <!-- Page Number Links -->
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($page === $i) ? 'active' : '' ?>">
                                <a class="page-link <?= ($page === $i) ? 'bg-accent border-accent text-white' : '' ?>" href="<?= BASE_URL ?>admin/enquiries.php?status=<?= urlencode($valid_filter) ?>&page=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next Page Link -->
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>admin/enquiries.php?status=<?= urlencode($valid_filter) ?>&page=<?= $page + 1 ?>">
                                Next
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        </div>
    </main>

    <!-- Bootstrap 5 Bundle JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
