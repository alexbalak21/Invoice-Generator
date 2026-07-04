/**
 * product-picker.js — product catalogue modal, API fetch, search debounce, add-to-table
 */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var modalEl          = document.getElementById('productPickerModal');
		var pickerBody       = document.getElementById('productPickerBody');
		var pickerStatus     = document.getElementById('productPickerStatus');
		var searchInput      = document.getElementById('productSearch');
		var addProductButton = document.getElementById('addProductButton');

		if (!modalEl) return;

		var productPickerModal = new bootstrap.Modal(modalEl);
		var productsLoaded     = false;
		var searchTimer;

		function renderProductRows(products) {
			if (products.length === 0) {
				pickerBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No products found.</td></tr>';
				if (pickerStatus) pickerStatus.textContent = '';
				return;
			}

			if (pickerStatus) pickerStatus.textContent = products.length + ' product(s)';

			pickerBody.innerHTML = products.map(function (p) {
				var ref         = (p.reference    || '').replace(/"/g, '&quot;');
				var name        = (p.name         || '').replace(/</g, '&lt;');
				var productUnit = (p.product_unit || '').replace(/</g, '&lt;');
				var price       = parseFloat(p.price) || 0;
				var pageUrl     = (p.page_url     || '').replace(/"/g, '&quot;');
				var nameCell    = pageUrl
					? '<a href="' + pageUrl + '" target="_blank" rel="noopener">' + name + '</a>'
					: name;

				return '<tr>' +
					'<td><code>' + (p.reference || '') + '</code></td>' +
					'<td>' + nameCell + '</td>' +
					'<td>' + productUnit + '</td>' +
					'<td class="text-end">' + price.toFixed(2) + '</td>' +
					'<td class="text-end">' +
						'<button type="button" class="btn btn-sm btn-primary"' +
							' data-ref="' + ref + '"' +
							' data-name="' + (p.name || '').replace(/"/g, '&quot;') + '"' +
							' data-unit="' + (p.product_unit || '').replace(/"/g, '&quot;') + '"' +
							' data-price="' + price + '">' +
							'Add' +
						'</button>' +
					'</td>' +
				'</tr>';
			}).join('');
		}

		function loadProducts(query) {
			var url = 'api/products.php' + (query ? '?q=' + encodeURIComponent(query) : '');
			pickerBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Loading…</td></tr>';
			if (pickerStatus) pickerStatus.textContent = '';

			fetch(url)
				.then(function (r) {
					if (!r.ok) throw new Error('HTTP ' + r.status);
					return r.json();
				})
				.then(function (data) {
					productsLoaded = true;
					renderProductRows(data);
				})
				.catch(function (err) {
					pickerBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">Could not load catalogue: ' + err.message + '</td></tr>';
				});
		}

		if (addProductButton) {
			addProductButton.addEventListener('click', function () {
				productPickerModal.show();
				if (!productsLoaded) loadProducts('');
			});
		}

		if (searchInput) {
			searchInput.addEventListener('input', function () {
				clearTimeout(searchTimer);
				var q = this.value.trim();
				searchTimer = setTimeout(function () { loadProducts(q); }, 300);
			});
		}

		// Add product to items table on click
		if (pickerBody) {
			pickerBody.addEventListener('click', function (e) {
				var btn = e.target.closest('button[data-ref]');
				if (!btn) return;

				var itemsBody = document.getElementById('itemsBody');
				var newIndex  = itemsBody ? itemsBody.children.length : 0;

				if (window.FormApp && window.FormApp.addItemRow) {
					window.FormApp.addItemRow({
						reference:    btn.dataset.ref,
						name:  btn.dataset.name,
						product_unit: btn.dataset.unit,
						quantity:     1,
						unit_price:   parseFloat(btn.dataset.price) || 0,
					}, newIndex);
				}

				if (window.FormApp && window.FormApp.updateFormTotals) {
					window.FormApp.updateFormTotals();
				}

				productPickerModal.hide();
			});
		}
	});
}());
