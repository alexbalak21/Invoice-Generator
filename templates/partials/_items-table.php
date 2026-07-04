<table class="items">
	<thead>
		<tr>
			<th class="reference">REFERENCE</th>
			<th class="name">NAME</th>
			<th class="product-unit">PRODUCT UNIT</th>
			<th class="qty">QTY</th>
			<th class="price">UNIT PRICE</th>
			<th class="amount">AMOUNT</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($items as $item): ?>
		<tr>
			<td class="reference"><?= h($item['reference'] ?? '') ?></td>
			<td class="name"><?= h($item['description'] ?? '') ?></td>
			<td class="product-unit"><?= h($item['product_unit'] ?? '') ?></td>
			<td class="center"><?= h($item['quantity'] ?? 0) ?><?php if (!empty($item['unit'])): ?> <?= h($item['unit']) ?><?php endif; ?></td>
			<td class="center"><?= money($item['unit_price'] ?? 0, $currencySymbol) ?></td>
			<td class="right" style="padding-right: 8px;"><?= money((($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0)) - ($item['discount'] ?? 0), $currencySymbol) ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
