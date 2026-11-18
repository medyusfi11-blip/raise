<?php
/**
 * Ibrahim Azzouz Cars — Application Configuration
 */

// ------------------------------------------------------------
// Detect environment
// ------------------------------------------------------------
$hostname = gethostname() ?: '';
$isLocal  = (
    in_array($hostname, ['localhost', '127.0.0.1', '']) ||
    str_contains($_SERVER['SERVER_NAME'] ?? '', 'localhost') ||
    str_contains($_SERVER['SERVER_NAME'] ?? '', '127.0.0.1') ||
    (php_uname('n') === 'localhost')
);

define('APP_ENV', $isLocal ? 'local' : 'production');

// ------------------------------------------------------------
// Application paths
// ------------------------------------------------------------
define('APP_ROOT', dirname(__DIR__));
define('APP_URL',  $isLocal
    ? 'http://localhost/ibrahim-azzouz-cars/public'
    : 'https://ibrahimazzouzcars.com/public'
);
define('UPLOAD_PATH', APP_ROOT . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads');
define('UPLOAD_URL',  APP_URL . '/uploads');

// ------------------------------------------------------------
// Application meta
// ------------------------------------------------------------
define('APP_NAME',  'Ibrahim Azzouz Cars');
define('APP_VERSION', '1.0.0');

// ------------------------------------------------------------
// Session configuration
// ------------------------------------------------------------
define('SESSION_LIFETIME', 3600 * 8); // 8 hours

// ------------------------------------------------------------
// Upload limits
// ------------------------------------------------------------
define('MAX_IMAGE_SIZE',     5 * 1024 * 1024); // 5 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// ------------------------------------------------------------
// Error reporting
// ------------------------------------------------------------
if (APP_ENV === 'local') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
}
