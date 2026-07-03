<?php
return [
    'name' => 'NOVOCIB',
    'logo' => 'img/logo.png',

    // Legal identity
    'legal_form' => 'SAS, société par actions simplifiée',
    'share_capital' => '260 158,00 €',

    // Official registered address (INSEE)
    'street' => 'BD de Chatillon, Quai Jean Voisin',
    'city' => 'Boulogne-sur-Mer',
    'zip' => '62200',
    'country' => 'France',

    // Official identifiers
    'siren' => '482 379 377',
    'siret' => '482 379 377 00047',
    'vat_number' => 'FR90 482 379 377',
    'eori' => 'FR48237937700047',

    // Contact
    'email' => 'lbalakireva@novocib.com',
    'website' => 'https://novocib.com',

    // Accounting defaults
    'default_currency' => 'EUR',
    'default_currency_symbol' => '€',
    'default_vat_rate' => 0,
    'default_invoice_due_days' => 30,
    'default_quote_valid_days' => 30,
    'default_payment_method' => 'Bank Transfer (Wire)',

    // Legal mentions
    'vat_mention' => 'VAT not applicable - export outside the European Union (Article 259 of the French Tax Code)',
    'late_payment_rate' => 4.5,
    'late_payment_flat_fee' => 40,
    'late_payment_text' => 'Late payment penalties apply from the due date.',
    'late_payment_fee_text' => 'A fixed recovery fee may also apply.',
    'terms_text' => 'Payment is due according to the terms listed on the document.',
];
