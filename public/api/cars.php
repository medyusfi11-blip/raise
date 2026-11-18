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
$carId  = isset($_GET['id']) ? (int) $_GET['id'] : 0;

match (true) {
    ($action === 'available' && $method === 'GET')                          => carsGetAvailable(),
    ($action === 'feature' && $method === 'POST')                           => (function() { requireLoginApi(); carsAddFeature(); })(),
    ($action === 'feature' && $method === 'DELETE' && isset($_GET['fid']))  => (function() { requireLoginApi(); carsRemoveFeature(); })(),
    ($method === 'GET' && $carId > 0)                                       => carsGetOne($carId),
    ($method === 'GET')                                                     => carsList(),
    ($method === 'POST')                                                    => (function() { requireLoginApi(); carsCreate(); })(),
    ($method === 'PUT' && $carId > 0)                                       => (function() use ($carId) { requireLoginApi(); carsUpdate($carId); })(),
    ($method === 'DELETE' && $carId > 0)                                    => (function() use ($carId) { requireLoginApi(); carsDelete($carId); })(),
    default                                                                => jsonResponse(['success' => false, 'message' => 'Invalid request.'], 400),
};

function carsList(): void
{
    $search = trim($_GET['search'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $type   = trim($_GET['type'] ?? '');
    $limit  = max(1, min(100, (int) ($_GET['limit'] ?? 20)));
    $offset = max(0, (int) ($_GET['offset'] ?? 0));

    $db = Database::getInstance();
    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = '(c.name LIKE :search OR c.model LIKE :search2)';
        $params['search'] = "%{$search}%";
        $params['search2'] = "%{$search}%";
    }
    if ($status !== '' && in_array($status, ['Available', 'Reserved', 'Maintenance'], true)) {
        $where[] = 'c.status = :status';
        $params['status'] = $status;
    }
    if ($type !== '' && in_array($type, ['Hatchback', 'SUV', 'Sedan', 'Van', 'Truck'], true)) {
        $where[] = 'c.type = :type';
        $params['type'] = $type;
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $countStmt = $db->query("SELECT COUNT(*) FROM cars c {$whereClause}", $params);
    $total = (int) $countStmt->fetchColumn();

    $sql = "SELECT c.id, c.name, c.model, c.type, c.year, c.price_per_day,
                   c.transmission, c.fuel_type, c.seats, c.units_available,
                   c.status, c.image, c.features, c.description,
                   c.created_at, c.updated_at
            FROM cars c {$whereClause}
            ORDER BY c.created_at DESC
            LIMIT :limit OFFSET :offset";

    // Bind int params separately (LIMIT/OFFSET must be bound as ints)
    $pdo = Database::getInstance()->getConnection();
    $stmt2 = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt2->bindValue($k, $v);
    }
    $stmt2->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt2->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt2->execute();
    $cars = $stmt2->fetchAll();

    foreach ($cars as &$car) {
        $car['id'] = (int) $car['id'];
        $car['year'] = (int) $car['year'];
        $car['price_per_day'] = (float) $car['price_per_day'];
        $car['seats'] = (int) $car['seats'];
        $car['units_available'] = (int) $car['units_available'];
        $car['features_list'] = json_decode($car['features'] ?? '[]', true);
    }
    unset($car);

    jsonResponse([
        'success' => true,
        'data' => $cars,
        'meta' => ['total' => $total, 'limit' => $limit, 'offset' => $offset],
    ]);
}

function carsGetOne(int $id): void
{
    $db = Database::getInstance();
    $stmt = $db->query('SELECT * FROM cars WHERE id = :id LIMIT 1', ['id' => $id]);
    $car = $stmt->fetch();

    if (!$car) {
        jsonResponse(['success' => false, 'message' => 'Car not found.'], 404);
    }

    $car['id'] = (int) $car['id'];
    $car['year'] = (int) $car['year'];
    $car['price_per_day'] = (float) $car['price_per_day'];
    $car['seats'] = (int) $car['seats'];
    $car['units_available'] = (int) $car['units_available'];
    $car['features_list'] = json_decode($car['features'] ?? '[]', true);

    // Get car_features
    try {
        $featStmt = $db->query('SELECT * FROM car_features WHERE car_id = :cid ORDER BY feature_name', ['cid' => $id]);
        $car['feature_items'] = $featStmt->fetchAll();
    } catch (PDOException $e) {
        $car['feature_items'] = [];
    }

    jsonResponse(['success' => true, 'data' => $car]);
}

function carsGetAvailable(): void
{
    $db = Database::getInstance();
    $stmt = $db->query(
        "SELECT c.id, c.name, c.model, c.type, c.year, c.price_per_day,
                c.transmission, c.fuel_type, c.seats, c.units_available,
                c.status, c.image, c.features, c.description
         FROM cars c
         WHERE c.status = 'Available' AND c.units_available > 0
         ORDER BY c.price_per_day ASC"
    );
    $cars = $stmt->fetchAll();

    foreach ($cars as &$car) {
        $car['id'] = (int) $car['id'];
        $car['year'] = (int) $car['year'];
        $car['price_per_day'] = (float) $car['price_per_day'];
        $car['seats'] = (int) $car['seats'];
        $car['units_available'] = (int) $car['units_available'];
        $car['features_list'] = json_decode($car['features'] ?? '[]', true);
    }
    unset($car);

    jsonResponse(['success' => true, 'data' => $cars]);
}

function carsCreate(): void
{
    $input = getJsonInput();

    $name  = trim($input['name'] ?? '');
    $model = trim($input['model'] ?? '');
    $type  = trim($input['type'] ?? '');
    $year  = (int) ($input['year'] ?? 0);
    $price = (float) ($input['price_per_day'] ?? $input['price'] ?? 0);
    $trans = trim($input['transmission'] ?? 'Manual');
    $fuel  = trim($input['fuel_type'] ?? $input['fuel'] ?? 'Petrol');
    $seats = (int) ($input['seats'] ?? 5);
    $units = (int) ($input['units_available'] ?? $input['units'] ?? 1);
    $desc  = trim($input['description'] ?? '');
    $image = trim($input['image'] ?? '');
    $status = trim($input['status'] ?? 'Available');
    $features = $input['features'] ?? [];

    $errors = [];
    if ($name === '') $errors[] = 'Name is required.';
    if ($model === '') $errors[] = 'Model is required.';
    if ($type === '' || !in_array($type, ['Hatchback', 'SUV', 'Sedan', 'Van', 'Truck'], true)) $errors[] = 'Valid type is required.';
    if ($year < 2000 || $year > (int) date('Y') + 1) $errors[] = 'Invalid year.';
    if ($price <= 0) $errors[] = 'Price must be greater than 0.';

    if ($errors) {
        jsonResponse(['success' => false, 'message' => 'Validation failed.', 'errors' => $errors], 422);
    }

    $db = Database::getInstance();
    $newId = $db->insert('cars', [
        'name' => $name,
        'model' => $model,
        'type' => $type,
        'year' => $year,
        'price_per_day' => $price,
        'transmission' => $trans,
        'fuel_type' => $fuel,
        'seats' => $seats,
        'units_available' => $units,
        'status' => $status,
        'image' => $image,
        'features' => json_encode($features),
        'description' => $desc !== '' ? $desc : null,
    ]);

    // Insert car_features
    if (is_array($features) && count($features) > 0) {
        try {
            foreach ($features as $feat) {
                $featName = is_array($feat) ? trim($feat['name'] ?? '') : trim((string) $feat);
                $featIcon = is_array($feat) ? trim($feat['icon'] ?? 'fas fa-check') : 'fas fa-check';
                if ($featName !== '') {
                    $db->insert('car_features', [
                        'car_id' => $newId,
                        'feature_name' => $featName,
                        'feature_icon' => $featIcon,
                    ]);
                }
            }
        } catch (PDOException $e) { /* features table optional */ }
    }

    jsonResponse(['success' => true, 'message' => 'Car created successfully.', 'data' => ['id' => $newId]], 201);
}

function carsUpdate(int $id): void
{
    $input = getJsonInput();
    $db = Database::getInstance();

    $stmt = $db->query('SELECT id, image FROM cars WHERE id = :id LIMIT 1', ['id' => $id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        jsonResponse(['success' => false, 'message' => 'Car not found.'], 404);
    }

    $updates = [];
    $fields = ['name', 'model', 'type', 'year', 'price_per_day', 'transmission', 'fuel_type', 'seats', 'units_available', 'status', 'description'];

    foreach ($fields as $field) {
        if (array_key_exists($field, $input)) {
            $updates[$field] = $input[$field];
        }
    }
    // Handle alias fields
    if (array_key_exists('price', $input) && !array_key_exists('price_per_day', $input)) {
        $updates['price_per_day'] = $input['price'];
    }
    if (array_key_exists('fuel', $input) && !array_key_exists('fuel_type', $input)) {
        $updates['fuel_type'] = $input['fuel'];
    }
    if (array_key_exists('units', $input) && !array_key_exists('units_available', $input)) {
        $updates['units_available'] = $input['units'];
    }
    if (array_key_exists('image', $input)) {
        $updates['image'] = $input['image'];
    }
    if (array_key_exists('features', $input)) {
        $updates['features'] = is_array($input['features']) ? json_encode($input['features']) : $input['features'];
    }

    if (empty($updates)) {
        jsonResponse(['success' => false, 'message' => 'No fields to update.'], 422);
    }

    $db->update('cars', $updates, 'id = :id', ['id' => $id]);

    // Update car_features if provided
    if (array_key_exists('features', $input) && is_array($input['features'])) {
        try {
            $db->delete('car_features', 'car_id = :cid', ['cid' => $id]);
            foreach ($input['features'] as $feat) {
                $featName = is_array($feat) ? trim($feat['name'] ?? '') : trim((string) $feat);
                $featIcon = is_array($feat) ? trim($feat['icon'] ?? 'fas fa-check') : 'fas fa-check';
                if ($featName !== '') {
                    $db->insert('car_features', [
                        'car_id' => $id,
                        'feature_name' => $featName,
                        'feature_icon' => $featIcon,
                    ]);
                }
            }
        } catch (PDOException $e) { /* ignore */ }
    }

    jsonResponse(['success' => true, 'message' => 'Car updated successfully.']);
}

function carsDelete(int $id): void
{
    $db = Database::getInstance();
    $stmt = $db->query('SELECT id, name, model, image, price_per_day FROM cars WHERE id = :id LIMIT 1', ['id' => $id]);
    $car = $stmt->fetch();

    if (!$car) {
        jsonResponse(['success' => false, 'message' => 'Car not found.'], 404);
    }

    try {
        $db->delete('car_features', 'car_id = :cid', ['cid' => $id]);
    } catch (PDOException $e) { /* ignore */ }

    // Ensure existing bookings keep financial/history data even if this car is removed
    try {
        $db->query(
            "UPDATE bookings
             SET car_name_snapshot = CASE WHEN COALESCE(car_name_snapshot, '') = '' THEN :name ELSE car_name_snapshot END,
                 car_model_snapshot = CASE WHEN COALESCE(car_model_snapshot, '') = '' THEN :model ELSE car_model_snapshot END,
                 car_image_snapshot = CASE WHEN COALESCE(car_image_snapshot, '') = '' THEN :image ELSE car_image_snapshot END,
                 price_per_day_snapshot = CASE WHEN COALESCE(price_per_day_snapshot, 0) = 0 THEN :price ELSE price_per_day_snapshot END
             WHERE car_id = :id",
            [
                'name' => $car['name'],
                'model' => $car['model'],
                'image' => $car['image'] ?? '',
                'price' => (float) ($car['price_per_day'] ?? 0),
                'id' => $id,
            ]
        );
    } catch (Throwable $e) { /* snapshot columns may not exist yet */ }

    $db->delete('cars', 'id = :id', ['id' => $id]);

    // Delete image file
    if (!empty($car['image']) && !str_starts_with($car['image'], 'http')) {
        $filePath = APP_ROOT . '/public/' . ltrim($car['image'], '/');
        if (is_file($filePath)) @unlink($filePath);
    }

    jsonResponse(['success' => true, 'message' => 'Car deleted successfully. Booking history and profits are preserved.']);
}

function carsAddFeature(): void
{
    $input = getJsonInput();
    $carId = (int) ($input['car_id'] ?? 0);
    $featName = trim($input['feature_name'] ?? '');
    $featIcon = trim($input['feature_icon'] ?? 'fas fa-check');

    if ($carId <= 0 || $featName === '') {
        jsonResponse(['success' => false, 'message' => 'car_id and feature_name are required.'], 422);
    }

    $db = Database::getInstance();
    $stmt = $db->query('SELECT id FROM cars WHERE id = :id LIMIT 1', ['id' => $carId]);
    if (!$stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Car not found.'], 404);
    }

    $newId = $db->insert('car_features', [
        'car_id' => $carId,
        'feature_name' => $featName,
        'feature_icon' => $featIcon,
    ]);

    jsonResponse(['success' => true, 'message' => 'Feature added.', 'data' => ['id' => $newId]], 201);
}

function carsRemoveFeature(): void
{
    $featureId = (int) ($_GET['fid'] ?? 0);
    if ($featureId <= 0) {
        jsonResponse(['success' => false, 'message' => 'Feature ID required.'], 422);
    }

    $db = Database::getInstance();
    $db->delete('car_features', 'id = :id', ['id' => $featureId]);
    jsonResponse(['success' => true, 'message' => 'Feature removed.']);
}
