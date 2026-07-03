<?php

require_once __DIR__ . '/../bootstrap.php';

$company = require __DIR__ . '/../config/company.php';

// ── Handle actions ──────────────────────────────────────────────────────
$action = $_GET['action'] ?? '';
$type   = in_array($_GET['type'] ?? '', ['invoice', 'quote'], true) ? $_GET['type'] : 'invoice';
$id     = (int) ($_GET['id'] ?? 0);

// Regenerate: load payload → push to session → redirect to preview
if ($action === 'view' && $id > 0) {
    $doc = DocumentRepository::load($type, $id);
    if ($doc) {
        $doc['show_toolbar'] = true;
        $_SESSION['document_preview'] = $doc;
        header('Location: preview.php');
        exit;
    }
    $flashError = 'Document not found.';
}

// Delete
if ($action === 'delete' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    DocumentRepository::delete($type, $id);
    header('Location: history.php?deleted=1');
    exit;
}

// ── Load lists ───────────────────────────────────────────────────────────
$db = get_db();
$dbAvailable = $db !== null;

$invoices = $dbAvailable ? DocumentRepository::list('invoice') : [];
$quotes   = $dbAvailable ? DocumentRepository::list('quote')   : [];

$currencySymbol = $company['default_currency_symbol'] ?? '€';

function fmtMoney(float $amount, string $symbol): string {
    return $symbol . ' ' . number_format($amount, 2, '.', ' ');
}
function fmtDate(string $date): string {
    if (!$date) return '—';
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d ? $d->format('d/m/Y') : $date;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document History</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        .badge-invoice { background: #dbeafe; color: #1e40af; }
        .badge-quote   { background: #fef3c7; color: #92400e; }
        .total-col     { font-variant-numeric: tabular-nums; }
        .table-history th { font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; }
        .action-btn    { font-size: .8rem; }
    </style>
</head>
<body class="app-shell">
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
        <div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Document deleted successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ── TABS ── -->
    <ul class="nav nav-tabs mb-4" id="historyTabs">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-invoices">
                Invoices
                <span class="badge bg-secondary ms-1"><?= count($invoices) ?></span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-quotes">
                Quotes
                <span class="badge bg-secondary ms-1"><?= count($quotes) ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- ── INVOICES ── -->
        <div class="tab-pane fade show active" id="tab-invoices">
            <?php if (empty($invoices)): ?>
                <div class="text-center text-muted py-5">
                    <p class="mb-2">No invoices saved yet.</p>
                    <a href="form.php?type=invoice" class="btn btn-primary">Create your first invoice</a>
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
                                <th>Due date</th>
                                <th class="text-end">Subtotal HT</th>
                                <th class="text-end">VAT</th>
                                <th class="text-end">Total TTC</th>
                                <th>Saved at</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $row): ?>
                            <tr>
                                <td class="text-muted small"><?= (int)$row['id'] ?></td>
                                <td><strong><?= htmlspecialchars($row['number']) ?></strong></td>
                                <td><?= htmlspecialchars($row['customer']) ?></td>
                                <td><?= fmtDate($row['issue_date']) ?></td>
                                <td><?= fmtDate($row['secondary_date'] ?? '') ?></td>
                                <td class="text-end total-col"><?= fmtMoney((float)$row['total_ht'],  $currencySymbol) ?></td>
                                <td class="text-end total-col"><?= fmtMoney((float)$row['total_vat'], $currencySymbol) ?></td>
                                <td class="text-end total-col fw-semibold"><?= fmtMoney((float)$row['total_ttc'], $currencySymbol) ?></td>
                                <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                <td class="text-end" style="white-space:nowrap">
                                    <a href="history.php?action=view&type=invoice&id=<?= (int)$row['id'] ?>"
                                       class="btn btn-sm btn-outline-primary action-btn">View / Print</a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger action-btn ms-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmDelete"
                                            data-type="invoice"
                                            data-id="<?= (int)$row['id'] ?>"
                                            data-label="<?= htmlspecialchars($row['number']) ?>">
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
        </div>

        <!-- ── QUOTES ── -->
        <div class="tab-pane fade" id="tab-quotes">
            <?php if (empty($quotes)): ?>
                <div class="text-center text-muted py-5">
                    <p class="mb-2">No quotes saved yet.</p>
                    <a href="form.php?type=quote" class="btn btn-outline-primary">Create your first quote</a>
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
                                <th>Valid until</th>
                                <th class="text-end">Subtotal HT</th>
                                <th class="text-end">VAT</th>
                                <th class="text-end">Total TTC</th>
                                <th>Saved at</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quotes as $row): ?>
                            <tr>
                                <td class="text-muted small"><?= (int)$row['id'] ?></td>
                                <td><strong><?= htmlspecialchars($row['number']) ?></strong></td>
                                <td><?= htmlspecialchars($row['customer']) ?></td>
                                <td><?= fmtDate($row['issue_date']) ?></td>
                                <td><?= fmtDate($row['secondary_date'] ?? '') ?></td>
                                <td class="text-end total-col"><?= fmtMoney((float)$row['total_ht'],  $currencySymbol) ?></td>
                                <td class="text-end total-col"><?= fmtMoney((float)$row['total_vat'], $currencySymbol) ?></td>
                                <td class="text-end total-col fw-semibold"><?= fmtMoney((float)$row['total_ttc'], $currencySymbol) ?></td>
                                <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                <td class="text-end" style="white-space:nowrap">
                                    <a href="history.php?action=view&type=quote&id=<?= (int)$row['id'] ?>"
                                       class="btn btn-sm btn-outline-primary action-btn">View / Print</a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger action-btn ms-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmDelete"
                                            data-type="quote"
                                            data-id="<?= (int)$row['id'] ?>"
                                            data-label="<?= htmlspecialchars($row['number']) ?>">
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
        </div>

    </div><!-- /tab-content -->
</div>

<!-- ── Delete confirmation modal ── -->
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Wire up delete modal
document.getElementById('confirmDelete').addEventListener('show.bs.modal', function(e) {
    const btn   = e.relatedTarget;
    const id    = btn.dataset.id;
    const type  = btn.dataset.type;
    const label = btn.dataset.label;
    document.getElementById('deleteLabel').textContent = label;
    document.getElementById('deleteForm').action =
        'history.php?action=delete&type=' + encodeURIComponent(type) + '&id=' + encodeURIComponent(id);
});
</script>
</body>
</html>
