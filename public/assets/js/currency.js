/**
 * currency.js — invoice currency select / FX block toggle
 */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var select      = document.getElementById('currencySelect');
		var symbolInput = document.getElementById('currencySymbolHidden');
		var fxBlock     = document.getElementById('fxRateBlock');
		var fxLabel     = document.getElementById('fxRateCurrencyLabel');

		if (!select) return;

		var form         = document.getElementById('documentForm');
		var baseCurrency = form ? (form.dataset.baseCurrency || 'EUR') : 'EUR';

		function onCurrencyChange() {
			var opt    = select.options[select.selectedIndex];
			var code   = opt.value;
			var symbol = opt.dataset.symbol || code;

			// Update the hidden currency_symbol input
			if (symbolInput) symbolInput.value = symbol;
			if (fxLabel)     fxLabel.textContent = code;

			// Show/hide FX rate block
			var isForeign = code !== baseCurrency;
			if (fxBlock) fxBlock.style.display = isForeign ? '' : 'none';

			// Recalculate totals preview (sidebar)
			if (window.FormApp && window.FormApp.updateFormTotals) {
				window.FormApp.updateFormTotals();
			}
		}

		select.addEventListener('change', onCurrencyChange);
	});
}());
