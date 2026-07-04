/**
 * form-totals.js — live totals preview recalculation
 * Exposes: window.FormApp.updateFormTotals()
 *          window.FormApp._setCurrencySymbol(symbol)
 */
(function () {
	'use strict';

	window.FormApp = window.FormApp || {};

	function updateFormTotals() {
		var form = document.getElementById('documentForm');
		if (!form) return;

		var defaultVat    = parseFloat(form.dataset.defaultVat) || 0;
		var symbolInput   = document.getElementById('currencySymbolHidden');
		var currencySymbol = (symbolInput && symbolInput.value) ? symbolInput.value : (form.dataset.currencySymbol || '€');

		var totalSubtotal = 0;
		var totalVat      = 0;

		document.querySelectorAll('[data-item-row]').forEach(function (row) {
			var quantity  = parseFloat(row.querySelector('input[name*="quantity"]').value)   || 0;
			var unitPrice = parseFloat(row.querySelector('input[name*="unit_price"]').value) || 0;
			var discount  = parseFloat(row.querySelector('input[name*="discount"]').value)   || 0;
			var vatRate   = parseFloat(row.querySelector('input[name*="vat_rate"]').value)   || defaultVat;

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
		// Re-calculate whenever any item field changes
		document.addEventListener('change', function (e) {
			if (e.target.closest('[data-item-row]') && e.target.dataset.field) {
				updateFormTotals();
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
