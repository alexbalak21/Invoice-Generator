<?php if ($showToolbar): ?>
<div class="page-toolbar no-print">
	<div>
		<strong><?= h($title) ?></strong>
		<span class="toolbar-subtitle">Printable A4 preview</span>
		<?php if ($dbSaved): ?>
			<span class="toolbar-saved-badge">&#10003; Saved #<?= h($dbId) ?></span>
		<?php endif; ?>
	</div>
	<div class="toolbar-actions">
		<a class="toolbar-link" href="history.php">History</a>
		<a class="toolbar-link" href="form.php?type=<?= h($type) ?>">Edit</a>
		<button type="button" class="toolbar-button" id="printBtn">Print</button>
	</div>
</div>
<script>
(function () {
	var btn = document.getElementById('printBtn');
	if (btn) btn.addEventListener('click', function () { window.print(); });
}());
</script>
<?php endif; ?>
