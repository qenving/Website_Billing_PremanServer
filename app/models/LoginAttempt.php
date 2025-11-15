<?php

class LoginAttempt extends Model {
    protected $table = 'login_attempts';

    public function log($email, $ip, $userAgent, $success, $reason = null) {
        return $this->create([
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'success' => $success ? 1 : 0,
            'reason' => $reason
        ]);
    }

    public function getRecentAttempts($email, $minutes = 15) {
        $since = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));
        $sql = "SELECT * FROM {$this->table} WHERE email = :email AND created_at >= :since ORDER BY created_at DESC";
        return $this->query($sql, ['email' => $email, 'since' => $since]);
    }

    public function getFailedAttempts($email, $minutes = 15) {
        $since = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email AND success = 0 AND created_at >= :since";
        $result = $this->query($sql, ['email' => $email, 'since' => $since]);
        return $result[0]['count'] ?? 0;
    }
}
