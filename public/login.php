<?php
/**
 * PropIntel CRM - Login Page
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/config/config.php';
require_once APP_PATH . '/config/database.php';
require_once APP_PATH . '/core/Auth.php';
require_once APP_PATH . '/core/Logger.php';
require_once APP_PATH . '/core/View.php';
require_once APP_PATH . '/core/Validator.php';

Auth::startSession();

// Redirect if already logged in
if (Auth::check()) {
    header('Location: /');
    exit;
}

$error    = '';
$redirect = filter_input(INPUT_GET, 'redirect', FILTER_SANITIZE_URL) ?: '/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        $error = 'Please enter your email and password.';
    } elseif (Auth::attempt($email, $password)) {
        $safeRedirect = filter_var($redirect, FILTER_VALIDATE_URL) ? '/' : $redirect;
        header('Location: ' . $safeRedirect);
        exit;
    } else {
        $error = 'Invalid email address or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        body { background: linear-gradient(135deg, #0f1923 0%, #1a2d45 100%); min-height: 100vh; }
        .login-card { max-width: 420px; margin: 0 auto; }
        .brand-logo { font-size: 2rem; font-weight: 800; color: #3b82f6; letter-spacing: -1px; }
        .brand-logo span { color: #f59e0b; }
        .card { border: none; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.4); }
        .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 0.2rem rgba(59,130,246,.25); }
        .btn-signin { background: linear-gradient(90deg, #3b82f6, #1d4ed8); border: none; padding: .75rem; font-size: 1rem; }
        .btn-signin:hover { background: linear-gradient(90deg, #2563eb, #1e3a8a); }
    </style>
</head>
<body class="d-flex align-items-center py-5">
<div class="container login-card">
    <div class="text-center mb-4">
        <div class="brand-logo">Prop<span>Intel</span> CRM</div>
        <p class="text-light opacity-75 mt-1">Real Estate Investment Intelligence</p>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <h4 class="mb-4 text-center fw-bold">Welcome Back</h4>

            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login.php?redirect=<?= htmlspecialchars(urlencode($redirect)) ?>" novalidate>
                <?= Auth::csrfField() ?>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="you@example.com"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            required
                            autocomplete="email"
                        >
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                        >
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-signin w-100 text-white fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
            </form>
        </div>
    </div>

    <p class="text-center text-light opacity-50 mt-3 small">
        <?= APP_NAME ?> v<?= APP_VERSION ?> &mdash; Secure Login
    </p>

    <?php if (APP_DEBUG): ?>
    <div class="alert alert-warning mt-3 small">
        <strong>Dev mode:</strong> admin@propintel.com / Admin1234!
    </div>
    <?php endif; ?>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>
</body>
</html>
