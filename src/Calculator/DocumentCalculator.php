<?php

class DocumentCalculator
{
    public static function calculateLineTotal(array $item): float
    {
        $quantity = max(0, normalize_number($item['quantity'] ?? 0));
        $unitPrice = max(0, normalize_number($item['unit_price'] ?? 0));
        $discount = max(0, normalize_number($item['discount'] ?? 0));

        return max(0, ($quantity * $unitPrice) - $discount);
    }

    public static function calculateTotals(array $items, $defaultVatRate = 0): array
    {
        $subtotal = 0.0;
        $vat = 0.0;
        $defaultVatRate = normalize_number($defaultVatRate);

        foreach ($items as $item) {
            $lineTotal = self::calculateLineTotal($item);
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
}
