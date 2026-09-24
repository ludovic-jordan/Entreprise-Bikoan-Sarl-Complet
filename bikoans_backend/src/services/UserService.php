<?php
namespace App\Services;
use App\Models\User;
class UserService
{
    private User $model;
    public function __construct()
    {
        $this->model = new User();
    }
    private function profile(array $u): array
    {
        return ['id' => (int) $u['id'], 'name' => $u['full_name'], 'email' => $u['email'], 'role' => $u['role']];
    }
    private function roleId(string $r): ?int
    {
        return ['Agent' => 1, 'Entrepreneur' => 2][$r] ?? null;
    }
    public function registerUser(array $d): array
    {
        $n = trim($d['full_name'] ?? '');
        $e = strtolower(trim($d['email'] ?? ''));
        $p = (string) ($d['password'] ?? '');
        $r = (string) ($d['role'] ?? '');
        $rid = $this->roleId($r);
        if (!$n || !filter_var($e, FILTER_VALIDATE_EMAIL) || strlen($p) < 6 || !$rid)
            return ['status' => 'error', 'message' => 'Please complete all fields. Password must contain at least 6 characters.'];
        if ($this->model->findByEmail($e))
            return ['status' => 'error', 'message' => 'This email address is already registered.'];
        $id = $this->model->create($n, $e, password_hash($p, PASSWORD_DEFAULT), $rid);
        return ['status' => 'success', 'message' => 'Account created successfully.', 'user' => ['id' => $id, 'name' => $n, 'email' => $e, 'role' => $r]];
    }
    public function login(array $d): array
    {
        $u = $this->model->findByEmail(strtolower(trim($d['email'] ?? '')));
        if ($u && strlen($u['password']) < 50) {
            return ['status' => 'error', 'message' => 'This account has an old truncated password hash. Please create the account again or reset its password.'];
        }
        $password = (string) ($d['password'] ?? '');
        $valid = $u && password_verify($password, $u['password']);
        // Migrate legacy accounts that were stored before password hashing was added.
        if ($u && !$valid && !str_starts_with($u['password'], '$2y$') && hash_equals($u['password'], $password)) {
            $this->model->updatePassword((int) $u['id'], password_hash($password, PASSWORD_DEFAULT));
            $valid = true;
        }
        if (!$valid)
            return ['status' => 'error', 'message' => 'Invalid email or password.'];
        return ['status' => 'success', 'message' => 'Login successful.', 'user' => $this->profile($u)];
    }
    public function changePassword(array $d): array
    {
        $u = $this->model->findByEmail(strtolower(trim($d['email'] ?? '')));
        if (!$u || !password_verify((string) ($d['old_password'] ?? ''), $u['password']))
            return ['status' => 'error', 'message' => 'The old password is incorrect.'];
        $p = (string) ($d['new_password'] ?? '');
        if (strlen($p) < 6)
            return ['status' => 'error', 'message' => 'The new password must contain at least 6 characters.'];
        $this->model->updatePassword((int) $u['id'], password_hash($p, PASSWORD_DEFAULT));
        return ['status' => 'success', 'message' => 'Password changed successfully.'];
    }
}
