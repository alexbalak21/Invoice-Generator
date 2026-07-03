<?php

/**
 * DocumentRepository
 *
 * Persists and retrieves invoices / quotes stored as JSON payloads.
 * Depends on get_db() from src/Database/db.php.
 */
class DocumentRepository
{
    // ------------------------------------------------------------------
    // Save (insert or update on duplicate number)
    // ------------------------------------------------------------------

    public static function save(array $document): int|false
    {
        $db = get_db();
        if ($db === null) {
            return false;
        }

        $type   = strtolower($document['type'] ?? 'invoice');
        $table  = $type === 'quote' ? 'quotes' : 'invoices';
        $meta   = $document['metadata'] ?? [];
        $totals = $document['totals']   ?? [];

        $number   = trim($meta['number']   ?? '');
        $customer = trim(
            ($document['customer']['company'] ?? '') !== ''
                ? $document['customer']['company']
                : ($document['customer']['name'] ?? '')
        );

        if ($number === '') {
            return false; // number is required
        }

        $issueDate  = $meta['issue_date']  ?? date('Y-m-d');
        $secondDate = $type === 'quote'
            ? ($meta['valid_until'] ?? null)
            : ($meta['due_date']    ?? null);
        $dateColumn = $type === 'quote' ? 'valid_until' : 'due_date';

        $payload   = json_encode($document, JSON_UNESCAPED_UNICODE);
        $totalHt   = (float) ($totals['subtotal']    ?? 0);
        $totalVat  = (float) ($totals['vat']         ?? 0);
        $totalTtc  = (float) ($totals['grand_total'] ?? 0);
        $currency  = $meta['currency'] ?? 'EUR';

        // Always store totals in the base accounting currency (EUR) for consistent
        // history reporting. If the document is in a foreign currency, convert back.
        $fxRate       = max(0.000001, (float) ($meta['fx_rate'] ?? 1));
        $baseCurrency = $meta['fx_base_currency'] ?? 'EUR';
        if ($currency !== $baseCurrency && $fxRate > 0 && $fxRate !== 1.0) {
            $totalHt  = round($totalHt  / $fxRate, 2);
            $totalVat = round($totalVat / $fxRate, 2);
            $totalTtc = round($totalTtc / $fxRate, 2);
        }

        try {
            $sql = "
                INSERT INTO `{$table}`
                    (`number`, `issue_date`, `{$dateColumn}`, `customer`,
                     `total_ht`, `total_vat`, `total_ttc`, `currency`, `payload`)
                VALUES
                    (:number, :issue_date, :secondary_date, :customer,
                     :total_ht, :total_vat, :total_ttc, :currency, :payload)
                ON DUPLICATE KEY UPDATE
                    `issue_date`      = VALUES(`issue_date`),
                    `{$dateColumn}`   = VALUES(`{$dateColumn}`),
                    `customer`        = VALUES(`customer`),
                    `total_ht`        = VALUES(`total_ht`),
                    `total_vat`       = VALUES(`total_vat`),
                    `total_ttc`       = VALUES(`total_ttc`),
                    `currency`        = VALUES(`currency`),
                    `payload`         = VALUES(`payload`),
                    `updated_at`      = CURRENT_TIMESTAMP
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':number'         => $number,
                ':issue_date'     => $issueDate,
                ':secondary_date' => $secondDate,
                ':customer'       => $customer,
                ':total_ht'       => $totalHt,
                ':total_vat'      => $totalVat,
                ':total_ttc'      => $totalTtc,
                ':currency'       => $currency,
                ':payload'        => $payload,
            ]);

            return (int) $db->lastInsertId() ?: self::findIdByNumber($type, $number);
        } catch (PDOException $e) {
            error_log('DocumentRepository::save failed: ' . $e->getMessage());
            return false;
        }
    }

    // ------------------------------------------------------------------
    // List (for history page)
    // ------------------------------------------------------------------

    public static function list(string $type, int $limit = 200, int $offset = 0): array
    {
        $db = get_db();
        if ($db === null) {
            return [];
        }

        $table      = $type === 'quote' ? 'quotes' : 'invoices';
        $dateColumn = $type === 'quote' ? 'valid_until' : 'due_date';

        try {
            $stmt = $db->prepare("
                SELECT `id`, `number`, `issue_date`, `{$dateColumn}` AS secondary_date,
                       `customer`, `total_ht`, `total_vat`, `total_ttc`,
                       `currency`, `created_at`, `updated_at`
                FROM   `{$table}`
                ORDER  BY `issue_date` DESC, `id` DESC
                LIMIT  :limit OFFSET :offset
            ");
            $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('DocumentRepository::list failed: ' . $e->getMessage());
            return [];
        }
    }

    // ------------------------------------------------------------------
    // Load single document by id (returns decoded payload array)
    // ------------------------------------------------------------------

    public static function load(string $type, int $id): ?array
    {
        $db = get_db();
        if ($db === null) {
            return null;
        }

        $table = $type === 'quote' ? 'quotes' : 'invoices';

        try {
            $stmt = $db->prepare("SELECT `payload` FROM `{$table}` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();

            if (!$row) {
                return null;
            }

            $doc = json_decode($row['payload'], true);
            return is_array($doc) ? $doc : null;
        } catch (PDOException $e) {
            error_log('DocumentRepository::load failed: ' . $e->getMessage());
            return null;
        }
    }

    // ------------------------------------------------------------------
    // Delete
    // ------------------------------------------------------------------

    public static function delete(string $type, int $id): bool
    {
        $db = get_db();
        if ($db === null) {
            return false;
        }

        $table = $type === 'quote' ? 'quotes' : 'invoices';

        try {
            $stmt = $db->prepare("DELETE FROM `{$table}` WHERE `id` = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('DocumentRepository::delete failed: ' . $e->getMessage());
            return false;
        }
    }

    // ------------------------------------------------------------------
    // Internal helpers
    // ------------------------------------------------------------------

    private static function findIdByNumber(string $type, string $number): int
    {
        $db    = get_db();
        $table = $type === 'quote' ? 'quotes' : 'invoices';
        $stmt  = $db->prepare("SELECT `id` FROM `{$table}` WHERE `number` = :n LIMIT 1");
        $stmt->execute([':n' => $number]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }
}
