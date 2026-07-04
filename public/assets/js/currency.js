/**
 * currency.js — currency select / FX block toggle
 * Reads window.FormApp.baseCurrency (set inline by the PHP view via a data attribute).
 */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var select      = document.getElementById('currencySelect');
		var symbolInput = document.getElementById('currencySymbolHidden');
		var fxBlock     = document.getElementById('fxRateBlock');
		var fxLabel     = document.getElementById('fxRateCurrencyLabel');

		if (!select) return;

		// Base (accounting) currency is written into the form element as a data attribute
		var form         = document.getElementById('documentForm');
		var baseCurrency = form ? (form.dataset.baseCurrency || 'EUR') : 'EUR';

		function onCurrencyChange() {
			var opt    = select.options[select.selectedIndex];
			var code   = opt.value;
			var symbol = opt.dataset.symbol || code;

			if (symbolInput) symbolInput.value = symbol;
			if (fxLabel)     fxLabel.textContent = code;

			var isForeign = code !== baseCurrency;
			if (fxBlock) fxBlock.style.display = isForeign ? '' : 'none';

			// Update live preview symbol and recalculate
			if (window.FormApp && window.FormApp._setCurrencySymbol) {
				window.FormApp._setCurrencySymbol(symbol);
			} else if (window.FormApp && window.FormApp.updateFormTotals) {
				window.FormApp.updateFormTotals();
			}
		}

		select.addEventListener('change', onCurrencyChange);
	});
}());
