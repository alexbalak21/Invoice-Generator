<?php

class DocumentValidator
{
    public static function validate(array $document): array
    {
        $errors = [];
        $type = strtolower($document['type'] ?? 'invoice');
        $metadata = $document['metadata'] ?? [];
        $customer = $document['customer'] ?? [];
        $items = $document['items'] ?? [];

        if (empty($metadata['number'])) {
            $errors[] = 'Document number is required.';
        }

        if (empty($metadata['issue_date'])) {
            $errors[] = 'Issue date is required.';
        }

        if ($type === 'invoice' && empty($metadata['due_date'])) {
            $errors[] = 'Due date is required for invoices.';
        }

        if ($type === 'quote' && empty($metadata['valid_until'])) {
            $errors[] = 'Valid until date is required for quotes.';
        }

        if (empty($customer['name'])) {
            $errors[] = 'Customer name is required.';
        }

        if (empty($customer['street'])) {
            $errors[] = 'Customer street is required.';
        }

        if (empty($items)) {
            $errors[] = 'At least one item is required.';
        }

        foreach ($items as $index => $item) {
            if (empty($item['description'])) {
                $errors[] = 'Item ' . ($index + 1) . ' description is required.';
            }
        }

        return $errors;
    }
}
