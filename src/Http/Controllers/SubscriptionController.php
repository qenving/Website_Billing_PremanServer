<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\BillingService;

class SubscriptionController
{
    private BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function index(Request $request): Response
    {
        return Response::json([
            'data' => $this->billingService->listSubscriptions(),
        ]);
    }
}
