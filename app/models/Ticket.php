<?php

class Ticket extends Model {
    protected $table = 'tickets';

    public function getByUser($userId) {
        return $this->where('user_id', '=', $userId);
    }

    public function getAllWithDetails() {
        $sql = "SELECT t.*, u.email, u.first_name, u.last_name,
                COUNT(tr.id) as reply_count
                FROM {$this->table} t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN ticket_replies tr ON t.id = tr.ticket_id
                GROUP BY t.id
                ORDER BY t.created_at DESC";
        return $this->query($sql);
    }

    public function getOpen() {
        return $this->where('status', '=', 'open');
    }
}
