<?php
require_once "data.php";

/*
Expected variables from data.php:
$company, $customer, $invoice, $items, $taxRate, $latePaymentRate, $latePaymentFlatFee
*/

$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item["qty"] * $item["price"];
}

$tax   = $subtotal * ($taxRate / 100);
$total = $subtotal + $tax;

$sym = htmlspecialchars($invoice["currency_symbol"] ?? "€");
$cur = htmlspecialchars($invoice["currency"] ?? "EUR");

function money($amount, $sym) {
    return $sym . " " . number_format($amount, 2, '.', ' ');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice <?= htmlspecialchars($invoice["number"]) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page">

    <!-- ========== HEADER ========== -->
    <header class="header">

        <div class="company">
            <img src="img/logo.png" class="logo" alt="Logo">
            <h2><?= htmlspecialchars($company["name"]) ?></h2>
            <p><?= htmlspecialchars($company["street"]) ?></p>
            <p><?= htmlspecialchars($company["zip"]) ?> <?= htmlspecialchars($company["city"]) ?> - <?= htmlspecialchars($company["country"]) ?></p>
            <p><?= htmlspecialchars($company["phone"]) ?></p>
            <p><?= htmlspecialchars($company["email"]) ?></p>
            <!-- Mandatory legal identifiers -->
            <div class="company-legal">
                <?= htmlspecialchars($company["legal_form"]) ?>
                <?php if (!empty($company["share_capital"])): ?>
                    — Share capital: <?= htmlspecialchars($company["share_capital"]) ?> €
                <?php endif; ?>
                <br>
                SIRET: <?= htmlspecialchars($company["siret"]) ?><br>
                VAT: <?= htmlspecialchars($company["vat_number"]) ?>
            </div>
        </div>

        <div class="invoice-title">
            <h1>INVOICE</h1>
            <table class="invoice-info">
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                </tr>
                <tr>
                    <td><?= htmlspecialchars($invoice["number"]) ?></td>
                    <td><?= htmlspecialchars($invoice["date"]) ?></td>
                </tr>
            </table>
        </div>

    </header>


    <!-- ========== INVOICE META (dates, PO ref, payment method) ========== -->
    <div class="meta-grid">
        <?php if (!empty($invoice["service_date"]) && $invoice["service_date"] !== $invoice["date"]): ?>
        <div class="meta-row">
            <span class="meta-label">Service / Delivery date:</span>
            <span><?= htmlspecialchars($invoice["service_date"]) ?></span>
        </div>
        <?php endif; ?>
        <div class="meta-row">
            <span class="meta-label">Payment due date:</span>
            <span><?= htmlspecialchars($invoice["due_date"]) ?></span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Payment method:</span>
            <span><?= htmlspecialchars($invoice["payment_method"]) ?></span>
        </div>
        <?php if (!empty($invoice["po_reference"])): ?>
        <div class="meta-row">
            <span class="meta-label">PO / Quote reference:</span>
            <span><?= htmlspecialchars($invoice["po_reference"]) ?></span>
        </div>
        <?php endif; ?>
        <div class="meta-row">
            <span class="meta-label">Currency:</span>
            <span><?= $cur ?></span>
        </div>
    </div>


    <!-- ========== BILL TO ========== -->
    <section class="bill-to">
        <div class="section-title">BILL TO</div>
        <div class="customer">
            <strong><?= htmlspecialchars($customer["name"]) ?></strong><br>
            <?php if (!empty($customer["company"])): ?>
                <?= htmlspecialchars($customer["company"]) ?><br>
            <?php endif; ?>
            <?php if (!empty($customer["department"])): ?>
                <?= htmlspecialchars($customer["department"]) ?><br>
            <?php endif; ?>
            <?= htmlspecialchars($customer["street"]) ?><br>
            <?= htmlspecialchars($customer["zip"]) ?> <?= htmlspecialchars($customer["city"]) ?> — <?= htmlspecialchars($customer["country"]) ?><br>
            <?php if (!empty($customer["phone"])): ?>
                <?= htmlspecialchars($customer["phone"]) ?><br>
            <?php endif; ?>
            <?= htmlspecialchars($customer["email"]) ?>
            <?php if (!empty($customer["vat_number"])): ?>
                <div class="customer-vat">VAT / GST No.: <?= htmlspecialchars($customer["vat_number"]) ?></div>
            <?php endif; ?>
        </div>
    </section>


    <!-- ========== ITEMS TABLE ========== -->
    <table class="items">
        <thead>
            <tr>
                <th class="description">DESCRIPTION</th>
                <th class="qty">QTY</th>
                <th class="price">UNIT PRICE</th>
                <th class="amount">AMOUNT</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item["description"]) ?></td>
                <td class="center"><?= $item["qty"] ?></td>
                <td class="right"><?= money($item["price"], $sym) ?></td>
                <td class="right"><?= money($item["qty"] * $item["price"], $sym) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>


    <!-- ========== TOTALS ========== -->
    <div class="totals">
        <table>
            <tr>
                <td>Subtotal (excl. VAT)</td>
                <td class="right"><?= money($subtotal, $sym) ?></td>
            </tr>
            <tr>
                <td>VAT (<?= $taxRate ?>%)</td>
                <td class="right"><?= money($tax, $sym) ?></td>
            </tr>
            <tr class="grand-total">
                <td>TOTAL <?= $cur ?></td>
                <td class="right"><?= money($total, $sym) ?></td>
            </tr>
        </table>
    </div>


    <!-- ========== VAT LEGAL MENTION ========== -->
    <?php if (!empty($invoice["vat_mention"])): ?>
    <div class="vat-mention">
        <?= htmlspecialchars($invoice["vat_mention"]) ?>
    </div>
    <?php endif; ?>


    <!-- ========== PAYMENT TERMS & MANDATORY B2B MENTIONS ========== -->
    <div class="payment-terms-block">
        <strong>Payment terms:</strong>
        Due date: <?= htmlspecialchars($invoice["due_date"]) ?> — <?= htmlspecialchars($invoice["payment_method"]) ?>.<br>
        In the event of late payment, penalties will apply at a rate of
        <strong><?= number_format($latePaymentRate, 2) ?>%</strong> per year
        from the due date (3× the ECB refinancing rate, as per Article L441-10 of the French Commercial Code).
        A fixed recovery fee of <strong><?= $latePaymentFlatFee ?> €</strong> will also be charged
        (Decree No. 2012-1115).
    </div>


    <!-- ========== NOTES ========== -->
    <?php if (!empty($invoice["notes"])): ?>
    <div class="notes-block">
        <strong>Notes:</strong> <?= htmlspecialchars($invoice["notes"]) ?>
    </div>
    <?php endif; ?>


    <!-- ========== FOOTER ========== -->
    <footer>
        <div class="thanks">Thank you for your business!</div>
        <div class="contact">
            If you have any questions regarding this invoice, please contact us. <br/>
            <?= htmlspecialchars($company["phone"]) ?> • <?= htmlspecialchars($company["email"]) ?>
        </div>
    </footer>

</div>
</body>
</html>