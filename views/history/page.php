<?php
$pageTitle  = 'Document History';
$extraHead  = '<style>
    .badge-invoice { background: #dbeafe; color: #1e40af; }
    .badge-quote   { background: #fef3c7; color: #92400e; }
    .total-col     { font-variant-numeric: tabular-nums; }
    .table-history th { font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; }
    .action-btn    { font-size: .8rem; }
</style>';
require __DIR__ . '/../../views/layouts/app.php';
?>
<div class="container py-4 py-md-5">

	<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
		<div>
			<div class="eyebrow">Document Generator</div>
			<h1 class="h2 mb-0">History</h1>
		</div>
		<div class="d-flex gap-2">
			<a class="btn btn-outline-primary" href="form.php?type=invoice">New Invoice</a>
			<a class="btn btn-outline-secondary" href="form.php?type=quote">New Quote</a>
			<a class="btn btn-link text-muted" href="index.php">Dashboard</a>
		</div>
	</div>

	<?php if (!$dbAvailable): ?>
		<div class="alert alert-warning">
			<strong>Database unavailable.</strong> Check your <code>config/database.php</code> settings and make sure MySQL is running.
		</div>
	<?php endif; ?>

	<?php if (!empty($flashError)): ?>
		<div class="alert alert-danger"><?= h($flashError) ?></div>
	<?php endif; ?>

	<?php if (isset($_GET['deleted'])): ?>
		<div class="alert alert-success alert-dismissible fade show" role="alert">
			Document deleted successfully.
			<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
		</div>
	<?php endif; ?>

	<ul class="nav nav-tabs mb-4" id="historyTabs">
		<li class="nav-item">
			<button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-invoices">
				Invoices <span class="badge bg-secondary ms-1"><?= count($invoices) ?></span>
			</button>
		</li>
		<li class="nav-item">
			<button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-quotes">
				Quotes <span class="badge bg-secondary ms-1"><?= count($quotes) ?></span>
			</button>
		</li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane fade show active" id="tab-invoices">
			<?php
			$rows           = $invoices;
			$docType        = 'invoice';
			$emptyMessage   = 'No invoices saved yet.';
			$emptyLinkLabel = 'Create your first invoice';
			$dateLabel      = 'Due date';
			include __DIR__ . '/_table.php';
			?>
		</div>
		<div class="tab-pane fade" id="tab-quotes">
			<?php
			$rows           = $quotes;
			$docType        = 'quote';
			$emptyMessage   = 'No quotes saved yet.';
			$emptyLinkLabel = 'Create your first quote';
			$dateLabel      = 'Valid until';
			include __DIR__ . '/_table.php';
			?>
		</div>
	</div>
</div>

<!-- Delete confirmation modal -->
<div class="modal fade" id="confirmDelete" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Delete document?</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<p>You are about to permanently delete <strong id="deleteLabel"></strong>. This cannot be undone.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<form method="post" id="deleteForm">
					<input type="hidden" name="_action" value="delete">
					<button type="submit" class="btn btn-danger">Yes, delete</button>
				</form>
			</div>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/history.js"></script>
</body>
</html>
