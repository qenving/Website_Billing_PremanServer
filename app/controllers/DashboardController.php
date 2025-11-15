<?php

require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Order.php';
require_once APP_PATH . '/models/Invoice.php';
require_once APP_PATH . '/models/Ticket.php';
require_once APP_PATH . '/models/ActivityLog.php';

class DashboardController extends Controller {

    public function index() {
        if (!Session::has('user_id')) {
            return $this->redirect('/login');
        }

        $userId = Session::get('user_id');
        $userRole = Session::get('user_role');

        if ($userRole === 'admin') {
            return $this->adminDashboard();
        } else {
            return $this->clientDashboard($userId);
        }
    }

    private function adminDashboard() {
        $userModel = new User();
        $orderModel = new Order();
        $invoiceModel = new Invoice();
        $ticketModel = new Ticket();
        $activityModel = new ActivityLog();

        $stats = [
            'total_users' => count($userModel->all()),
            'total_clients' => count($userModel->getClients()),
            'active_orders' => count($orderModel->getActive()),
            'unpaid_invoices' => count($invoiceModel->getUnpaid()),
            'open_tickets' => count($ticketModel->getOpen())
        ];

        $recentOrders = array_slice($orderModel->getAllWithDetails(), 0, 10);
        $recentInvoices = array_slice($invoiceModel->getAllWithDetails(), 0, 10);
        $recentActivities = $activityModel->getRecent(20);

        $this->view('admin.dashboard', compact('stats', 'recentOrders', 'recentInvoices', 'recentActivities'));
    }

    private function clientDashboard($userId) {
        $orderModel = new Order();
        $invoiceModel = new Invoice();
        $ticketModel = new Ticket();

        $orders = $orderModel->getByUser($userId);
        $invoices = array_slice($invoiceModel->getByUser($userId), 0, 5);
        $tickets = array_slice($ticketModel->getByUser($userId), 0, 5);

        $stats = [
            'active_services' => count(array_filter($orders, fn($o) => $o['status'] === 'active')),
            'unpaid_invoices' => count(array_filter($invoices, fn($i) => $i['status'] === 'unpaid')),
            'open_tickets' => count(array_filter($tickets, fn($t) => $t['status'] === 'open'))
        ];

        $this->view('client.dashboard', compact('stats', 'orders', 'invoices', 'tickets'));
    }
}
