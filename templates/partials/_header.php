<header class="header">

	<div class="company">
		<?php if ($logoAvailable): ?>
			<img src="<?= h($logoUrl) ?>" class="logo" alt="Logo">
		<?php endif; ?>
		<h2><?= h($company['name'] ?? '') ?></h2>
		<p><?= h($company['street'] ?? '') ?></p>
		<p><?= h(trim(($company['zip'] ?? '') . ' ' . ($company['city'] ?? '') . (($company['country'] ?? '') !== '' ? ' - ' . ($company['country'] ?? '') : ''))) ?></p>
		<p><?= h($company['email'] ?? '') ?></p>
		<div class="company-legal">
			<?= h($company['legal_form'] ?? '') ?>
			<?php if (!empty($company['share_capital'])): ?>
				- Share capital: <?= h($company['share_capital']) ?>
			<?php endif; ?>
			<br>
			SIRET: <?= h($company['siret'] ?? '') ?><br>
			VAT: <?= h($company['vat_number'] ?? '') ?>
		</div>
	</div>

	<div class="invoice-title">
		<h1><?= h($title) ?></h1>
		<table class="invoice-info">
			<tr>
				<th><?= h($numberLabel) ?></th>
				<th><?= h($dateLabel) ?></th>
			</tr>
			<tr>
				<td><?= h($metadata['number'] ?? '') ?></td>
				<td><?= h($issueDate) ?></td>
			</tr>
		</table>
	</div>

</header>
