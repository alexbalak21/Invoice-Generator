<div class="meta-grid">
	<?php if (!empty($reference)): ?>
	<div class="meta-row">
		<span class="meta-label">Reference:</span>
		<span><?= h($reference) ?></span>
	</div>
	<?php endif; ?>
	<?php if (!empty($secondaryDate) && ($sections['due_date'] || $sections['valid_until'])): ?>
	<div class="meta-row">
		<span class="meta-label"><?= h($dueLabel) ?>:</span>
		<span><?= h($secondaryDate) ?></span>
	</div>
	<?php endif; ?>
	<?php if (!empty($paymentMethod)): ?>
	<div class="meta-row">
		<span class="meta-label">Payment method:</span>
		<span><?= h($paymentMethod) ?></span>
	</div>
	<?php endif; ?>
	<?php if (!$sections['acceptance'] && !empty($paymentTerms)): ?>
	<div class="meta-row">
		<span class="meta-label">Payment terms:</span>
		<span><?= h($paymentTerms) ?></span>
	</div>
	<?php endif; ?>
	<div class="meta-row">
		<span class="meta-label">Currency:</span>
		<span>
			<?= h($currencyCode) ?>
			<?php if ($hasFx): ?>
				<span class="fx-rate-meta">(1&nbsp;<?= h($fxBaseCurrency) ?>&nbsp;=&nbsp;<?= number_format($fxRate, 4) ?>&nbsp;<?= h($currencyCode) ?>)</span>
			<?php endif; ?>
		</span>
	</div>
</div>

<section class="bill-to">
	<div class="section-title">BILL TO</div>
	<div class="customer">
		<strong><?= h($customer['name'] ?? '') ?></strong><br>
		<?php if (!empty($customer['company'])): ?>
			<?= h($customer['company']) ?><br>
		<?php endif; ?>
		<?php if (!empty($customer['department'])): ?>
			<?= h($customer['department']) ?><br>
		<?php endif; ?>
		<?= h($customer['street'] ?? '') ?><br>
		<?= h(trim(($customer['zip'] ?? '') . ' ' . ($customer['city'] ?? '') . (($customer['country'] ?? '') !== '' ? ' — ' . ($customer['country'] ?? '') : ''))) ?><br>
		<?php if (!empty($customer['phone'])): ?>
			<?= h($customer['phone']) ?><br>
		<?php endif; ?>
		<?= h($customer['email'] ?? '') ?>
		<?php if (!empty($customer['vat_number'])): ?>
			<div class="customer-vat">VAT / GST No.: <?= h($customer['vat_number']) ?></div>
		<?php endif; ?>
	</div>
</section>
