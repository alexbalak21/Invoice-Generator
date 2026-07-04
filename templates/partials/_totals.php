<?php
// Resolve base currency symbol
$baseCurrencySymbol = '€';
if (!empty($fxBaseCurrency)) {
    $allCurrencies = require __DIR__ . '/../../config/currencies.php';
    if (isset($allCurrencies[$fxBaseCurrency]['symbol'])) {
        $baseCurrencySymbol = $allCurrencies[$fxBaseCurrency]['symbol'];
    }
}
$showFxColumns = $hasFx;
?>
<div class="totals">
	<table>
		<tr>
			<td>Subtotal (excl. VAT)</td>
			<?php if ($showFxColumns): ?>
			<td class="right base-total"><?= money($totals['subtotal'] ?? 0, $baseCurrencySymbol) ?></td>
			<td class="right fx-col"><?= money(($totals['subtotal'] ?? 0) * $fxRate, $currencySymbol) ?></td>
			<?php else: ?>
			<td class="right"><?= money($totals['subtotal'] ?? 0, $currencySymbol) ?></td>
			<?php endif; ?>
		</tr>
		<tr>
			<td>VAT</td>
			<?php if ($showFxColumns): ?>
			<td class="right base-total"><?= money($totals['vat'] ?? 0, $baseCurrencySymbol) ?></td>
			<td class="right fx-col"><?= money(($totals['vat'] ?? 0) * $fxRate, $currencySymbol) ?></td>
			<?php else: ?>
			<td class="right"><?= money($totals['vat'] ?? 0, $currencySymbol) ?></td>
			<?php endif; ?>
		</tr>
		<tr class="grand-total">
			<td>TOTAL <?= h($showFxColumns ? $currencyCode : $currencyCode) ?></td>
			<?php if ($showFxColumns): ?>
			<td class="right base-total"><?= money($totals['grand_total'] ?? 0, $baseCurrencySymbol) ?></td>
			<td class="right fx-col"><?= money(($totals['grand_total'] ?? 0) * $fxRate, $currencySymbol) ?></td>
			<?php else: ?>
			<td class="right"><?= money($totals['grand_total'] ?? 0, $currencySymbol) ?></td>
			<?php endif; ?>
		</tr>
		<?php if ($showFxColumns): ?>
		<tr class="fx-rate-row">
			<td colspan="3" class="fx-rate-note">
				Conversion rate: 1&nbsp;<?= h($fxBaseCurrency) ?>&nbsp;=&nbsp;<?= number_format($fxRate, 4) ?>&nbsp;<?= h($currencyCode) ?>
			</td>
		</tr>
		<?php endif; ?>
	</table>
</div>
