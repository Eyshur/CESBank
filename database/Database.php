<?php

date_default_timezone_set('Asia/Manila');

class Database
{
    private static ?Database $instance = null;
    private $conn;

    private $host     = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "cesbank_db";

    public function __construct()
    {
        try {
            $dsn = "mysql:host=$this->host;dbname=$this->database;charset=utf8mb4";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT         => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);

        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            http_response_code(500);
            die(json_encode([
                "success" => false,
                "message" => "Unable to connect to the database. Please try again later.",
            ]));
        }
    }

    /**
     * Returns the single shared Database instance for this request.
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function setConnection($conn)
    {
        $this->conn = $conn;
    }
}