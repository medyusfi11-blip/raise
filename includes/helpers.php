<?php
declare(strict_types=1);

/**
 * Helper functions for Ibrahim Azzouz Cars.
 * Requires: config/app.php loaded first.
 */

function sanitize(mixed $input): string
{
    if (is_array($input)) return array_map('sanitize', $input);
    return htmlspecialchars((string) $input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function sanitize_string(string $input): string
{
    return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function jsonResponse(array $data, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function formatPrice(float|int $price): string
{
    return number_format((float) $price, 0, '.', ',') . ' DH';
}

function formatDate(string $date, string $format = 'd/m/Y'): string
{
    $dt = new DateTime($date);
    return $dt->format($format);
}

function calculateRentalDays(string $startDate, string $endDate): int
{
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $diff = $start->diff($end);
    return max(1, (int) $diff->days);
}

function getSiteSettings($pdo): array
{
    $settings = [];
    try {
        $stmt = $pdo->query('SELECT setting_key, setting_value FROM settings');
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (PDOException $e) { /* ignore */ }
    return $settings;
}

function uploadCarImage(array $file, string $oldImage = ''): array
{
    $errors = [];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $msgs = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds server size limit.',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit.',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server missing temp folder.',
            UPLOAD_ERR_CANT_WRITE => 'Server failed to write file.',
        ];
        $errors[] = $msgs[$file['error']] ?? 'Unknown upload error.';
        return ['success' => false, 'errors' => $errors];
    }

    if ($file['size'] > MAX_IMAGE_SIZE) {
        $errors[] = 'File must be under ' . (MAX_IMAGE_SIZE / 1024 / 1024) . ' MB.';
        return ['success' => false, 'errors' => $errors];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    if (!isset($allowed[$mimeType])) {
        $errors[] = 'Invalid file type. Allowed: JPG, PNG, WEBP.';
        return ['success' => false, 'errors' => $errors];
    }

    $uploadDir = APP_ROOT . '/public/uploads/cars';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext = $allowed[$mimeType];
    $filename = 'car_' . bin2hex(random_bytes(12)) . '.' . $ext;
    $filepath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        $errors[] = 'Failed to save uploaded file.';
        return ['success' => false, 'errors' => $errors];
    }

    // Delete old image
    if ($oldImage !== '' && !str_starts_with($oldImage, 'http')) {
        $oldPath = APP_ROOT . '/public/' . ltrim($oldImage, '/');
        if (is_file($oldPath)) @unlink($oldPath);
    }

    $relativePath = 'uploads/cars/' . $filename;
    return [
        'success' => true,
        'path' => $relativePath,
        'filename' => $filename,
    ];
}

function flash(string $key, ?string $value = null): ?string
{
    startSession();
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
        return $value;
    }
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function getStatusBadgeClass(string $status): string
{
    $map = [
        'Available' => 'bg-green-500/20 text-green-400',
        'Reserved' => 'bg-yellow-500/20 text-yellow-400',
        'Maintenance' => 'bg-red-500/20 text-red-400',
        'Pending' => 'bg-yellow-500/20 text-yellow-400',
        'Accepted' => 'bg-green-500/20 text-green-400',
        'Rejected' => 'bg-red-500/20 text-red-400',
        'Completed' => 'bg-blue-500/20 text-blue-400',
    ];
    return $map[$status] ?? 'bg-gray-500/20 text-gray-400';
}
