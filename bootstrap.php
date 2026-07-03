<?php

require_once __DIR__ . '/src/Logger/Logger.php';
require_once __DIR__ . '/src/Calculator/DocumentCalculator.php';
require_once __DIR__ . '/src/Builder/DocumentBuilder.php';
require_once __DIR__ . '/src/Helpers/helpers.php';
require_once __DIR__ . '/src/Database/db.php';
require_once __DIR__ . '/src/Repository/DocumentRepository.php';

$settings = require __DIR__ . '/config/settings.php';

if (!empty($settings['timezone'])) {
    date_default_timezone_set($settings['timezone']);
}

// Initialise the central logger (all modules use Logger:: after this point)
Logger::init(__DIR__ . '/logs/app.log');

Logger::info('Bootstrap complete', [
    'php'    => PHP_VERSION,
    'script' => basename($_SERVER['SCRIPT_FILENAME'] ?? 'cli'),
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
    'uri'    => $_SERVER['REQUEST_URI']    ?? '',
]);
