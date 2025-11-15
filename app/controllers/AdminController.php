<?php

require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Service.php';
require_once APP_PATH . '/models/Order.php';
require_once APP_PATH . '/models/Invoice.php';
require_once APP_PATH . '/models/Payment.php';
require_once APP_PATH . '/models/Ticket.php';
require_once APP_PATH . '/models/ActivityLog.php';
require_once APP_PATH . '/models/Setting.php';
require_once APP_PATH . '/security/integrity_checker.php';
require_once APP_PATH . '/core/ErrorHandler.php';

class AdminController extends Controller {

    private function checkAdmin() {
        if (!Session::has('user_id') || Session::get('user_role') !== 'admin') {
            $this->redirect('/login');
            exit;
        }
    }

    public function users() {
        $this->checkAdmin();
        $userModel = new User();
        $users = $userModel->all();
        $this->view('admin.users', compact('users'));
    }

    public function services() {
        $this->checkAdmin();
        $serviceModel = new Service();
        $services = $serviceModel->getAllWithOrders();
        $this->view('admin.services', compact('services'));
    }

    public function orders() {
        $this->checkAdmin();
        $orderModel = new Order();
        $orders = $orderModel->getAllWithDetails();
        $this->view('admin.orders', compact('orders'));
    }

    public function invoices() {
        $this->checkAdmin();
        $invoiceModel = new Invoice();
        $invoices = $invoiceModel->getAllWithDetails();
        $this->view('admin.invoices', compact('invoices'));
    }

    public function payments() {
        $this->checkAdmin();
        $paymentModel = new Payment();
        $payments = $paymentModel->getAllWithDetails();
        $this->view('admin.payments', compact('payments'));
    }

    public function tickets() {
        $this->checkAdmin();
        $ticketModel = new Ticket();
        $tickets = $ticketModel->getAllWithDetails();
        $this->view('admin.tickets', compact('tickets'));
    }

    public function settings() {
        $this->checkAdmin();
        $settingModel = new Setting();
        $settings = $settingModel->all();
        $this->view('admin.settings', compact('settings'));
    }

    public function activityLogs() {
        $this->checkAdmin();
        $activityModel = new ActivityLog();
        $activities = $activityModel->getRecent(100);
        $this->view('admin.activity_logs', compact('activities'));
    }

    public function integrityChecker() {
        $this->checkAdmin();
        $result = IntegrityChecker::verify();
        $hashInfo = IntegrityChecker::getHashInfo();
        $this->view('admin.integrity_checker', compact('result', 'hashInfo'));
    }

    public function integrityGenerate() {
        $this->checkAdmin();
        $result = IntegrityChecker::generateHashes();
        Session::flash('success', $result['message']);
        $this->redirect('/admin/integrity-checker');
    }

    public function errorLogs() {
        $this->checkAdmin();
        $errors = ErrorHandler::getLogs(100);
        $this->view('admin.error_logs', compact('errors'));
    }
}
