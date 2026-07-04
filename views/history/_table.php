<?php
/**
 * Reusable history table partial.
 *
 * Expected variables (set by the caller before include):
 *   $rows           array   — rows from DocumentRepository::list()
 *   $docType        string  — 'invoice' or 'quote'
 *   $emptyMessage   string  — message shown when $rows is empty
 *   $emptyLinkLabel string  — CTA link label for the empty state
 *   $dateLabel      string  — column header for the secondary date (Due date / Valid until)
 *   $currencySymbol string
 */
?>
<?php if (empty($rows)): ?>
	<div class="text-center text-muted py-5">
		<p class="mb-2"><?= h($emptyMessage) ?></p>
		<a href="form.php?type=<?= h($docType) ?>" class="btn btn-outline-primary"><?= h($emptyLinkLabel) ?></a>
	</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
	<div class="table-responsive">
		<table class="table table-hover table-history mb-0">
			<thead class="table-light">
				<tr>
					<th>#</th>
					<th>Number</th>
					<th>Customer</th>
					<th>Issue date</th>
					<th><?= h($dateLabel) ?></th>
					<th class="text-end">Subtotal HT</th>
					<th class="text-end">VAT</th>
					<th class="text-end">Total TTC</th>
					<th>Saved at</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($rows as $row): ?>
				<tr>
					<td class="text-muted small"><?= (int) $row['id'] ?></td>
					<td><strong><?= h($row['number']) ?></strong></td>
					<td><?= h($row['customer']) ?></td>
					<td><?= fmtDate($row['issue_date']) ?></td>
					<td><?= fmtDate($row['secondary_date'] ?? '') ?></td>
					<td class="text-end total-col"><?= fmtMoney((float) $row['total_ht'],  $currencySymbol) ?></td>
					<td class="text-end total-col"><?= fmtMoney((float) $row['total_vat'], $currencySymbol) ?></td>
					<td class="text-end total-col fw-semibold"><?= fmtMoney((float) $row['total_ttc'], $currencySymbol) ?></td>
					<td class="text-muted small"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
					<td class="text-end" style="white-space:nowrap">
						<a href="history.php?action=view&type=<?= h($docType) ?>&id=<?= (int) $row['id'] ?>"
						   class="btn btn-sm btn-outline-primary action-btn">View / Print</a>
						<button type="button"
								class="btn btn-sm btn-outline-danger action-btn ms-1"
								data-bs-toggle="modal"
								data-bs-target="#confirmDelete"
								data-type="<?= h($docType) ?>"
								data-id="<?= (int) $row['id'] ?>"
								data-label="<?= h($row['number']) ?>">
							Delete
						</button>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
<?php endif; ?>
