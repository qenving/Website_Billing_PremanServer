<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\BillingService;

class InvoiceController
{
    private BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function index(Request $request): Response
    {
        return Response::json([
            'data' => $this->billingService->listInvoices(),
        ]);
    }

    public function store(Request $request): Response
    {
        $invoice = $this->billingService->createInvoice($request->json());

        return Response::json([
            'message' => 'Invoice created successfully.',
            'data' => $invoice,
        ], 201);
    }

    public function options(Request $request): Response
    {
        return Response::plain('', 204);
    }
}
