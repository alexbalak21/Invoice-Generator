<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../src/Renderer/DocumentRenderer.php';

$document = $_SESSION['document_preview'] ?? null;

if (!is_array($document)) {
    header('Location: index.php');
    exit;
}

(new DocumentRenderer())->render($document);
