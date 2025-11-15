<?php

class ActivityLog extends Model {
    protected $table = 'activity_logs';

    public function log($userId, $type, $description, $metadata = []) {
        $request = new Request();
        return $this->create([
            'user_id' => $userId,
            'type' => $type,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => json_encode($metadata)
        ]);
    }

    public function getByUser($userId, $limit = 50) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :userId ORDER BY created_at DESC LIMIT :limit";
        return $this->query($sql, ['userId' => $userId, 'limit' => $limit]);
    }

    public function getByType($type, $limit = 50) {
        $sql = "SELECT * FROM {$this->table} WHERE type = :type ORDER BY created_at DESC LIMIT :limit";
        return $this->query($sql, ['type' => $type, 'limit' => $limit]);
    }

    public function getRecent($limit = 100) {
        $sql = "SELECT a.*, u.email, u.first_name, u.last_name
                FROM {$this->table} a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.created_at DESC
                LIMIT :limit";
        return $this->query($sql, ['limit' => $limit]);
    }
}
