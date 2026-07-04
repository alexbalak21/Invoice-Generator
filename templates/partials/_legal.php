<?php if (!empty($legal['vat_mention'])): ?>
<div class="vat-mention">
	<?= h($legal['vat_mention']) ?>
</div>
<?php endif; ?>

<?php if ($isQuote): ?>
	<div class="payment-terms-block">
		<strong>Quote valid until:</strong> <?= h($secondaryDate) ?>.<br>
		<?php if (!empty($acceptance['text'])): ?>
			<?= h($acceptance['text']) ?>
		<?php else: ?>
			Please confirm your approval before work starts.
		<?php endif; ?>
	</div>
<?php else: ?>
	<div class="payment-terms-block">
		<strong>Payment terms:</strong>
		Due date: <?= h($secondaryDate) ?><?= !empty($paymentMethod) ? ' — ' . h($paymentMethod) . '.' : '.' ?><br>
		<?php if ($showLatePayment): ?>
			<?php if ($latePaymentRate > 0): ?>
				In the event of late payment, penalties will apply at a rate of
				<strong><?= number_format($latePaymentRate, 2) ?>%</strong> per year.
			<?php endif; ?>
			<?php if ($latePaymentFee > 0): ?>
				A fixed recovery fee of <strong><?= h(number_format($latePaymentFee, 2)) ?> <?= h($currencySymbol) ?></strong> may also apply.
			<?php endif; ?>
		<?php endif; ?>
	</div>
<?php endif; ?>
