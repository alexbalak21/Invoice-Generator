<?php

/**
 * API endpoint: GET /public/api/products.php
 * Returns the products table as JSON for the product picker.
 * Optional query param: ?q=search+term  (searches reference + title)
 */

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../src/Database/db.php';

header('Content-Type: application/json; charset=utf-8');

$db = get_db();

if ($db === null) {
    http_response_code(503);
    echo json_encode(['error' => 'Database unavailable']);
    exit;
}

$search = trim($_GET['q'] ?? '');

try {
    if ($search !== '') {
        $stmt = $db->prepare(
            'SELECT ID, reference, title, size, price
               FROM products
              WHERE reference LIKE :q OR title LIKE :q
           ORDER BY title ASC
              LIMIT 100'
        );
        $stmt->execute([':q' => '%' . $search . '%']);
    } else {
        $stmt = $db->query(
            'SELECT ID, reference, title, size, price
               FROM products
           ORDER BY title ASC
              LIMIT 200'
        );
    }

    $products = $stmt->fetchAll();
    echo json_encode($products, JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed']);
}
