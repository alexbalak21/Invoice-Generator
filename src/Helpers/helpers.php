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

/**
 * Return the full config entry for a document type (with safe fallback).
 */
function document_type_config(string $type): array
{
    static $all = null;
    if ($all === null) {
        $all = require __DIR__ . '/../../config/document_types.php';
    }
    return $all[$type] ?? $all['invoice'];
}

/**
 * Return the section-visibility flags for a document type.
 * Safely merges with invoice defaults so callers can always use isset().
 */
function document_sections(string $type): array
{
    $defaults = [
        'bank_details'  => false,
        'due_date'      => false,
        'valid_until'   => false,
        'payment_terms' => false,
        'acceptance'    => false,
        'terms'         => false,
    ];
    $cfg = document_type_config($type);
    return array_merge($defaults, $cfg['sections'] ?? []);
}

/** Human-readable label used in form headings (e.g. "Proforma Invoice"). */
function document_title(string $type): string
{
    return document_type_config($type)['label'] ?? ucfirst($type);
}

/** The big printed header on the document (e.g. "PROFORMA INVOICE"). */
function document_label(string $type): string
{
    return document_type_config($type)['title_label'] ?? strtoupper($type);
}

/** All registered type keys. */
function all_document_types(): array
{
    static $all = null;
    if ($all === null) {
        $all = require __DIR__ . '/../../config/document_types.php';
    }
    return array_keys($all);
}
