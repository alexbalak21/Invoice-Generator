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
		'quantity' => 1,
		'unit' => 'pcs',
		'unit_price' => '',
		'vat_rate' => $company['default_vat_rate'] ?? 0,
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

$currency = $meta['currency'] ?: ($company['default_currency'] ?? 'EUR');
$currencySymbol = $meta['currency_symbol'] ?: ($company['default_currency_symbol'] ?? '€');
$defaultVatRate = (float) ($company['default_vat_rate'] ?? 0);
$totals = calculate_totals($defaultItems, $defaultVatRate);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= h(document_title($type)) ?> Form</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="../style.css">
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
								<label class="form-label">Currency</label>
								<input class="form-control" type="text" name="meta[currency]" value="<?= h($currency) ?>">
							</div>
							<div class="col-md-4">
								<label class="form-label">Currency symbol</label>
								<input class="form-control" type="text" name="meta[currency_symbol]" value="<?= h($currencySymbol) ?>">
							</div>
							<div class="col-md-4">
								<label class="form-label">Payment terms</label>
								<input class="form-control" type="text" name="meta[payment_terms]" value="<?= h($meta['payment_terms'] ?? '30 days') ?>">
							</div>
							<div class="col-md-12">
								<label class="form-label">VAT mention</label>
								<input class="form-control" type="text" name="meta[vat_mention]" value="<?= h($meta['vat_mention'] ?? '') ?>">
							</div>
						</div>
					</div>
				</div>

				<div class="card shadow-sm border-0 mb-4">
					<div class="card-body p-4">
						<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
							<h2 class="h5 mb-0">Items</h2>
							<button type="button" class="btn btn-outline-primary btn-sm" id="addItemButton">Add row</button>
						</div>

						<div class="table-responsive">
							<table class="table align-middle items-editor">
								<thead>
									<tr>
										<th>Reference</th>
										<th>Description</th>
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

<script>
(function () {
	const form = document.getElementById('documentForm');
	const body = document.getElementById('itemsBody');
	const template = document.getElementById('itemRowTemplate');
	const addButton = document.getElementById('addItemButton');
	const importButton = document.getElementById('jsonImportButton');
	const importInput = document.getElementById('jsonImportInput');
	const previewSubtotal = document.getElementById('previewSubtotal');
	const previewVat = document.getElementById('previewVat');
	const previewGrandTotal = document.getElementById('previewGrandTotal');
	const typeInput = form.querySelector('input[name="type"]');
	const invoiceField = form.querySelector('[data-invoice-field]');
	const quoteField = form.querySelector('[data-quote-field]');
	const currencySymbol = form.dataset.currencySymbol || '€';
	const defaultItemTemplate = {
		reference: '',
		description: '',
		quantity: 1,
		unit_price: 0,
		vat_rate: form.dataset.defaultVat || 0,
		unit: '',
		discount: 0,
	};

	function getFieldByName(name) {
		return Array.from(form.elements).find((element) => element.name === name) || null;
	}

	function setFieldValue(name, value) {
		const field = getFieldByName(name);
		if (!field) {
			return;
		}

		if (field.type === 'checkbox') {
			field.checked = Boolean(value);
			return;
		}

		field.value = value ?? '';
	}

	function applyDocumentType(nextType) {
		const resolvedType = nextType === 'quote' ? 'quote' : 'invoice';
		typeInput.value = resolvedType;

		if (invoiceField) {
			invoiceField.classList.toggle('d-none', resolvedType === 'quote');
		}

		if (quoteField) {
			quoteField.classList.toggle('d-none', resolvedType === 'invoice');
		}
	}

	function money(value) {
		return currencySymbol + ' ' + Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}

	function updateRowNames() {
		Array.from(body.querySelectorAll('tr[data-item-row]')).forEach((row, index) => {
			row.querySelectorAll('[name]').forEach((input) => {
				input.name = input.name.replace(/items\[(.*?)\]/, 'items[' + index + ']');
			});
		});
	}

	function addRow() {
		const index = body.querySelectorAll('tr[data-item-row]').length;
		const fragment = template.content.cloneNode(true);
		fragment.querySelectorAll('[name]').forEach((input) => {
			input.name = input.name.replace(/__INDEX__/g, index);
		});
		body.appendChild(fragment);
		updateRowNames();
		updateTotals();
	}

	function removeRow(button) {
		const row = button.closest('tr[data-item-row]');
		if (!row) {
			return;
		}

		if (body.querySelectorAll('tr[data-item-row]').length <= 1) {
			row.querySelectorAll('input').forEach((input) => {
				if (input.type === 'number') {
					input.value = input.name.includes('[quantity]') ? 1 : 0;
				} else {
					input.value = '';
				}
			});
			updateTotals();
			return;
		}

		row.remove();
		updateRowNames();
		updateTotals();
	}

	function syncItemRows(items) {
		const rows = Array.from(body.querySelectorAll('tr[data-item-row]'));
		const targetItems = Array.isArray(items) && items.length ? items : [defaultItemTemplate];

		while (rows.length < targetItems.length) {
			addRow();
			rows.push(body.querySelector('tr[data-item-row]:last-child'));
		}

		while (rows.length > targetItems.length && rows.length > 1) {
			const row = rows.pop();
			row.remove();
		}

		updateRowNames();

		Array.from(body.querySelectorAll('tr[data-item-row]')).forEach((row, index) => {
			const item = targetItems[index] || {};
			const referenceField = row.querySelector('[name$="[reference]"]');
			const descriptionField = row.querySelector('[name$="[description]"]');
			const quantityField = row.querySelector('[name$="[quantity]"]');
			const unitField = row.querySelector('[name$="[unit]"]');
			const unitPriceField = row.querySelector('[name$="[unit_price]"]');
			const discountField = row.querySelector('[name$="[discount]"]');
			const vatRateField = row.querySelector('[name$="[vat_rate]"]');

			if (referenceField) referenceField.value = item.reference ?? '';
			if (descriptionField) descriptionField.value = item.description ?? '';
			if (quantityField) quantityField.value = item.quantity ?? 1;
			if (unitField) unitField.value = item.unit ?? '';
			if (unitPriceField) unitPriceField.value = item.unit_price ?? 0;
			if (discountField) discountField.value = item.discount ?? 0;
			if (vatRateField) vatRateField.value = item.vat_rate ?? form.dataset.defaultVat ?? 0;
		});
	}

	function populateForm(documentData) {
		if (!documentData || typeof documentData !== 'object') {
			return;
		}

		applyDocumentType(documentData.type || typeInput.value);

		['customer', 'meta', 'notes', 'acceptance'].forEach((section) => {
			const sectionData = documentData[section];
			if (!sectionData || typeof sectionData !== 'object') {
				return;
			}

			Object.entries(sectionData).forEach(([key, value]) => {
				setFieldValue(section + '[' + key + ']', value);
			});
		});

		syncItemRows(documentData.items);
		updateTotals();
	}

	async function importJsonFile(file) {
		const text = await file.text();
		const documentData = JSON.parse(text);
		populateForm(documentData);
	}

	function updateTotals() {
		let subtotal = 0;
		let vat = 0;

		body.querySelectorAll('tr[data-item-row]').forEach((row) => {
			const quantity = parseFloat(row.querySelector('[data-field="quantity"]').value || '0');
			const unitPrice = parseFloat(row.querySelector('[data-field="unit_price"]').value || '0');
			const discount = parseFloat(row.querySelector('[data-field="discount"]').value || '0');
			const vatRate = parseFloat(row.querySelector('[data-field="vat_rate"]').value || '0');
			const lineTotal = Math.max(0, (quantity * unitPrice) - discount);

			subtotal += lineTotal;
			vat += lineTotal * (vatRate / 100);
		});

		previewSubtotal.textContent = money(subtotal);
		previewVat.textContent = money(vat);
		previewGrandTotal.textContent = money(subtotal + vat);
	}

	body.addEventListener('input', updateTotals);
	body.addEventListener('click', (event) => {
		if (event.target.matches('[data-remove-row]')) {
			event.preventDefault();
			removeRow(event.target);
		}
	});

	addButton.addEventListener('click', addRow);
	if (importButton && importInput) {
		importButton.addEventListener('click', () => importInput.click());
		importInput.addEventListener('change', async () => {
			const file = importInput.files && importInput.files[0];
			if (!file) {
				return;
			}

			try {
				await importJsonFile(file);
			} catch (error) {
				window.alert('Invalid JSON file. Please upload a formatted document export.');
			} finally {
				importInput.value = '';
			}
		});
	}
	updateTotals();
})();
</script>
</body>
</html>
