<?php if (!empty($notes['public'])): ?>
<div class="notes-block">
	<strong>Notes:</strong> <?= h($notes['public']) ?>
</div>
<?php endif; ?>

<?php if (!empty($bank)): ?>
<div class="bank-details">
	<strong>Bank Details</strong>
	<table class="bank-info">
		<tr>
			<td class="bank-label">Beneficiary:</td>
			<td><?= h($bank['beneficiary'] ?? '') ?></td>
		</tr>
		<tr>
			<td class="bank-label">Bank name:</td>
			<td><?= h($bank['bank_name'] ?? '') ?></td>
		</tr>
		<tr>
			<td class="bank-label">Bank address:</td>
			<td><?= h($bank['bank_address'] ?? '') ?></td>
		</tr>
		<tr>
			<td class="bank-label">IBAN:</td>
			<td><strong><?= h($bank['iban'] ?? '') ?></strong></td>
		</tr>
		<tr>
			<td class="bank-label">BIC:</td>
			<td><?= h($bank['bic'] ?? '') ?></td>
		</tr>
	</table>
</div>
<?php endif; ?>

<?php if (!empty($terms)): ?>
<div class="terms-block">
	<strong>Terms &amp; Conditions</strong>
	<div class="terms-content"><?= h($terms) ?></div>
</div>
<?php endif; ?>
