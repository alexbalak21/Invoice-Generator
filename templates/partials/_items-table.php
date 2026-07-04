<?php
// When the invoice currency differs from the base (€), show dual columns:
// base-currency price  +  converted invoice-currency price
$showFxColumns = $hasFx;
$baseCurrencySymbol = '€';  // always the company base symbol
// Resolve company base symbol from config if needed
if (!empty($fxBaseCurrency)) {
    $allCurrencies = require __DIR__ . '/../../config/currencies.php';
    if (isset($allCurrencies[$fxBaseCurrency]['symbol'])) {
        $baseCurrencySymbol = $allCurrencies[$fxBaseCurrency]['symbol'];
    }
}
?>
<table class="items<?= $showFxColumns ? ' items--dual-currency' : '' ?>">
	<thead>
		<tr>
			<th class="reference">REFERENCE</th>
			<th class="name">NAME</th>
			<th class="product-unit">PRODUCT UNIT</th>
			<th class="qty">QTY</th>
			<?php if ($showFxColumns): ?>
			<th class="price"><?= h($fxBaseCurrency) ?> UNIT PRICE</th>
			<th class="amount"><?= h($fxBaseCurrency) ?> AMOUNT</th>
			<th class="amount fx-col"><?= h($currencyCode) ?> UNIT PRICE</th>
			<th class="amount fx-col"><?= h($currencyCode) ?> AMOUNT</th>
			<?php else: ?>
			<th class="price">UNIT PRICE</th>
			<th class="amount">AMOUNT</th>
			<?php endif; ?>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($items as $item): ?>
		<?php
			$unitPrice  = $item['unit_price'] ?? 0;
			$qty        = $item['quantity'] ?? 0;
			$discount   = $item['discount'] ?? 0;
			$lineTotal  = max(0, ($qty * $unitPrice) - $discount);
			// Converted values: base price × fx_rate
			$unitPriceFx = $unitPrice * $fxRate;
			$lineTotalFx = $lineTotal * $fxRate;
		?>
		<tr>
			<td class="reference"><?= h($item['reference'] ?? '') ?></td>
			<td class="name"><?= h($item['description'] ?? '') ?></td>
			<td class="product-unit"><?= h($item['product_unit'] ?? '') ?></td>
			<td class="center"><?= h($qty) ?><?php if (!empty($item['unit'])): ?> <?= h($item['unit']) ?><?php endif; ?></td>
			<?php if ($showFxColumns): ?>
			<td class="center"><?= money($unitPrice, $baseCurrencySymbol) ?></td>
			<td class="right"><?= money($lineTotal, $baseCurrencySymbol) ?></td>
			<td class="center fx-col"><?= money($unitPriceFx, $currencySymbol) ?></td>
			<td class="right fx-col" style="padding-right: 8px;"><?= money($lineTotalFx, $currencySymbol) ?></td>
			<?php else: ?>
			<td class="center"><?= money($unitPrice, $currencySymbol) ?></td>
			<td class="right" style="padding-right: 8px;"><?= money($lineTotal, $currencySymbol) ?></td>
			<?php endif; ?>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
