<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

startSession();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$action = $_GET['action'] ?? '';

match ($action) {
    'login'  => handleLogin(),
    'logout' => handleLogout(),
    'check'  => handleCheck(),
    default  => jsonResponse(['success' => false, 'message' => 'Invalid action.'], 400),
};

function handleLogin(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
    }

    $input = getJsonInput();
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    if ($username === '' || $password === '') {
        jsonResponse(['success' => false, 'message' => 'Username and password are required.'], 422);
    }

    $admin = attemptLogin($username, $password);

    if (!$admin) {
        jsonResponse(['success' => false, 'message' => 'Invalid username or password.'], 401);
    }

    jsonResponse([
        'success' => true,
        'message' => 'Login successful.',
        'data' => $admin,
    ]);
}

function handleLogout(): void
{
    logout();
    jsonResponse(['success' => true, 'message' => 'Logged out successfully.']);
}

function handleCheck(): void
{
    if (!isLoggedIn()) {
        jsonResponse(['success' => false, 'message' => 'Not authenticated.'], 401);
    }
    $admin = getCurrentAdmin();
    jsonResponse(['success' => true, 'data' => $admin]);
}
