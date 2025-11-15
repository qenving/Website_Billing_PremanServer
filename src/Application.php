<?php

declare(strict_types=1);

namespace App;

use App\Exceptions\ValidationException;
use App\Http\Request;
use App\Http\Response;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SubscriptionController;
use App\Routing\Router;
use App\Services\BillingService;
use App\Storage\JsonDataStore;
use Throwable;

class Application
{
    private Router $router;

    private BillingService $billingService;

    public function __construct(?BillingService $billingService = null)
    {
        $this->billingService = $billingService ?? new BillingService(
            new JsonDataStore(__DIR__ . '/../storage/data.json')
        );
        $this->router = new Router();
        $this->registerRoutes();
    }

    public function handle(array $server, string $rawBody): Response
    {
        $request = Request::fromGlobals($server, $rawBody);

        try {
            return $this->router->dispatch($request);
        } catch (ValidationException $exception) {
            return Response::json([
                'error' => $exception->getMessage(),
                'details' => $exception->errors(),
            ], 422);
        } catch (Throwable $exception) {
            return Response::json([
                'error' => 'Server Error',
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    private function registerRoutes(): void
    {
        $dashboardController = new DashboardController($this->billingService);
        $clientController = new ClientController($this->billingService);
        $productController = new ProductController($this->billingService);
        $subscriptionController = new SubscriptionController($this->billingService);
        $invoiceController = new InvoiceController($this->billingService);

        $this->router->get('/', [$dashboardController, 'index']);
        $this->router->get('/api/status', [$dashboardController, 'status']);
        $this->router->get('/api/clients', [$clientController, 'index']);
        $this->router->get('/api/products', [$productController, 'index']);
        $this->router->get('/api/subscriptions', [$subscriptionController, 'index']);
        $this->router->get('/api/invoices', [$invoiceController, 'index']);
        $this->router->post('/api/invoices', [$invoiceController, 'store']);
        $this->router->options('/api/invoices', [$invoiceController, 'options']);
    }
}
