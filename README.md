# Document Generator

A lightweight PHP web application for generating print-ready A4 invoices and quotes. Built around a reusable Bootstrap 5 form UI, a shared HTML/CSS document template, and an optional MySQL database for history and persistence.

---

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Project Structure](#project-structure)
- [Usage](#usage)
- [JSON Import / Export](#json-import--export)
- [Architecture](#architecture)
- [API Reference](#api-reference)
- [Currency & FX Handling](#currency--fx-handling)
- [VAT & Tax Handling](#vat--tax-handling)
- [Document History](#document-history)
- [Extending the App](#extending-the-app)

---

## Features

- **Invoice and quote generation** from a single shared template
- **Bootstrap 5 form UI** with live line-total calculations and dynamic item rows
- **Print-ready A4 document rendering** — fill the form, click Print, save as PDF from the browser
- **JSON import** — upload a structured JSON file to prefill the form in one click
- **JSON export** — save the current form state as a JSON file for later reuse
- **Multi-currency support** — select any configured currency with an optional FX conversion rate; totals stored in the base accounting currency
- **Per-line VAT rates** with company-level defaults
- **Per-line discounts**
- **Product picker** — searchable product catalogue backed by the database
- **Document history** — saved invoices and quotes listed in a sortable table with view and delete actions
- **Graceful DB degradation** — the form and document rendering work without a database connection; only history and product lookup require one
- **Session-based form state** — validation errors return the user to the form with all fields pre-populated
- **Legal mentions** — configurable late-payment penalties, VAT exemption text, and acceptance block for quotes

---

## Requirements

- PHP 8.1 or later
- A local web server (Apache, Nginx, or PHP's built-in server)
- MySQL 5.7+ or MariaDB 10.3+ (optional — needed for history and product lookup)
- Composer (optional — used only for autoloading; the app also works with manual `require_once`)

---

## Installation

**1. Clone or unzip the project into your web server's document root (or a virtual host directory).**

```bash
# Example using PHP's built-in server from the project root
php -S localhost:8080
```

Then open `http://localhost:8080` in your browser. The root `index.php` redirects straight to the dashboard.

**2. (Optional) Install Composer dependencies:**

```bash
composer install
```

The `composer.json` registers a classmap autoloader for `src/`. If you skip Composer, the existing `require_once` calls in `bootstrap.php` and the public pages load all classes manually.

**3. Point your web server's document root to the project root,** not to `public/`. The `public/` subdirectory is browser-accessible; `src/`, `config/`, and `templates/` are application-internal.

---

## Configuration

All configuration lives in the `config/` directory. Edit these files to match your organisation.

### `config/company.php`

The primary settings file. Contains:

| Key | Description |
|-----|-------------|
| `name` | Company display name |
| `logo` | Path to logo image (relative to `public/assets/`) |
| `legal_form`, `share_capital` | Legal identity shown on documents |
| `street`, `city`, `zip`, `country` | Registered address |
| `siren`, `siret`, `vat_number`, `eori` | Official identifiers |
| `email`, `website` | Contact details |
| `default_currency`, `default_currency_symbol` | Base accounting currency |
| `default_vat_rate` | Default VAT rate applied to new line items (0–100) |
| `default_invoice_due_days` | Days added to issue date for invoice due date |
| `default_quote_valid_days` | Days added to issue date for quote validity |
| `default_payment_method` | Pre-filled payment method on new documents |
| `vat_mention` | Legal VAT exemption text printed on documents |
| `late_payment_rate`, `late_payment_flat_fee` | Penalty details |
| `late_payment_text`, `late_payment_fee_text`, `terms_text` | Footer legal copy |

### `config/bank.php`

Bank account details (beneficiary, IBAN, BIC, etc.) printed on invoice payment blocks.

### `config/currencies.php`

Defines the currencies available in the currency selector. Add or remove entries as needed:

```php
'USD' => ['symbol' => '$', 'name' => 'US Dollar'],
```

### `config/taxes.php`

Named VAT rate presets available in the form:

```php
'FR_STANDARD' => 20,
'FR_REDUCED'  => 10,
'EXPORT'      => 0,
```

### `config/settings.php`

Application-level settings:

| Key | Default | Description |
|-----|---------|-------------|
| `language` | `en` | UI language (display only for now) |
| `currency` | `EUR` | Fallback currency |
| `timezone` | `Europe/Paris` | PHP timezone set on bootstrap |
| `date_format` | `d/m/Y` | Display date format |
| `theme` | `default` | Reserved for future theming |

### `config/database.php`

MySQL connection settings. Update `host`, `dbname`, `username`, and `password` for your environment.

---

## Database Setup

The database is optional. Run the two SQL scripts in `SQL/` once against your database to create the required tables.

```bash
mysql -u root -p document_generator < SQL/history.sql
mysql -u root -p document_generator < SQL/products.sql
```

### `SQL/history.sql` — creates two tables:

**`invoices`** — one row per saved invoice, with columns: `id`, `number` (unique), `issue_date`, `due_date`, `customer`, `total_ht`, `total_vat`, `total_ttc`, `currency`, `payload` (full JSON), `created_at`, `updated_at`.

**`quotes`** — identical structure with `valid_until` instead of `due_date`.

Documents are upserted on `number`, so re-generating an existing invoice number updates rather than duplicates the record.

### `SQL/products.sql` — creates the `products` table:

Columns: `ID`, `reference`, `name`, `description`, `product_unit`, `price`, `page_url`, `updated_on`. A sample dataset of scientific assay kit products is included as seed data.

---

## Project Structure

```
Document-Generator/
├── index.php                  # Root launcher — redirects to public/index.php
├── bootstrap.php              # Loads all classes, config, and sets timezone
├── composer.json              # Classmap autoloader for src/
│
├── config/
│   ├── company.php            # Company identity, accounting defaults, legal text
│   ├── bank.php               # Bank / payment details
│   ├── currencies.php         # Available currencies
│   ├── database.php           # MySQL connection settings
│   ├── document_types.php     # Supported document type slugs
│   ├── settings.php           # App-level settings (timezone, date format)
│   └── taxes.php              # Named VAT rate presets
│
├── public/                    # Browser-accessible files
│   ├── index.php              # Dashboard (links to invoice / quote form)
│   ├── form.php               # Shared Bootstrap form for invoice and quote
│   ├── generate.php           # POST handler — validates, builds, saves, redirects
│   ├── preview.php            # Loads document from session and renders it
│   ├── invoice.php            # Thin redirect → form.php?type=invoice
│   ├── quote.php              # Thin redirect → form.php?type=quote
│   ├── history.php            # Saved document list with view / delete actions
│   ├── invoice.json           # Sample invoice JSON for import
│   ├── quote.json             # Sample quote JSON for import
│   ├── assets/
│   │   └── css/app.css        # Dashboard and form styles (Bootstrap overrides)
│   └── api/
│       └── products.php       # JSON endpoint for product picker autocomplete
│
├── src/
│   ├── Builder/
│   │   └── DocumentBuilder.php      # Assembles the document array from POST data
│   ├── Calculator/
│   │   └── DocumentCalculator.php   # Line totals, subtotal, VAT, grand total
│   ├── Database/
│   │   └── db.php                   # PDO singleton via get_db()
│   ├── Helpers/
│   │   └── helpers.php              # h(), sanitize_input(), normalize_number(), money(), etc.
│   ├── Renderer/
│   │   └── DocumentRenderer.php     # Includes the document template
│   ├── Repository/
│   │   └── DocumentRepository.php   # save(), list(), load(), delete()
│   └── Validation/
│       └── DocumentValidator.php    # Returns an array of validation error strings
│
├── templates/
│   ├── document.php           # Print-ready A4 HTML template
│   └── document.css           # Document-specific print stylesheet
│
├── data/
│   └── products.json          # Static product catalogue (fallback / seed data)
│
├── SQL/
│   ├── history.sql            # Creates invoices and quotes tables
│   └── products.sql           # Creates products table with sample data
│
├── storage/                   # Reserved for generated output (not yet used by code)
│   ├── drafts/
│   ├── exports/
│   ├── invoices/
│   ├── pdf/
│   └── quotes/
│
├── INVOICE.json               # Root-level sample invoice (for reference / testing)
└── QUOTE.json                 # Root-level sample quote (for reference / testing)
```

---

## Usage

### 1. Open the Dashboard

Navigate to your server root. The dashboard shows two action cards: **Create Invoice** and **Create Quote**, and a link to **View History**.

### 2. Fill the Form

Both document types use the same `public/form.php` (the `type` query parameter switches between them). The form is divided into sections:

- **Document metadata** — number, issue date, due date (invoice) or valid-until date (quote), reference, payment method and terms, currency
- **Customer** — name, company, department, address, email, VAT number
- **Line items** — reference, description, unit, quantity, unit price, discount, VAT rate. Rows can be added dynamically; totals update in real time via JavaScript
- **Notes** — public notes (printed on the document) and internal notes (not printed)
- **Legal** — VAT mention override; optional late-payment clause toggle (invoice only)
- **Acceptance** — signature block shown on quotes

### 3. Generate

Clicking **Generate Document** posts to `generate.php`, which:

1. Sanitises and validates all input via `DocumentBuilder` and `DocumentValidator`
2. On validation failure, stores errors and form state in the session and redirects back to `form.php`
3. On success, stores the document array in `$_SESSION['document_preview']`, saves it to the database (if available), and redirects to `preview.php`

### 4. Print / Save as PDF

The preview page renders the A4 document template with a print toolbar. Use the browser's **Print** dialog and select **Save as PDF**. The document stylesheet (`templates/document.css`) is optimised for A4 with `@media print` rules.

---

## JSON Import / Export

### Import

Click **Upload JSON** on the form page to select a `.json` file. The file is read client-side and used to prefill all form fields without a server round-trip.

**Expected top-level keys:**

```json
{
  "type": "invoice",
  "customer": { ... },
  "meta": { ... },
  "items": [ ... ],
  "notes": { "public": "", "internal": "" },
  "acceptance": { "enabled": false, "text": "" }
}
```

See [`public/invoice.json`](public/invoice.json) and [`public/quote.json`](public/quote.json) for full examples. The root-level [`INVOICE.json`](INVOICE.json) and [`QUOTE.json`](QUOTE.json) are additional reference files showing a real-world multi-currency export scenario.

**`customer` fields:**

| Field | Description |
|-------|-------------|
| `name` | Contact person name |
| `company` | Organisation name |
| `department` | Department / faculty |
| `street`, `city`, `zip`, `country` | Billing address |
| `email`, `phone` | Contact |
| `vat_number` | Customer VAT / tax ID |

**`meta` fields:**

| Field | Description |
|-------|-------------|
| `number` | Document number (e.g. `INV-2026-001`) |
| `issue_date` | ISO date (`YYYY-MM-DD`) |
| `due_date` | Invoice only |
| `valid_until` | Quote only |
| `reference` | Purchase order or internal reference |
| `payment_method` | e.g. `Bank Transfer (Wire)` |
| `payment_terms` | e.g. `100% Advance Payment` |
| `currency`, `currency_symbol` | e.g. `USD`, `$` |
| `fx_rate` | Units of foreign currency per 1 base currency unit |
| `vat_mention` | VAT exemption text (overrides company default) |
| `incoterms` | Shipping terms (printed in notes area) |
| `hsn_code` | Harmonised system code for customs |

**`items` array — each object:**

| Field | Description |
|-------|-------------|
| `reference` | Product SKU or catalogue reference |
| `description` | Line item description (required) |
| `product_unit` | Packaging or unit description (e.g. `1 plate, 96 assays`) |
| `quantity` | Numeric |
| `unit` | Unit label (e.g. `pcs`, `service`) |
| `unit_price` | Numeric, in the document currency |
| `discount` | Flat discount amount (not percentage) |
| `vat_rate` | VAT percentage for this line (0–100) |

### Export

Click **Save JSON** on the form page to download the current form state as a `.json` file. The file can be re-uploaded later to restore the full form.

---

## Architecture

The application follows a simple request-cycle pattern without a framework:

```
Browser
  │
  ├── GET  /public/form.php?type=invoice   ← Bootstrap form
  │
  └── POST /public/generate.php
            │
            ├── DocumentBuilder::fromPost()   ← sanitise & assemble document array
            ├── DocumentValidator::validate() ← return errors or proceed
            ├── DocumentRepository::save()    ← upsert to DB (if available)
            └── redirect → /public/preview.php
                              │
                              └── DocumentRenderer::render() ← include templates/document.php
```

### Key classes

**`DocumentBuilder`** (`src/Builder/DocumentBuilder.php`)

Static factory. Accepts the raw `$_POST` array, the company config array, and the document type string. Returns a normalised document array with keys: `type`, `company`, `customer`, `items`, `metadata`, `payment`, `shipping`, `totals`, `legal`, `notes`, `acceptance`, `show_toolbar`.

Handles: input sanitisation, FX rate normalisation, default date calculation, item filtering (blank rows are dropped).

**`DocumentCalculator`** (`src/Calculator/DocumentCalculator.php`)

Static utility. Two methods:
- `calculateLineTotal(array $item): float` — `(qty × unit_price) - discount`
- `calculateTotals(array $items, $defaultVatRate): array` — returns `subtotal`, `vat`, `grand_total`

**`DocumentValidator`** (`src/Validation/DocumentValidator.php`)

Static. Validates the assembled document array (not raw POST). Returns an array of human-readable error strings. Rules: number required, issue date required, due date required for invoices, valid_until required for quotes, customer name and street required, at least one item required, each item must have a description.

**`DocumentRepository`** (`src/Repository/DocumentRepository.php`)

Static. Uses the PDO singleton from `get_db()`. Methods:
- `save(array $document): int|false` — INSERT … ON DUPLICATE KEY UPDATE; always stores totals in the base accounting currency
- `list(string $type, int $limit, int $offset): array` — returns summary rows for the history table
- `load(string $type, int $id): ?array` — returns the full decoded document payload
- `delete(string $type, int $id): bool`

**`DocumentRenderer`** (`src/Renderer/DocumentRenderer.php`)

Thin wrapper. Calls `include` on `templates/document.php` with `$document` in scope.

**`helpers.php`** (`src/Helpers/helpers.php`)

Global utility functions:
- `h($value)` — HTML-escapes a value for safe output
- `sanitize_input($value)` — trims strings recursively through arrays
- `normalize_number($value)` — parses European and US number formats to `float`
- `money($amount, $symbol)` — formats a float as a currency string
- `add_days_to_date($date, $days)` — adds N days to a `Y-m-d` string
- `document_title($type)` / `document_label($type)` — returns human-readable type labels
- `build_document_from_post(...)` — thin wrapper around `DocumentBuilder::fromPost` that sets `show_toolbar = true`

---

## API Reference

### `GET /public/api/products.php`

Returns a JSON array of products for the in-form product picker autocomplete.

**Query parameters:**

| Parameter | Description |
|-----------|-------------|
| `q` | Optional search term. Searches `reference` and `title` columns with `LIKE`. Omit for the full list (up to 200 results). |

**Response (200):**

```json
[
  {
    "ID": 7,
    "reference": "K0507-02",
    "title": "ADK Phosphorylation Assay Kit",
    "size": null,
    "price": 530
  }
]
```

**Error responses:**

- `503` — database unavailable
- `500` — query failed

---

## Currency & FX Handling

The application is designed for a company that invoices in its own accounting currency (e.g. EUR) but occasionally issues documents in a foreign currency on request.

- The **base currency** is set in `config/company.php` (`default_currency`).
- On the form, a currency selector lets the user pick any currency from `config/currencies.php`.
- When a foreign currency is selected, an **FX rate** field appears (`fx_rate` = units of foreign currency per 1 base currency unit, e.g. `90` means 1 EUR = 90 INR).
- All **line-item amounts are entered in the foreign currency**.
- The `DocumentBuilder` stores both the foreign-currency amounts and the FX rate in the document array.
- `DocumentRepository::save()` converts totals back to the base currency before writing to the database, so history totals are always comparable.
- The rendered document shows amounts in the selected foreign currency.

---

## VAT & Tax Handling

- The company default VAT rate (`default_vat_rate` in `config/company.php`) is applied to new line items.
- Each line item can override its own VAT rate independently.
- Named VAT presets are defined in `config/taxes.php` (e.g. `FR_STANDARD` = 20%, `EXPORT` = 0%).
- A VAT mention text (e.g. the French export exemption article) is configurable per company and can be overridden per document.
- `DocumentCalculator` accumulates VAT per line and rounds the totals to 2 decimal places.

---

## Document History

The history page (`public/history.php`) lists all saved invoices and quotes from the database in two separate tabs, sorted by issue date descending.

- **View** — loads the stored JSON payload back into the session and redirects to the preview page, regenerating the document exactly as it was saved.
- **Delete** — sends a POST request to the same page with `action=delete`; the row is removed from the database.
- If the database is unavailable, the history page displays a notice and the tables are empty; no error is thrown.

---

## Extending the App

### Adding a new document type

1. Add the slug to `config/document_types.php` (e.g. `'credit_note'`).
2. Update `DocumentBuilder::fromPost()` and `DocumentValidator::validate()` to handle type-specific fields.
3. Add a new entry page (e.g. `public/credit_note.php`) that redirects to `form.php?type=credit_note`.
4. Adjust `templates/document.php` to render type-specific sections conditionally.

### Adding a new currency

Add an entry to `config/currencies.php`:

```php
'CAD' => ['symbol' => 'CA$', 'name' => 'Canadian Dollar'],
```

### Adding a new tax preset

Add an entry to `config/taxes.php`:

```php
'DE_STANDARD' => 19,
```

### Replacing the classmap autoloader with PSR-4

Update `composer.json`:

```json
"autoload": {
  "psr-4": {
    "DocumentGenerator\\": "src/"
  }
}
```

Then namespace all classes accordingly and run `composer dump-autoload`.

### Storing generated PDFs

The `storage/` directory tree is reserved for file output. To save PDFs server-side, integrate a headless Chrome or wkhtmltopdf call in `generate.php` after the document is built, and write the output to `storage/pdf/`.