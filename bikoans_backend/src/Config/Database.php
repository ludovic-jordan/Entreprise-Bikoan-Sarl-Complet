<?php
namespace App\Config;
use mysqli;
class Database
{
    private $host = 'localhost';
    private $db_name = 'chip';
    private $username = 'root';
    private $password = '';
    public function getConnection(): mysqli
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = new mysqli($this->host, $this->username, $this->password, $this->db_name);
        $db->set_charset('utf8mb4');
        return $db;
    }
}
