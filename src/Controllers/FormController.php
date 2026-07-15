<?php

class FormController
{
    /**
     * Resolve and hydrate the complete view-data array for the form page.
     *
     * @param  array $company  The company config array.
     * @return array           Flat array of variables ready for extract().
     */
    public static function getFormState(array $company): array
    {
        $type = strtolower(sanitize_input($_GET['type'] ?? ($_SESSION['document_form_state']['type'] ?? 'invoice')));
        if (!in_array($type, all_document_types(), true)) {
            $type = 'invoice';
        }
        $sections = document_sections($type);

        $state  = $_SESSION['document_form_state'] ?? [];
        $errors = $_SESSION['form_errors'] ?? [];
        unset($_SESSION['form_errors']);

        // Normalize legacy item keys: some older saves used 'description' instead of 'name'.
        if (!empty($state['items']) && is_array($state['items'])) {
            foreach ($state['items'] as $k => $it) {
                if (is_array($it) && !isset($it['name']) && isset($it['description'])) {
                    $state['items'][$k]['name'] = $it['description'];
                }
            }
        }

        $today              = date('Y-m-d');
        $defaultIssueDate   = $state['meta']['issue_date'] ?? $today;
        $defaultDueDate     = $state['meta']['due_date']    ?? add_days_to_date($defaultIssueDate, (int) ($company['default_invoice_due_days'] ?? 30));
        $defaultValidUntil  = $state['meta']['valid_until'] ?? add_days_to_date($defaultIssueDate, (int) ($company['default_quote_valid_days']  ?? 30));

        $defaultItems = $state['items'] ?? [
            ['reference' => '', 'name' => '', 'product_unit' => ''],
        ];

        $customer = $state['customer'] ?? [
            'name'       => '',
            'company'    => '',
            'department' => '',
            'street'     => '',
            'city'       => '',
            'zip'        => '',
            'country'    => '',
            'phone'      => '',
            'email'      => '',
            'vat_number' => '',
        ];

        $meta = $state['meta'] ?? [
            'number'         => '',
            'issue_date'     => $defaultIssueDate,
            'due_date'       => $defaultDueDate,
            'valid_until'    => $defaultValidUntil,
            'reference'      => '',
            'payment_method' => $company['default_payment_method'] ?? 'Bank Transfer (Wire)',
            'payment_terms'  => '30 days',
            'currency'       => $company['default_currency']        ?? 'EUR',
            'currency_symbol'=> $company['default_currency_symbol'] ?? '€',
            'vat_mention'    => $company['vat_mention']             ?? '',
        ];

        // Ensure vat_mention is always present
        $meta += ['vat_mention' => $company['vat_mention'] ?? ''];

        if (empty($state['meta']['issue_date'])) {
            $meta['issue_date'] = $defaultIssueDate;
        }

        if ($sections['due_date']) {
            $meta['due_date']    = $state['meta']['due_date']    ?? $defaultDueDate;
        }
        if ($sections['valid_until']) {
            $meta['valid_until'] = $state['meta']['valid_until'] ?? $defaultValidUntil;
        }

        $notes = $state['notes'] ?? ['public' => '', 'internal' => ''];
        $terms = $state['terms'] ?? ($company['terms'] ?? '');

        // Terms lines: array of {title, description} pairs
        $defaultTermsLines = [
            ['title' => 'Payment Terms',  'description' => '100% advance payment by wire transfer.'],
            ['title' => 'Late Payment',   'description' => 'A fixed recovery fee of 40 € may be applied in case of late payment.'],
            ['title' => 'Order Policy',   'description' => 'Once the order is placed, it cannot be canceled.'],
            ['title' => 'Transport',      'description' => 'Transport via FedEx, using the customer\'s account.'],
        ];
        $termsLines = $state['terms_lines'] ?? $defaultTermsLines;

        // Bank account selector: 'international' | 'french' | 'none'
        $typeCfg            = document_type_config($type);
        $bankAccountDefault = $typeCfg['bank_account_default'] ?? 'none';
        $bankAccount        = $state['bank_account'] ?? $bankAccountDefault;

        // Currency is always taken from the company config — never from session / imported data
        $currency        = $company['default_currency']        ?? 'EUR';
        $currencySymbol  = $company['default_currency_symbol'] ?? '€';
        $companyCurrency = $currency;

        $currencies     = require __DIR__ . '/../../config/currencies.php';
        $defaultVatRate = (float) ($company['default_vat_rate'] ?? 0);
        $totals         = calculate_totals($defaultItems, $defaultVatRate);

        return compact(
            'type', 'sections', 'state', 'errors', 'today',
            'defaultIssueDate', 'defaultDueDate', 'defaultValidUntil', 'defaultItems',
            'customer', 'meta', 'notes', 'terms', 'termsLines', 'bankAccount',
            'currency', 'currencySymbol', 'companyCurrency',
            'currencies', 'defaultVatRate', 'totals'
        );
    }
}
