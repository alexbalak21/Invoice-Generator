<?php

class HistoryController
{
    /**
     * Handle action dispatch (view / delete) and return view data.
     *
     * Exits (redirect) when an action causes a redirect.
     *
     * @param  array $company
     * @return array  Variables for extract() into the view.
     */
    public static function handle(array $company): array
    {
        $action = $_GET['action'] ?? '';
        $allTypes = array_keys(require __DIR__ . '/../../config/document_types.php');
        $type   = in_array($_GET['type'] ?? '', $allTypes, true) ? $_GET['type'] : 'invoice';
        $id     = (int) ($_GET['id'] ?? 0);

        $flashError = null;

        if ($action !== '') {
            Logger::info('history — action requested', [
                'action' => $action,
                'type'   => $type,
                'id'     => $id,
                'ip'     => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ]);
        }

        // Regenerate: load payload → push to session → redirect to preview
        if ($action === 'view' && $id > 0) {
            $doc = DocumentRepository::load($type, $id);
            if ($doc) {
                $doc['show_toolbar'] = true;
                $_SESSION['document_preview'] = $doc;
                Logger::info('history — document loaded for preview', ['type' => $type, 'id' => $id]);
                header('Location: preview.php');
                exit;
            }
            Logger::warning('history — document not found for view', ['type' => $type, 'id' => $id]);
            $flashError = 'Document not found.';
        }

        // Delete
        if ($action === 'delete' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $deleted = DocumentRepository::delete($type, $id);
            if (!$deleted) {
                Logger::warning('history — delete had no effect', ['type' => $type, 'id' => $id]);
            }
            header('Location: history.php?deleted=1');
            exit;
        }

        // Load lists
        $db          = get_db();
        $dbAvailable = $db !== null;

        if (!$dbAvailable) {
            Logger::warning('history — DB unavailable, rendering empty history');
        }

        $invoices       = $dbAvailable ? DocumentRepository::list('invoice') : [];
        $quotes         = $dbAvailable ? DocumentRepository::list('quote')   : [];
        $currencySymbol = $company['default_currency_symbol'] ?? '€';

        return compact('dbAvailable', 'flashError', 'invoices', 'quotes', 'currencySymbol');
    }
}
