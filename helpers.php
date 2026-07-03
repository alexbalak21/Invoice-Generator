<?php

if (session_status() === PHP_SESSION_NONE && PHP_SAPI !== 'cli') {
    session_start();
}

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function sanitize_input($value)
{
    if (is_array($value)) {
        $clean = [];
        foreach ($value as $key => $item) {
            $clean[$key] = sanitize_input($item);
        }

        return $clean;
    }

    return trim((string) $value);
}

function normalize_number($value)
{
    if (is_array($value)) {
        return 0.0;
    }

    $value = trim((string) $value);

    if ($value === '') {
        return 0.0;
    }

    $value = preg_replace('/[^0-9,\.\-]/', '', $value);

    if (substr_count($value, ',') > 0 && substr_count($value, '.') > 0) {
        if (strrpos($value, ',') > strrpos($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '', $value);
        }
    } else {
        $value = str_replace(',', '.', $value);
    }

    return (float) $value;
}

function money($amount, $symbol = '€')
{
    return $symbol . ' ' . number_format((float) $amount, 2, '.', ' ');
}

function format_money($amount, $symbol = '€')
{
    return money($amount, $symbol);
}

function calculate_line_total(array $item)
{
    $quantity = max(0, normalize_number($item['quantity'] ?? 0));
    $unitPrice = max(0, normalize_number($item['unit_price'] ?? 0));
    $discount = max(0, normalize_number($item['discount'] ?? 0));

    return max(0, ($quantity * $unitPrice) - $discount);
}

function calculate_totals(array $items, $defaultVatRate = 0)
{
    $subtotal = 0.0;
    $vat = 0.0;
    $defaultVatRate = normalize_number($defaultVatRate);

    foreach ($items as $item) {
        $lineTotal = calculate_line_total($item);
        $rate = array_key_exists('vat_rate', $item) && $item['vat_rate'] !== ''
            ? normalize_number($item['vat_rate'])
            : $defaultVatRate;

        $subtotal += $lineTotal;
        $vat += $lineTotal * ($rate / 100);
    }

    $subtotal = round($subtotal, 2);
    $vat = round($vat, 2);

    return [
        'subtotal' => $subtotal,
        'vat' => $vat,
        'grand_total' => round($subtotal + $vat, 2),
    ];
}

function add_days_to_date($date, $days)
{
    if (empty($date)) {
        return '';
    }

    try {
        $dateTime = new DateTime($date);
        $dateTime->modify('+' . (int) $days . ' days');

        return $dateTime->format('Y-m-d');
    } catch (Exception $exception) {
        return $date;
    }
}

function document_title($type)
{
    return $type === 'quote' ? 'Quote' : 'Invoice';
}

function document_label($type)
{
    return strtoupper($type === 'quote' ? 'Quote' : 'Invoice');
}

function normalize_item_rows(array $items, $defaultVatRate = 0)
{
    $normalized = [];
    foreach ($items as $item) {
        $normalized[] = [
            'reference' => sanitize_input($item['reference'] ?? ''),
            'description' => sanitize_input($item['description'] ?? ''),
            'quantity' => normalize_number($item['quantity'] ?? 0),
            'unit' => sanitize_input($item['unit'] ?? ''),
            'unit_price' => normalize_number($item['unit_price'] ?? 0),
            'discount' => normalize_number($item['discount'] ?? 0),
            'vat_rate' => $item['vat_rate'] ?? $defaultVatRate,
        ];
    }

    return $normalized;
}

function filter_document_items(array $items)
{
    $filtered = [];

    foreach ($items as $item) {
        $hasContent = !empty($item['reference']) || !empty($item['description']) || normalize_number($item['quantity'] ?? 0) > 0 || normalize_number($item['unit_price'] ?? 0) > 0;
        if ($hasContent) {
            $filtered[] = $item;
        }
    }

    return $filtered;
}

function build_document_from_post(array $post, array $company, $type)
{
    $type = strtolower(sanitize_input($type));
    if (!in_array($type, ['invoice', 'quote'], true)) {
        $type = 'invoice';
    }

    $defaultVatRate = normalize_number($company['default_vat_rate'] ?? 0);
    $defaultCurrency = sanitize_input($company['default_currency'] ?? 'EUR');
    $defaultCurrencySymbol = sanitize_input($company['default_currency_symbol'] ?? '€');

    $customer = sanitize_input($post['customer'] ?? []);
    $meta = sanitize_input($post['meta'] ?? []);
    $notes = sanitize_input($post['notes'] ?? []);
    $acceptance = sanitize_input($post['acceptance'] ?? []);
    $items = normalize_item_rows($post['items'] ?? [], $defaultVatRate);
    $items = filter_document_items($items);

    if (empty($meta['issue_date'])) {
        $meta['issue_date'] = date('Y-m-d');
    }

    if ($type === 'invoice' && empty($meta['due_date'])) {
        $meta['due_date'] = add_days_to_date($meta['issue_date'], (int) ($company['default_invoice_due_days'] ?? 30));
    }

    if ($type === 'quote' && empty($meta['valid_until'])) {
        $meta['valid_until'] = add_days_to_date($meta['issue_date'], (int) ($company['default_quote_valid_days'] ?? 30));
    }

    $meta['currency'] = $meta['currency'] ?: $defaultCurrency;
    $meta['currency_symbol'] = $meta['currency_symbol'] ?: $defaultCurrencySymbol;
    $meta['payment_method'] = $meta['payment_method'] ?: ($company['default_payment_method'] ?? 'Bank Transfer');

    $totals = calculate_totals($items, $defaultVatRate);

    return [
        'type' => $type,
        'company' => $company,
        'customer' => $customer,
        'items' => $items,
        'metadata' => [
            'number' => sanitize_input($meta['number'] ?? ''),
            'issue_date' => sanitize_input($meta['issue_date'] ?? ''),
            'due_date' => sanitize_input($meta['due_date'] ?? ''),
            'valid_until' => sanitize_input($meta['valid_until'] ?? ''),
            'reference' => sanitize_input($meta['reference'] ?? ''),
            'payment_method' => sanitize_input($meta['payment_method'] ?? ''),
            'currency' => sanitize_input($meta['currency'] ?? $defaultCurrency),
            'currency_symbol' => sanitize_input($meta['currency_symbol'] ?? $defaultCurrencySymbol),
        ],
        'payment' => [
            'method' => sanitize_input($meta['payment_method'] ?? ''),
            'due_date' => sanitize_input($meta['due_date'] ?? ''),
            'payment_terms' => sanitize_input($meta['payment_terms'] ?? ''),
            'amount_paid' => normalize_number($meta['amount_paid'] ?? 0),
        ],
        'shipping' => sanitize_input($post['shipping'] ?? []),
        'totals' => $totals,
        'legal' => [
            'vat_mention' => sanitize_input($meta['vat_mention'] ?? ($company['vat_mention'] ?? '')),
            'late_payment' => sanitize_input($company['late_payment_text'] ?? ''),
            'recovery_fee' => sanitize_input($company['late_payment_fee_text'] ?? ''),
            'terms' => sanitize_input($company['terms_text'] ?? ''),
        ],
        'notes' => [
            'public' => sanitize_input($notes['public'] ?? ''),
            'internal' => sanitize_input($notes['internal'] ?? ''),
        ],
        'acceptance' => [
            'enabled' => $type === 'quote' ? true : !empty($acceptance['enabled']),
            'text' => sanitize_input($acceptance['text'] ?? 'Quote received before execution, read and approved, agreed.'),
        ],
        'show_toolbar' => true,
    ];
}
