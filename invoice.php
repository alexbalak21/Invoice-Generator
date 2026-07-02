<?php
require_once "data.php";

/*
Expected variables from data.php

$company
$customer
$invoice
$items
$taxRate
*/

$subtotal = 0;

foreach ($items as $item) {
    $subtotal += $item["qty"] * $item["price"];
}

$tax = $subtotal * ($taxRate / 100);
$total = $subtotal + $tax;
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

    <!-- ================= HEADER ================= -->

    <header class="header">

        <div class="company">
            <img src="img/logo.png" class="logo" alt="Logo">
            <h2><?= htmlspecialchars($company["name"]) ?></h2>
            <p><?= htmlspecialchars($company["street"]) ?></p>
            <p><?= htmlspecialchars($company["city"]) ?> <?= htmlspecialchars($company["zip"]) ?></p>
            <p><?= htmlspecialchars($company["phone"]) ?></p>
            <p><?= htmlspecialchars($company["email"]) ?></p>
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


    <!-- ================= BILL TO ================= -->

    <section class="bill-to">

        <div class="section-title">
            BILL TO
        </div>

        <div class="customer">

            <strong><?= htmlspecialchars($customer["name"]) ?></strong><br>

            <?= htmlspecialchars($customer["company"]) ?><br>

            <?= htmlspecialchars($customer["street"]) ?><br>

            <?= htmlspecialchars($customer["city"]) ?> <?= htmlspecialchars($customer["zip"]) ?><br>

            <?= htmlspecialchars($customer["phone"]) ?><br>

            <?= htmlspecialchars($customer["email"]) ?>

        </div>

    </section>


    <!-- ================= ITEMS ================= -->

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

        <?php foreach($items as $item): ?>

            <tr>

                <td>

                    <?= htmlspecialchars($item["description"]) ?>

                </td>

                <td class="center">

                    <?= $item["qty"] ?>

                </td>

                <td class="right">

                    <?= number_format($item["price"],2) ?> €

                </td>

                <td class="right">

                    <?= number_format($item["qty"] * $item["price"],2) ?> €

                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>


    <!-- ================= TOTAL ================= -->

    <div class="totals">

        <table>

            <tr>

                <td>Subtotal</td>

                <td class="right">

                    <?= number_format($subtotal,2) ?> €

                </td>

            </tr>

            <tr>

                <td>Tax (<?= $taxRate ?>%)</td>

                <td class="right">

                    <?= number_format($tax,2) ?> €

                </td>

            </tr>

            <tr class="grand-total">

                <td>TOTAL</td>

                <td class="right">

                    <?= number_format($total,2) ?> €

                </td>

            </tr>

        </table>

    </div>


    <!-- ================= FOOTER ================= -->

    <footer>

        <div class="thanks">

            Thank you for your business!

        </div>

        <div class="contact">

            If you have any questions regarding this invoice please contact us.

            <br><br>

            <?= htmlspecialchars($company["phone"]) ?>

            •

            <?= htmlspecialchars($company["email"]) ?>

        </div>

    </footer>

</div>
</body>
</html>