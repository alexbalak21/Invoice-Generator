<?php

$company = [
    "name" => "Novocib",
    "street" => "123 Business Street",
    "city" => "Paris",
    "zip" => "75000",
    "phone" => "+33 1 23 45 67 89",
    "email" => "contact@novocib.com"
];

$customer = [
    "name" => "John Smith",
    "company" => "ACME Corp",
    "street" => "45 Customer Avenue",
    "city" => "London",
    "zip" => "EC1A 1AA",
    "phone" => "+44 123456789",
    "email" => "john@acme.com"
];

$invoice = [
    "number" => "INV-2026-001",
    "date" => "02/07/2026",
    "due_date" => "16/07/2026"
];

$items = [

[
"description"=>"Service Fee",
"qty"=>1,
"price"=>200
],

[
"description"=>"Labour (5 hours @ 75€/h)",
"qty"=>5,
"price"=>75
],

[
"description"=>"New Client Discount",
"qty"=>1,
"price"=>-50
]

];

$taxRate = 4.25;