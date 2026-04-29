<?php
/**
 * PropIntel CRM - Authentication & Session Management
 */

class Auth
{
    /** Start or resume the session */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    /** Attempt login; returns true on success */
    public static function attempt(string $email, string $password): bool
    {
        $user = Database::fetchOne(
            'SELECT * FROM users WHERE email = ? AND active = 1 LIMIT 1',
            [strtolower(trim($email))]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            Logger::warning('Failed login attempt', ['email' => $email, 'ip' => self::ip()]);
            return false;
        }

        // Regenerate session ID to prevent fixation
        session_regenerate_id(true);

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email']= $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['csrf_token']= self::generateCsrf();

        // Update last login timestamp
        Database::query(
            'UPDATE users SET last_login = NOW() WHERE id = ?',
            [$user['id']]
        );

        Logger::info('User logged in', ['user_id' => $user['id'], 'email' => $email]);
        return true;
    }

    /** Destroy session and log out */
    public static function logout(): void
    {
        self::startSession();
        $userId = $_SESSION['user_id'] ?? null;
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        if ($userId) {
            Logger::info('User logged out', ['user_id' => $userId]);
        }
    }

    /** Return true if a user is logged in */
    public static function check(): bool
    {
        self::startSession();
        return !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
    }

    /** Redirect to login if not authenticated */
    public static function require(): void
    {
        if (!self::check()) {
            header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
            exit;
        }
    }

    /** Return current user ID or null */
    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /** Return current user role or null */
    public static function role(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    /** Return current user name */
    public static function name(): string
    {
        return $_SESSION['user_name'] ?? '';
    }

    /** Return full session user array */
    public static function user(): array
    {
        return [
            'id'    => $_SESSION['user_id']    ?? null,
            'name'  => $_SESSION['user_name']  ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role'  => $_SESSION['user_role']  ?? '',
        ];
    }

    /** Check if current user has one of the given roles */
    public static function hasRole(string|array $roles): bool
    {
        $roles = (array)$roles;
        return in_array(self::role(), $roles, true);
    }

    /** Abort with 403 if user lacks required role */
    public static function requireRole(string|array $roles): void
    {
        self::require();
        if (!self::hasRole($roles)) {
            http_response_code(403);
            include VIEW_PATH . '/errors/403.php';
            exit;
        }
    }

    // ── CSRF ──────────────────────────────────────────────────────────────────

    public static function generateCsrf(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function csrfToken(): string
    {
        self::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = self::generateCsrf();
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . self::csrfToken() . '">';
    }

    public static function verifyCsrf(?string $token = null): bool
    {
        $token    = $token ?? ($_POST[CSRF_TOKEN_NAME] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
        $expected = $_SESSION['csrf_token'] ?? '';
        return $expected && hash_equals($expected, $token);
    }

    public static function requireCsrf(): void
    {
        if (!self::verifyCsrf()) {
            http_response_code(419);
            die(json_encode(['error' => 'CSRF token mismatch']));
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}
