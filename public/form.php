<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../src/Controllers/FormController.php';

$company = require __DIR__ . '/../config/company.php';

extract(FormController::getFormState($company));

require __DIR__ . '/../views/form/page.php';
