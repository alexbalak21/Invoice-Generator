<?php

require_once __DIR__ . '/helpers.php';
$company = require __DIR__ . '/config/company.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$type = strtolower(sanitize_input($_POST['type'] ?? 'invoice'));
if (!in_array($type, ['invoice', 'quote'], true)) {
    $type = 'invoice';
}

$errors = [];
$document = build_document_from_post($_POST, $company, $type);

if (empty($document['metadata']['number'])) {
    $errors[] = 'Document number is required.';
}

if (empty($document['metadata']['issue_date'])) {
    $errors[] = 'Issue date is required.';
}

if ($type === 'invoice' && empty($document['metadata']['due_date'])) {
    $errors[] = 'Due date is required for invoices.';
}

if ($type === 'quote' && empty($document['metadata']['valid_until'])) {
    $errors[] = 'Valid until date is required for quotes.';
}

if (empty($document['customer']['name'])) {
    $errors[] = 'Customer name is required.';
}

if (empty($document['customer']['street'])) {
    $errors[] = 'Customer street is required.';
}

if (empty($document['items'])) {
    $errors[] = 'At least one item is required.';
}

foreach ($document['items'] as $index => $item) {
    if (empty($item['description'])) {
        $errors[] = 'Item ' . ($index + 1) . ' description is required.';
    }
}

$document['shipping'] = sanitize_input($_POST['shipping'] ?? []);
$document['show_toolbar'] = true;

if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['document_form_state'] = [
        'type' => $type,
        'customer' => $document['customer'],
        'meta' => $document['metadata'],
        'items' => $document['items'],
        'notes' => $document['notes'],
        'acceptance' => $document['acceptance'],
    ];

    header('Location: form.php?type=' . urlencode($type));
    exit;
}

$_SESSION['document_form_state'] = [
    'type' => $type,
    'customer' => $document['customer'],
    'meta' => $document['metadata'],
    'items' => $document['items'],
    'notes' => $document['notes'],
    'acceptance' => $document['acceptance'],
    'legal' => $document['legal'],
];

include __DIR__ . '/template.php';
