<?php

class User extends Model {
    protected $table = 'users';

    public function findByEmail($email) {
        return $this->first('email', '=', $email);
    }

    public function updateLastLogin($userId, $ip) {
        return $this->update($userId, [
            'last_login' => date('Y-m-d H:i:s'),
            'last_login_ip' => $ip
        ]);
    }

    public function isAdmin($userId) {
        $user = $this->find($userId);
        return $user && $user['role'] === 'admin';
    }

    public function isActive($userId) {
        $user = $this->find($userId);
        return $user && $user['status'] === 'active';
    }

    public function getAdmins() {
        return $this->where('role', '=', 'admin');
    }

    public function getClients() {
        return $this->where('role', '=', 'client');
    }
}
