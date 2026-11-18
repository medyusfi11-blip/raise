<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

startSession();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

requireLoginApi();

$method = $_SERVER['REQUEST_METHOD'];
$input = getJsonInput();
$action = $input['action'] ?? '';

if ($method === 'GET') {
    $pdo = Database::getInstance()->getConnection();
    $settings = getSiteSettings($pdo);
    jsonResponse(['success' => true, 'data' => $settings]);
    exit;
}

if ($method === 'POST') {
    switch ($action) {
        case 'update_settings':
            $allowed = ['site_name', 'site_phone', 'site_whatsapp', 'site_instagram', 'site_email'];
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                'INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            );
            foreach ($allowed as $key) {
                if (array_key_exists($key, $input)) {
                    $stmt->execute(['k' => $key, 'v' => trim((string) $input[$key])]);
                }
            }
            jsonResponse(['success' => true, 'message' => 'Settings updated.']);
            break;

        case 'change_password':
            $current = (string) ($input['current_password'] ?? '');
            $newPass = (string) ($input['new_password'] ?? '');
            $confirm = (string) ($input['confirm_password'] ?? '');

            if ($current === '' || $newPass === '') {
                jsonResponse(['success' => false, 'message' => 'All password fields required.']);
            }
            if (strlen($newPass) < 6) {
                jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters.']);
            }
            if ($newPass !== $confirm) {
                jsonResponse(['success' => false, 'message' => 'Passwords do not match.']);
            }

            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare('SELECT password FROM admins WHERE id = :id');
            $stmt->execute(['id' => $_SESSION['admin_id']]);
            $admin = $stmt->fetch();

            if (!$admin || !password_verify($current, $admin['password'])) {
                jsonResponse(['success' => false, 'message' => 'Current password incorrect.']);
            }

            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE admins SET password = :p WHERE id = :id')
                ->execute(['p' => $hash, 'id' => $_SESSION['admin_id']]);

            jsonResponse(['success' => true, 'message' => 'Password changed.']);
            break;

        case 'update_profile':
            $fullName = trim((string) ($input['full_name'] ?? ''));
            $email = trim((string) ($input['email'] ?? ''));

            if ($fullName === '' || $email === '') {
                jsonResponse(['success' => false, 'message' => 'Name and email required.']);
            }

            $pdo = Database::getInstance()->getConnection();
            $pdo->prepare('UPDATE admins SET full_name = :n, email = :e WHERE id = :id')
                ->execute(['n' => $fullName, 'e' => $email, 'id' => $_SESSION['admin_id']]);

            jsonResponse(['success' => true, 'message' => 'Profile updated.']);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Unknown action.'], 400);
    }
    exit;
}

jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
