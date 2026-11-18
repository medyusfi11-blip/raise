<?php
declare(strict_types=1);

/**
 * Authentication helpers for Ibrahim Azzouz Cars admin.
 * Requires: session started, config/app.php, config/database.php, includes/helpers.php
 */

function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        if (APP_ENV === 'production') {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_secure', '1');
            ini_set('session.use_strict_mode', '1');
        }
        session_start();
    }
}

function generateCsrfToken(): string
{
    startSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    startSession();
    if ($token === null || $token === '') return false;
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function attemptLogin(string $username, string $password): ?array
{
    $db = Database::getInstance();
    $stmt = $db->query(
        'SELECT id, username, password, full_name, email, role FROM admins WHERE username = :u LIMIT 1',
        ['u' => $username]
    );
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password'])) {
        return null;
    }

    startSession();
    session_regenerate_id(true);

    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['logged_in'] = true;

    $db->query('UPDATE admins SET last_login = NOW() WHERE id = :id', ['id' => $admin['id']]);

    unset($admin['password']);
    return $admin;
}

function logout(): void
{
    startSession();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function isLoggedIn(): bool
{
    startSession();
    return !empty($_SESSION['logged_in']) && !empty($_SESSION['admin_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireLoginApi(): void
{
    if (!isLoggedIn()) {
        jsonResponse(['success' => false, 'message' => 'Authentication required.'], 401);
    }
}

function getCurrentAdmin(): ?array
{
    if (!isLoggedIn()) return null;
    try {
        $db = Database::getInstance();
        $stmt = $db->query(
            'SELECT id, username, full_name, email, role, created_at, last_login FROM admins WHERE id = :id LIMIT 1',
            ['id' => $_SESSION['admin_id']]
        );
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

function getJsonInput(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
