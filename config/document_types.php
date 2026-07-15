<?php

/**
 * Document type definitions.
 *
 * sections flags:
 *  bank_details   – wire-transfer / IBAN block (which account: 'international'|'french'|'none')
 *  due_date       – payment due date field
 *  valid_until    – "valid until" date (quotes)
 *  payment_terms  – payment-terms block in the legal section
 *  acceptance     – "please confirm your approval" text (quotes)
 *  terms          – terms & conditions block
 *
 * bank_account_default: pre-selected value in the form selector
 *   'international' | 'french' | 'none'
 */

return [

    'invoice' => [
        'label'                => 'Invoice',
        'title_label'          => 'INVOICE',
        'number_prefix'        => 'INV',
        'bank_account_default' => 'international',
        'sections' => [
            'bank_details'  => true,
            'due_date'      => true,
            'valid_until'   => false,
            'payment_terms' => true,
            'acceptance'    => false,
            'terms'         => true,
        ],
    ],

    'proforma' => [
        'label'                => 'Proforma Invoice',
        'title_label'          => 'PROFORMA INVOICE',
        'number_prefix'        => 'PRO',
        'bank_account_default' => 'international',
        'sections' => [
            'bank_details'  => true,
            'due_date'      => true,
            'valid_until'   => false,
            'payment_terms' => true,
            'acceptance'    => false,
            'terms'         => true,
        ],
    ],

    'quote' => [
        'label'                => 'Quote',
        'title_label'          => 'QUOTE',
        'number_prefix'        => 'Q',
        'bank_account_default' => 'none',
        'sections' => [
            'bank_details'  => false,
            'due_date'      => false,
            'valid_until'   => true,
            'payment_terms' => false,
            'acceptance'    => true,
            'terms'         => true,
        ],
    ],

];
