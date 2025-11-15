<?php

class Service extends Model {
    protected $table = 'services';

    public function getActive() {
        return $this->where('status', '=', 'active');
    }

    public function getAllWithOrders() {
        $sql = "SELECT s.*, COUNT(o.id) as order_count
                FROM {$this->table} s
                LEFT JOIN orders o ON s.id = o.service_id
                GROUP BY s.id
                ORDER BY s.created_at DESC";
        return $this->query($sql);
    }
}
