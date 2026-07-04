/**
 * form-items.js — item row add / remove / template cloning
 * Exposes: window.FormApp.addItemRow(item, index)
 */
(function () {
	'use strict';

	window.FormApp = window.FormApp || {};

	function setRowFieldValue(row, field, value) {
		var cell = row.querySelector('[data-field="' + field + '"]');
		if (!cell) return;
		cell.textContent = value !== undefined && value !== null ? value : '';
		var hidden = row.querySelector('input[data-hidden-field="' + field + '"]');
		if (hidden) hidden.value = cell.textContent.trim();
	}

	function syncRowHiddenInputs(row) {
		row.querySelectorAll('[data-field]').forEach(function (cell) {
			var field = cell.dataset.field;
			var hidden = row.querySelector('input[data-hidden-field="' + field + '"]');
			if (hidden) hidden.value = cell.textContent.trim();
		});
	}

	/**
	 * Clone the item row template, populate it with data, and append it to #itemsBody.
	 * @param {object} item   - Optional field values (reference, description, etc.)
	 * @param {number} index  - Row index used in input names (items[N][field])
	 */
	function addItemRow(item, index) {
		item = item || {};
		const template = document.getElementById('itemRowTemplate');
		if (!template) return;

		const fragment = template.content.cloneNode(true);
		const row = fragment.querySelector('[data-item-row]');
		if (!row) return;

		row.querySelectorAll('input[type="hidden"]').forEach(function (input) {
			input.name = input.name.replace('__INDEX__', index);
		});

		setRowFieldValue(row, 'reference', item.reference || '');
		setRowFieldValue(row, 'description', item.description || '');
		setRowFieldValue(row, 'product_unit', item.product_unit || '');
		setRowFieldValue(row, 'quantity', item.quantity !== undefined ? item.quantity : 1);
		setRowFieldValue(row, 'unit', item.unit || '');
		setRowFieldValue(row, 'discount', item.discount !== undefined ? item.discount : 0);
		setRowFieldValue(row, 'unit_price', item.unit_price !== undefined ? item.unit_price : 0);
		setRowFieldValue(row, 'vat_rate', item.vat_rate !== undefined ? item.vat_rate : '');

		document.getElementById('itemsBody').appendChild(fragment);
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
	window.FormApp.syncRowHiddenInputs = syncRowHiddenInputs;
}());
