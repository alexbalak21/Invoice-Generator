<?php

require_once __DIR__ . '/../bootstrap.php';
$company = require __DIR__ . '/../config/company.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document Generator</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body class="app-shell dashboard-shell">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="hero-panel p-4 p-md-5 mb-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-4">
                    <div>
                        <div class="eyebrow">Document Generator</div>
                        <h1 class="display-6 fw-semibold mb-3">Create print-ready invoices and quotes.</h1>
                        <p class="hero-copy mb-0">
                            A lightweight PHP prototype that keeps the existing A4 template and turns it into a reusable workflow.
                        </p>
                    </div>
                    <div class="hero-meta text-md-end">
                        <div class="small text-uppercase text-muted mb-1">Company</div>
                        <div class="fw-semibold"><?= h($company['name']) ?></div>
                        <div class="text-muted small"><?= h($company['city']) ?>, <?= h($company['country']) ?></div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <a class="action-card action-card-invoice h-100 text-decoration-none" href="invoice.php">
                        <div class="action-card-label">Invoice</div>
                        <h2 class="h3 mb-3 text-dark">Create Invoice</h2>
                        <p class="text-muted mb-4">Open the Bootstrap form with invoice defaults, due date, payment method, and VAT-ready totals.</p>
                        <span class="btn btn-primary btn-lg">Start invoice</span>
                    </a>
                </div>
                <div class="col-md-6">
                    <a class="action-card action-card-quote h-100 text-decoration-none" href="quote.php">
                        <div class="action-card-label">Quote</div>
                        <h2 class="h3 mb-3 text-dark">Create Quote</h2>
                        <p class="text-muted mb-4">Use the same template system for quotations with valid-until date and acceptance text.</p>
                        <span class="btn btn-outline-primary btn-lg">Start quote</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
