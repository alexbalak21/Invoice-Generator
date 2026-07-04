/**
 * form-items.js — item row add / remove / template cloning
 * Exposes: window.FormApp.addItemRow(item, index)
 */
(function () {
	'use strict';

	window.FormApp = window.FormApp || {};

	/**
	 * Clone the item row template, populate it with data, and append it to #itemsBody.
	 * @param {object} item   - Optional field values (reference, description, etc.)
	 * @param {number} index  - Row index used in input names (items[N][field])
	 */
	function addItemRow(item, index) {
		item = item || {};
		const template = document.getElementById('itemRowTemplate');
		if (!template) return;

		const row = template.content.cloneNode(true);

		// Replace placeholder index in all input names
		row.querySelectorAll('input').forEach(function (input) {
			input.name = input.name.replace('__INDEX__', index);
		});

		// Fill values where provided
		var fields = {
			'reference':    'reference',
			'description':  'description',
			'product_unit': 'product_unit',
			'quantity':     'quantity',
			'unit':         '[unit]',   // selector uses $= to avoid matching product_unit
			'discount':     'discount',
			'unit_price':   'unit_price',
			'vat_rate':     'vat_rate',
		};

		if (item.reference    !== undefined) row.querySelector('input[name*="reference"]').value     = item.reference;
		if (item.description  !== undefined) row.querySelector('input[name*="description"]').value   = item.description;
		if (item.product_unit !== undefined) row.querySelector('input[name*="product_unit"]').value  = item.product_unit;
		if (item.quantity     !== undefined) row.querySelector('input[name*="quantity"]').value      = item.quantity;
		if (item.unit         !== undefined) row.querySelector('input[name$="[unit]"]').value        = item.unit;
		if (item.discount     !== undefined) row.querySelector('input[name*="discount"]').value      = item.discount;
		if (item.unit_price   !== undefined) row.querySelector('input[name*="unit_price"]').value    = item.unit_price;
		if (item.vat_rate     !== undefined) row.querySelector('input[name*="vat_rate"]').value      = item.vat_rate;

		document.getElementById('itemsBody').appendChild(row);
	}

	// Delegated remove-row listener (covers both PHP-rendered and dynamic rows)
	document.addEventListener('DOMContentLoaded', function () {
		var itemsBody = document.getElementById('itemsBody');
		if (!itemsBody) return;

		itemsBody.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-remove-row]');
			if (!btn) return;
			btn.closest('[data-item-row]').remove();
			if (window.FormApp.updateFormTotals) window.FormApp.updateFormTotals();
		});

		var addItemButton = document.getElementById('addItemButton');
		if (addItemButton) {
			addItemButton.addEventListener('click', function () {
				var newIndex = itemsBody.children.length;
				addItemRow({}, newIndex);
				if (window.FormApp.updateFormTotals) window.FormApp.updateFormTotals();
			});
		}
	});

	window.FormApp.addItemRow = addItemRow;
}());
