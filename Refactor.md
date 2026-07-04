# Document-Generator — Refactor Plan

## Executive Summary

The app is well-structured at the `src/` level (Builder, Calculator, Repository, Validator are already clean single-responsibility classes), but the **public-facing pages mix controller logic, HTML, and JavaScript all in one file**. The two biggest offenders are `public/form.php` (844 lines) and `public/history.php` (290 lines), and the template `templates/document.php` (344 lines) also mixes PHP setup with a full HTML document. `src/Helpers/helpers.php` has a secondary issue: duplicate logic that already lives in classes.

The goal is a clean **Controller → View → Partial** split, a dedicated JS module layer, and a small tidy-up of the `src/` layer.

---

## Problems Identified

### 1. `public/form.php` — 844 lines, everything in one file

**What's in there:**
- ~60 lines of PHP controller logic (session reading, date defaults, state hydration)
- A full Bootstrap HTML page (~300 lines)
- An inline `<script>` block with ~350 lines of JavaScript across 4 concerns:
  - JSON import/export
  - Items table (add row, remove row, template cloning)
  - Totals live preview recalculation
  - Product picker modal + API fetch

**Problems:**
- Impossible to unit-test the JS
- Any designer touching the form HTML has to scroll past hundreds of lines of JS
- The PHP setup block duplicates logic with `DocumentBuilder` (date defaults, currency defaults)

---

### 2. `public/history.php` — 290 lines, mixed controller + view

**What's in there:**
- ~40 lines of PHP controller (action dispatch: view, delete, load lists)
- Two inline helper functions (`fmtMoney`, `fmtDate`) that belong in a shared view-helper
- A full HTML page with two tabs (invoices, quotes) — near-identical markup repeated twice
- A small inline `<script>` for the delete confirmation modal

**Problems:**
- The two table blocks (invoices, quotes) are copy-pasted; a change to one column must be applied twice
- Controller logic (redirect after delete, session manipulation) is tangled with HTML output

---

### 3. `templates/document.php` — 344 lines, setup + full HTML page

**What's in there:**
- ~45 lines of PHP variable extraction / setup at the top
- A full A4-layout HTML page with header, items table, totals, legal section, notes, acceptance block
- An inline toolbar `<script>` and print logic

**Problems:**
- No partials — the header, items table, totals block, and legal section are all one blob
- Hard to reuse individual sections (e.g. for a future email renderer)

---

### 4. `src/Helpers/helpers.php` — duplicate logic

**Problems:**
- `normalize_item_rows()` and `filter_document_items()` are free-function duplicates of `DocumentBuilder::normalizeItemRows()` / `DocumentBuilder::filterDocumentItems()` (the private methods). Logic is in two places.
- `build_document_from_post()` is a thin wrapper around `DocumentBuilder::fromPost()` — it adds `show_toolbar: true`, which is view concern, not builder concern.
- `calculate_line_total()` and `calculate_totals()` are aliases for `DocumentCalculator` methods — fine as aliases, but worth a note.
- `money()` and `format_money()` are the same function under two names.

---

## Proposed Structure After Refactor

```
public/
  form.php              ← controller only (~40 lines)
  history.php           ← controller only (~40 lines)
  generate.php          ← unchanged (already clean)
  preview.php           ← unchanged
  index.php             ← unchanged
  assets/
    js/
      form-items.js     ← NEW: item row add/remove/template
      form-totals.js    ← NEW: live totals preview
      form-json.js      ← NEW: JSON import/export
      product-picker.js ← NEW: modal + API fetch
      history.js        ← NEW: delete-confirm modal wiring
      currency.js       ← NEW: currency select / FX block

views/
  form/
    page.php            ← NEW: HTML shell for the form page
    _customer.php       ← NEW: partial — customer card
    _document-info.php  ← NEW: partial — document info card
    _items.php          ← NEW: partial — items table + template row
    _notes.php          ← NEW: partial — notes + terms + acceptance
    _sidebar.php        ← NEW: partial — totals preview + submit button
  history/
    page.php            ← NEW: HTML shell for history page
    _table.php          ← NEW: partial — reusable doc table (used for both tabs)
  layouts/
    app.php             ← NEW: shared <head>, Bootstrap CDN, nav shell
    document.php        ← NEW: shared <head> for the A4 preview

templates/
  document.php          ← slimmed down: only variable setup + include partials
  partials/
    _header.php         ← NEW: company + customer block
    _items-table.php    ← NEW: line items table
    _totals.php         ← NEW: subtotal / VAT / grand total block
    _legal.php          ← NEW: VAT mention, late payment, terms
    _notes.php          ← NEW: public notes + acceptance
    _toolbar.php        ← NEW: print toolbar (no-print)

src/
  Helpers/
    helpers.php         ← remove duplicates; keep only true aliases
    ViewHelpers.php     ← NEW: fmtDate(), fmtMoney() — view-layer functions
  Builder/
    DocumentBuilder.php ← make normalizeItemRows/filterDocumentItems public or move to own class
```

---

## Refactor Steps — Ordered by Impact

### Step 1 — Extract JS from `form.php` (highest ROI)

Split the inline `<script>` blocks into four files under `public/assets/js/`:

| New file | Responsibility |
|---|---|
| `form-items.js` | `addItemRow()`, remove-row delegation, `addItemButton` handler |
| `form-totals.js` | `updateFormTotals()`, change listener on item fields |
| `form-json.js` | `gatherFormJson()`, `validateFormJson()`, `downloadJson()`, `importFormData()`, save/import button handlers |
| `product-picker.js` | `loadProducts()`, `renderProductRows()`, modal show, search debounce, add-to-table click |
| `currency.js` | `onCurrencyChange()`, symbol/FX-block toggle |

Each file should export nothing (IIFE or just scoped functions called via `DOMContentLoaded`). They can share a tiny `window.FormApp = {}` namespace for the cross-file calls (`updateFormTotals`, `addItemRow`, `_setCurrencySymbol`).

**Result:** `form.php` loses ~400 lines. The JS is independently testable and editable.

---

### Step 2 — Extract HTML partials from `form.php`

Move each Bootstrap card into a partial under `views/form/`:

```php
// In form.php (after refactor):
require __DIR__ . '/../views/form/page.php';

// In views/form/page.php:
include __DIR__ . '/../../views/layouts/app.php';  // <head> + open <body>
include __DIR__ . '/_customer.php';
include __DIR__ . '/_document-info.php';
include __DIR__ . '/_items.php';
include __DIR__ . '/_notes.php';
include __DIR__ . '/_sidebar.php';
```

Each partial receives variables via the already-existing `$customer`, `$meta`, `$notes`, etc. — no parameter passing needed beyond what the controller already sets.

**Result:** Each partial is 40–80 lines of pure HTML, easy to modify in isolation.

---

### Step 3 — Extract controller logic from `form.php`

Move the ~60-line PHP setup block into a dedicated controller:

```
src/Controllers/FormController.php
```

```php
class FormController {
    public static function getFormState(array $company): array {
        // Returns: type, state, errors, meta, customer, items, notes, terms, currency vars
    }
}
```

`public/form.php` then becomes:

```php
<?php
require_once __DIR__ . '/../bootstrap.php';
$company = require __DIR__ . '/../config/company.php';
$viewData = FormController::getFormState($company);
extract($viewData);
require __DIR__ . '/../views/form/page.php';
```

---

### Step 4 — Refactor `history.php`

**4a — Extract the reusable table partial**

Both the invoices tab and quotes tab render the same table structure. Replace both with a single partial `views/history/_table.php` that accepts `$rows`, `$type`, `$dateLabel`, and `$currencySymbol`.

**4b — Move `fmtDate()` and `fmtMoney()` to `ViewHelpers.php`**

Create `src/Helpers/ViewHelpers.php` and move these there (and any future view-formatting functions).

**4c — Extract controller**

```
src/Controllers/HistoryController.php
```

Handles the `view` / `delete` action dispatch, loads lists. `public/history.php` becomes a ~15-line file.

---

### Step 5 — Extract partials from `templates/document.php`

Create `templates/partials/`:

| Partial | Lines extracted | What it contains |
|---|---|---|
| `_toolbar.php` | ~20 | Print toolbar, JS print handler |
| `_header.php` | ~40 | Company block + customer block side-by-side |
| `_items-table.php` | ~60 | `<table>` with line items, line totals |
| `_totals.php` | ~30 | Subtotal / VAT / grand total / FX row |
| `_legal.php` | ~30 | VAT mention, late payment text, terms |
| `_notes.php` | ~20 | Public notes + acceptance block |

`templates/document.php` keeps only the variable setup (~45 lines) and becomes a clean include chain.

---

### Step 6 — Clean up `src/Helpers/helpers.php`

| Action | Detail |
|---|---|
| Remove `normalize_item_rows()` | Duplicate of `DocumentBuilder::normalizeItemRows()` — make that method `public static` and call it directly |
| Remove `filter_document_items()` | Same — duplicate of `DocumentBuilder::filterDocumentItems()` |
| Remove `build_document_from_post()` | Setting `show_toolbar: true` is a controller concern; do it in the controller |
| Remove `format_money()` | Alias for `money()` — pick one name and use it everywhere |

---

## File Size Targets After Refactor

| File | Before | Target |
|---|---|---|
| `public/form.php` | 844 lines | ~20 lines (bootstrap + extract + include) |
| `views/form/page.php` | — | ~50 lines (HTML shell only) |
| `views/form/_customer.php` | — | ~30 lines |
| `views/form/_document-info.php` | — | ~60 lines |
| `views/form/_items.php` | — | ~50 lines |
| `views/form/_notes.php` | — | ~40 lines |
| `views/form/_sidebar.php` | — | ~20 lines |
| `public/assets/js/form-items.js` | — | ~60 lines |
| `public/assets/js/form-totals.js` | — | ~40 lines |
| `public/assets/js/form-json.js` | — | ~100 lines |
| `public/assets/js/product-picker.js` | — | ~80 lines |
| `public/history.php` | 290 lines | ~15 lines |
| `views/history/page.php` | — | ~50 lines |
| `views/history/_table.php` | — | ~40 lines (used twice) |
| `templates/document.php` | 344 lines | ~60 lines (setup + includes) |
| Each template partial | — | 20–60 lines |
| `src/Helpers/helpers.php` | 139 lines | ~70 lines |

---

## What Stays the Same

These are already well-structured and need no changes:

- `src/Builder/DocumentBuilder.php` — clean, single responsibility (just make the two private methods public)
- `src/Calculator/DocumentCalculator.php` — clean
- `src/Repository/DocumentRepository.php` — clean
- `src/Validation/DocumentValidator.php` — clean
- `src/Logger/Logger.php` — clean
- `src/Database/db.php` — clean
- `bootstrap.php` — clean
- All `config/` files — clean
- `public/generate.php` — already a thin controller, no changes needed
- `public/api/products.php` — fine as-is

---

## Suggested Implementation Order

1. **JS extraction** (Step 1) — zero PHP changes, zero risk, immediate win
2. **HTML partials for `form.php`** (Step 2) — pure extraction, same output
3. **HTML partials for `templates/document.php`** (Step 5) — same output
4. **History refactor** (Step 4) — small file, quick win
5. **Controller extraction** (Steps 3 + 4c) — last because it's the only structural PHP change
6. **Helpers cleanup** (Step 6) — do last to avoid breaking anything mid-refactor