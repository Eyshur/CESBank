<?php
/**
 * Model.php
 * Base class for all data models. Provides generic, reusable
 * CRUD helpers built on PDO prepared statements so child classes
 * (User, Transaction, Reports, ...) only need to define table-specific logic.
 */

require_once __DIR__ . '/../database/Database.php';

abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Fetch all rows from the table, optionally ordered.
     */
    public function all(string $orderBy = null): array
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Find a single row by its primary key.
     */
    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Find a single row matching arbitrary column conditions.
     * Example: $this->findWhere(['username' => 'admin']);
     */
    public function findWhere(array $conditions): array|false
    {
        $clauses = [];
        foreach ($conditions as $column => $value) {
            $clauses[] = "{$column} = :{$column}";
        }
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $clauses) . " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);
        return $stmt->fetch();
    }

    /**
     * Fetch all rows matching arbitrary column conditions.
     */
    public function where(array $conditions, string $orderBy = null): array
    {
        $clauses = [];
        foreach ($conditions as $column => $value) {
            $clauses[] = "{$column} = :{$column}";
        }
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $clauses);
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);
        return $stmt->fetchAll();
    }

    /**
     * Insert a row. $data is an associative array of column => value.
     * Returns the new row's primary key.
     */
    public function create(array $data): int
    {
        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($col) => ":{$col}", array_keys($data)));

        $sql  = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update a row by primary key. $data is an associative array of column => value.
     */
    public function update(int $id, array $data): bool
    {
        $assignments = implode(', ', array_map(fn($col) => "{$col} = :{$col}", array_keys($data)));
        $sql = "UPDATE {$this->table} SET {$assignments} WHERE {$this->primaryKey} = :id";

        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Delete a row by primary key.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Escape hatch for custom queries in child classes.
     */
    protected function raw(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}