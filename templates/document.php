<?php

$type = strtolower($document["type"] ?? "invoice");
$isQuote = $type === "quote";
$company = $document["company"] ?? [];
$customer = $document["customer"] ?? [];
$metadata = $document["metadata"] ?? [];
$items = $document["items"] ?? [];
$payment = $document["payment"] ?? [];
$legal = $document["legal"] ?? [];
$notes = $document["notes"] ?? [];
$totals = $document["totals"] ?? [];
$acceptance = $document["acceptance"] ?? [];
$bank = require __DIR__ . '/../config/bank.php';
$terms = $document["terms"] ?? ($company["terms"] ?? "");

$currencySymbol  = $metadata["currency_symbol"]  ?? ($document["currency_symbol"]  ?? "€");
$currencyCode    = $metadata["currency"]         ?? ($document["currency"]         ?? "EUR");
$fxRate          = (float) ($metadata["fx_rate"]          ?? 1);
$fxBaseCurrency  = $metadata["fx_base_currency"]  ?? "EUR";
$hasFx           = $currencyCode !== $fxBaseCurrency && $fxRate > 0 && $fxRate != 1.0;

if (empty($totals)) {
	$totals = calculate_totals($items, 0);
}

$showToolbar = !empty($document["show_toolbar"]);
$title = $isQuote ? "QUOTE" : "INVOICE";
$numberLabel = $isQuote ? "Quote #" : "Invoice #";
$dateLabel = $isQuote ? "Issue Date" : "Date";
$dueLabel = $isQuote ? "Valid Until" : "Payment Due Date";
$issueDate = $metadata["issue_date"] ?? "";
$secondaryDate = $isQuote ? ($metadata["valid_until"] ?? "") : ($metadata["due_date"] ?? "");
$reference = $metadata["reference"] ?? "";
$paymentMethod = $payment["method"] ?? ($metadata["payment_method"] ?? "");
$paymentTerms = $payment["payment_terms"] ?? "";
$latePaymentRate = (float) ($company["late_payment_rate"] ?? 0);
$latePaymentFee = (float) ($company["late_payment_flat_fee"] ?? 0);
// Default to true when the key is absent (e.g. documents saved before this option existed).
$showLatePayment = !array_key_exists("show_late_payment", $legal) || !empty($legal["show_late_payment"]);
$logoPath = $company["logo"] ?? "img/logo.png";
$logoFile = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . str_replace(["/", "\\"], DIRECTORY_SEPARATOR, ltrim($logoPath, "/\\"));
$logoUrl = '/' . ltrim($logoPath, "/\\");
$logoAvailable = is_file($logoFile);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= h($title) ?> <?= h($metadata["number"] ?? "") ?></title>
	<link rel="stylesheet" href="../templates/document.css">
</head>
<body class="document-page">

<?php
$dbId   = $document['db_id']   ?? null;
$dbSaved = $dbId !== null;
if ($showToolbar): ?>
<div class="page-toolbar no-print">
	<div>
		<strong><?= h($title) ?></strong>
		<span class="toolbar-subtitle">Printable A4 preview</span>
		<?php if ($dbSaved): ?>
			<span class="toolbar-saved-badge">&#10003; Saved #<?= h($dbId) ?></span>
		<?php endif; ?>
	</div>
	<div class="toolbar-actions">
		<a class="toolbar-link" href="history.php">History</a>
		<a class="toolbar-link" href="form.php?type=<?= h($type) ?>">Edit</a>
		<button type="button" class="toolbar-button" id="printBtn"
			data-save-url="api/save_document.php"
			data-type="<?= h($type) ?>">
			Print
		</button>
	</div>
</div>
<script>
(function(){
	const btn = document.getElementById('printBtn');
	if (!btn) return;
	btn.addEventListener('click', function() {
		window.print();
	});
})();
</script>
<?php endif; ?>

<div class="page">

	<!-- ========== HEADER ========== -->
	<header class="header">

		<div class="company">
			<?php if ($logoAvailable): ?>
				<img src="<?= h($logoUrl) ?>" class="logo" alt="Logo">
			<?php endif; ?>
			<h2><?= h($company["name"] ?? "") ?></h2>
			<p><?= h($company["street"] ?? "") ?></p>
			<p><?= h(trim(($company["zip"] ?? "") . " " . ($company["city"] ?? "") . (($company["country"] ?? "") !== "" ? " - " . ($company["country"] ?? "") : ""))) ?></p>
			<p><?= h($company["email"] ?? "") ?></p>
			<!-- Mandatory legal identifiers -->
			<div class="company-legal">
				<?= h($company["legal_form"] ?? "") ?>
				<?php if (!empty($company["share_capital"])): ?>
					-  Share capital: <?= h($company["share_capital"]) ?>
				<?php endif; ?>
				<br>
				SIRET: <?= h($company["siret"] ?? "") ?><br>
				VAT: <?= h($company["vat_number"] ?? "") ?>
			</div>
		</div>

		<div class="invoice-title">
			<h1><?= h($title) ?></h1>
			<table class="invoice-info">
				<tr>
					<th><?= h($numberLabel) ?></th>
					<th><?= h($dateLabel) ?></th>
				</tr>
				<tr>
					<td><?= h($metadata["number"] ?? "") ?></td>
					<td><?= h($issueDate) ?></td>
				</tr>
			</table>
		</div>

	</header>

	<div class="content">

	<!-- ========== INVOICE META (dates, PO ref, payment method) ========== -->
	<div class="meta-grid">
		<?php if (!empty($reference)): ?>
		<div class="meta-row">
			<span class="meta-label">Reference:</span>
			<span><?= h($reference) ?></span>
		</div>
		<?php endif; ?>
		<div class="meta-row">
			<span class="meta-label"><?= h($dueLabel) ?>:</span>
			<span><?= h($secondaryDate) ?></span>
		</div>
		<?php if (!empty($paymentMethod)): ?>
		<div class="meta-row">
			<span class="meta-label">Payment method:</span>
			<span><?= h($paymentMethod) ?></span>
		</div>
		<?php endif; ?>
		<?php if (!$isQuote && !empty($paymentTerms)): ?>
		<div class="meta-row">
			<span class="meta-label">Payment terms:</span>
			<span><?= h($paymentTerms) ?></span>
		</div>
		<?php endif; ?>
		<div class="meta-row">
			<span class="meta-label">Currency:</span>
			<span>
				<?= h($currencyCode) ?>
				<?php if ($hasFx): ?>
					<span class="fx-rate-meta">(1&nbsp;<?= h($fxBaseCurrency) ?>&nbsp;=&nbsp;<?= number_format($fxRate, 4) ?>&nbsp;<?= h($currencyCode) ?>)</span>
				<?php endif; ?>
			</span>
		</div>
	</div>


	<!-- ========== BILL TO ========== -->
	<section class="bill-to">
		<div class="section-title">BILL TO</div>
		<div class="customer">
			<strong><?= h($customer["name"] ?? "") ?></strong><br>
			<?php if (!empty($customer["company"])): ?>
				<?= h($customer["company"]) ?><br>
			<?php endif; ?>
			<?php if (!empty($customer["department"])): ?>
				<?= h($customer["department"]) ?><br>
			<?php endif; ?>
			<?= h($customer["street"] ?? "") ?><br>
			<?= h(trim(($customer["zip"] ?? "") . " " . ($customer["city"] ?? "") . (($customer["country"] ?? "") !== "" ? " — " . ($customer["country"] ?? "") : ""))) ?><br>
			<?php if (!empty($customer["phone"])): ?>
				<?= h($customer["phone"]) ?><br>
			<?php endif; ?>
			<?= h($customer["email"] ?? "") ?>
			<?php if (!empty($customer["vat_number"])): ?>
				<div class="customer-vat">VAT / GST No.: <?= h($customer["vat_number"]) ?></div>
			<?php endif; ?>
		</div>
	</section>


	<!-- ========== ITEMS TABLE ========== -->
	<table class="items">
		<thead>
			<tr>
				<th class="name">NAME</th>
				<th class="reference">REFERENCE</th>
					<th class="product-unit">PRODUCT UNIT</th>
								<th class="qty">QTY</th>
				<th class="price">UNIT PRICE</th>
				<th class="amount">AMOUNT</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($items as $item): ?>
			<tr>
				<td class="name"><?= h($item["description"] ?? "") ?></td>
				<td class="reference"><?= h($item["reference"] ?? "") ?></td>
						<td class="product-unit"><?= h($item["product_unit"] ?? "") ?></td>
				<td class="center"><?= h($item["quantity"] ?? 0) ?><?php if (!empty($item["unit"])): ?> <?= h($item["unit"]) ?><?php endif; ?></td>
				<td class="right"><?= money($item["unit_price"] ?? 0, $currencySymbol) ?></td>
				<td class="right"><?= money((($item["quantity"] ?? 0) * ($item["unit_price"] ?? 0)) - ($item["discount"] ?? 0), $currencySymbol) ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>


	<!-- ========== TOTALS ========== -->
	<div class="totals">
		<table>
			<tr>
				<td>Subtotal (excl. VAT)</td>
				<td class="right"><?= money($totals["subtotal"] ?? 0, $currencySymbol) ?></td>
			</tr>
			<tr>
				<td>VAT</td>
				<td class="right"><?= money($totals["vat"] ?? 0, $currencySymbol) ?></td>
			</tr>
			<tr class="grand-total">
				<td>TOTAL <?= h($currencyCode) ?></td>
				<td class="right"><?= money($totals["grand_total"] ?? 0, $currencySymbol) ?></td>
			</tr>
			<?php if ($hasFx): ?>
			<tr class="fx-equivalent">
				<td class="fx-note">
					Equivalent in <?= h($fxBaseCurrency) ?>
					<span class="fx-rate-tag">(rate&nbsp;1&nbsp;<?= h($fxBaseCurrency) ?>&nbsp;=&nbsp;<?= number_format($fxRate, 6) ?>&nbsp;<?= h($currencyCode) ?>)</span>
				</td>
				<td class="right fx-note">
					≈&nbsp;<?= h($fxBaseCurrency) ?>&nbsp;<?= number_format(($totals["grand_total"] ?? 0) / $fxRate, 2) ?>
				</td>
			</tr>
			<?php endif; ?>
		</table>
	</div>

	<div class="spacer"></div>


	<!-- ========== VAT LEGAL MENTION ========== -->
	<?php if (!empty($legal["vat_mention"])): ?>
	<div class="vat-mention">
		<?= h($legal["vat_mention"]) ?>
	</div>
	<?php endif; ?>

	<!-- ========== PAYMENT TERMS & MANDATORY B2B MENTIONS ========== -->
	<?php if ($isQuote): ?>
		<div class="payment-terms-block">
			<strong>Quote valid until:</strong> <?= h($secondaryDate) ?>.<br>
			<?php if (!empty($acceptance["text"])): ?>
				<?= h($acceptance["text"]) ?>
			<?php else: ?>
				Please confirm your approval before work starts.
			<?php endif; ?>
		</div>
	<?php else: ?>
		<div class="payment-terms-block">
			<strong>Payment terms:</strong>
			Due date: <?= h($secondaryDate) ?><?= !empty($paymentMethod) ? " — " . h($paymentMethod) . "." : "." ?><br>
			<?php if ($showLatePayment): ?>
				<?php if ($latePaymentRate > 0): ?>
					In the event of late payment, penalties will apply at a rate of
					<strong><?= number_format($latePaymentRate, 2) ?>%</strong> per year.
				<?php endif; ?>
				<?php if ($latePaymentFee > 0): ?>
					A fixed recovery fee of <strong><?= h(number_format($latePaymentFee, 2)) ?> <?= h($currencySymbol) ?></strong> may also apply.
				<?php endif; ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>


	<!-- ========== NOTES ========== -->
	<?php if (!empty($notes["public"])): ?>
	<div class="notes-block">
		<strong>Notes:</strong> <?= h($notes["public"]) ?>
	</div>
	<?php endif; ?>


	<!-- ========== BANK DETAILS ========== -->
	<?php if (!empty($bank)): ?>
	<div class="bank-details">
		<strong>Bank Details</strong>
		<table class="bank-info">
			<tr>
				<td class="bank-label">Beneficiary:</td>
				<td><?= h($bank["beneficiary"] ?? "") ?></td>
			</tr>
			<tr>
				<td class="bank-label">Bank name:</td>
				<td><?= h($bank["bank_name"] ?? "") ?></td>
			</tr>
			<tr>
				<td class="bank-label">Bank address:</td>
				<td><?= h($bank["bank_address"] ?? "") ?></td>
			</tr>
			<tr>
				<td class="bank-label">IBAN:</td>
				<td><strong><?= h($bank["iban"] ?? "") ?></strong></td>
			</tr>
			<tr>
				<td class="bank-label">BIC:</td>
				<td><?= h($bank["bic"] ?? "") ?></td>
			</tr>
		</table>
	</div>
	<?php endif; ?>

	<!-- ========== TERMS & CONDITIONS ========== -->
	<?php if (!empty($terms)): ?>
	<div class="terms-block">
		<strong>Terms &amp; Conditions</strong>
		<div class="terms-content"><?= h($terms) ?></div>
	</div>
	<?php endif; ?>


	<!-- ========== FOOTER ========== -->
	</div>

	<footer>
		<div class="thanks">Thank you for your business!</div>
		<div class="contact">
			If you have any questions regarding this invoice, please contact us. <br/>
			<?= h($company["phone"] ?? "") ?> • <?= h($company["email"] ?? "") ?>
		</div>
	</footer>

</div>
</body>
</html>
