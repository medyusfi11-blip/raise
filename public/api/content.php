<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

startSession();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

match (true) {
    ($action === 'settings' && $method === 'GET')  => contentGetSettings(),
    ($action === 'settings' && $method === 'PUT')  => (function() { requireLoginApi(); contentUpdateSettings(); })(),
    ($action === 'stats' && $method === 'GET')     => (function() { requireLoginApi(); contentGetStats(); })(),
    ($action === 'password' && $method === 'POST') => (function() { requireLoginApi(); contentChangePassword(); })(),
    ($action === 'profile' && $method === 'POST')  => (function() { requireLoginApi(); contentUpdateProfile(); })(),
    default => jsonResponse(['success' => false, 'message' => 'Invalid request.'], 400),
};

function contentGetSettings(): void
{
    $pdo = Database::getInstance()->getConnection();
    $settings = getSiteSettings($pdo);
    jsonResponse(['success' => true, 'data' => $settings]);
}

function contentUpdateSettings(): void
{
    $input = getJsonInput();
    if (empty($input)) {
        jsonResponse(['success' => false, 'message' => 'No settings provided.'], 422);
    }

    $allowed = ['site_name', 'site_phone', 'site_whatsapp', 'site_instagram', 'site_email'];
    $pdo = Database::getInstance()->getConnection();

    $stmt = $pdo->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );

    $updated = 0;
    foreach ($input as $key => $value) {
        if (in_array($key, $allowed, true)) {
            $stmt->execute(['k' => $key, 'v' => (string) $value]);
            $updated++;
        }
    }

    jsonResponse(['success' => true, 'message' => "{$updated} setting(s) updated."]);
}

function contentGetStats(): void
{
    $pdo = Database::getInstance()->getConnection();

    $totalCars = (int) $pdo->query('SELECT COUNT(*) FROM cars')->fetchColumn();
    $availableCars = (int) $pdo->query("SELECT COUNT(*) FROM cars WHERE status = 'Available'")->fetchColumn();
    $totalBookings = (int) $pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
    $pendingBookings = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn();
    $revenue = (float) $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM bookings WHERE status IN ('Accepted', 'Completed')")->fetchColumn();

    // Recent bookings
    $recentStmt = $pdo->query(
        "SELECT b.id, b.customer_name,
                COALESCE(NULLIF(b.car_name_snapshot, ''), c.name, 'Deleted car') AS car_name,
                COALESCE(NULLIF(b.car_model_snapshot, ''), c.model, '') AS car_model,
                b.total_price, b.status, b.created_at
         FROM bookings b
         LEFT JOIN cars c ON c.id = b.car_id
         ORDER BY b.created_at DESC LIMIT 5"
    );
    $recentBookings = $recentStmt->fetchAll();

    jsonResponse([
        'success' => true,
        'data' => [
            'total_cars' => $totalCars,
            'available_cars' => $availableCars,
            'total_bookings' => $totalBookings,
            'pending_bookings' => $pendingBookings,
            'revenue' => $revenue,
            'currency' => 'DH',
            'recent_bookings' => $recentBookings,
        ],
    ]);
}

function contentChangePassword(): void
{
    $input = getJsonInput();
    $current = $input['current_password'] ?? '';
    $new = $input['new_password'] ?? '';
    $confirm = $input['confirm_password'] ?? '';

    if ($current === '' || $new === '') {
        jsonResponse(['success' => false, 'message' => 'All fields are required.'], 422);
    }
    if (strlen($new) < 6) {
        jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters.'], 422);
    }
    if ($new !== $confirm) {
        jsonResponse(['success' => false, 'message' => 'Passwords do not match.'], 422);
    }

    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->prepare('SELECT password FROM admins WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['admin_id']]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($current, $admin['password'])) {
        jsonResponse(['success' => false, 'message' => 'Current password is incorrect.'], 401);
    }

    $hash = password_hash($new, PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE admins SET password = :p WHERE id = :id')->execute(['p' => $hash, 'id' => $_SESSION['admin_id']]);

    jsonResponse(['success' => true, 'message' => 'Password changed successfully.']);
}

function contentUpdateProfile(): void
{
    $input = getJsonInput();
    $fullName = trim($input['full_name'] ?? '');
    $email = trim($input['email'] ?? '');

    if ($fullName === '' || $email === '') {
        jsonResponse(['success' => false, 'message' => 'Name and email are required.'], 422);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Invalid email.'], 422);
    }

    $pdo = Database::getInstance()->getConnection();
    $pdo->prepare('UPDATE admins SET full_name = :n, email = :e WHERE id = :id')
        ->execute(['n' => $fullName, 'e' => $email, 'id' => $_SESSION['admin_id']]);

    jsonResponse(['success' => true, 'message' => 'Profile updated.']);
}
