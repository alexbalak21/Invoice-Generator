<div class="col-12 col-xl-4">
	<div class="card shadow-sm border-0 sticky-top preview-card" style="top: 1.5rem;">
		<div class="card-body p-4">
			<h2 class="h5 mb-3">Preview totals</h2>
			<div class="totals-preview">
				<div class="d-flex justify-content-between"><span>Subtotal</span><strong id="previewSubtotal"><?= h(format_money($totals['subtotal'], $currencySymbol)) ?></strong></div>
				<div class="d-flex justify-content-between"><span>VAT</span><strong id="previewVat"><?= h(format_money($totals['vat'], $currencySymbol)) ?></strong></div>
				<hr>
				<div class="d-flex justify-content-between totals-preview-grand"><span>Total (<?= h($companyCurrency) ?>)</span><strong id="previewGrandTotal"><?= h(format_money($totals['grand_total'], $currencySymbol)) ?></strong></div>
				<div class="d-flex justify-content-between mt-1 small" id="previewFxRow"
					style="<?= ($meta['currency'] ?? $currency) === $companyCurrency || empty($meta['fx_rate']) ? 'display:none' : '' ?>">
					<span class="text-muted">≈ <?= h($meta['currency'] ?? '') ?> equivalent</span>
					<strong id="previewFxTotal" style="color:#4a3d8f;">
					<?php
						$pfxRate = (float)($meta['fx_rate'] ?? 0);
						$pfxSym  = $meta['currency_symbol'] ?? '';
						echo ($pfxRate > 0 && !empty($pfxSym))
							? h($pfxSym . ' ' . number_format(($totals['grand_total'] ?? 0) * $pfxRate, 2))
							: '';
					?>
					</strong>
				</div>
			</div>
			<p class="text-muted small mt-3 mb-0">The PHP renderer remains the final calculation source. This preview is only a convenience.</p>
			<button type="submit" class="btn btn-primary btn-lg w-100 mt-4">Generate Document</button>
		</div>
	</div>
</div>
