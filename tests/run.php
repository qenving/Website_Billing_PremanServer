<?php

declare(strict_types=1);

require __DIR__ . '/../src/Support/autoload.php';

use App\Services\BillingService;
use App\Storage\JsonDataStore;

$baseData = file_get_contents(__DIR__ . '/../storage/data.json');
if ($baseData === false) {
    throw new RuntimeException('Failed to read base data set.');
}

$tempFile = tempnam(sys_get_temp_dir(), 'billing');
if ($tempFile === false) {
    throw new RuntimeException('Unable to create temporary storage file.');
}
file_put_contents($tempFile, $baseData);

$service = new BillingService(new JsonDataStore($tempFile));

$assert = function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException('Assertion failed: ' . $message);
    }
};

$snapshot = $service->getDashboardSnapshot();
$assert($snapshot['clients'] === 2, 'expected two clients');
$assert($snapshot['products'] === 3, 'expected three products');

$invoice = $service->createInvoice([
    'client_id' => 2,
    'line_items' => [
        ['product_id' => 1, 'quantity' => 2],
        ['product_id' => 3, 'quantity' => 1],
    ],
]);

$expectedTotal = 2 * 75000 + 120000;
$assert(abs($invoice['total'] - $expectedTotal) < 0.0001, 'invoice total should match line items');
$assert($invoice['id'] === 2, 'new invoice id should be incremented');

$invoices = $service->listInvoices();
$assert(count($invoices) === 2, 'invoices count should include new invoice');

unlink($tempFile);

echo "All tests passed" . PHP_EOL;
