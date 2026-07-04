/**
 * form-totals.js — live totals preview recalculation
 * Exposes: window.FormApp.updateFormTotals()
 *          window.FormApp._setCurrencySymbol(symbol)
 */
(function () {
	'use strict';

	window.FormApp = window.FormApp || {};

	function parseCellNumber(row, field, fallback) {
		var cell = row.querySelector('[data-field="' + field + '"]');
		return parseFloat((cell && cell.textContent) || '') || fallback;
	}

	function updateFormTotals() {
		var form = document.getElementById('documentForm');
		if (!form) return;

		var defaultVat    = parseFloat(form.dataset.defaultVat) || 0;
		var symbolInput   = document.getElementById('currencySymbolHidden');
		var currencySymbol = (symbolInput && symbolInput.value) ? symbolInput.value : (form.dataset.currencySymbol || '€');

		var totalSubtotal = 0;
		var totalVat      = 0;

		document.querySelectorAll('[data-item-row]').forEach(function (row) {
			var quantity  = parseCellNumber(row, 'quantity', 0);
			var unitPrice = parseCellNumber(row, 'unit_price', 0);
			var discount  = parseCellNumber(row, 'discount', 0);
			var vatRate   = parseCellNumber(row, 'vat_rate', defaultVat);

			var subtotal = (quantity * unitPrice) - discount;
			totalSubtotal += subtotal;
			totalVat      += subtotal * (vatRate / 100);
		});

		var grandTotal = totalSubtotal + totalVat;

		function fmt(value) {
			return currencySymbol + ' ' + value.toFixed(2).replace('.', ',');
		}

		var elSubtotal   = document.getElementById('previewSubtotal');
		var elVat        = document.getElementById('previewVat');
		var elGrandTotal = document.getElementById('previewGrandTotal');

		if (elSubtotal)   elSubtotal.textContent   = fmt(totalSubtotal);
		if (elVat)        elVat.textContent         = fmt(totalVat);
		if (elGrandTotal) elGrandTotal.textContent  = fmt(grandTotal);
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.addEventListener('input', function (e) {
			var target = e.target.closest('[data-field]');
			if (!target) return;
			if (window.FormApp.syncRowHiddenInputs) {
				var row = target.closest('[data-item-row]');
				if (row) window.FormApp.syncRowHiddenInputs(row);
			}
			updateFormTotals();
		});

		document.addEventListener('focusout', function (e) {
			var target = e.target.closest('[data-field]');
			if (!target) return;
			if (window.FormApp.syncRowHiddenInputs) {
				var row = target.closest('[data-item-row]');
				if (row) window.FormApp.syncRowHiddenInputs(row);
			}
		});
	});

	window.FormApp.updateFormTotals    = updateFormTotals;
	window.FormApp._setCurrencySymbol  = function (symbol) {
		var symbolInput = document.getElementById('currencySymbolHidden');
		if (symbolInput) symbolInput.value = symbol;
		updateFormTotals();
	};
}());
