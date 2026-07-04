<?php

class DocumentBuilder
{
    public static function fromPost(array $post, array $company, string $type): array
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
        $items = self::normalizeItemRows($post['items'] ?? [], $defaultVatRate);
        $items = self::filterDocumentItems($items);

        if (empty($meta['issue_date'])) {
            $meta['issue_date'] = date('Y-m-d');
        }

        if ($type === 'invoice' && empty($meta['due_date'])) {
            $meta['due_date'] = add_days_to_date($meta['issue_date'], (int) ($company['default_invoice_due_days'] ?? 30));
        }

        if ($type === 'quote' && empty($meta['valid_until'])) {
            $meta['valid_until'] = add_days_to_date($meta['issue_date'], (int) ($company['default_quote_valid_days'] ?? 30));
        }

        // Allow foreign currencies (e.g. USD) with an explicit conversion rate.
        // If no foreign currency is selected, fall back to the company default.
        $selectedCurrency = sanitize_input($meta['currency'] ?? $defaultCurrency);
        $selectedSymbol   = sanitize_input($meta['currency_symbol'] ?? $defaultCurrencySymbol);
        if (empty($selectedCurrency)) { $selectedCurrency = $defaultCurrency; }
        if (empty($selectedSymbol))   { $selectedSymbol   = $defaultCurrencySymbol; }
        $meta['currency']        = $selectedCurrency;
        $meta['currency_symbol'] = $selectedSymbol;

        // FX rate: how many foreign-currency units equal 1 base-currency unit.
        // Only meaningful when currency !== company default currency.
        $fxRate = ($selectedCurrency !== $defaultCurrency)
            ? max(0.000001, normalize_number($meta['fx_rate'] ?? 0))
            : 1.0;
        $meta['fx_rate'] = $fxRate;
        $meta['fx_base_currency'] = $defaultCurrency;


        $meta['payment_method'] = $meta['payment_method'] ?: ($company['default_payment_method'] ?? 'Bank Transfer');

        $totals = DocumentCalculator::calculateTotals($items, $defaultVatRate);

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
                'payment_terms' => sanitize_input($meta['payment_terms'] ?? ''),
                'currency' => sanitize_input($meta['currency'] ?? $defaultCurrency),
                'currency_symbol' => sanitize_input($meta['currency_symbol'] ?? $defaultCurrencySymbol),
                'fx_rate' => $fxRate,
                'fx_base_currency' => $defaultCurrency,
                'vat_mention' => sanitize_input($meta['vat_mention'] ?? ($company['vat_mention'] ?? '')),
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
                // Optional: include the late-payment penalty / recovery fee mention on invoices.
                'show_late_payment' => !empty($post['legal']['show_late_payment']),
            ],
            'notes' => [
                'public' => sanitize_input($notes['public'] ?? ''),
                'internal' => sanitize_input($notes['internal'] ?? ''),
            ],
            'acceptance' => [
                'enabled' => $type === 'quote' ? true : !empty($acceptance['enabled']),
                'text' => sanitize_input($acceptance['text'] ?? 'Quote received before execution, read and approved, agreed.'),
            ],
            'show_toolbar' => false,
        ];
    }

    public static function normalizeItemRows(array $items, $defaultVatRate = 0): array
    {
        $normalized = [];

        foreach ($items as $item) {
            $normalized[] = [
                'reference' => sanitize_input($item['reference'] ?? ''),
                'description' => sanitize_input($item['description'] ?? ''),
                'product_unit' => sanitize_input($item['product_unit'] ?? ''),
                'quantity' => normalize_number($item['quantity'] ?? 0),
                'unit' => sanitize_input($item['unit'] ?? ''),
                'unit_price' => normalize_number($item['unit_price'] ?? 0),
                'discount' => normalize_number($item['discount'] ?? 0),
                'vat_rate' => $item['vat_rate'] ?? $defaultVatRate,
            ];
        }

        return $normalized;
    }

    public static function filterDocumentItems(array $items): array
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
}
