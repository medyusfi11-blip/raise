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

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$bookingId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

match (true) {
    ($action === 'stats' && $method === 'GET')    => (function() { requireLoginApi(); bookingsStats(); })(),
    ($method === 'GET' && $bookingId > 0)         => (function() use ($bookingId) { requireLoginApi(); bookingsGetOne($bookingId); })(),
    ($method === 'GET')                           => (function() { requireLoginApi(); bookingsList(); })(),
    ($method === 'POST')                          => bookingsCreate(),
    ($method === 'PUT' && $bookingId > 0)         => (function() use ($bookingId) { requireLoginApi(); bookingsUpdate($bookingId); })(),
    ($method === 'DELETE' && $bookingId > 0)      => (function() use ($bookingId) { requireLoginApi(); bookingsDelete($bookingId); })(),
    default                                       => jsonResponse(['success' => false, 'message' => 'Invalid request.'], 400),
};

function bookingsList(): void
{
    $status = trim($_GET['status'] ?? '');
    $search = trim($_GET['search'] ?? '');
    $limit  = max(1, min(100, (int) ($_GET['limit'] ?? 50)));
    $offset = max(0, (int) ($_GET['offset'] ?? 0));

    $where = [];
    $params = [];

    $allowedStatuses = ['Pending', 'Accepted', 'Rejected', 'Completed'];
    if ($status !== '' && in_array($status, $allowedStatuses, true)) {
        $where[] = 'b.status = :status';
        $params['status'] = $status;
    }

    $carNameExpr = "COALESCE(NULLIF(b.car_name_snapshot, ''), c.name, 'Deleted car')";
    $carModelExpr = "COALESCE(NULLIF(b.car_model_snapshot, ''), c.model, '')";

    if ($search !== '') {
        $where[] = "(b.customer_name LIKE :s1 OR b.customer_email LIKE :s2 OR b.customer_phone LIKE :s3 OR {$carModelExpr} LIKE :s4 OR {$carNameExpr} LIKE :s5)";
        $params['s1'] = "%{$search}%";
        $params['s2'] = "%{$search}%";
        $params['s3'] = "%{$search}%";
        $params['s4'] = "%{$search}%";
        $params['s5'] = "%{$search}%";
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $pdo = Database::getInstance()->getConnection();

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM bookings b LEFT JOIN cars c ON c.id = b.car_id {$whereClause}");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $sql = "SELECT b.id, b.car_id,
                   {$carNameExpr} AS car_name,
                   {$carModelExpr} AS car_model,
                   COALESCE(NULLIF(b.car_image_snapshot, ''), c.image, '') AS car_image,
                   COALESCE(NULLIF(b.price_per_day_snapshot, 0), c.price_per_day, 0) AS price_per_day_snapshot,
                   b.customer_name, b.customer_email, b.customer_phone, b.customer_city,
                   b.start_date, b.end_date, b.total_days, b.total_price,
                   b.status, b.notes, b.created_at, b.updated_at
            FROM bookings b
            LEFT JOIN cars c ON c.id = b.car_id
            {$whereClause}
            ORDER BY b.created_at DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $bookings = $stmt->fetchAll();

    foreach ($bookings as &$b) {
        $b['id'] = (int) $b['id'];
        $b['car_id'] = $b['car_id'] !== null ? (int) $b['car_id'] : null;
        $b['total_days'] = (int) $b['total_days'];
        $b['total_price'] = (float) $b['total_price'];
        $b['price_per_day_snapshot'] = (float) ($b['price_per_day_snapshot'] ?? 0);
    }
    unset($b);

    jsonResponse([
        'success' => true,
        'data' => $bookings,
        'meta' => ['total' => $total, 'limit' => $limit, 'offset' => $offset],
    ]);
}

function bookingsGetOne(int $id): void
{
    $db = Database::getInstance();
    $stmt = $db->query(
        "SELECT b.*,
                COALESCE(NULLIF(b.car_name_snapshot, ''), c.name, 'Deleted car') AS car_name,
                COALESCE(NULLIF(b.car_model_snapshot, ''), c.model, '') AS car_model,
                COALESCE(NULLIF(b.car_image_snapshot, ''), c.image, '') AS car_image,
                COALESCE(NULLIF(b.price_per_day_snapshot, 0), c.price_per_day, 0) AS car_price_per_day
         FROM bookings b
         LEFT JOIN cars c ON c.id = b.car_id
         WHERE b.id = :id LIMIT 1",
        ['id' => $id]
    );
    $booking = $stmt->fetch();

    if (!$booking) {
        jsonResponse(['success' => false, 'message' => 'Booking not found.'], 404);
    }

    $booking['car_id'] = $booking['car_id'] !== null ? (int) $booking['car_id'] : null;
    $booking['total_days'] = (int) ($booking['total_days'] ?? 0);
    $booking['total_price'] = (float) ($booking['total_price'] ?? 0);

    jsonResponse(['success' => true, 'data' => $booking]);
}

function bookingsCreate(): void
{
    $input = getJsonInput();

    $name  = trim($input['customer_name'] ?? '');
    $email = trim($input['customer_email'] ?? '');
    $phone = trim($input['customer_phone'] ?? '');
    $city  = trim($input['customer_city'] ?? '');
    $carId = (int) ($input['car_id'] ?? 0);
    $start = trim($input['start_date'] ?? '');
    $end   = trim($input['end_date'] ?? '');
    $notes = trim($input['notes'] ?? '');

    $errors = [];
    if ($name === '') $errors['customer_name'] = 'Name is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['customer_email'] = 'Valid email is required.';
    if ($phone === '') $errors['customer_phone'] = 'Phone is required.';
    if ($carId <= 0) $errors['car_id'] = 'A valid car is required.';
    if ($start === '' || !DateTime::createFromFormat('Y-m-d', $start)) $errors['start_date'] = 'Valid start date is required.';
    if ($end === '' || !DateTime::createFromFormat('Y-m-d', $end)) $errors['end_date'] = 'Valid end date is required.';
    if ($start !== '' && $end !== '' && strtotime($end) <= strtotime($start)) $errors['end_date'] = 'End date must be after start date.';

    if ($errors) {
        jsonResponse(['success' => false, 'message' => 'Validation failed.', 'errors' => $errors], 422);
    }

    $db = Database::getInstance();
    $stmt = $db->query('SELECT id, price_per_day, units_available, name, model FROM cars WHERE id = :id AND status = "Available" LIMIT 1', ['id' => $carId]);
    $car = $stmt->fetch();

    if (!$car) {
        jsonResponse(['success' => false, 'message' => 'Car not available.'], 404);
    }

    $days = calculateRentalDays($start, $end);
    $totalPrice = round((float) $car['price_per_day'] * $days, 2);

    $bookingData = [
        'car_id' => $carId,
        'customer_name' => $name,
        'customer_email' => $email,
        'customer_phone' => $phone,
        'customer_city' => $city,
        'start_date' => $start,
        'end_date' => $end,
        'total_days' => $days,
        'total_price' => $totalPrice,
        'status' => 'Pending',
        'notes' => $notes !== '' ? $notes : null,
    ];

    // Snapshot car details so history/profits remain even if the car is later deleted or changed
    $carStmt = $db->query('SELECT name, model, image, price_per_day FROM cars WHERE id = :id LIMIT 1', ['id' => $carId]);
    $carInfo = $carStmt->fetch() ?: $car;
    $bookingData['car_name_snapshot'] = $carInfo['name'] ?? ($car['name'] ?? '');
    $bookingData['car_model_snapshot'] = $carInfo['model'] ?? ($car['model'] ?? '');
    $bookingData['car_image_snapshot'] = $carInfo['image'] ?? '';
    $bookingData['price_per_day_snapshot'] = (float) ($carInfo['price_per_day'] ?? $car['price_per_day'] ?? 0);

    try {
        $newId = $db->insert('bookings', $bookingData);
    } catch (Throwable $e) {
        // Backward compatibility if snapshot columns are not migrated yet
        unset($bookingData['car_name_snapshot'], $bookingData['car_model_snapshot'], $bookingData['car_image_snapshot'], $bookingData['price_per_day_snapshot']);
        $newId = $db->insert('bookings', $bookingData);
    }

    jsonResponse([
        'success' => true,
        'message' => 'Booking submitted successfully! We will contact you shortly.',
        'data' => [
            'booking_id' => $newId,
            'car' => $car['name'] . ' ' . $car['model'],
            'days' => $days,
            'total_price' => $totalPrice,
        ],
    ], 201);
}

function bookingsUpdate(int $id): void
{
    $input = getJsonInput();
    $status = trim($input['status'] ?? '');

    $allowedStatuses = ['Pending', 'Accepted', 'Rejected', 'Completed'];
    if ($status === '' || !in_array($status, $allowedStatuses, true)) {
        jsonResponse(['success' => false, 'message' => 'Invalid status. Allowed: Pending, Accepted, Rejected, Completed.'], 422);
    }

    $db = Database::getInstance();
    $stmt = $db->query('SELECT id, status FROM bookings WHERE id = :id LIMIT 1', ['id' => $id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        jsonResponse(['success' => false, 'message' => 'Booking not found.'], 404);
    }

    $updates = ['status' => $status];
    if (array_key_exists('notes', $input)) {
        $updates['notes'] = $input['notes'];
    }

    $db->update('bookings', $updates, 'id = :id', ['id' => $id]);

    jsonResponse(['success' => true, 'message' => 'Booking updated successfully.', 'data' => ['id' => $id, 'status' => $status]]);
}

function bookingsDelete(int $id): void
{
    $db = Database::getInstance();
    $stmt = $db->query('SELECT id FROM bookings WHERE id = :id LIMIT 1', ['id' => $id]);
    if (!$stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Booking not found.'], 404);
    }

    $db->delete('bookings', 'id = :id', ['id' => $id]);
    jsonResponse(['success' => true, 'message' => 'Booking deleted.']);
}

function bookingsStats(): void
{
    $db = Database::getInstance();
    $pdo = Database::getInstance()->getConnection();

    $total = (int) $pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
    $pending = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn();
    $accepted = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Accepted'")->fetchColumn();
    $rejected = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Rejected'")->fetchColumn();
    $completed = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Completed'")->fetchColumn();
    $revenue = (float) $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM bookings WHERE status IN ('Accepted', 'Completed')")->fetchColumn();

    jsonResponse([
        'success' => true,
        'data' => [
            'total' => $total,
            'pending' => $pending,
            'accepted' => $accepted,
            'rejected' => $rejected,
            'completed' => $completed,
            'revenue' => $revenue,
            'currency' => 'DH',
        ],
    ]);
}
