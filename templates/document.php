<?php

// ── Variable setup ────────────────────────────────────────────────────────────
$type     = strtolower($document['type'] ?? 'invoice');
$isQuote  = $type === 'quote';
$company  = $document['company']    ?? [];
$customer = $document['customer']   ?? [];
$metadata = $document['metadata']   ?? [];
$items    = $document['items']      ?? [];
$payment  = $document['payment']    ?? [];
$legal    = $document['legal']      ?? [];
$notes    = $document['notes']      ?? [];
$totals   = $document['totals']     ?? [];
$acceptance = $document['acceptance'] ?? [];
$bank     = require __DIR__ . '/../config/bank.php';
$terms    = $document['terms']      ?? ($company['terms'] ?? '');

$currencySymbol = $metadata['currency_symbol']  ?? ($document['currency_symbol']  ?? '€');
$currencyCode   = $metadata['currency']         ?? ($document['currency']         ?? 'EUR');
$fxRate         = (float) ($metadata['fx_rate'] ?? 1);
$fxBaseCurrency = $metadata['fx_base_currency'] ?? 'EUR';
$hasFx          = $currencyCode !== $fxBaseCurrency && $fxRate > 0 && $fxRate != 1.0;

if (empty($totals)) {
    $totals = calculate_totals($items, 0);
}

$showToolbar     = !empty($document['show_toolbar']);
$dbId            = $document['db_id'] ?? null;
$dbSaved         = $dbId !== null;
$title           = $isQuote ? 'QUOTE' : 'INVOICE';
$numberLabel     = $isQuote ? 'Quote #'    : 'Invoice #';
$dateLabel       = $isQuote ? 'Issue Date' : 'Date';
$dueLabel        = $isQuote ? 'Valid Until' : 'Payment Due Date';
$issueDate       = $metadata['issue_date'] ?? '';
$secondaryDate   = $isQuote ? ($metadata['valid_until'] ?? '') : ($metadata['due_date'] ?? '');
$reference       = $metadata['reference']  ?? '';
$paymentMethod   = $payment['method']      ?? ($metadata['payment_method'] ?? '');
$paymentTerms    = $payment['payment_terms'] ?? '';
$latePaymentRate = (float) ($company['late_payment_rate']    ?? 0);
$latePaymentFee  = (float) ($company['late_payment_flat_fee'] ?? 0);
$showLatePayment = !array_key_exists('show_late_payment', $legal) || !empty($legal['show_late_payment']);
$logoPath        = $company['logo'] ?? 'img/logo.png';
$logoFile        = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($logoPath, '/\\'));
$logoUrl         = '/' . ltrim($logoPath, '/\\');
$logoAvailable   = is_file($logoFile);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= h($title) ?> <?= h($metadata['number'] ?? '') ?></title>
	<link rel="stylesheet" href="../templates/document.css">
</head>
<body class="document-page">

<?php include __DIR__ . '/partials/_toolbar.php'; ?>

<div class="page">

	<?php include __DIR__ . '/partials/_header.php'; ?>

	<div class="content">

		<?php include __DIR__ . '/partials/_meta.php'; ?>

		<?php include __DIR__ . '/partials/_items-table.php'; ?>

		<?php include __DIR__ . '/partials/_totals.php'; ?>

		<div class="spacer"></div>

		<?php include __DIR__ . '/partials/_legal.php'; ?>

		<?php include __DIR__ . '/partials/_notes.php'; ?>

	</div>

	<footer>
		<div class="thanks">Thank you for your business!</div>
		<div class="contact">
			If you have any questions regarding this invoice, please contact us.<br>
			<?= h($company['phone'] ?? '') ?> • <?= h($company['email'] ?? '') ?>
		</div>
	</footer>

</div>
</body>
</html>
