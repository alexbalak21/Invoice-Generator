<?php
$pageTitle = h(document_title($type)) . ' Form';
require __DIR__ . '/../../views/layouts/app.php';
?>
<div class="container py-4 py-md-5">
	<div class="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap">
		<div>
			<div class="eyebrow">Create <?= h(document_label($type)) ?></div>
			<h1 class="h2 mb-1">Printable A4 document</h1>
			<p class="text-muted mb-0">Fill the form, generate the HTML document, then print to PDF from the browser.</p>
		</div>
		<div class="d-flex flex-wrap gap-2">
			<a class="btn btn-outline-secondary" href="<?= h($type) ?>.json" download>Download sample JSON</a>
			<button type="button" class="btn btn-outline-secondary" id="jsonSaveButton">Save JSON</button>
			<button type="button" class="btn btn-outline-primary" id="jsonImportButton">Upload JSON</button>
			<a class="btn btn-outline-secondary" href="index.php">Back to dashboard</a>
		</div>
	</div>

	<?php if (!empty($errors)): ?>
		<div class="alert alert-danger">
			<strong>Please fix the highlighted issues.</strong>
			<ul class="mb-0 mt-2">
				<?php foreach ($errors as $error): ?>
					<li><?= h($error) ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<form method="post" action="generate.php" class="document-form" id="documentForm"
		data-default-vat="<?= h($defaultVatRate) ?>"
		data-currency-symbol="<?= h($currencySymbol) ?>"
		data-base-currency="<?= h($companyCurrency) ?>">

		<input type="hidden" name="type" value="<?= h($type) ?>">
		<input type="file" id="jsonImportInput" class="d-none" accept="application/json,.json">

		<div class="row g-4">
			<div class="col-12 col-xl-8">
				<?php include __DIR__ . '/_customer.php'; ?>
				<?php include __DIR__ . '/_document-info.php'; ?>
				<?php include __DIR__ . '/_items.php'; ?>
				<?php include __DIR__ . '/_notes.php'; ?>
			</div>
			<?php include __DIR__ . '/_sidebar.php'; ?>
		</div>
	</form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- Product Picker Modal -->
<div class="modal fade" id="productPickerModal" tabindex="-1" aria-labelledby="productPickerLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="productPickerLabel">Add from catalogue</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<div class="mb-3">
					<input type="search" id="productSearch" class="form-control" placeholder="Search by reference or name…" autocomplete="off">
				</div>
				<div id="productPickerStatus" class="text-muted small mb-2"></div>
				<div class="table-responsive">
					<table class="table table-hover table-sm align-middle" id="productPickerTable">
						<thead class="table-light">
							<tr>
								<th>Reference</th>
								<th>Name</th>
								<th>Unit</th>
								<th class="text-end">Unit price</th>
								<th></th>
							</tr>
						</thead>
						<tbody id="productPickerBody">
							<tr><td colspan="5" class="text-center text-muted py-4">Loading…</td></tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script src="assets/js/form-items.js"></script>
<script src="assets/js/editable-table.js"></script>
<script src="assets/js/form-totals.js"></script>
<script src="assets/js/form-json.js"></script>
<script src="assets/js/product-picker.js"></script>
<script src="assets/js/currency.js"></script>
</body>
</html>
