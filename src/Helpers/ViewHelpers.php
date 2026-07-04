<?php

/**
 * ViewHelpers — view-layer formatting utilities.
 *
 * These are deliberately plain functions (not a class) so partials can call
 * them without any extra boilerplate.  Bootstrap.php must load this file.
 */

if (!function_exists('fmtMoney')) {
    function fmtMoney(float $amount, string $symbol): string
    {
        return $symbol . ' ' . number_format($amount, 2, '.', ' ');
    }
}

if (!function_exists('fmtDate')) {
    function fmtDate(string $date): string
    {
        if (!$date) return '—';
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d ? $d->format('d/m/Y') : $date;
    }
}
