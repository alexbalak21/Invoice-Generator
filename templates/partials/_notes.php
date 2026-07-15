<?php if (!empty($notes['public'])): ?>
<div class="notes-block">
	<strong>Notes:</strong> <?= h($notes['public']) ?>
</div>
<?php endif; ?>

<?php if (!empty($termsLines)): ?>
<div class="terms-block">
	<strong>Terms &amp; Conditions</strong>
	<ul class="terms-list">
		<?php foreach ($termsLines as $line): ?>
			<?php if (!empty($line['title']) || !empty($line['description'])): ?>
			<li>
				<?php if (!empty($line['title'])): ?>
					<b><?= h($line['title']) ?></b>:
				<?php endif; ?>
				<?= h($line['description'] ?? '') ?>
			</li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
</div>
<?php elseif (!empty($terms)): ?>
<div class="terms-block">
	<strong>Terms &amp; Conditions</strong>
	<div class="terms-content"><?= h($terms) ?></div>
</div>
<?php endif; ?>
