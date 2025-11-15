<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\BillingService;

class DashboardController
{
    private BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function index(Request $request): Response
    {
        $snapshot = $this->billingService->getDashboardSnapshot();

        $html = <<<HTML
        <html>
            <head>
                <meta charset="utf-8">
                <title>HBM Billing Manager</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 2rem; background: #f6f8fa; }
                    h1 { color: #1f2937; }
                    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 2rem 0; }
                    .card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.12); }
                    code { background: #e5e7eb; padding: 0.25rem 0.5rem; border-radius: 4px; }
                </style>
            </head>
            <body>
                <h1>HBM Hosting & Billing Manager</h1>
                <p>A lightweight demo environment that runs without Composer dependencies.</p>
                <div class="grid">
                    <div class="card"><strong>Clients</strong><br><span>{$snapshot['clients']}</span></div>
                    <div class="card"><strong>Products</strong><br><span>{$snapshot['products']}</span></div>
                    <div class="card"><strong>Subscriptions</strong><br><span>{$snapshot['subscriptions']}</span></div>
                    <div class="card"><strong>Invoices</strong><br><span>{$snapshot['invoices']}</span></div>
                </div>
                <p>Use the API endpoints below to interact with the data:</p>
                <ul>
                    <li><code>GET /api/status</code></li>
                    <li><code>GET /api/clients</code></li>
                    <li><code>GET /api/products</code></li>
                    <li><code>GET /api/subscriptions</code></li>
                    <li><code>GET /api/invoices</code></li>
                    <li><code>POST /api/invoices</code> with JSON body</li>
                </ul>
            </body>
        </html>
        HTML;

        return Response::html($html);
    }

    public function status(Request $request): Response
    {
        return Response::json([
            'status' => 'ok',
            'snapshot' => $this->billingService->getDashboardSnapshot(),
        ]);
    }
}
