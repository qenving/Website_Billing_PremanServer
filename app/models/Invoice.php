<?php

class Invoice extends Model {
    protected $table = 'invoices';

    public function generateInvoiceNumber() {
        $prefix = 'INV';
        $date = date('Ymd');
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE invoice_number LIKE :pattern";
        $result = $this->query($sql, ['pattern' => $prefix . $date . '%']);
        $count = ($result[0]['count'] ?? 0) + 1;
        return $prefix . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function getByUser($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :userId ORDER BY created_at DESC";
        return $this->query($sql, ['userId' => $userId]);
    }

    public function getUnpaid() {
        return $this->where('status', '=', 'unpaid');
    }

    public function markAsPaid($invoiceId, $paymentId = null) {
        return $this->update($invoiceId, [
            'status' => 'paid',
            'paid_date' => date('Y-m-d H:i:s')
        ]);
    }

    public function getAllWithDetails() {
        $sql = "SELECT i.*, u.email, u.first_name, u.last_name
                FROM {$this->table} i
                LEFT JOIN users u ON i.user_id = u.id
                ORDER BY i.created_at DESC";
        return $this->query($sql);
    }
}
