<?php
namespace App\Config;

class Database {
    private static $instance = null;
    private $connection;
    private $driver = 'pgsql';
    
    private function __construct() {
        $configFile = __DIR__ . '/../../config/database.php';
        
        if (file_exists($configFile) && !getenv('PGHOST')) {
            $config = require $configFile;
            $this->driver = 'mysql';
            
            try {
                $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
                $this->connection = new \PDO($dsn, $config['username'], $config['password'], [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']} COLLATE {$config['collation']}"
                ]);
            } catch (\PDOException $e) {
                die("Erro de conexão com o banco de dados MySQL: " . $e->getMessage());
            }
        } else {
            $host = getenv('PGHOST');
            $port = getenv('PGPORT');
            $dbname = getenv('PGDATABASE');
            $user = getenv('PGUSER');
            $password = getenv('PGPASSWORD');
            $this->driver = 'pgsql';
            
            try {
                $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
                $this->connection = new \PDO($dsn, $user, $password, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false
                ]);
            } catch (\PDOException $e) {
                die("Erro de conexão com o banco de dados: " . $e->getMessage());
            }
        }
    }
    
    public function getDriver(): string {
        return $this->driver;
    }
    
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection(): \PDO {
        return $this->connection;
    }
    
    public function query(string $sql, array $params = []): \PDOStatement {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetch(string $sql, array $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function insert(string $table, array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        if ($this->driver === 'mysql') {
            $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
            $this->query($sql, $data);
            return (int)$this->connection->lastInsertId();
        } else {
            $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders}) RETURNING id";
            $stmt = $this->query($sql, $data);
            $result = $stmt->fetch();
            return $result['id'] ?? 0;
        }
    }
    
    public function update(string $table, array $data, string $where, array $whereParams = []): int {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = :{$column}";
        }
        $setString = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$setString} WHERE {$where}";
        $stmt = $this->query($sql, array_merge($data, $whereParams));
        return $stmt->rowCount();
    }
    
    public function delete(string $table, string $where, array $params = []): int {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function lastInsertId(): string {
        return $this->connection->lastInsertId();
    }
    
    public function boolValue(bool $value): string|int {
        if ($this->driver === 'mysql') {
            return $value ? 1 : 0;
        }
        return $value ? 't' : 'f';
    }
}
