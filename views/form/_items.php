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
							<td><div class="editable-cell" contenteditable="true" data-field="reference"><?= h($item['reference'] ?? '') ?></div><input type="hidden" name="items[<?= h($index) ?>][reference]" data-hidden-field="reference" value="<?= h($item['reference'] ?? '') ?>"></td>
							<td><div class="editable-cell" contenteditable="true" data-field="name"><?= h($item['name'] ?? '') ?></div><input type="hidden" name="items[<?= h($index) ?>][name]" data-hidden-field="description" value="<?= h($item['name'] ?? '') ?>"></td>
							<td><div class="editable-cell" contenteditable="true" data-field="product_unit"><?= h($item['product_unit'] ?? '') ?></div><input type="hidden" name="items[<?= h($index) ?>][product_unit]" data-hidden-field="product_unit" value="<?= h($item['product_unit'] ?? '') ?>"></td>
							<td class="text-end"><div class="editable-cell" contenteditable="true" data-field="quantity"><?= h($item['quantity'] ?? 1) ?></div><input type="hidden" name="items[<?= h($index) ?>][quantity]" data-hidden-field="quantity" value="<?= h($item['quantity'] ?? 1) ?>"></td>
							<td><div class="editable-cell" contenteditable="true" data-field="unit"><?= h($item['unit'] ?? '') ?></div><input type="hidden" name="items[<?= h($index) ?>][unit]" data-hidden-field="unit" value="<?= h($item['unit'] ?? '') ?>"></td>
							<td class="text-end"><div class="editable-cell" contenteditable="true" data-field="discount"><?= h($item['discount'] ?? 0) ?></div><input type="hidden" name="items[<?= h($index) ?>][discount]" data-hidden-field="discount" value="<?= h($item['discount'] ?? 0) ?>"></td>
							<td class="text-end"><div class="editable-cell" contenteditable="true" data-field="unit_price"><?= h($item['unit_price'] ?? '') ?></div><input type="hidden" name="items[<?= h($index) ?>][unit_price]" data-hidden-field="unit_price" value="<?= h($item['unit_price'] ?? '') ?>"></td>
							<td class="text-end"><div class="editable-cell" contenteditable="true" data-field="vat_rate"><?= h($item['vat_rate'] ?? $defaultVatRate) ?></div><input type="hidden" name="items[<?= h($index) ?>][vat_rate]" data-hidden-field="vat_rate" value="<?= h($item['vat_rate'] ?? $defaultVatRate) ?>"></td>
							<td class="text-end"><button type="button" class="btn btn-link text-danger p-0" data-remove-row>Remove</button></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<template id="itemRowTemplate">
			<tr data-item-row>
				<td><div class="editable-cell" contenteditable="true" data-field="reference"></div><input type="hidden" name="items[__INDEX__][reference]" data-hidden-field="reference" value=""></td>
				<td><div class="editable-cell" contenteditable="true" data-field="name"></div><input type="hidden" name="items[__INDEX__][name]" data-hidden-field="description" value=""></td>
				<td><div class="editable-cell" contenteditable="true" data-field="product_unit"></div><input type="hidden" name="items[__INDEX__][product_unit]" data-hidden-field="product_unit" value=""></td>
				<td class="text-end"><div class="editable-cell" contenteditable="true" data-field="quantity">1</div><input type="hidden" name="items[__INDEX__][quantity]" data-hidden-field="quantity" value="1"></td>
				<td><div class="editable-cell" contenteditable="true" data-field="unit"></div><input type="hidden" name="items[__INDEX__][unit]" data-hidden-field="unit" value=""></td>
				<td class="text-end"><div class="editable-cell" contenteditable="true" data-field="discount">0</div><input type="hidden" name="items[__INDEX__][discount]" data-hidden-field="discount" value="0"></td>
				<td class="text-end"><div class="editable-cell" contenteditable="true" data-field="unit_price">0</div><input type="hidden" name="items[__INDEX__][unit_price]" data-hidden-field="unit_price" value="0"></td>
				<td class="text-end"><div class="editable-cell" contenteditable="true" data-field="vat_rate"><?= h($defaultVatRate) ?></div><input type="hidden" name="items[__INDEX__][vat_rate]" data-hidden-field="vat_rate" value="<?= h($defaultVatRate) ?>"></td>
				<td class="text-end"><button type="button" class="btn btn-link text-danger p-0" data-remove-row>Remove</button></td>
			</tr>
		</template>
	</div>
</div>
