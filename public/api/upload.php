<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

startSession();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}

requireLoginApi();

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $codes = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds server size limit.',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit.',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server missing temp folder.',
        UPLOAD_ERR_CANT_WRITE => 'Server failed to write file.',
    ];
    $errCode = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
    jsonResponse(['success' => false, 'message' => $codes[$errCode] ?? 'Unknown error.'], 422);
}

$result = uploadCarImage($_FILES['file'], $_POST['old_image'] ?? '');

if ($result['success']) {
    jsonResponse([
        'success' => true,
        'message' => 'File uploaded successfully.',
        'data' => ['url' => $result['path'], 'filename' => $result['filename']],
    ], 201);
} else {
    jsonResponse(['success' => false, 'message' => implode(' ', $result['errors'])], 422);
}
