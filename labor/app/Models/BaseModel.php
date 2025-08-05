<?php
namespace App\Models;

use PDO;
use PDOException;
use Exception;

abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = ['password', 'created_at', 'updated_at'];
    protected $casts = [];
    
    public function __construct(PDO $database) {
        $this->db = $database;
    }
    
    /**
     * Find record by ID
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        return $result ? $this->castAttributes($result) : null;
    }
    
    /**
     * Find all records with optional conditions
     */
    public function findAll(array $conditions = [], array $orderBy = [], int $limit = null, int $offset = 0): array {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $key => $value) {
                $whereClause[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        if (!empty($orderBy)) {
            $orderClause = [];
            foreach ($orderBy as $column => $direction) {
                $orderClause[] = "{$column} {$direction}";
            }
            $sql .= " ORDER BY " . implode(', ', $orderClause);
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset > 0) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();
        
        return array_map([$this, 'castAttributes'], $results);
    }
    
    /**
     * Create new record
     */
    public function create(array $data): int {
        $data = $this->filterFillable($data);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Update record by ID
     */
    public function update(int $id, array $data): bool {
        $data = $this->filterFillable($data);
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $setClause = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $setClause[] = "{$column} = ?";
            $params[] = $value;
        }
        
        $params[] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE {$this->primaryKey} = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Delete record by ID
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Find record by specific column
     */
    public function findBy(string $column, $value): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = ?");
        $stmt->execute([$value]);
        $result = $stmt->fetch();
        
        return $result ? $this->castAttributes($result) : null;
    }
    
    /**
     * Count records with optional conditions
     */
    public function count(array $conditions = []): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $key => $value) {
                $whereClause[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return (int)$result['count'];
    }
    
    /**
     * Execute raw SQL query
     */
    public function rawQuery(string $sql, array $params = []): array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Execute raw SQL query for single result
     */
    public function rawQuerySingle(string $sql, array $params = []): ?array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result ? $this->castAttributes($result) : null;
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction(): bool {
        return $this->db->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit(): bool {
        return $this->db->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback(): bool {
        return $this->db->rollback();
    }
    
    /**
     * Filter data to only include fillable fields
     */
    protected function filterFillable(array $data): array {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Cast attributes based on defined casts
     */
    protected function castAttributes(array $data): array {
        foreach ($this->casts as $attribute => $cast) {
            if (isset($data[$attribute])) {
                switch ($cast) {
                    case 'int':
                        $data[$attribute] = (int)$data[$attribute];
                        break;
                    case 'float':
                        $data[$attribute] = (float)$data[$attribute];
                        break;
                    case 'bool':
                        $data[$attribute] = (bool)$data[$attribute];
                        break;
                    case 'json':
                        $data[$attribute] = json_decode($data[$attribute], true);
                        break;
                    case 'date':
                        $data[$attribute] = new \DateTime($data[$attribute]);
                        break;
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Hide sensitive attributes
     */
    public function toArray(array $data): array {
        return array_diff_key($data, array_flip($this->hidden));
    }
    
    /**
     * Get paginated results
     */
    public function paginate(int $page = 1, int $perPage = 15, array $conditions = [], array $orderBy = []): array {
        $offset = ($page - 1) * $perPage;
        $total = $this->count($conditions);
        $data = $this->findAll($conditions, $orderBy, $perPage, $offset);
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }
} 