<?php

class TicketReply extends Model {
    protected $table = 'ticket_replies';

    public function getByTicket($ticketId) {
        $sql = "SELECT tr.*, u.first_name, u.last_name, u.email
                FROM {$this->table} tr
                LEFT JOIN users u ON tr.user_id = u.id
                WHERE tr.ticket_id = :ticketId
                ORDER BY tr.created_at ASC";
        return $this->query($sql, ['ticketId' => $ticketId]);
    }

    public function addReply($ticketId, $userId, $message, $isAdmin = false) {
        return $this->create([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'message' => $message,
            'is_admin' => $isAdmin ? 1 : 0
        ]);
    }
}
