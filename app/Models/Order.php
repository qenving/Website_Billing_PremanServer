<?php

class Order extends Model {
    protected $table = 'orders';

    public function getByUser($userId) {
        $sql = "SELECT o.*, s.name as service_name
                FROM {$this->table} o
                LEFT JOIN services s ON o.service_id = s.id
                WHERE o.user_id = :userId
                ORDER BY o.created_at DESC";
        return $this->query($sql, ['userId' => $userId]);
    }

    public function getAllWithDetails() {
        $sql = "SELECT o.*, s.name as service_name, u.email, u.first_name, u.last_name
                FROM {$this->table} o
                LEFT JOIN services s ON o.service_id = s.id
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC";
        return $this->query($sql);
    }

    public function getActive() {
        return $this->where('status', '=', 'active');
    }
}
