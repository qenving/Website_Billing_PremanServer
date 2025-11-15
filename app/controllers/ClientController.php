<?php

require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Service.php';
require_once APP_PATH . '/models/Order.php';
require_once APP_PATH . '/models/Invoice.php';
require_once APP_PATH . '/models/Payment.php';
require_once APP_PATH . '/models/Ticket.php';
require_once APP_PATH . '/models/TicketReply.php';

class ClientController extends Controller {

    private function checkAuth() {
        if (!Session::has('user_id')) {
            $this->redirect('/login');
            exit;
        }
    }

    public function services() {
        $this->checkAuth();
        $serviceModel = new Service();
        $services = $serviceModel->getActive();
        $this->view('client.services', compact('services'));
    }

    public function myOrders() {
        $this->checkAuth();
        $userId = Session::get('user_id');
        $orderModel = new Order();
        $orders = $orderModel->getByUser($userId);
        $this->view('client.orders', compact('orders'));
    }

    public function invoices() {
        $this->checkAuth();
        $userId = Session::get('user_id');
        $invoiceModel = new Invoice();
        $invoices = $invoiceModel->getByUser($userId);
        $this->view('client.invoices', compact('invoices'));
    }

    public function viewInvoice($invoiceId) {
        $this->checkAuth();
        $userId = Session::get('user_id');
        $invoiceModel = new Invoice();
        $invoice = $invoiceModel->find($invoiceId);

        if (!$invoice || $invoice['user_id'] != $userId) {
            return $this->redirect('/client/invoices');
        }

        $this->view('client.invoice_detail', compact('invoice'));
    }

    public function tickets() {
        $this->checkAuth();
        $userId = Session::get('user_id');
        $ticketModel = new Ticket();
        $tickets = $ticketModel->getByUser($userId);
        $this->view('client.tickets', compact('tickets'));
    }

    public function viewTicket($ticketId) {
        $this->checkAuth();
        $userId = Session::get('user_id');
        $ticketModel = new Ticket();
        $ticket = $ticketModel->find($ticketId);

        if (!$ticket || $ticket['user_id'] != $userId) {
            return $this->redirect('/client/tickets');
        }

        $ticketReplyModel = new TicketReply();
        $replies = $ticketReplyModel->getByTicket($ticketId);

        $this->view('client.ticket_detail', compact('ticket', 'replies'));
    }

    public function account() {
        $this->checkAuth();
        $userId = Session::get('user_id');
        $userModel = new User();
        $user = $userModel->find($userId);
        $this->view('client.account', compact('user'));
    }
}
