<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../src/Calculator/DocumentCalculator.php';
require_once __DIR__ . '/../src/Builder/DocumentBuilder.php';
require_once __DIR__ . '/../src/Validation/DocumentValidator.php';

$company = require __DIR__ . '/../config/company.php';
$documentTypes = require __DIR__ . '/../config/document_types.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$type = strtolower(sanitize_input($_POST['type'] ?? 'invoice'));
if (!in_array($type, $documentTypes, true) || !in_array($type, ['invoice', 'quote'], true)) {
    $type = 'invoice';
}

$document = DocumentBuilder::fromPost($_POST, $company, $type);
$errors = DocumentValidator::validate($document);

if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['document_form_state'] = [
        'type' => $type,
        'customer' => $document['customer'],
        'meta' => $document['metadata'],
        'items' => $document['items'],
        'notes' => $document['notes'],
        'acceptance' => $document['acceptance'],
        'legal' => $document['legal'],
    ];

    header('Location: form.php?type=' . urlencode($type));
    exit;
}

$_SESSION['document_preview'] = $document;
$_SESSION['document_preview']['show_toolbar'] = true;

// ── Persist to DB (insert or update by document number) ──
$savedId = DocumentRepository::save($document);
$_SESSION['document_preview']['db_id'] = $savedId ?: null;

header('Location: preview.php');
exit;
