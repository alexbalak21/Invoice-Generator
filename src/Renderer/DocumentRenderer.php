<?php

class DocumentRenderer
{
    public function render(array $document): void
    {
        include __DIR__ . '/../../templates/document.php';
    }
}
