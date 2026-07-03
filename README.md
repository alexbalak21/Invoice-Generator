# Document Generator

Reusable PHP document generator for invoices and quotes.

## Start

Open the project root in your local web server and use [index.php](index.php) to enter the app. The root launcher redirects to the dashboard in [public/index.php](public/index.php).

You can also open the form pages directly:

- [public/invoice.php](public/invoice.php)
- [public/quote.php](public/quote.php)

## Features

- Bootstrap 5 form UI
- Print-ready A4 document rendering
- Invoice and quote generation
- JSON import for form prefilling
- Single shared stylesheet in [style.css](style.css)

## JSON Import

The form pages support an `Upload JSON` button. Use it to load a formatted JSON file and prefill the fields.

Sample files:

- [public/invoice.json](public/invoice.json)
- [public/quote.json](public/quote.json)

Expected top-level keys:

- `type`
- `customer`
- `meta`
- `items`
- `notes`
- `acceptance`

## Project Layout

- `config/` holds company and application settings.
- `public/` contains browser-accessible pages.
- `src/` contains builder, calculator, validation, renderer, and helper code.
- `templates/` contains the printable document template.
- `storage/` is reserved for generated output and saved documents.

## Notes

- The project keeps a root launcher for convenience.
- The dashboard styling is scoped to the homepage only, while the form pages keep their own Bootstrap layout.
