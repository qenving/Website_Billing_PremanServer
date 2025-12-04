<?php

class Payment extends Model {
    protected $table = 'payments';

    public function getByInvoice($invoiceId) {
        return $this->where('invoice_id', '=', $invoiceId);
    }

    public function getByUser($userId) {
        $sql = "SELECT p.*, i.invoice_number
                FROM {$this->table} p
                LEFT JOIN invoices i ON p.invoice_id = i.id
                WHERE p.user_id = :userId
                ORDER BY p.created_at DESC";
        return $this->query($sql, ['userId' => $userId]);
    }

    public function getAllWithDetails() {
        $sql = "SELECT p.*, i.invoice_number, u.email, u.first_name, u.last_name
                FROM {$this->table} p
                LEFT JOIN invoices i ON p.invoice_id = i.id
                LEFT JOIN users u ON p.user_id = u.id
                ORDER BY p.created_at DESC";
        return $this->query($sql);
    }
}
