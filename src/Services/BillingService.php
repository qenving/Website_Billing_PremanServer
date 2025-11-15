<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Storage\JsonDataStore;

class BillingService
{
    private JsonDataStore $store;

    public function __construct(JsonDataStore $store)
    {
        $this->store = $store;
    }

    /**
     * @return array<string, int>
     */
    public function getDashboardSnapshot(): array
    {
        $data = $this->store->load();

        return [
            'clients' => count($data['clients']),
            'products' => count($data['products']),
            'subscriptions' => count($data['subscriptions']),
            'invoices' => count($data['invoices']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listClients(): array
    {
        $data = $this->store->load();

        return array_values($data['clients']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listProducts(): array
    {
        $data = $this->store->load();

        return array_values($data['products']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listSubscriptions(): array
    {
        $data = $this->store->load();

        return array_values($data['subscriptions']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listInvoices(): array
    {
        $data = $this->store->load();

        return array_values($data['invoices']);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createInvoice(array $payload): array
    {
        $data = $this->store->load();
        $errors = [];

        $clientId = isset($payload['client_id']) ? (int) $payload['client_id'] : 0;
        if ($clientId <= 0 || !isset($data['clients'][$clientId])) {
            $errors['client_id'] = 'Client not found.';
        }

        $lineItems = $payload['line_items'] ?? [];
        if (!is_array($lineItems) || $lineItems === []) {
            $errors['line_items'] = 'At least one line item is required.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $items = [];
        $total = 0.0;

        foreach ($lineItems as $index => $item) {
            if (!is_array($item)) {
                $errors['line_items_' . $index] = 'Invalid line item structure.';
                continue;
            }

            $productId = isset($item['product_id']) ? (int) $item['product_id'] : 0;
            $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 0;

            if ($productId <= 0 || !isset($data['products'][$productId])) {
                $errors['line_items_' . $index] = 'Product not found.';
                continue;
            }

            if ($quantity <= 0) {
                $errors['line_items_' . $index] = 'Quantity must be greater than zero.';
                continue;
            }

            $product = $data['products'][$productId];
            $lineTotal = $product['price'] * $quantity;
            $items[] = [
                'product_id' => $productId,
                'description' => $product['name'],
                'quantity' => $quantity,
                'unit_price' => $product['price'],
                'total' => $lineTotal,
            ];
            $total += $lineTotal;
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $invoiceId = $this->nextIdentifier($data['invoices']);
        $invoice = [
            'id' => $invoiceId,
            'client_id' => $clientId,
            'status' => 'pending',
            'issued_at' => gmdate('c'),
            'line_items' => $items,
            'total' => round($total, 2),
        ];

        $data['invoices'][$invoiceId] = $invoice;
        $this->store->save($data);

        return $invoice;
    }

    /**
     * @param array<int|string, array<string, mixed>> $records
     */
    private function nextIdentifier(array $records): int
    {
        if ($records === []) {
            return 1;
        }

        $max = max(array_map('intval', array_keys($records)));

        return $max + 1;
    }
}
