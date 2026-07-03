<?php

require_once __DIR__ . '/helpers.php';

$settings = require __DIR__ . '/config/settings.php';

if (!empty($settings['timezone'])) {
    date_default_timezone_set($settings['timezone']);
}
