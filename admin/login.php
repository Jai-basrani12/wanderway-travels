<?php
// Include site configuration
require_once __DIR__ . '/../config/config.php';

// Redirect to dashboard if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: " . BASE_URL . "admin/dashboard.php");
    exit;
}

$error_message = '';

// Handle form submission
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error_message = 'Security token invalid or expired. Please refresh the page.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error_message = 'Invalid username or password.';
    } else {
        // Query admin user with prepared statement
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($admin = $result->fetch_assoc()) {
            // Verify password hash
            if (password_verify($password, $admin['password'])) {
                // Regenerate session ID on successful login
                session_regenerate_id(true);

                // Store credentials in session
                $_SESSION['admin_id'] = (int)$admin['id'];
                $_SESSION['admin_username'] = $admin['username'];

                $stmt->close();
                header("Location: " . BASE_URL . "admin/dashboard.php");
                exit;
            }
        }
        $stmt->close();

        // Generic error message on failure
        $error_message = 'Invalid username or password.';
    }
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - WanderWay Travels</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Theme CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 py-5">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                
                <!-- Brand Header -->
                <div class="text-center mb-4">
                    <a href="<?= BASE_URL ?>" class="text-decoration-none">
                        <div class="d-inline-flex align-items-center justify-content-center bg-accent text-white rounded-circle shadow-sm mb-2" style="width: 54px; height: 54px;">
                            <i class="bi bi-compass fs-2"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-0">
                            Wander<span class="text-accent">Way</span> Travels
                        </h4>
                    </a>
                    <p class="text-muted small mt-1">Administrator Portal</p>
                </div>

                <!-- Login Card -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-sm-5">
                        <h5 class="card-title fw-bold text-center mb-4">Admin Sign In</h5>

                        <!-- Error Alert -->
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger d-flex align-items-center py-2 px-3 rounded-3 small mb-4" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
                                <div><?= htmlspecialchars($error_message) ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Login Form -->
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                            <div class="mb-3">
                                <label for="username" class="form-label small fw-semibold">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="username" name="username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" placeholder="Enter username" required autofocus>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label small fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="Enter password" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-accent w-100 py-2 fw-semibold shadow-sm">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Back to Site Link -->
                <div class="text-center mt-4">
                    <a href="<?= BASE_URL ?>" class="text-muted small text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Return to Main Website
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
