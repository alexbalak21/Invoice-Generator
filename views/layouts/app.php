<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= h($pageTitle ?? 'Document Generator') ?></title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/css/app.css">
	<?php if (!empty($extraHead)) echo $extraHead; ?>
</head>
<body class="app-shell">
