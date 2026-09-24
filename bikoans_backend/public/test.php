<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Models\User;
use App\Services\UserService;

header('Content-Type: application/json; charset=UTF-8');

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["step" => "fail", "message" => "No DB connection"]);
    exit;
}

// Check tables exist
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo json_encode(["step" => "tables", "tables" => $tables]) . "\n";

// Check users table structure
try {
    $cols = $db->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["step" => "users_structure", "columns" => $cols]) . "\n";
} catch (Exception $e) {
    echo json_encode(["step" => "users_structure_error", "message" => $e->getMessage()]) . "\n";
}

// Check roles table
try {
    $roles = $db->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["step" => "roles", "data" => $roles]) . "\n";
} catch (Exception $e) {
    echo json_encode(["step" => "roles_error", "message" => $e->getMessage()]) . "\n";
}

// Try a direct insert
try {
    $testEmail = "test_' . time() . '@example.com";
    $hash = password_hash('test123', PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO users (full_name, email, password, role_id) VALUES (:full_name, :email, :password, :role_id)");
    $ok = $stmt->execute([
        ':full_name' => 'Test User',
        ':email' => $testEmail,
        ':password' => $hash,
        ':role_id' => 1
    ]);
    $id = $db->lastInsertId();
    echo json_encode(["step" => "direct_insert", "ok" => $ok, "last_id" => $id, "email" => $testEmail]) . "\n";
} catch (Exception $e) {
    echo json_encode(["step" => "direct_insert_error", "message" => $e->getMessage()]) . "\n";
}

// Try through the model
try {
    $userModel = new User();
    $svc = new UserService();
    $result = $svc->registerUser([
        'full_name' => 'Model Test',
        'email' => 'model_' . time() . '@example.com',
        'password' => 'test123',
        'role' => 'Agent'
    ]);
    echo json_encode(["step" => "service_register", "result" => $result]) . "\n";
} catch (Exception $e) {
    echo json_encode(["step" => "service_register_error", "message" => $e->getMessage(), "trace" => $e->getTraceAsString()]) . "\n";
}

// Show all users
try {
    $users = $db->query("SELECT id, full_name, email, role_id, created_at FROM users")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["step" => "all_users", "count" => count($users), "users" => $users]) . "\n";
} catch (Exception $e) {
    echo json_encode(["step" => "all_users_error", "message" => $e->getMessage()]) . "\n";
}