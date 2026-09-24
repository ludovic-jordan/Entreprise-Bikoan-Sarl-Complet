<?php
namespace App\Models;
use App\Config\Database;
use mysqli;
class User
{
    private mysqli $db;
    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }
    public function findByEmail(string $email): ?array
    {
        // Keep authentication independent of the optional roles table. The signup form
        // stores the role id, so it can safely be displayed even when roles has no name column.
        $s = $this->db->prepare('SELECT id, full_name, email, password, CASE role_id WHEN 1 THEN "Agent" WHEN 2 THEN "Entrepreneur" ELSE "User" END AS role FROM users WHERE email=? LIMIT 1');
        $s->bind_param('s', $email);
        $s->execute();
        $r = $s->get_result();
        return $r->fetch_assoc() ?: null;
    }
    public function create(string $name, string $email, string $password, int $role): int
    {
        $s = $this->db->prepare('INSERT INTO users (full_name,email,password,role_id) VALUES (?,?,?,?)');
        $s->bind_param('sssi', $name, $email, $password, $role);
        $s->execute();
        return $this->db->insert_id;
    }
    public function updatePassword(int $id, string $password): void
    {
        $s = $this->db->prepare('UPDATE users SET password=? WHERE id=?');
        $s->bind_param('si', $password, $id);
        $s->execute();
    }
}
