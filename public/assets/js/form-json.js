/**
 * form-json.js — JSON import / export / validate / download
 */
(function () {
	'use strict';

	window.FormApp = window.FormApp || {};

	function gatherFormJson() {
		var form = document.getElementById('documentForm');
		var type = form.querySelector('input[name="type"]').value || 'invoice';

		var customerFields = ['name', 'company', 'department', 'street', 'city', 'zip', 'country', 'phone', 'email', 'vat_number'];
		var customer = {};
		customerFields.forEach(function (key) {
			var input = form.querySelector('input[name="customer[' + key + ']"]');
			customer[key] = input ? input.value.trim() : '';
		});

		var metaFields = ['number', 'issue_date', 'due_date', 'valid_until', 'reference', 'payment_method', 'payment_terms', 'currency', 'currency_symbol', 'vat_mention', 'fx_rate'];
		var meta = {};
		metaFields.forEach(function (key) {
			var input = form.querySelector('[name="meta[' + key + ']"]');
			meta[key] = input ? input.value.trim() : '';
		});

		var notes = {
			public:   (form.querySelector('textarea[name="notes[public]"]')   || {}).value || '',
			internal: (form.querySelector('textarea[name="notes[internal]"]') || {}).value || '',
		};

		var terms = (form.querySelector('textarea[name="terms"]') || {}).value || '';

		var acceptance = {
			enabled: Boolean(form.querySelector('input[name="acceptance[enabled]"]') && form.querySelector('input[name="acceptance[enabled]"]').checked),
			text:    (form.querySelector('textarea[name="acceptance[text]"]') || {}).value || '',
		};

		var items = [];
		document.querySelectorAll('[data-item-row]').forEach(function (row) {
			var item = {
				reference:    (row.querySelector('[data-field="reference"]')    || {}).textContent.trim(),
				name:  (row.querySelector('[data-field="name"]')  || {}).textContent.trim(),
				product_unit: (row.querySelector('[data-field="product_unit"]') || {}).textContent.trim(),
				quantity:     parseFloat((row.querySelector('[data-field="quantity"]')   || {}).textContent) || 0,
				unit:         (row.querySelector('[data-field="unit"]')       || {}).textContent.trim(),
				unit_price:   parseFloat((row.querySelector('[data-field="unit_price"]') || {}).textContent) || 0,
				discount:     parseFloat((row.querySelector('[data-field="discount"]')   || {}).textContent) || 0,
				vat_rate:     parseFloat((row.querySelector('[data-field="vat_rate"]')   || {}).textContent) || 0,
			};
			// Only include rows that have some data
			if (item.reference || item.name || item.product_unit || item.quantity || item.unit || item.unit_price || item.discount || item.vat_rate) {
				items.push(item);
			}
		});

		return { type: type, customer: customer, meta: meta, items: items, notes: notes, acceptance: acceptance, terms: terms };
	}

	function validateFormJson(data) {
		var errors = [];
		if (!data.type) {
			errors.push('Document type is missing.');
		}
		if (!data.customer.name)   errors.push('Customer name is required.');
		if (!data.customer.street) errors.push('Customer street is required.');
		if (!data.meta.number)     errors.push('Document number is required.');
		if (!data.meta.issue_date) errors.push('Issue date is required.');
		if (data.type === 'invoice' && !data.meta.due_date)    errors.push('Due date is required for invoices.');
		if (data.type === 'quote'   && !data.meta.valid_until) errors.push('Valid until is required for quotes.');
		if (!Array.isArray(data.items) || data.items.length === 0) {
			errors.push('At least one item is required.');
		} else {
			data.items.forEach(function (item, i) {
				if (!item.name) errors.push('Item ' + (i + 1) + ': name is required.');
			});
		}
		return { valid: errors.length === 0, errors: errors };
	}

	function downloadJson(content, filename) {
		var blob   = new Blob([content], { type: 'application/json' });
		var url    = URL.createObjectURL(blob);
		var anchor = document.createElement('a');
		anchor.href = url;
		anchor.download = filename;
		document.body.appendChild(anchor);
		anchor.click();
		anchor.remove();
		URL.revokeObjectURL(url);
	}

	function importFormData(data) {
		// Customer
		if (data.customer) {
			Object.keys(data.customer).forEach(function (key) {
				var input = document.querySelector('input[name="customer[' + key + ']"]');
				if (input) input.value = data.customer[key] || '';
			});
		}

		// Meta — currency fields are locked to company defaults, never overwritten on import
		if (data.meta) {
			var lockedFields = ['currency', 'currency_symbol'];
			Object.keys(data.meta).forEach(function (key) {
				if (lockedFields.includes(key)) return;
				var input = document.querySelector('[name="meta[' + key + ']"]');
				if (input) input.value = data.meta[key] || '';
			});
		}

		// Items
		if (data.items && Array.isArray(data.items)) {
			var itemsBody = document.getElementById('itemsBody');
			itemsBody.innerHTML = '';
			data.items.forEach(function (item, index) {
				if (window.FormApp.addItemRow) window.FormApp.addItemRow(item, index);
			});
		}

		// Notes
		if (data.notes) {
			var pubField = document.querySelector('textarea[name="notes[public]"]');
			var intField = document.querySelector('textarea[name="notes[internal]"]');
			if (pubField && data.notes.public)   pubField.value = data.notes.public;
			if (intField && data.notes.internal) intField.value = data.notes.internal;
		}

		// Terms
		if (data.terms) {
			var termsField = document.querySelector('textarea[name="terms"]');
			if (termsField) termsField.value = data.terms;
		}

		// Acceptance
		if (data.acceptance) {
			var chk = document.querySelector('input[name="acceptance[enabled]"]');
			if (chk && data.acceptance.enabled) chk.checked = true;
			var txt = document.querySelector('textarea[name="acceptance[text]"]');
			if (txt && data.acceptance.text) txt.value = data.acceptance.text;
		}

		// Reset file input so the same file can be re-imported
		var fileInput = document.getElementById('jsonImportInput');
		if (fileInput) fileInput.value = '';

		// Trigger fx rate block visibility after import
		var currSelect = document.getElementById('currencySelect');
		var fxBlock    = document.getElementById('fxRateBlock');
		var fxLabel    = document.getElementById('fxRateCurrencyLabel');
		var form       = document.getElementById('documentForm');
		var baseCurrency = form ? (form.dataset.baseCurrency || 'EUR') : 'EUR';
		if (currSelect && fxBlock) {
			var code = currSelect.value;
			if (fxLabel) fxLabel.textContent = code;
			fxBlock.style.display = (code && code !== baseCurrency) ? '' : 'none';
		}

		if (window.FormApp.updateFormTotals) window.FormApp.updateFormTotals();
	}

	// Autosave draft to localStorage
	function saveDraftToLocalStorage() {
		try {
			var data = gatherFormJson();
			localStorage.setItem('dg.document_form_state', JSON.stringify(data));
		} catch (err) {
			// ignore
		}
	}

	function restoreDraftFromLocalStorage() {
		try {
			var raw = localStorage.getItem('dg.document_form_state');
			if (!raw) return null;
			return JSON.parse(raw);
		} catch (err) {
			return null;
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		var importButton = document.getElementById('jsonImportButton');
		var importInput  = document.getElementById('jsonImportInput');
		var saveButton   = document.getElementById('jsonSaveButton');

		if (importButton && importInput) {
			importButton.addEventListener('click', function () { importInput.click(); });

			importInput.addEventListener('change', function (e) {
				var file = e.target.files[0];
				if (!file) return;
				var reader = new FileReader();
				reader.onload = function (event) {
					try {
						var data = JSON.parse(event.target.result);
						importFormData(data);
					} catch (err) {
						alert('Invalid JSON file: ' + err.message);
					}
				};
				reader.readAsText(file);
			});
		}

		if (saveButton) {
			saveButton.addEventListener('click', function () {
				var data       = gatherFormJson();
				var validation = validateFormJson(data);
				if (!validation.valid) {
					alert('Cannot save JSON:\n' + validation.errors.join('\n'));
					return;
				}
				try {
					var json = JSON.stringify(data, null, 2);
					JSON.parse(json); // sanity-check round-trip
					downloadJson(json, data.type + '.json');
				} catch (err) {
					alert('JSON generation failed: ' + err.message);
				}
			});
		}

		// Try restore draft when loading the form if there are no item names populated
		try {
			var draft = restoreDraftFromLocalStorage();
			if (draft) {
				var anyName = false;
				document.querySelectorAll('[data-item-row]').forEach(function (row) {
					if ((row.querySelector('[data-field="name"]') || {}).textContent.trim()) anyName = true;
				});
				if (!anyName) importFormData(draft);
			}
		} catch (err) {}

		// Clear draft when the form is submitted to generate the document
		var formEl = document.getElementById('documentForm');
		if (formEl) {
			formEl.addEventListener('submit', function () {
				localStorage.removeItem('dg.document_form_state');
			});
		}
	});

	// Expose for cross-module use
	window.FormApp.gatherFormJson   = gatherFormJson;
	window.FormApp.validateFormJson = validateFormJson;
	window.FormApp.downloadJson     = downloadJson;
	window.FormApp.importFormData   = importFormData;
	window.FormApp.saveDraft         = saveDraftToLocalStorage;
	window.FormApp.restoreDraft      = restoreDraftFromLocalStorage;
}());
