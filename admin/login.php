<?php
// Include site configuration
require_once __DIR__ . '/../config/config.php';

// Redirect to dashboard if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: " . BASE_URL . "admin/dashboard.php");
    exit;
}

$error_message = '';
$max_attempts = 5;
$lockout_duration = 15 * 60; // 15 minutes in seconds
$is_locked = false;

// Check if currently locked out
if (isset($_SESSION['login_locked_until'])) {
    if (time() < $_SESSION['login_locked_until']) {
        $is_locked = true;
        $remaining_minutes = (int)ceil(($_SESSION['login_locked_until'] - time()) / 60);
        $error_message = "Too many failed login attempts. Account access is temporarily locked for {$remaining_minutes} more minute(s).";
    } else {
        // Lockout expired, reset counter
        unset($_SESSION['login_locked_until']);
        $_SESSION['login_attempts'] = 0;
    }
}

// Handle form submission
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if ($is_locked) {
        // Reject submissions while locked out
        $remaining_minutes = (int)ceil(($_SESSION['login_locked_until'] - time()) / 60);
        $error_message = "Too many failed login attempts. Account access is temporarily locked for {$remaining_minutes} more minute(s).";
    } elseif (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error_message = 'Security token invalid or expired. Please refresh the page.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error_message = 'Please enter both username and password.';
        } else {
            // Query admin user with prepared statement
            $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($admin = $result->fetch_assoc()) {
                // Verify password hash
                if (password_verify($password, $admin['password'])) {
                    // Reset failed attempt counter on success
                    unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);

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

            // Track failed attempt
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            
            if ($_SESSION['login_attempts'] >= $max_attempts) {
                $_SESSION['login_locked_until'] = time() + $lockout_duration;
                $is_locked = true;
                $error_message = "Too many failed login attempts (5/5). Account access is locked for 15 minutes.";
            } else {
                $remaining_tries = $max_attempts - $_SESSION['login_attempts'];
                $error_message = "Invalid username or password. ({$remaining_tries} attempt(s) remaining)";
            }
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
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/img/favicon.svg">
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
                            <div class="alert <?= $is_locked ? 'alert-danger' : 'alert-warning' ?> d-flex align-items-center py-2 px-3 rounded-3 small mb-4" role="alert">
                                <i class="bi <?= $is_locked ? 'bi-shield-slash-fill' : 'bi-exclamation-triangle-fill' ?> me-2 fs-6"></i>
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
                                    <input type="text" 
                                           class="form-control border-start-0 ps-0" 
                                           id="username" 
                                           name="username" 
                                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" 
                                           placeholder="Enter username" 
                                           required 
                                           autofocus 
                                           <?= $is_locked ? 'disabled' : '' ?>>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label small fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control border-start-0 ps-0" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Enter password" 
                                           required 
                                           <?= $is_locked ? 'disabled' : '' ?>>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-accent w-100 py-2 fw-semibold shadow-sm" <?= $is_locked ? 'disabled' : '' ?>>
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
