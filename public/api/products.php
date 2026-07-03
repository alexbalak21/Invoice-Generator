<?php

/**
 * API endpoint: GET /public/api/products.php
 * Returns the products table as JSON for the product picker.
 * Optional query param: ?q=search+term  (searches reference + name)
 */

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../src/Database/db.php';

header('Content-Type: application/json; charset=utf-8');

$search   = trim($_GET['q'] ?? '');
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

Logger::info('Products API request', [
    'ip'     => $clientIp,
    'search' => $search !== '' ? $search : null,
]);

$db = get_db();

if ($db === null) {
    Logger::error('Products API — database unavailable');
    http_response_code(503);
    echo json_encode(['error' => 'Database unavailable']);
    exit;
}

try {
    if ($search !== '') {
        $stmt = $db->prepare(
            'SELECT ID, reference, name, product_unit, price, page_url
               FROM products
              WHERE reference LIKE :q OR name LIKE :q
           ORDER BY name ASC
              LIMIT 100'
        );
        $stmt->execute([':q' => '%' . $search . '%']);
    } else {
        $stmt = $db->query(
            'SELECT ID, reference, name, product_unit, price, page_url
               FROM products
           ORDER BY name ASC
              LIMIT 200'
        );
    }

    $products = $stmt->fetchAll();

    Logger::info('Products API query successful', [
        'search' => $search !== '' ? $search : null,
        'count'  => count($products),
    ]);

    echo json_encode($products, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    Logger::error('Products API query failed', ['message' => $e->getMessage()]);
    http_response_code(500);
    echo json_encode(['error' => 'Query failed']);
}
