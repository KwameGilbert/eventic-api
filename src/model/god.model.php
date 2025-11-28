<?php

declare(strict_types=1);

require_once CONFIG . 'Database.php';
/**
 * God Model
 * Main base model for all models
 */
class GodModel
{
    /** @var PDO */
    protected PDO $db;

    /** @var string */
    private string $lastError = '';

    public function __construct()
    {
       try {
        $database = new Database();
        $connection = $database->getConnection();
        if (!$connection) {
            throw new PDOException('Database connection is null');
        }
        $this->db = $connection;
       } catch (PDOException $e) {
        error_log("Error : " . $e->getMessage());
        $this->lastError = $e->getMessage();
        throw $e;
       }
    }

     /**
     * Execute a prepared statement with error handling
     */
    public function executeQuery(\PDOStatement $statement, array $params = []): bool
    {
        try {
            return $statement->execute($params);
        } catch (PDOException $e) {
            $this->lastError = 'Query execution failed: ' . $e->getMessage();
            error_log($this->lastError . ' - SQL: ' . $statement->queryString);
            return false;
        }
    }

    /**
     * Get the last error message
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }

    /**
     * Get the database connection
     */
    public function getConnection(): PDO
    {
        return $this->db;
    }

   
}