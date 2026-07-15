<?php

require_once __DIR__ . '/../bootstrap.php';
$company      = require __DIR__ . '/../config/company.php';
$documentTypes = require __DIR__ . '/../config/document_types.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document Generator</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="app-shell dashboard-shell">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            <div class="hero-panel p-4 p-md-5 mb-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-4">
                    <div>
                        <div class="eyebrow">Document Generator</div>
                        <h1 class="display-6 fw-semibold mb-3">Create print-ready documents.</h1>
                        <p class="hero-copy mb-0">
                            Invoice, quote, proforma &mdash; one template, all document types.
                        </p>
                    </div>
                    <div class="hero-meta text-md-end">
                        <div class="small text-uppercase text-muted mb-1">Company</div>
                        <div class="fw-semibold"><?= h($company['name']) ?></div>
                        <div class="text-muted small"><?= h($company['city']) ?>, <?= h($company['country']) ?></div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-4">
                <a class="btn btn-outline-secondary btn-sm" href="history.php">&#128196; View History</a>
            </div>

            <!-- Single "Create Document" card -->
            <div class="action-card action-card-invoice p-4 p-md-5">
                <div class="action-card-label mb-1">New Document</div>
                <h2 class="h3 mb-1 text-dark">Create a document</h2>
                <p class="text-muted mb-4">Choose a document type, then fill the form to generate a print-ready A4 PDF.</p>

                <form method="get" action="form.php" class="d-flex flex-column flex-sm-row align-items-sm-end gap-3">

                    <div class="flex-grow-1">
                        <label for="docTypeSelect" class="form-label fw-semibold small text-uppercase text-muted mb-1">
                            Document type
                        </label>
                        <select name="type" id="docTypeSelect" class="form-select form-select-lg">
                            <?php foreach ($documentTypes as $key => $cfg): ?>
                                <option value="<?= h($key) ?>"><?= h($cfg['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            Create &rarr;
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
</body>
</html>
