<?php

require_once __DIR__ . '/../bootstrap.php';
$company = require __DIR__ . '/../config/company.php';

$type = strtolower(sanitize_input($_GET['type'] ?? ($_SESSION['document_form_state']['type'] ?? 'invoice')));
if (!in_array($type, ['invoice', 'quote'], true)) {
	$type = 'invoice';
}

$state = $_SESSION['document_form_state'] ?? [];
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);

$today = date('Y-m-d');
$defaultIssueDate = $state['meta']['issue_date'] ?? $today;
$defaultDueDate = $state['meta']['due_date'] ?? add_days_to_date($defaultIssueDate, (int) ($company['default_invoice_due_days'] ?? 30));
$defaultValidUntil = $state['meta']['valid_until'] ?? add_days_to_date($defaultIssueDate, (int) ($company['default_quote_valid_days'] ?? 30));
$defaultItems = $state['items'] ?? [
	[
		'reference' => '',
		'description' => '',
		'product_unit' => '',
	],
];

$customer = $state['customer'] ?? [
	'name' => '',
	'company' => '',
	'department' => '',
	'street' => '',
	'city' => '',
	'zip' => '',
	'country' => '',
	'phone' => '',
	'email' => '',
	'vat_number' => '',
];

$meta = $state['meta'] ?? [
	'number' => '',
	'issue_date' => $defaultIssueDate,
	'due_date' => $defaultDueDate,
	'valid_until' => $defaultValidUntil,
	'reference' => '',
	'payment_method' => $company['default_payment_method'] ?? 'Bank Transfer (Wire)',
	'payment_terms' => '30 days',
	'currency' => $company['default_currency'] ?? 'EUR',
	'currency_symbol' => $company['default_currency_symbol'] ?? '€',
	'vat_mention' => $company['vat_mention'] ?? '',
];

$meta += [
	'vat_mention' => $company['vat_mention'] ?? '',
];

if (empty($state['meta']['issue_date'])) {
	$meta['issue_date'] = $defaultIssueDate;
}

if ($type === 'invoice') {
	$meta['due_date'] = $state['meta']['due_date'] ?? $defaultDueDate;
} else {
	$meta['valid_until'] = $state['meta']['valid_until'] ?? $defaultValidUntil;
}

$notes = $state['notes'] ?? [
	'public' => '',
	'internal' => '',
];

$terms = $state['terms'] ?? ($company['terms'] ?? '');

// The company always bills in its own accounting currency, no matter what currency
// a customer's PO or an imported JSON file happens to use — so this is never taken
// from $meta / imported data, only from the company config.
$currency = $company['default_currency'] ?? 'EUR';
$currencySymbol = $company['default_currency_symbol'] ?? '€';
$companyCurrency = $currency; // accounting base currency (never changes)
$currencies = require __DIR__ . '/../config/currencies.php';
$defaultVatRate = (float) ($company['default_vat_rate'] ?? 0);
$totals = calculate_totals($defaultItems, $defaultVatRate);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= h(document_title($type)) ?> Form</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="app-shell">
<div class="container py-4 py-md-5">
	<div class="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap">
		<div>
			<div class="eyebrow">Create <?= h(document_label($type)) ?></div>
			<h1 class="h2 mb-1">Printable A4 document</h1>
			<p class="text-muted mb-0">Fill the form, generate the HTML document, then print to PDF from the browser.</p>
		</div>
		<div class="d-flex flex-wrap gap-2">
			<a class="btn btn-outline-secondary" href="<?= h($type) ?>.json" download>Download sample JSON</a>
			<button type="button" class="btn btn-outline-secondary" id="jsonSaveButton">Save JSON</button>
			<button type="button" class="btn btn-outline-primary" id="jsonImportButton">Upload JSON</button>
			<a class="btn btn-outline-secondary" href="index.php">Back to dashboard</a>
		</div>
	</div>

	<?php if (!empty($errors)): ?>
		<div class="alert alert-danger">
			<strong>Please fix the highlighted issues.</strong>
			<ul class="mb-0 mt-2">
				<?php foreach ($errors as $error): ?>
					<li><?= h($error) ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<form method="post" action="generate.php" class="document-form" id="documentForm" data-default-vat="<?= h($defaultVatRate) ?>" data-currency-symbol="<?= h($currencySymbol) ?>">
		<input type="hidden" name="type" value="<?= h($type) ?>">
		<input type="file" id="jsonImportInput" class="d-none" accept="application/json,.json">

		<div class="row g-4">
			<div class="col-12 col-xl-8">
				<div class="card shadow-sm border-0 mb-4">
					<div class="card-body p-4">
						<h2 class="h5 mb-4">Customer</h2>
						<div class="row g-3">
							<div class="col-md-6">
								<label class="form-label">Customer name</label>
								<input class="form-control" type="text" name="customer[name]" value="<?= h($customer['name'] ?? '') ?>" required>
							</div>
							<div class="col-md-6">
								<label class="form-label">Company</label>
								<input class="form-control" type="text" name="customer[company]" value="<?= h($customer['company'] ?? '') ?>">
							</div>
							<div class="col-md-12">
								<label class="form-label">Department</label>
								<input class="form-control" type="text" name="customer[department]" value="<?= h($customer['department'] ?? '') ?>">
							</div>
							<div class="col-md-12">
								<label class="form-label">Street</label>
								<input class="form-control" type="text" name="customer[street]" value="<?= h($customer['street'] ?? '') ?>" required>
							</div>
							<div class="col-md-4">
								<label class="form-label">City</label>
								<input class="form-control" type="text" name="customer[city]" value="<?= h($customer['city'] ?? '') ?>" required>
							</div>
							<div class="col-md-4">
								<label class="form-label">ZIP</label>
								<input class="form-control" type="text" name="customer[zip]" value="<?= h($customer['zip'] ?? '') ?>" required>
							</div>
							<div class="col-md-4">
								<label class="form-label">Country</label>
								<input class="form-control" type="text" name="customer[country]" value="<?= h($customer['country'] ?? '') ?>" required>
							</div>
							<div class="col-md-6">
								<label class="form-label">Email</label>
								<input class="form-control" type="email" name="customer[email]" value="<?= h($customer['email'] ?? '') ?>" required>
							</div>
							<div class="col-md-6">
								<label class="form-label">Phone</label>
								<input class="form-control" type="text" name="customer[phone]" value="<?= h($customer['phone'] ?? '') ?>">
							</div>
							<div class="col-md-12">
								<label class="form-label">VAT number</label>
								<input class="form-control" type="text" name="customer[vat_number]" value="<?= h($customer['vat_number'] ?? '') ?>">
							</div>
						</div>
					</div>
				</div>

				<div class="card shadow-sm border-0 mb-4">
					<div class="card-body p-4">
						<h2 class="h5 mb-4">Document info</h2>
						<div class="row g-3">
							<div class="col-md-6">
								<label class="form-label">Document number</label>
								<input class="form-control" type="text" name="meta[number]" value="<?= h($meta['number'] ?? '') ?>" required>
							</div>
							<div class="col-md-6">
								<label class="form-label">Issue date</label>
								<input class="form-control" type="date" name="meta[issue_date]" value="<?= h($meta['issue_date'] ?? $today) ?>" required>
							</div>
							<div class="col-md-6 <?= $type === 'quote' ? 'd-none' : '' ?>" data-invoice-field>
								<label class="form-label">Due date</label>
								<input class="form-control" type="date" name="meta[due_date]" value="<?= h($meta['due_date'] ?? $defaultDueDate) ?>">
							</div>
							<div class="col-md-6 <?= $type === 'invoice' ? 'd-none' : '' ?>" data-quote-field>
								<label class="form-label">Valid until</label>
								<input class="form-control" type="date" name="meta[valid_until]" value="<?= h($meta['valid_until'] ?? $defaultValidUntil) ?>">
							</div>
							<div class="col-md-6">
								<label class="form-label">Reference</label>
								<input class="form-control" type="text" name="meta[reference]" value="<?= h($meta['reference'] ?? '') ?>">
							</div>
							<div class="col-md-6">
								<label class="form-label">Payment method</label>
								<input class="form-control" type="text" name="meta[payment_method]" value="<?= h($meta['payment_method'] ?? '') ?>">
							</div>
							<div class="col-md-4">
								<label class="form-label">Invoice currency</label>
								<select class="form-select" name="meta[currency]" id="currencySelect">
								<?php foreach ($currencies as $code => $cur): ?>
									<option value="<?= h($code) ?>"
										data-symbol="<?= h($cur['symbol']) ?>"
										<?= ($meta['currency'] ?? $currency) === $code ? 'selected' : '' ?>>
										<?= h($code) ?> — <?= h($cur['name']) ?>
									</option>
								<?php endforeach; ?>
								</select>
								<input type="hidden" name="meta[currency_symbol]" id="currencySymbolHidden" value="<?= h($meta['currency_symbol'] ?? $currencySymbol) ?>">
								<div class="form-text">Base company currency: <?= h($companyCurrency) ?>.</div>
							</div>
							<div class="col-md-4" id="fxRateBlock" style="<?= ($meta['currency'] ?? $currency) === $companyCurrency ? 'display:none' : '' ?>">
								<label class="form-label">
									Conversion rate
									<span class="text-muted small" id="fxRateLabel">(1 <?= h($companyCurrency) ?> = ? <span id="fxRateCurrencyLabel"><?= h($meta['currency'] ?? $currency) ?></span>)</span>
								</label>
								<input class="form-control" type="number" step="0.000001" min="0.000001"
									name="meta[fx_rate]" id="fxRateInput"
									value="<?= h($meta['fx_rate'] ?? '') ?>"
									placeholder="e.g. 1.08">
								<div class="form-text">Used to show the <?= h($companyCurrency) ?> equivalent on the document.</div>
							</div>
							<div class="col-md-4">
								<label class="form-label">Payment terms</label>
								<input class="form-control" type="text" name="meta[payment_terms]" value="<?= h($meta['payment_terms'] ?? '30 days') ?>">
							</div>
							<div class="col-md-12">
								<label class="form-label">VAT mention</label>
								<input class="form-control" type="text" name="meta[vat_mention]" value="<?= h($meta['vat_mention'] ?? '') ?>">
							</div>
							<div class="col-12 <?= $type === 'quote' ? 'd-none' : '' ?>" data-invoice-field>
								<label class="form-check-label">
									<input class="form-check-input me-2" type="checkbox" name="legal[show_late_payment]" value="1" <?= (!isset($state['legal']['show_late_payment']) || !empty($state['legal']['show_late_payment'])) ? 'checked' : '' ?>>
									Include late payment penalty &amp; recovery fee mention
								</label>
								<div class="form-text">"In the event of late payment, penalties will apply at a rate of 4.50% per year. A fixed recovery fee of 40.00 € may also apply." Untick to leave this out of the document.</div>
							</div>
						</div>
					</div>
				</div>

				<div class="card shadow-sm border-0 mb-4">
					<div class="card-body p-4">
						<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
							<h2 class="h5 mb-0">Items</h2>
							<div class="d-flex gap-2">
								<button type="button" class="btn btn-outline-secondary btn-sm" id="addProductButton">&#43; From catalogue</button>
								<button type="button" class="btn btn-outline-primary btn-sm" id="addItemButton">&#43; Custom row</button>
							</div>
						</div>

						<div class="table-responsive">
							<table class="table align-middle items-editor">
								<thead>
									<tr>
										<th>Reference</th>
										<th>Name</th>
										<th>Product unit</th>
															<th class="text-end">Qty</th>
										<th>Unit</th>
										<th class="text-end">Discount</th>
										<th class="text-end">Unit price</th>
										<th class="text-end">VAT %</th>
										<th></th>
									</tr>
								</thead>
								<tbody id="itemsBody" data-items-body>
									<?php foreach ($defaultItems as $index => $item): ?>
										<tr data-item-row>
											<td><input class="form-control form-control-sm" type="text" name="items[<?= h($index) ?>][reference]" value="<?= h($item['reference'] ?? '') ?>"></td>
											<td><input class="form-control form-control-sm" type="text" name="items[<?= h($index) ?>][description]" value="<?= h($item['description'] ?? '') ?>" required></td>
									<td><input class="form-control form-control-sm" type="text" name="items[<?= h($index) ?>][product_unit]" value="<?= h($item['product_unit'] ?? '') ?>" data-field="product_unit"></td>
					
											<td><input class="form-control form-control-sm text-end" type="number" min="0" step="0.01" name="items[<?= h($index) ?>][quantity]" value="<?= h($item['quantity'] ?? 1) ?>" data-field="quantity"></td>
											<td><input class="form-control form-control-sm" type="text" name="items[<?= h($index) ?>][unit]" value="<?= h($item['unit'] ?? '') ?>" data-field="unit"></td>
											<td><input class="form-control form-control-sm text-end" type="number" min="0" step="0.01" name="items[<?= h($index) ?>][discount]" value="<?= h($item['discount'] ?? 0) ?>" data-field="discount"></td>
											<td><input class="form-control form-control-sm text-end" type="number" min="0" step="0.01" name="items[<?= h($index) ?>][unit_price]" value="<?= h($item['unit_price'] ?? '') ?>" data-field="unit_price"></td>
											<td><input class="form-control form-control-sm text-end" type="number" min="0" step="0.01" name="items[<?= h($index) ?>][vat_rate]" value="<?= h($item['vat_rate'] ?? $defaultVatRate) ?>" data-field="vat_rate"></td>
											<td class="text-end"><button type="button" class="btn btn-link text-danger p-0" data-remove-row>Remove</button></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>

						<template id="itemRowTemplate">
							<tr data-item-row>
								<td><input class="form-control form-control-sm" type="text" name="items[__INDEX__][reference]"></td>
								<td><input class="form-control form-control-sm" type="text" name="items[__INDEX__][description]" required></td>
								<td><input class="form-control form-control-sm" type="text" name="items[__INDEX__][product_unit]" value="" data-field="product_unit"></td>
					
								<td><input class="form-control form-control-sm text-end" type="number" min="0" step="0.01" name="items[__INDEX__][quantity]" value="1" data-field="quantity"></td>
								<td><input class="form-control form-control-sm" type="text" name="items[__INDEX__][unit]" value="" data-field="unit"></td>
								<td><input class="form-control form-control-sm text-end" type="number" min="0" step="0.01" name="items[__INDEX__][discount]" value="0" data-field="discount"></td>
								<td><input class="form-control form-control-sm text-end" type="number" min="0" step="0.01" name="items[__INDEX__][unit_price]" value="0" data-field="unit_price"></td>
								<td><input class="form-control form-control-sm text-end" type="number" min="0" step="0.01" name="items[__INDEX__][vat_rate]" value="<?= h($defaultVatRate) ?>" data-field="vat_rate"></td>
								<td class="text-end"><button type="button" class="btn btn-link text-danger p-0" data-remove-row>Remove</button></td>
							</tr>
						</template>
					</div>
				</div>

				<div class="card shadow-sm border-0">
					<div class="card-body p-4">
						<h2 class="h5 mb-3">Notes</h2>
						<div class="row g-3">
							<div class="col-12">
								<label class="form-label">Public notes</label>
								<textarea class="form-control" rows="4" name="notes[public]"><?= h($notes['public'] ?? '') ?></textarea>
							</div>
							<div class="col-12">
								<label class="form-label">Internal notes</label>
								<textarea class="form-control" rows="3" name="notes[internal]"><?= h($notes['internal'] ?? '') ?></textarea>
							</div>
							<div class="col-12">
								<label class="form-label">Terms &amp; Conditions</label>
								<textarea class="form-control" rows="4" name="terms"><?= h($terms) ?></textarea>
							</div>
							<div class="col-12">
								<label class="form-check-label">
									<input class="form-check-input me-2" type="checkbox" name="acceptance[enabled]" value="1" <?= ($type === 'quote' || !empty(($state['acceptance']['enabled'] ?? false))) ? 'checked' : '' ?>>
									Include acceptance text in the printed document
								</label>
							</div>
							<div class="col-12">
								<label class="form-label">Acceptance text</label>
								<textarea class="form-control" rows="2" name="acceptance[text]"><?= h($state['acceptance']['text'] ?? 'Quote received before execution, read and approved, agreed.') ?></textarea>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-xl-4">
				<div class="card shadow-sm border-0 sticky-top preview-card" style="top: 1.5rem;">
					<div class="card-body p-4">
						<h2 class="h5 mb-3">Preview totals</h2>
						<div class="totals-preview">
							<div class="d-flex justify-content-between"><span>Subtotal</span><strong id="previewSubtotal"><?= h(format_money($totals['subtotal'], $currencySymbol)) ?></strong></div>
							<div class="d-flex justify-content-between"><span>VAT</span><strong id="previewVat"><?= h(format_money($totals['vat'], $currencySymbol)) ?></strong></div>
							<hr>
							<div class="d-flex justify-content-between totals-preview-grand"><span>Total</span><strong id="previewGrandTotal"><?= h(format_money($totals['grand_total'], $currencySymbol)) ?></strong></div>
						</div>
						<p class="text-muted small mt-3 mb-0">The PHP renderer remains the final calculation source. This preview is only a convenience.</p>
						<button type="submit" class="btn btn-primary btn-lg w-100 mt-4">Generate Document</button>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- ===== Product Picker Modal ===== -->
<div class="modal fade" id="productPickerModal" tabindex="-1" aria-labelledby="productPickerLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="productPickerLabel">Add from catalogue</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<div class="mb-3">
					<input type="search" id="productSearch" class="form-control" placeholder="Search by reference or name…" autocomplete="off">
				</div>
				<div id="productPickerStatus" class="text-muted small mb-2"></div>
				<div class="table-responsive">
					<table class="table table-hover table-sm align-middle" id="productPickerTable">
						<thead class="table-light">
							<tr>
								<th>Reference</th>
								<th>Name</th>
								<th>Unit</th>
								<th class="text-end">Unit price</th>
								<th></th>
							</tr>
						</thead>
						<tbody id="productPickerBody">
							<tr><td colspan="5" class="text-center text-muted py-4">Loading…</td></tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	// JSON Import functionality
	const jsonImportButton = document.getElementById('jsonImportButton');
	const jsonImportInput = document.getElementById('jsonImportInput');

	if (jsonImportButton && jsonImportInput) {
		jsonImportButton.addEventListener('click', function() {
			jsonImportInput.click();
		});

		jsonImportInput.addEventListener('change', function(e) {
			const file = e.target.files[0];
			if (!file) return;

			const reader = new FileReader();
			reader.onload = function(event) {
				try {
					const data = JSON.parse(event.target.result);
					importFormData(data);
				} catch (error) {
					alert('Invalid JSON file: ' + error.message);
				}
			};
			reader.readAsText(file);
		});
	}

	const jsonSaveButton = document.getElementById('jsonSaveButton');
	if (jsonSaveButton) {
		jsonSaveButton.addEventListener('click', function() {
			const data = gatherFormJson();
			const validation = validateFormJson(data);
			if (!validation.valid) {
				alert('Cannot save JSON:\n' + validation.errors.join('\n'));
				return;
			}

			try {
				const json = JSON.stringify(data, null, 2);
				JSON.parse(json);
				downloadJson(json, data.type + '.json');
			} catch (error) {
				alert('JSON generation failed: ' + error.message);
			}
		});
	}

	function gatherFormJson() {
		const form = document.getElementById('documentForm');
		const type = form.querySelector('input[name="type"]').value || 'invoice';

		const customerFields = ['name', 'company', 'department', 'street', 'city', 'zip', 'country', 'phone', 'email', 'vat_number'];
		const customer = {};
		customerFields.forEach(key => {
			const input = form.querySelector(`input[name="customer[${key}]"]`);
			customer[key] = input ? input.value.trim() : '';
		});

		const metaFields = ['number', 'issue_date', 'due_date', 'valid_until', 'reference', 'payment_method', 'payment_terms', 'currency', 'currency_symbol', 'vat_mention', 'fx_rate'];
		const meta = {};
		metaFields.forEach(key => {
			const input = form.querySelector(`[name="meta[${key}]"]`);
			meta[key] = input ? input.value.trim() : '';
		});

		const notes = {
			public: (form.querySelector('textarea[name="notes[public]"]') || {}).value || '',
			internal: (form.querySelector('textarea[name="notes[internal]"]') || {}).value || '',
		};

		const terms = (form.querySelector('textarea[name="terms"]') || {}).value || '';

		const acceptance = {
			enabled: Boolean(form.querySelector('input[name="acceptance[enabled]"]')?.checked),
			text: (form.querySelector('textarea[name="acceptance[text]"]') || {}).value || '',
		};

		const items = [];
		document.querySelectorAll('[data-item-row]').forEach(row => {
			const item = {
				reference: (row.querySelector('input[name*="reference"]') || {}).value.trim(),
				description: (row.querySelector('input[name*="description"]') || {}).value.trim(),
									product_unit: (row.querySelector('input[name*="product_unit"]') || {}).value.trim(),
													quantity: parseFloat((row.querySelector('input[name*="quantity"]') || {}).value) || 0,
									unit: (row.querySelector('input[name$="[unit]"]') || {}).value.trim(),
				unit_price: parseFloat((row.querySelector('input[name*="unit_price"]') || {}).value) || 0,
				discount: parseFloat((row.querySelector('input[name*="discount"]') || {}).value) || 0,
				vat_rate: parseFloat((row.querySelector('input[name*="vat_rate"]') || {}).value) || 0,
			};
			if (item.reference || item.description || item.product_unit || item.quantity || item.unit || item.unit_price || item.discount || item.vat_rate) {
				items.push(item);
			}
		});


		return {
			type,
			customer,
			meta,
			items,
			notes,
			acceptance,
			terms,
		};
	}

	function validateFormJson(data) {
		const errors = [];
		if (![ 'invoice', 'quote' ].includes(data.type)) {
			errors.push('Type must be invoice or quote.');
		}
		if (!data.customer.name) {
			errors.push('Customer name is required.');
		}
		if (!data.customer.street) {
			errors.push('Customer street is required.');
		}
		if (!data.meta.number) {
			errors.push('Document number is required.');
		}
		if (!data.meta.issue_date) {
			errors.push('Issue date is required.');
		}
		if (data.type === 'invoice' && !data.meta.due_date) {
			errors.push('Due date is required for invoices.');
		}
		if (data.type === 'quote' && !data.meta.valid_until) {
			errors.push('Valid until is required for quotes.');
		}
		if (!Array.isArray(data.items) || data.items.length === 0) {
			errors.push('At least one item is required.');
		} else {
			data.items.forEach((item, index) => {
				if (!item.description) {
					errors.push('Item ' + (index + 1) + ': description is required.');
				}
			});
		}

		return { valid: errors.length === 0, errors };
	}

	function downloadJson(content, filename) {
		const blob = new Blob([content], { type: 'application/json' });
		const url = URL.createObjectURL(blob);
		const anchor = document.createElement('a');
		anchor.href = url;
		anchor.download = filename;
		document.body.appendChild(anchor);
		anchor.click();
		anchor.remove();
		URL.revokeObjectURL(url);
	}

	function importFormData(data) {
		// Import customer data
		if (data.customer) {
			Object.keys(data.customer).forEach(key => {
				const input = document.querySelector(`input[name="customer[${key}]"]`);
				if (input) input.value = data.customer[key] || '';
			});
		}

		// Import meta data
		if (data.meta) {
			// Currency is locked to the company's own accounting currency (EUR) and must
			// never be overwritten by an imported JSON file, which may be denominated
			// in the customer's local currency (e.g. INR).
			const lockedFields = ['currency', 'currency_symbol'];
			Object.keys(data.meta).forEach(key => {
				if (lockedFields.includes(key)) return;
				const input = document.querySelector(`input[name="meta[${key}]"]`);
				if (input) input.value = data.meta[key] || '';
			});
		}

		// Import items
		if (data.items && Array.isArray(data.items)) {
			const itemsBody = document.getElementById('itemsBody');
			itemsBody.innerHTML = '';
			data.items.forEach((item, index) => {
				addItemRow(item, index);
			});
		}

		// Import notes
		if (data.notes) {
			if (data.notes.public) {
				document.querySelector('textarea[name="notes[public]"]').value = data.notes.public;
			}
			if (data.notes.internal) {
				document.querySelector('textarea[name="notes[internal]"]').value = data.notes.internal;
			}
		}

		// Import terms
		if (data.terms) {
			const termsField = document.querySelector('textarea[name="terms"]');
			if (termsField) termsField.value = data.terms;
		}

		// Import acceptance data
		if (data.acceptance) {
			const acceptanceCheckbox = document.querySelector('input[name="acceptance[enabled]"]');
			if (acceptanceCheckbox && data.acceptance.enabled) {
				acceptanceCheckbox.checked = true;
			}
			const acceptanceText = document.querySelector('textarea[name="acceptance[text]"]');
			if (acceptanceText && data.acceptance.text) {
				acceptanceText.value = data.acceptance.text;
			}
		}

		// Reset file input
		jsonImportInput.value = '';
		
		// Trigger totals recalculation
		updateFormTotals();
	}

	// Add item row with data
	function addItemRow(item, index) {
		const template = document.getElementById('itemRowTemplate');
		const row = template.content.cloneNode(true);
		const itemRow = row.querySelector('[data-item-row]');
		
		// Update all input names to use correct index
		row.querySelectorAll('input').forEach(input => {
			const name = input.name.replace('__INDEX__', index);
			input.name = name;
		});

		// Fill in values
		if (item.reference) row.querySelector('input[name*="reference"]').value = item.reference;
		if (item.description) row.querySelector('input[name*="description"]').value = item.description;
		if (item.product_unit) row.querySelector('input[name*="product_unit"]').value = item.product_unit;
		if (item.quantity) row.querySelector('input[name*="quantity"]').value = item.quantity;
		if (item.unit) row.querySelector('input[name$="[unit]"]').value = item.unit;
		if (item.discount) row.querySelector('input[name*="discount"]').value = item.discount;
		if (item.unit_price) row.querySelector('input[name*="unit_price"]').value = item.unit_price;
		if (item.vat_rate !== undefined) row.querySelector('input[name*="vat_rate"]').value = item.vat_rate;

		// Add remove button functionality
		const removeBtn = row.querySelector('[data-remove-row]');
		if (removeBtn) {
			removeBtn.addEventListener('click', function() {
				this.closest('[data-item-row]').remove();
				updateFormTotals();
			});
		}

		document.getElementById('itemsBody').appendChild(row);
	}

	// Update form totals preview
	function updateFormTotals() {
		const form = document.getElementById('documentForm');
		const defaultVat = parseFloat(form.dataset.defaultVat) || 0;
		const symbolInput = document.getElementById('currencySymbolHidden');
		const currencySymbol = (symbolInput && symbolInput.value) ? symbolInput.value : (form.dataset.currencySymbol || '€');
		
		const items = [];
		document.querySelectorAll('[data-item-row]').forEach(row => {
			const quantity = parseFloat(row.querySelector('input[name*="quantity"]').value) || 0;
			const unitPrice = parseFloat(row.querySelector('input[name*="unit_price"]').value) || 0;
			const discount = parseFloat(row.querySelector('input[name*="discount"]').value) || 0;
			const vatRate = parseFloat(row.querySelector('input[name*="vat_rate"]').value) || defaultVat;
			
			const subtotal = (quantity * unitPrice) - discount;
			items.push({ subtotal, vatRate });
		});

		let totalSubtotal = 0;
		let totalVat = 0;
		
		items.forEach(item => {
			totalSubtotal += item.subtotal;
			totalVat += item.subtotal * (item.vatRate / 100);
		});

		const grandTotal = totalSubtotal + totalVat;

		// Update display
		const formatMoney = (value) => {
			return currencySymbol + ' ' + value.toFixed(2).replace('.', ',');
		};

		document.getElementById('previewSubtotal').textContent = formatMoney(totalSubtotal);
		document.getElementById('previewVat').textContent = formatMoney(totalVat);
		document.getElementById('previewGrandTotal').textContent = formatMoney(grandTotal);
	}

	// Add item button handler
	const addItemButton = document.getElementById('addItemButton');
	if (addItemButton) {
		addItemButton.addEventListener('click', function() {
			const itemsBody = document.getElementById('itemsBody');
			const newIndex = itemsBody.children.length;
			addItemRow({}, newIndex);
			updateFormTotals();
		});
	}

	// Add listener to all numeric fields for totals update
	document.addEventListener('change', function(e) {
		if (e.target.closest('[data-item-row]') && e.target.dataset.field) {
			updateFormTotals();
		}
	});

	// ===== Product Picker =====
	const productPickerModal = new bootstrap.Modal(document.getElementById('productPickerModal'));
	const productPickerBody  = document.getElementById('productPickerBody');
	const productPickerStatus = document.getElementById('productPickerStatus');
	const productSearch      = document.getElementById('productSearch');
	const addProductButton   = document.getElementById('addProductButton');

	let allProducts = [];
	let productsLoaded = false;

	function renderProductRows(products) {
		if (products.length === 0) {
			productPickerBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No products found.</td></tr>';
			productPickerStatus.textContent = '';
			return;
		}
		productPickerStatus.textContent = products.length + ' product(s)';
		productPickerBody.innerHTML = products.map(function(p) {
			const ref         = (p.reference    || '').replace(/"/g, '&quot;');
			const name        = (p.name         || '').replace(/</g, '&lt;');
			const productUnit = (p.product_unit || '').replace(/</g, '&lt;');
			const price       = parseFloat(p.price) || 0;
			const pageUrl     = (p.page_url || '').replace(/"/g, '&quot;');
			const nameCell    = pageUrl
				? '<a href="' + pageUrl + '" target="_blank" rel="noopener">' + name + '</a>'
				: name;
			return '<tr>' +
				'<td><code>' + (p.reference || '') + '</code></td>' +
				'<td>' + nameCell + '</td>' +
				'<td>' + (p.product_unit || '') + '</td>' +
				'<td class="text-end">' + price.toFixed(2) + '</td>' +
				'<td class="text-end">' +
					'<button type="button" class="btn btn-sm btn-primary" ' +
						'data-ref="' + ref + '" ' +
						'data-name="' + (p.name || '').replace(/"/g, '&quot;') + '" ' +
						'data-unit="' + (p.product_unit || '').replace(/"/g, '&quot;') + '" ' +
						'data-price="' + price + '">' +
						'Add' +
					'</button>' +
				'</td>' +
			'</tr>';
		}).join('');
	}

	function loadProducts(query) {
		const url = 'api/products.php' + (query ? '?q=' + encodeURIComponent(query) : '');
		productPickerBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Loading…</td></tr>';
		productPickerStatus.textContent = '';
		fetch(url)
			.then(function(r) {
				if (!r.ok) throw new Error('HTTP ' + r.status);
				return r.json();
			})
			.then(function(data) {
				allProducts = data;
				productsLoaded = true;
				renderProductRows(data);
			})
			.catch(function(err) {
				productPickerBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">Could not load catalogue: ' + err.message + '</td></tr>';
			});
	}

	if (addProductButton) {
		addProductButton.addEventListener('click', function() {
			productPickerModal.show();
			if (!productsLoaded) {
				loadProducts('');
			}
		});
	}

	// Search with debounce
	let searchTimer;
	if (productSearch) {
		productSearch.addEventListener('input', function() {
			clearTimeout(searchTimer);
			const q = this.value.trim();
			searchTimer = setTimeout(function() {
				loadProducts(q);
			}, 300);
		});
	}

	// Add product to items table on click
	document.getElementById('productPickerBody').addEventListener('click', function(e) {
		const btn = e.target.closest('button[data-ref]');
		if (!btn) return;

		const itemsBody = document.getElementById('itemsBody');
		const newIndex  = itemsBody.children.length;
		addItemRow({
			reference:    btn.dataset.ref,
			description:  btn.dataset.name,
			product_unit: btn.dataset.unit,
			quantity:     1,
			unit_price:   parseFloat(btn.dataset.price) || 0,
		}, newIndex);
		updateFormTotals();
		productPickerModal.hide();
	});
});
</script>


<script>
(function () {
	const select      = document.getElementById('currencySelect');
	const symbolInput = document.getElementById('currencySymbolHidden');
	const fxBlock     = document.getElementById('fxRateBlock');
	const fxLabel     = document.getElementById('fxRateCurrencyLabel');
	const baseCurrency = <?= json_encode($companyCurrency) ?>;

	if (!select) return;

	function onCurrencyChange() {
		const opt    = select.options[select.selectedIndex];
		const code   = opt.value;
		const symbol = opt.dataset.symbol || code;
		symbolInput.value = symbol;
		if (fxLabel) fxLabel.textContent = code;

		const isForeign = code !== baseCurrency;
		if (fxBlock) fxBlock.style.display = isForeign ? '' : 'none';

		// Update the live preview symbol
		if (window._setCurrencySymbol) window._setCurrencySymbol(symbol);
		// Also trigger a totals refresh so the symbol updates live
		if (typeof updateFormTotals === 'function') updateFormTotals();
	}

	select.addEventListener('change', onCurrencyChange);
})();
</script>
</body>
</html>