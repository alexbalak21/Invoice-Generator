<div class="card shadow-sm border-0 mb-4">
	<div class="card-body p-4">
		<h2 class="h5 mb-4">Document info</h2>
		<div class="row g-3">
			<div class="col-md-6">
				<label class="form-label">Document number</label>
				<input class="form-control" type="text" name="meta[number]" value="<?= h($meta['number'] ?? '') ?>" required>
			</div>
			<div class="col-md-6">
				<label class="form-label">Issue date</label>
				<input class="form-control" type="date" name="meta[issue_date]" value="<?= h($meta['issue_date'] ?? $today) ?>" required>
			</div>

			<?php if ($sections['due_date']): ?>
			<div class="col-md-6">
				<label class="form-label">Due date</label>
				<input class="form-control" type="date" name="meta[due_date]" value="<?= h($meta['due_date'] ?? $defaultDueDate) ?>">
			</div>
			<?php endif; ?>

			<?php if ($sections['valid_until']): ?>
			<div class="col-md-6">
				<label class="form-label">Valid until</label>
				<input class="form-control" type="date" name="meta[valid_until]" value="<?= h($meta['valid_until'] ?? $defaultValidUntil) ?>">
			</div>
			<?php endif; ?>

			<div class="col-md-6">
				<label class="form-label">Reference</label>
				<input class="form-control" type="text" name="meta[reference]" value="<?= h($meta['reference'] ?? '') ?>">
			</div>

			<?php if ($sections['payment_terms']): ?>
			<div class="col-md-6">
				<label class="form-label">Payment method</label>
				<input class="form-control" type="text" name="meta[payment_method]" value="<?= h($meta['payment_method'] ?? '') ?>">
			</div>
			<?php endif; ?>

			<div class="col-md-4">
				<label class="form-label">Invoice currency</label>
				<select class="form-select" name="meta[currency]" id="currencySelect">
				<?php foreach ($currencies as $code => $cur): ?>
					<option value="<?= h($code) ?>"
						data-symbol="<?= h($cur['symbol']) ?>"
						<?= ($meta['currency'] ?? $currency) === $code ? 'selected' : '' ?>>
						<?= h($code) ?> — <?= h($cur['name']) ?>
					</option>
				<?php endforeach; ?>
				</select>
				<input type="hidden" name="meta[currency_symbol]" id="currencySymbolHidden" value="<?= h($meta['currency_symbol'] ?? $currencySymbol) ?>">
				<div class="form-text">
					Base prices are always in <?= h($companyCurrency) ?>.
					<?php if (($meta['currency'] ?? $currency) !== $companyCurrency): ?>
						Converted to <?= h($meta['currency'] ?? $currency) ?> on the document using the rate below.
					<?php endif; ?>
				</div>
			</div>
			<div class="col-md-4" id="fxRateBlock" style="<?= ($meta['currency'] ?? $currency) === $companyCurrency ? 'display:none' : '' ?>">
				<label class="form-label">
					Conversion rate
					<span class="text-muted small" id="fxRateLabel">(1 <?= h($companyCurrency) ?> = ? <span id="fxRateCurrencyLabel"><?= h($meta['currency'] ?? $currency) ?></span>)</span>
				</label>
				<input class="form-control" type="number" step="0.000001" min="0.000001"
					name="meta[fx_rate]" id="fxRateInput"
					value="<?= h($meta['fx_rate'] ?? '') ?>"
					placeholder="e.g. 108.89">
				<div class="form-text">Base <?= h($companyCurrency) ?> prices × this rate = invoice currency amounts.</div>
			</div>
			<div class="col-md-4">
				<label class="form-label">Payment terms</label>
				<input class="form-control" type="text" name="meta[payment_terms]" value="<?= h($meta['payment_terms'] ?? '30 days') ?>">
			</div>
			<div class="col-md-12">
				<label class="form-label">VAT mention</label>
				<input class="form-control" type="text" name="meta[vat_mention]" value="<?= h($meta['vat_mention'] ?? '') ?>">
			</div>

			<?php if ($sections['payment_terms']): ?>
			<div class="col-12">
				<label class="form-check-label">
					<input class="form-check-input me-2" type="checkbox" name="legal[show_late_payment]" value="1" <?= (!isset($state['legal']['show_late_payment']) || !empty($state['legal']['show_late_payment'])) ? 'checked' : '' ?>>
					Include late payment penalty &amp; recovery fee mention
				</label>
				<div class="form-text">"In the event of late payment, penalties will apply at a rate of 4.50% per year. A fixed recovery fee of 40.00 € may also apply." Untick to leave this out of the document.</div>
			</div>
			<?php endif; ?>

		</div>
	</div>
</div>
