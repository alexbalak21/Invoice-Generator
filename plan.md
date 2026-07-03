GOAL
-----
Build a working PHP prototype that generates:
- Invoices
- Quotes

From a Bootstrap form and renders printable A4 HTML (PDF via browser print).

No framework. No database. No classes.

------------------------------------------------------------
PHASE 1 — PROJECT SETUP
------------------------------------------------------------

Create folder structure:

Invoice-Generator/
│
├── config/
│   └── company.php
│
├── templates/
│   ├── invoice.php
│   ├── quote.php
│   └── style.css
│
├── index.php
├── form.php
├── generate.php
├── helpers.php
│
├── img/
│   └── logo.png
│
└── SQL/

------------------------------------------------------------
PHASE 2 — COMPANY CONFIG
------------------------------------------------------------

Create config/company.php

Store:

- name
- logo
- address
- email
- phone
- VAT number
- SIRET
- IBAN
- BIC

RULE:
✔ Read-only
✔ Never editable in form
✔ Always injected into document

------------------------------------------------------------
PHASE 3 — HELPERS (helpers.php)
------------------------------------------------------------

Create pure PHP functions:

- calculateSubtotal(items)
- calculateVAT(items)
- calculateTotal(items)
- formatMoney(value)
- sanitize(input)

RULE:
✔ No HTML
✔ No Bootstrap
✔ No templates logic

------------------------------------------------------------
PHASE 4 — INDEX DASHBOARD (index.php)
------------------------------------------------------------

Create simple Bootstrap page:

Buttons:

- "Create Invoice"
- "Create Quote"

Each button redirects to:

form.php?type=invoice
form.php?type=quote

------------------------------------------------------------
PHASE 5 — BOOTSTRAP FORM (form.php)
------------------------------------------------------------

Single dynamic form for both invoice + quote.

READ type from GET:

$type = invoice | quote

FORM SECTIONS:

1. Document Type (hidden or selector)

2. Customer Section:
- name
- company
- address
- email
- phone
- VAT number

3. Document Info:
- number
- issue date
- due date (invoice only)
- valid until (quote only)
- payment method
- notes

4. Items Table (dynamic JS):
Columns:
- reference
- description
- quantity
- unit price
- VAT %

Buttons:
- Add row
- Remove row

5. Submit button:
"Generate Document"

------------------------------------------------------------
PHASE 6 — GENERATE CONTROLLER (generate.php)
------------------------------------------------------------

Steps:

1. Read POST data
2. Load config/company.php
3. Load helpers.php
4. Build $document array:

$document = [
  "type" => $_POST["type"],
  "company" => config company,
  "customer" => form data,
  "items" => items array,
  "meta" => document info,
  "totals" => calculated values
];

5. Call helper functions:
- subtotal
- VAT
- grand total

6. Choose template:

IF type == invoice
    load templates/invoice.php

IF type == quote
    load templates/quote.php

7. Render HTML page

------------------------------------------------------------
PHASE 7 — TEMPLATE SYSTEM
------------------------------------------------------------

IMPORTANT RULE:

✔ invoice.php = MASTER DESIGN
✔ quote.php = SAME DESIGN

Only differences:

INVOICE:
- Title: INVOICE
- Due date shown
- Payment section visible

QUOTE:
- Title: QUOTE
- Valid until shown
- Acceptance section added

NO layout differences allowed.

------------------------------------------------------------
PHASE 8 — CSS (style.css)
------------------------------------------------------------

Requirements:

- A4 page (210mm x 297mm)
- Print optimized
- Clean modern invoice style
- Primary color: #3771c8
- No page breaks inside tables
- Compact spacing (fit 1 page)

Sections:

- header (logo + company)
- customer box
- document info box
- items table
- totals box
- footer

Include:

@media print {
  hide buttons
}

------------------------------------------------------------
PHASE 9 — JAVASCRIPT (inside form.php)
------------------------------------------------------------

Add JS for:

- Add item row
- Remove item row
- Auto calculate totals (optional preview only)

RULE:
✔ PHP is final authority for calculations
✔ JS is only UI helper

------------------------------------------------------------
PHASE 10 — PRINT FLOW
------------------------------------------------------------

User flow:

1. Open form.php
2. Fill data
3. Click Generate
4. generate.php renders HTML
5. User clicks Print
6. Browser → Save as PDF

NO PDF LIBRARY.

------------------------------------------------------------
PHASE 11 — SUCCESS CRITERIA
------------------------------------------------------------

System is complete when:

✔ Invoice prints clean A4 PDF (1 page)
✔ Quote prints clean A4 PDF (1 page)
✔ Same layout system for both
✔ Fast form (< 1 minute usage)
✔ No database required
✔ No framework required
✔ No duplicated templates logic

------------------------------------------------------------
PHASE 12 — STRICT RULES FOR AI AGENT
------------------------------------------------------------

DO NOT:
- introduce Laravel / Symfony
- use database
- create classes
- over-engineer architecture
- duplicate invoice/quote layouts
- break A4 layout design

DO:
- reuse invoice.php design everywhere
- keep PHP procedural
- keep Bootstrap for form only
- keep CSS single file
- keep helpers minimal

------------------------------------------------------------
FINAL GOAL
------------------------------------------------------------

A lightweight PHP tool that lets a user:

1. Click "Invoice or Quote"
2. Fill a Bootstrap form
3. Click Generate
4. Print professional A4 document
5. Save as PDF from browser
```

---

# ⚡ What this plan gives you

This is now a **real build instruction for an AI coding agent**:

- no ambiguity
- no architecture confusion
- no overengineering risk
- clear file-by-file responsibilities
- safe for fast prototype delivery

---

If you want next step, I can help you:

👉 turn your current `invoice.php` into a **reusable template engine**  
👉 build your **Bootstrap form exactly like a SaaS UI (modern UX)**  
👉 or generate the **full working prototype code in one go**