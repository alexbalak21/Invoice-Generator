<?php

require_once __DIR__ . '/src/Calculator/DocumentCalculator.php';
require_once __DIR__ . '/src/Builder/DocumentBuilder.php';
require_once __DIR__ . '/src/Helpers/helpers.php';
require_once __DIR__ . '/src/Database/db.php';
require_once __DIR__ . '/src/Repository/DocumentRepository.php';

$settings = require __DIR__ . '/config/settings.php';

if (!empty($settings['timezone'])) {
    date_default_timezone_set($settings['timezone']);
}
