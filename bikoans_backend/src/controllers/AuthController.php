<?php
namespace App\Controllers;
use App\Services\UserService;
class AuthController
{
    private UserService $service;
    public function __construct()
    {
        $this->service = new UserService();
    }
    public function handle(string $action): void
    {
        $d = json_decode(file_get_contents('php://input'), true) ?: [];
        $r = match ($action) { 'register' => $this->service->registerUser($d), 'login' => $this->service->login($d), 'change-password' => $this->service->changePassword($d), default => ['status' => 'error', 'message' => 'Unknown action.']};
        http_response_code($r['status'] === 'success' ? 200 : 400);
        echo json_encode($r);
    }
}
