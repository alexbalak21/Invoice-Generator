<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../src/Calculator/DocumentCalculator.php';
require_once __DIR__ . '/../src/Builder/DocumentBuilder.php';
require_once __DIR__ . '/../src/Validation/DocumentValidator.php';

$company       = require __DIR__ . '/../config/company.php';
$documentTypes = require __DIR__ . '/../config/document_types.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Logger::warning('generate.php — non-POST access, redirecting', [
        'method' => $_SERVER['REQUEST_METHOD'],
        'ip'     => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ]);
    header('Location: index.php');
    exit;
}

$type = strtolower(sanitize_input($_POST['type'] ?? 'invoice'));
if (!in_array($type, $documentTypes, true) || !in_array($type, ['invoice', 'quote'], true)) {
    Logger::warning('generate.php — invalid document type, defaulting to invoice', ['type' => $type]);
    $type = 'invoice';
}

Logger::info('Document generation started', [
    'type' => $type,
    'ip'   => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'number' => sanitize_input($_POST['meta']['number'] ?? ''),
]);

$document = DocumentBuilder::fromPost($_POST, $company, $type);
$errors   = DocumentValidator::validate($document);

if (!empty($errors)) {
    Logger::warning('Document validation failed', [
        'type'   => $type,
        'number' => $document['metadata']['number'] ?? '',
        'errors' => $errors,
    ]);

    $_SESSION['form_errors']          = $errors;
    $_SESSION['document_form_state']  = [
        'type'       => $type,
        'customer'   => $document['customer'],
        'meta'       => $document['metadata'],
        'items'      => $document['items'],
        'notes'      => $document['notes'],
        'acceptance' => $document['acceptance'],
        'legal'      => $document['legal'],
    ];

    header('Location: form.php?type=' . urlencode($type));
    exit;
}

$_SESSION['document_preview']                 = $document;
$_SESSION['document_preview']['show_toolbar'] = true;

// Persist to DB (insert or update by document number)
$savedId = DocumentRepository::save($document);
$_SESSION['document_preview']['db_id'] = $savedId ?: null;

if ($savedId) {
    Logger::info('Document generation complete — redirecting to preview', [
        'type'   => $type,
        'number' => $document['metadata']['number'] ?? '',
        'db_id'  => $savedId,
    ]);
} else {
    Logger::warning('Document generated but not persisted to DB', [
        'type'   => $type,
        'number' => $document['metadata']['number'] ?? '',
    ]);
}

header('Location: preview.php');
exit;
