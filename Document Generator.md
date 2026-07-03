# Document Generator
## Technical Specification
### Version 1.0

---

# 1. Project Overview

## 1.1 Purpose

The goal of this project is to develop a reusable PHP document generation system capable of producing professional business documents directly from a web interface.

The application allows a user to quickly complete a Bootstrap-based form and generate print-ready documents optimized for A4 paper and PDF export.

The system is intended for small and medium businesses that regularly create commercial documents such as quotations, invoices, delivery notes and purchase orders.

Rather than creating separate applications for each document type, the project will provide a single rendering engine capable of generating multiple document types using the same HTML template and CSS stylesheet.

The generated documents should look professional, print correctly from every modern browser and require minimal user interaction.

---

# 2. Objectives

The application must:

- Generate professional A4 documents.
- Produce browser-printable PDF files.
- Require no external PDF library.
- Be simple to maintain.
- Be easy to extend.
- Minimize duplicated code.
- Separate data from presentation.
- Keep company configuration outside the user interface.
- Support multiple currencies.
- Support multiple VAT rules.
- Support international customers.
- Be responsive enough for desktop usage.
- Use Bootstrap 5 for the administration interface.
- Use plain PHP without frameworks.

---

# 3. Main Features

The application will support the following document types:

- Quote (Devis)
- Invoice (Facture)
- Proforma Invoice
- Credit Note (Avoir)
- Delivery Note (Bon de livraison)
- Purchase Order
- Receipt

Future versions may support:

- Commercial Proposal
- Subscription Renewal
- Service Report
- Work Order
- Packing List
- Certificate of Analysis

---

# 4. General Principles

The project follows several important principles.

## Single Source of Truth

Company information is never duplicated.

The company name, address, logo, VAT number, banking information and legal details are stored once inside the configuration directory.

Every generated document automatically uses this configuration.

---

## Generic Rendering Engine

There is only one rendering engine.

The renderer receives a PHP array describing the document.

Example:

```php
$document = [

    "type" => "quote",

    "customer" => [...],

    "items" => [...],

    "payment" => [...],

    "notes" => "...",

];
```

The renderer decides automatically which sections should appear depending on the document type.

---

## Reusable Components

Every visual section is isolated.

Examples:

- Header
- Company block
- Customer block
- Items table
- Totals
- VAT block
- Footer
- Signature section

These components are reused by every document.

---

## Separation of Responsibilities

The project is divided into four independent layers.

Presentation Layer

Bootstrap forms.

Business Layer

Document calculations.

Rendering Layer

HTML template.

Configuration Layer

Company information.

Each layer should remain independent.

No HTML should contain calculations.

No CSS should contain business logic.

No configuration should appear inside templates.

---

## Print First

The application is designed primarily for printed documents.

Every generated page must:

- Fit A4 paper.
- Print correctly from Chrome, Edge and Firefox.
- Export correctly as PDF.
- Preserve colors.
- Avoid page breaks inside tables.
- Keep totals together.
- Keep signatures together.

The screen preview is secondary.

The printed document is the main output.

---


# 5. System Architecture

## 5.1 Architecture Overview

The application follows a modular architecture where each component has a single responsibility.

```
+------------------------------------------------------+
|                  Bootstrap UI                        |
|      (Invoice / Quote Creation Forms)                |
+-------------------------+----------------------------+
                          |
                          |
                          ▼
+------------------------------------------------------+
|                Form Validation Layer                 |
|     Required fields, numbers, VAT validation         |
+-------------------------+----------------------------+
                          |
                          ▼
+------------------------------------------------------+
|               Document Builder Service               |
|      Converts form data into a Document object       |
+-------------------------+----------------------------+
                          |
                          ▼
+------------------------------------------------------+
|                Calculation Service                   |
|     Subtotal, VAT, Discounts, Shipping, Total        |
+-------------------------+----------------------------+
                          |
                          ▼
+------------------------------------------------------+
|                 Rendering Engine                     |
|     template.php + partials + style.css             |
+-------------------------+----------------------------+
                          |
                          ▼
+------------------------------------------------------+
|             Browser Preview / Print / PDF            |
+------------------------------------------------------+
```

The application is intentionally divided into multiple layers.

Each layer has a single responsibility.

This keeps the code easier to maintain and extend.

---

# 5.2 Application Workflow

The complete document generation workflow is illustrated below.

```
User opens Quote page
        │
        ▼
Bootstrap Form
        │
        ▼
User enters customer information
        │
        ▼
User adds document items
        │
        ▼
Totals calculated automatically
        │
        ▼
User clicks Generate
        │
        ▼
Document Builder creates Document object
        │
        ▼
Renderer loads template
        │
        ▼
HTML document generated
        │
        ▼
Browser Print
        │
        ▼
PDF
```

No PDF library is required.

The browser is responsible for PDF generation.

---

# 5.3 Main Modules

The project is composed of several independent modules.

## Configuration Module

Stores information that rarely changes.

Examples

- Company name
- Logo
- Address
- VAT number
- Banking details
- Default currency
- Colors
- Footer

This information is never editable through the document forms.

Instead it is loaded automatically from configuration files.

---

## Form Module

Responsible for collecting user input.

Examples

- Customer
- Items
- Payment terms
- Notes
- Shipping
- Discounts

The forms are built using Bootstrap 5.

Every document type has its own form configuration.

---

## Validation Module

Responsible for validating user input.

Examples

Required fields

Numeric values

Email addresses

VAT numbers

Date formats

Negative prices

Invalid quantities

Validation errors should be displayed directly below the corresponding input.

---

## Document Builder

The Document Builder converts the submitted form into a standardized PHP array.

Example

```php
$document = [

    "type" => "invoice",

    "company" => [...],

    "customer" => [...],

    "items" => [...],

    "payment" => [...],

    "legal" => [...],

    "totals" => [...]

];
```

The rendering engine only understands this structure.

It never accesses raw POST variables.

---

## Calculation Module

Responsible for every financial calculation.

Examples

Subtotal

VAT

Shipping

Discounts

Grand Total

Amount Paid

Remaining Balance

Every calculation is centralized inside one service.

Templates must never perform calculations.

Example

Bad

```php
<?= $price * $qty ?>
```

Good

```php
<?= money($document["totals"]["subtotal"]) ?>
```

---

## Rendering Engine

The rendering engine generates the final HTML document.

Responsibilities

- Load template
- Load CSS
- Display sections
- Hide unused sections
- Display legal information
- Render totals
- Render footer

The renderer should contain almost no business logic.

It simply displays data.

---

# 5.4 Document Life Cycle

Every document follows the same internal life cycle.

```
Configuration
        │
        ▼
User Input
        │
        ▼
Validation
        │
        ▼
Document Object
        │
        ▼
Calculations
        │
        ▼
Rendering
        │
        ▼
Preview
        │
        ▼
Print
        │
        ▼
PDF
```

Every document type uses exactly the same pipeline.

Only the document configuration changes.

---

# 5.5 Why This Architecture?

This architecture offers several advantages.

## Easy Maintenance

Each module has one responsibility.

Modifying VAT calculations never requires editing HTML.

Changing the logo never requires editing PHP.

Updating styles never requires editing templates.

---

## Easy Extension

Adding a new document type becomes straightforward.

For example

```
Credit Note

↓

Create form

↓

Reuse renderer

↓

Reuse CSS

↓

Done
```

No duplicated templates.

No duplicated calculations.

---

## Better Testing

Each module can be tested independently.

Examples

Test subtotal calculations.

Test VAT calculations.

Test template rendering.

Test customer validation.

Test document numbering.

Testing becomes much easier because responsibilities are clearly separated.

---

## Future Improvements

This architecture also makes future features much easier to implement.

Examples

- Database storage
- User authentication
- Multiple companies
- REST API
- PDF library integration
- Email sending
- Electronic signatures
- Client portal
- Saved drafts
- Automatic document numbering
- Multi-language support
- Currency conversion
- Document version history

The core architecture does not need to change when these features are added.

Only additional services are introduced.

---


# 6. Project Folder Structure

## 6.1 Directory Tree

The project is organized into independent modules.

```
document_generator/
│
├── config/
│   ├── company.php
│   ├── settings.php
│   ├── currencies.php
│   ├── taxes.php
│   └── document_types.php
│
├── public/
│   ├── index.php
│   ├── invoice.php
│   ├── quote.php
│   ├── preview.php
│   ├── generate.php
│   │
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   ├── img/
│   │   └── fonts/
│   │
│   └── uploads/
│
├── src/
│   ├── Builder/
│   ├── Calculator/
│   ├── Helpers/
│   ├── Models/
│   ├── Renderer/
│   ├── Services/
│   └── Validation/
│
├── templates/
│   ├── document.php
│   ├── partials/
│   │   ├── header.php
│   │   ├── company.php
│   │   ├── customer.php
│   │   ├── metadata.php
│   │   ├── items.php
│   │   ├── totals.php
│   │   ├── payment.php
│   │   ├── acceptance.php
│   │   ├── footer.php
│   │   └── signature.php
│   │
│   └── style.css
│
├── storage/
│   ├── invoices/
│   ├── quotes/
│   ├── drafts/
│   ├── pdf/
│   └── exports/
│
├── tests/
│
├── vendor/
│
├── composer.json
│
└── README.md
```

---

# 6.2 Configuration Directory

```
config/
```

Contains every configuration value that rarely changes.

Examples

- Company information
- Default colors
- VAT rates
- Default payment terms
- Default footer
- Default currency

No user should edit these values through the interface.

They are maintained by the administrator.

---

## company.php

Contains the permanent company information.

Example

```php
return [

    "name" => "...",

    "logo" => "...",

    "address" => "...",

    "phone" => "...",

    "email" => "...",

    "vat_number" => "...",

    "siret" => "...",

    "iban" => "...",

    "bic" => "...",

];
```

---

## settings.php

Application configuration.

Example

```php
return [

    "language"=>"en",

    "currency"=>"EUR",

    "timezone"=>"Europe/Paris",

    "date_format"=>"d/m/Y",

    "theme"=>"default"

];
```

---

## currencies.php

List of supported currencies.

Example

```php
return [

    "EUR"=>[

        "symbol"=>"€",

        "name"=>"Euro"

    ],

    "USD"=>[

        "symbol"=>"$",

        "name"=>"US Dollar"

    ],

    "GBP"=>[

        "symbol"=>"£",

        "name"=>"British Pound"

    ]

];
```

---

## taxes.php

Stores VAT presets.

```php
return [

    "FR_STANDARD"=>20,

    "FR_REDUCED"=>10,

    "EXPORT"=>0,

    "EU_B2B"=>0

];
```

---

## document_types.php

Central list of supported documents.

```php
return [

    "invoice",

    "quote",

    "credit_note",

    "proforma",

    "delivery_note",

    "purchase_order"

];
```

---

# 6.3 Public Directory

```
public/
```

Contains every page accessible from the browser.

Examples

```
invoice.php

quote.php

preview.php

generate.php
```

No business logic should be written here.

Each page should simply load the required services.

Example

```php
require "../bootstrap.php";

$form = new QuoteForm();

$form->render();
```

---

# 6.4 Assets

```
public/assets/
```

Contains static resources.

```
css/

js/

img/

fonts/
```

Examples

Bootstrap

Logo

Icons

Custom CSS

JavaScript

Webfonts

---

# 6.5 Uploads

```
public/uploads/
```

Temporary upload directory.

Examples

Customer logos

Attachments

Imported CSV

Future PDF uploads

This folder should never contain source code.

---

# 6.6 Source Directory

```
src/
```

Contains the application's PHP code.

No HTML should be stored here.

Every folder has one responsibility.

---

## Builder

Responsible for constructing a complete Document object.

Input

```
POST data
```

Output

```
Document
```

---

## Calculator

Performs every calculation.

Responsibilities

Subtotal

VAT

Discount

Shipping

Grand Total

Amount Paid

Balance Due

No calculations should be performed inside templates.

---

## Helpers

Contains utility functions.

Examples

```
money()

date()

sanitize()

formatAddress()

formatPhone()
```

---

## Models

Contains PHP classes describing business entities.

Examples

```
Company

Customer

Document

Item

Payment

Totals
```

Models should only contain data.

They should not generate HTML.

---

## Renderer

Converts a Document object into HTML.

Responsibilities

Load template

Load partials

Display data

Render printable document

---

## Services

Business services.

Examples

```
DocumentNumberGenerator

CurrencyFormatter

VatResolver

PdfExporter

DocumentStorage
```

Each service should solve one specific problem.

---

## Validation

Responsible for validating user input.

Examples

```
Required fields

VAT numbers

Dates

Numbers

Emails

Currencies

Duplicate items
```

Validation errors are returned to the form.

---

# 6.7 Templates Directory

```
templates/
```

Contains the printable HTML.

The renderer loads one master template.

```
document.php
```

The master template includes reusable sections.

```
header.php

customer.php

items.php

totals.php

footer.php
```

This prevents duplicated HTML.

Every document type shares the same layout.

Only certain blocks appear depending on the document type.

Example

Quote

```
Acceptance section

✔

Payment Due

✘
```

Invoice

```
Acceptance section

✘

Late payment penalties

✔
```

---

# 6.8 Storage Directory

```
storage/
```

Contains generated data.

Examples

Saved Quotes

Saved Invoices

Generated PDFs

Drafts

Exports

Nothing in this directory should be directly accessible through the browser.

---

# 6.9 Tests

```
tests/
```

Contains automated tests.

Examples

VAT calculations

Document totals

Discount calculations

Validation

Document numbering

Renderer

Every critical business rule should be tested.

---

# 6.10 Design Principles

The folder structure follows five principles.

## Separation of Concerns

Each directory has one responsibility.

---

## Reusability

Every component should be reusable.

---

## Extensibility

Adding a new document type should require minimal changes.

---

## Maintainability

Business logic should never be mixed with HTML.

---

## Scalability

The architecture should support future features such as:

- Database persistence
- User authentication
- Multi-company support
- REST API
- PDF generation libraries
- Electronic signatures
- Email delivery
- Version history
- Cloud storage
- Role management

The folder structure should remain stable even as these features are added.

---

# 7. Data Model

## 7.1 Overview

The application revolves around a single object called the **Document**.

Regardless of the document type (Quote, Invoice, Credit Note, etc.), the renderer always receives the same data structure.

The document type determines which sections are displayed, but the structure remains identical.

The renderer should never need to know whether it is rendering an invoice or a quote beyond checking the document type.

---

# 7.2 Document Structure

Every document contains the following sections.

```php
$document = [

    "type" => "...",

    "metadata" => [...],

    "company" => [...],

    "customer" => [...],

    "items" => [...],

    "payment" => [...],

    "shipping" => [...],

    "totals" => [...],

    "legal" => [...],

    "notes" => [...],

];
```

Each section has a specific responsibility.

---

# 7.3 Document Type

Defines which document is generated.

Example

```php
"type" => "invoice"
```

Supported values

```text
invoice
quote
credit_note
proforma
purchase_order
delivery_note
receipt
```

The document type controls:

- Title
- Visible sections
- Required fields
- Footer
- Signature block
- Legal mentions

---

# 7.4 Metadata

Contains document identification.

```php
"metadata" => [

    "number" => "INV-2026-0001",

    "issue_date" => "2026-07-03",

    "due_date" => "2026-08-02",

    "valid_until" => "2026-08-15",

    "purchase_order" => "",

    "reference" => "",

    "currency" => "EUR",

    "language" => "en"

]
```

Some fields are optional depending on the document type.

Example

Quotes use

```
Valid Until
```

Invoices use

```
Due Date
```

---

# 7.5 Company

The company issuing the document.

This information comes from the configuration files.

The user cannot edit these values.

Example

```php
"company" => [

    "name" => "Novocib",

    "logo" => "assets/img/logo.png",

    "legal_form" => "SAS",

    "share_capital" => "10000",

    "street" => "...",

    "city" => "...",

    "zip" => "...",

    "country" => "...",

    "phone" => "...",

    "email" => "...",

    "website" => "...",

    "siren" => "...",

    "siret" => "...",

    "vat_number" => "...",

    "rcs" => "...",

    "iban" => "...",

    "bic" => "...",

    "bank" => "..."

]
```

This data is automatically loaded.

---

# 7.6 Customer

Represents the customer receiving the document.

```php
"customer" => [

    "company" => "",

    "contact" => "",

    "department" => "",

    "street" => "",

    "city" => "",

    "zip" => "",

    "country" => "",

    "phone" => "",

    "email" => "",

    "vat_number" => ""

]
```

This information is entered through the form.

---

# 7.7 Items

Items are the heart of the document.

Every line contains all required information.

```php
"items" => [

    [

        "reference" => "K0307",

        "description" => "PRECICE dCK Assay Kit",

        "quantity" => 1,

        "unit" => "pcs",

        "unit_price" => 950,

        "discount" => 0,

        "vat_rate" => 0

    ],

    [

        "reference" => "SHIP",

        "description" => "FedEx Shipping",

        "quantity" => 1,

        "unit" => "service",

        "unit_price" => 120,

        "discount" => 0,

        "vat_rate" => 0

    ]

]
```

The renderer calculates

```
Line Total

=

Quantity

×

Unit Price

−

Discount
```

Totals should never be entered manually.

---

# 7.8 Payment

Contains payment information.

```php
"payment" => [

    "method" => "Wire Transfer",

    "due_date" => "2026-08-02",

    "payment_terms" => "30 days",

    "amount_paid" => 0

]
```

Invoices will display all payment information.

Quotes may only display payment terms.

---

# 7.9 Shipping

Optional section.

```php
"shipping" => [

    "carrier" => "FedEx",

    "service" => "Priority",

    "incoterm" => "DAP",

    "tracking_number" => "",

    "delivery_address" => ""

]
```

Hidden if empty.

---

# 7.10 Totals

Totals are generated automatically.

```php
"totals" => [

    "subtotal" => 0,

    "discount" => 0,

    "shipping" => 0,

    "vat" => 0,

    "grand_total" => 0,

    "balance_due" => 0

]
```

The user never edits these values.

They are calculated.

---

# 7.11 Legal

Contains mandatory legal information.

```php
"legal" => [

    "vat_mention" => "",

    "late_payment" => "",

    "recovery_fee" => "",

    "terms" => ""

]
```

Different document types display different legal blocks.

---

# 7.12 Notes

Free text.

```php
"notes" => [

    "public" => "",

    "internal" => ""

]
```

Public notes appear on the document.

Internal notes are never printed.

---

# 7.13 Acceptance

Only used by quotations.

```php
"acceptance" => [

    "enabled" => true,

    "text" => "Quote received before execution, read and approved, agreed."

]
```

Invoices ignore this section.

---

# 7.14 Visibility Rules

Each document type displays only the relevant sections.

| Section | Invoice | Quote | Proforma | Delivery Note |
|----------|:-------:|:-----:|:---------:|:-------------:|
| Customer | ✓ | ✓ | ✓ | ✓ |
| Items | ✓ | ✓ | ✓ | ✓ |
| Totals | ✓ | ✓ | ✓ | Optional |
| Payment | ✓ | Optional | Optional | No |
| Acceptance | No | ✓ | No | No |
| VAT Mention | ✓ | ✓ | ✓ | Optional |
| Signature | Optional | ✓ | Optional | ✓ |

The renderer decides what to display based on the document type.

---

# 7.15 Why a Generic Model?

A generic model offers several advantages.

- One renderer for every document.
- One stylesheet.
- One calculation engine.
- One validation system.
- Easier maintenance.
- Easier testing.
- Easier future expansion.

Adding a new document type should only require:

1. Defining the new type.
2. Creating its form.
3. Configuring which sections are visible.

The rendering engine remains unchanged.

---