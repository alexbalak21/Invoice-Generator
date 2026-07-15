<?php if (!empty($sections['bank_details']) && !empty($bank)): ?>
<div class="bank-details">
	<strong>Bank Details</strong>
	<table class="bank-info">
		<?php if (!empty($bank['beneficiary'])): ?>
		<tr>
			<td class="bank-label">Beneficiary:</td>
			<td><?= h($bank['beneficiary']) ?></td>
		</tr>
		<?php endif; ?>
		<?php if (!empty($bank['bank_name'])): ?>
		<tr>
			<td class="bank-label">Bank name:</td>
			<td><?= h($bank['bank_name']) ?></td>
		</tr>
		<?php endif; ?>
		<?php if (!empty($bank['bank_address'])): ?>
		<tr>
			<td class="bank-label">Bank address:</td>
			<td><?= h($bank['bank_address']) ?></td>
		</tr>
		<?php endif; ?>
		<?php if (!empty($bank['iban'])): ?>
		<tr>
			<td class="bank-label">IBAN:</td>
			<td><strong><?= h($bank['iban']) ?></strong></td>
		</tr>
		<?php endif; ?>
		<?php if (!empty($bank['bic'])): ?>
		<tr>
			<td class="bank-label">BIC:</td>
			<td><?= h($bank['bic']) ?></td>
		</tr>
		<?php endif; ?>
	</table>
</div>
<?php endif; ?>
