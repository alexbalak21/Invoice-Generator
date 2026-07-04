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
