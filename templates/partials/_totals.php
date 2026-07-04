<div class="totals">
	<table>
		<tr>
			<td>Subtotal (excl. VAT)</td>
			<td class="right"><?= money($totals['subtotal'] ?? 0, $currencySymbol) ?></td>
		</tr>
		<tr>
			<td>VAT</td>
			<td class="right"><?= money($totals['vat'] ?? 0, $currencySymbol) ?></td>
		</tr>
		<tr class="grand-total">
			<td>TOTAL <?= h($currencyCode) ?></td>
			<td class="right"><?= money($totals['grand_total'] ?? 0, $currencySymbol) ?></td>
		</tr>
		<?php if ($hasFx): ?>
		<tr class="fx-equivalent">
			<td class="fx-note">
				Equivalent in <?= h($fxBaseCurrency) ?>
				<span class="fx-rate-tag">(rate&nbsp;1&nbsp;<?= h($fxBaseCurrency) ?>&nbsp;=&nbsp;<?= number_format($fxRate, 6) ?>&nbsp;<?= h($currencyCode) ?>)</span>
			</td>
			<td class="right fx-note">
				≈&nbsp;<?= h($fxBaseCurrency) ?>&nbsp;<?= number_format(($totals['grand_total'] ?? 0) / $fxRate, 2) ?>
			</td>
		</tr>
		<?php endif; ?>
	</table>
</div>
