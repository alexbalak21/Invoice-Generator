<?php

$company = [
    "name"          => "Novocib",
    "legal_form"    => "SAS",               // Legal form (SAS, SARL, SA…)
    "share_capital" => "10 000",            // Share capital in €
    "street"        => "Halle à Marée, Quai Jean Voisin",
    "city"          => "Boulogne-sur-Mer",
    "zip"           => "62200",
    "country"       => "France",
    "siren"         => "482 379 377",
    "siret"         => "482 379 377 00012",
    "vat_number"    => "FR90482379377",
    "phone"         => "+33 3 21 99 00 00",
    "email"         => "lbalakireva@novocib.com",
];

$customer = [
    "name"       => "Prof. Dr. Suresh Kumar Rayala",
    "company"    => "Indian Institute of Technology Madras",
    "department" => "Department of Biotechnology",
    "street"     => "Chennai, Tamil Nadu",
    "city"       => "Chennai",
    "zip"        => "600036",
    "country"    => "India",
    "phone"      => "",
    "email"      => "abiramiseetharaman@gmail.com",
    "vat_number" => "33AAAA13615G1Z6",      // GST/VAT number (EU B2B: required; others: optional)
];

$invoice = [
    "number"       => "INV-2026-0702",
    "date"         => "02/07/2026",
    "service_date" => "02/07/2026",         // Date of service/delivery (if different from invoice date)
    "due_date"     => "02/07/2026",         // Payment due date
    "po_reference" => "",                   // Client PO or quote reference (optional)
    "payment_method" => "Bank Transfer (Wire)",
    // VAT legal mention — pick one that applies:
    // "Export outside EU"   → "VAT not applicable – Article 259 CGI"
    // "EU B2B reverse charge" → "Reverse charge – Article 283-2 CGI"
    // "Micro-enterprise"    → "VAT not applicable – Article 293 B CGI"
    "vat_mention"  => "VAT not applicable – export outside the European Union (Article 259 of the French Tax Code)",
    "notes"        => "Kits are in stock. Goods will be dispatched within 2 business days of confirmed payment receipt.",
    "currency"     => "Euro (EUR)",
    "currency_symbol" => "€",
];

$items = [
    [
        "description" => "PRECICE® dCK Phosphorylation Assay Kit (Ref. K0307-01)",
        "qty"         => 1,
        "price"       => 0,                 // Fill in unit price
    ],
    [
        "description" => "International Shipping – FedEx (DAP Chennai)",
        "qty"         => 1,
        "price"       => 0,                 // Fill in shipping cost
    ],
    // Add discounts as negative-price lines, e.g.:
    // [ "description" => "Discount – New client", "qty" => 1, "price" => -500 ],
];

$taxRate = 0;   // 0% for exports outside EU; change to 20 for standard French VAT

// Late payment penalty rate (French B2B mandatory mention)
$latePaymentRate = 3 * 1.5;    // 3× ECB refinancing rate (currently ~4.5%) — adjust as needed
$latePaymentFlatFee = 40;      // €40 fixed recovery fee — mandatory for B2B in France