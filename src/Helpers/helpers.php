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
    return DocumentCalculator::calculateLineTotal($item);
}

function calculate_totals(array $items, $defaultVatRate = 0)
{
    return DocumentCalculator::calculateTotals($items, $defaultVatRate);
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
    $document = DocumentBuilder::fromPost($post, $company, $type);
    $document['show_toolbar'] = true;

    return $document;
}
