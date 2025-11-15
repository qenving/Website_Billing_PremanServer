<?php

declare(strict_types=1);

namespace App\Storage;

use RuntimeException;

class JsonDataStore
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->ensureFileExists();
    }

    /**
     * @return array{
     *     clients: array<int, array<string, mixed>>,
     *     products: array<int, array<string, mixed>>,
     *     subscriptions: array<int, array<string, mixed>>,
     *     invoices: array<int, array<string, mixed>>
     * }
     */
    public function load(): array
    {
        $contents = file_get_contents($this->path);
        if ($contents === false) {
            throw new RuntimeException('Unable to read data store.');
        }

        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Failed to decode JSON data store.');
        }

        /** @var array{
         *     clients: array<int, array<string, mixed>>,
         *     products: array<int, array<string, mixed>>,
         *     subscriptions: array<int, array<string, mixed>>,
         *     invoices: array<int, array<string, mixed>>
         * } $decoded
         */
        return $decoded;
    }

    /**
     * @param array{
     *     clients: array<int, array<string, mixed>>,
     *     products: array<int, array<string, mixed>>,
     *     subscriptions: array<int, array<string, mixed>>,
     *     invoices: array<int, array<string, mixed>>
     * } $data
     */
    public function save(array $data): void
    {
        $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            throw new RuntimeException('Failed to encode JSON data store.');
        }

        $result = file_put_contents($this->path, $encoded . PHP_EOL);
        if ($result === false) {
            throw new RuntimeException('Unable to persist data store to disk.');
        }
    }

    private function ensureFileExists(): void
    {
        if (is_file($this->path)) {
            return;
        }

        $directory = dirname($this->path);
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
                throw new RuntimeException('Unable to create storage directory.');
            }
        }

        $seedData = [
            'clients' => [
                1 => [
                    'id' => 1,
                    'name' => 'PT Nusantara Digital',
                    'email' => 'finance@nusantara.id',
                    'company' => 'Nusantara Digital',
                ],
                2 => [
                    'id' => 2,
                    'name' => 'Siti Rahmawati',
                    'email' => 'siti.rahmawati@example.com',
                    'company' => 'Freelancer',
                ],
            ],
            'products' => [
                1 => [
                    'id' => 1,
                    'name' => 'Hosting Basic',
                    'price' => 75000.0,
                    'billing_cycle' => 'monthly',
                ],
                2 => [
                    'id' => 2,
                    'name' => 'Hosting Pro',
                    'price' => 150000.0,
                    'billing_cycle' => 'monthly',
                ],
                3 => [
                    'id' => 3,
                    'name' => 'Domain .com',
                    'price' => 120000.0,
                    'billing_cycle' => 'yearly',
                ],
            ],
            'subscriptions' => [
                1 => [
                    'id' => 1,
                    'client_id' => 1,
                    'product_id' => 2,
                    'status' => 'active',
                    'next_due_date' => '2024-12-15',
                ],
                2 => [
                    'id' => 2,
                    'client_id' => 2,
                    'product_id' => 1,
                    'status' => 'active',
                    'next_due_date' => '2024-12-30',
                ],
            ],
            'invoices' => [
                1 => [
                    'id' => 1,
                    'client_id' => 1,
                    'status' => 'paid',
                    'issued_at' => '2024-11-10T00:00:00+00:00',
                    'line_items' => [
                        [
                            'product_id' => 2,
                            'description' => 'Hosting Pro',
                            'quantity' => 1,
                            'unit_price' => 150000.0,
                            'total' => 150000.0,
                        ],
                    ],
                    'total' => 150000.0,
                ],
            ],
        ];

        $this->save($seedData);
    }
}
