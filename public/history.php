<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../src/Controllers/HistoryController.php';
require_once __DIR__ . '/../src/Helpers/ViewHelpers.php';

$company = require __DIR__ . '/../config/company.php';

extract(HistoryController::handle($company));

require __DIR__ . '/../views/history/page.php';
