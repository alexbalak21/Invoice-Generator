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
    $value = preg_replace('/[^0-9,.\-]/', '', $value);
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

/**
 * Format a monetary amount with a currency symbol.
 * Canonical name: money(). Use this everywhere.
 */
function money($amount, $symbol = '€')
{
    return $symbol . ' ' . number_format((float) $amount, 2, '.', ' ');
}

/**
 * @deprecated Use money() directly.
 */
function format_money($amount, $symbol = '€')
{
    return money($amount, $symbol);
}

/** Thin alias — delegates to DocumentCalculator. */
function calculate_line_total(array $item)
{
    return DocumentCalculator::calculateLineTotal($item);
}

/** Thin alias — delegates to DocumentCalculator. */
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
