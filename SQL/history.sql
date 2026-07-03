-- ============================================================
-- Document Generator — History tables
-- Run once against your `document_generator` database
-- ============================================================

CREATE TABLE IF NOT EXISTS `invoices` (
  `id`           INT          NOT NULL AUTO_INCREMENT,
  `number`       VARCHAR(80)  NOT NULL COMMENT 'Invoice number (e.g. INV-2025-001)',
  `issue_date`   DATE         NOT NULL,
  `due_date`     DATE         DEFAULT NULL,
  `customer`     VARCHAR(255) NOT NULL COMMENT 'Customer name or company',
  `total_ht`     DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'Subtotal excl. VAT',
  `total_vat`    DECIMAL(12,2) NOT NULL DEFAULT 0,
  `total_ttc`    DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'Grand total incl. VAT',
  `currency`     VARCHAR(10)  NOT NULL DEFAULT 'EUR',
  `payload`      LONGTEXT     NOT NULL COMMENT 'Full document JSON for regeneration',
  `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invoice_number` (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `quotes` (
  `id`           INT          NOT NULL AUTO_INCREMENT,
  `number`       VARCHAR(80)  NOT NULL COMMENT 'Quote number (e.g. QUO-2025-001)',
  `issue_date`   DATE         NOT NULL,
  `valid_until`  DATE         DEFAULT NULL,
  `customer`     VARCHAR(255) NOT NULL COMMENT 'Customer name or company',
  `total_ht`     DECIMAL(12,2) NOT NULL DEFAULT 0,
  `total_vat`    DECIMAL(12,2) NOT NULL DEFAULT 0,
  `total_ttc`    DECIMAL(12,2) NOT NULL DEFAULT 0,
  `currency`     VARCHAR(10)  NOT NULL DEFAULT 'EUR',
  `payload`      LONGTEXT     NOT NULL COMMENT 'Full document JSON for regeneration',
  `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_quote_number` (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
