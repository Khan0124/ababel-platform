<?php
namespace App\Core;

use Exception;

/**
 * Base Model Class
 * Provides common database operations with proper error handling and audit logging
 */
abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = ['password'];
    protected $auditEnabled = true;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find record by primary key
     */
    public function find($id)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
            $stmt = $this->db->query($sql, [$id]);
            $result = $stmt->fetch();
            
            return $result ? $this->hideSensitiveFields($result) : null;
            
        } catch (Exception $e) {
            $this->logError('Error finding record', $e, ['id' => $id]);
            return null;
        }
    }
    
    /**
     * Get all records with optional conditions
     */
    public function all($conditions = [], $orderBy = null, $limit = null)
    {
        try {
            $sql = "SELECT * FROM {$this->table}";
            $params = [];
            
            if (!empty($conditions)) {
                $whereClause = [];
                foreach ($conditions as $field => $value) {
                    $whereClause[] = "$field = ?";
                    $params[] = $value;
                }
                $sql .= " WHERE " . implode(" AND ", $whereClause);
            }
            
            if ($orderBy) {
                $sql .= " ORDER BY $orderBy";
            }
            
            if ($limit) {
                $sql .= " LIMIT $limit";
            }
            
            $stmt = $this->db->query($sql, $params);
            $results = $stmt->fetchAll();
            
            return array_map([$this, 'hideSensitiveFields'], $results);
            
        } catch (Exception $e) {
            $this->logError('Error getting all records', $e, ['conditions' => $conditions]);
            return [];
        }
    }
    
    /**
     * Create new record
     */
    public function create($data)
    {
        try {
            // Filter fillable fields
            $data = $this->filterFillable($data);
            
            // Add timestamps if they don't exist
            if (!isset($data['created_at'])) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }
            if (!isset($data['updated_at'])) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }
            
            $fields = array_keys($data);
            $values = array_values($data);
            $placeholders = array_fill(0, count($fields), '?');
            
            $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            
            $this->db->query($sql, $values);
            $insertId = $this->db->lastInsertId();
            
            // Log audit trail
            if ($this->auditEnabled) {
                $this->logAudit('create', $insertId, null, $data);
            }
            
            return $insertId;
            
        } catch (Exception $e) {
            $this->logError('Error creating record', $e, ['data' => $data]);
            throw $e;
        }
    }
    
    /**
     * Update record
     */
    public function update($id, $data)
    {
        try {
            // Get old values for audit
            $oldValues = null;
            if ($this->auditEnabled) {
                $oldRecord = $this->find($id);
                $oldValues = $oldRecord ? $oldRecord : null;
            }
            
            // Filter fillable fields
            $data = $this->filterFillable($data);
            
            // Add updated timestamp
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $fields = [];
            $values = [];
            
            foreach ($data as $field => $value) {
                $fields[] = "$field = ?";
                $values[] = $value;
            }
            
            $values[] = $id;
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = ?";
            
            $result = $this->db->query($sql, $values)->rowCount();
            
            // Log audit trail
            if ($this->auditEnabled && $result > 0) {
                $this->logAudit('update', $id, $oldValues, $data);
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->logError('Error updating record', $e, ['id' => $id, 'data' => $data]);
            throw $e;
        }
    }
    
    /**
     * Delete record
     */
    public function delete($id)
    {
        try {
            // Get old values for audit
            $oldValues = null;
            if ($this->auditEnabled) {
                $oldRecord = $this->find($id);
                $oldValues = $oldRecord ? $oldRecord : null;
            }
            
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
            $result = $this->db->query($sql, [$id])->rowCount();
            
            // Log audit trail
            if ($this->auditEnabled && $result > 0) {
                $this->logAudit('delete', $id, $oldValues, null);
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->logError('Error deleting record', $e, ['id' => $id]);
            throw $e;
        }
    }
    
    /**
     * Soft delete record (if table has deleted_at column)
     */
    public function softDelete($id)
    {
        try {
            // Check if table has deleted_at column
            $columns = $this->getTableColumns();
            if (!in_array('deleted_at', $columns)) {
                throw new Exception('Table does not support soft delete');
            }
            
            $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE {$this->primaryKey} = ?";
            return $this->db->query($sql, [$id])->rowCount();
            
        } catch (Exception $e) {
            $this->logError('Error soft deleting record', $e, ['id' => $id]);
            throw $e;
        }
    }
    
    /**
     * Restore soft deleted record
     */
    public function restore($id)
    {
        try {
            $sql = "UPDATE {$this->table} SET deleted_at = NULL WHERE {$this->primaryKey} = ?";
            return $this->db->query($sql, [$id])->rowCount();
            
        } catch (Exception $e) {
            $this->logError('Error restoring record', $e, ['id' => $id]);
            throw $e;
        }
    }
    
    /**
     * Find record by specific field
     */
    public function findBy($field, $value)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE $field = ?";
            $stmt = $this->db->query($sql, [$value]);
            $result = $stmt->fetch();
            
            return $result ? $this->hideSensitiveFields($result) : null;
            
        } catch (Exception $e) {
            $this->logError('Error finding record by field', $e, ['field' => $field, 'value' => $value]);
            return null;
        }
    }
    
    /**
     * Find records by specific field
     */
    public function findAllBy($field, $value, $orderBy = null, $limit = null)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE $field = ?";
            $params = [$value];
            
            if ($orderBy) {
                $sql .= " ORDER BY $orderBy";
            }
            
            if ($limit) {
                $sql .= " LIMIT $limit";
            }
            
            $stmt = $this->db->query($sql, $params);
            $results = $stmt->fetchAll();
            
            return array_map([$this, 'hideSensitiveFields'], $results);
            
        } catch (Exception $e) {
            $this->logError('Error finding records by field', $e, ['field' => $field, 'value' => $value]);
            return [];
        }
    }
    
    /**
     * Count records with optional conditions
     */
    public function count($conditions = [])
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table}";
            $params = [];
            
            if (!empty($conditions)) {
                $whereClause = [];
                foreach ($conditions as $field => $value) {
                    $whereClause[] = "$field = ?";
                    $params[] = $value;
                }
                $sql .= " WHERE " . implode(" AND ", $whereClause);
            }
            
            $stmt = $this->db->query($sql, $params);
            $result = $stmt->fetch();
            
            return (int) $result['count'];
            
        } catch (Exception $e) {
            $this->logError('Error counting records', $e, ['conditions' => $conditions]);
            return 0;
        }
    }
    
    /**
     * Execute raw query
     */
    public function rawQuery($sql, $params = [])
    {
        try {
            return $this->db->query($sql, $params);
        } catch (Exception $e) {
            $this->logError('Error executing raw query', $e, ['sql' => $sql, 'params' => $params]);
            throw $e;
        }
    }
    
    /**
     * Get table columns
     */
    public function getTableColumns()
    {
        try {
            $sql = "DESCRIBE {$this->table}";
            $stmt = $this->db->query($sql);
            $columns = $stmt->fetchAll();
            
            return array_column($columns, 'Field');
            
        } catch (Exception $e) {
            $this->logError('Error getting table columns', $e);
            return [];
        }
    }
    
    /**
     * Filter data to only include fillable fields
     */
    protected function filterFillable($data)
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Hide sensitive fields from output
     */
    protected function hideSensitiveFields($data)
    {
        if (empty($this->hidden)) {
            return $data;
        }
        
        foreach ($this->hidden as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }
        
        return $data;
    }
    
    /**
     * Log audit trail
     */
    protected function logAudit($action, $recordId, $oldValues = null, $newValues = null)
    {
        try {
            $security = SecurityManager::getInstance();
            
            $sql = "INSERT INTO audit_log (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $this->db->query($sql, [
                $_SESSION['user_id'] ?? null,
                $action,
                $this->table,
                $recordId,
                $oldValues ? json_encode($oldValues) : null,
                $newValues ? json_encode($newValues) : null,
                $security->getClientIP(),
                $security->getUserAgent()
            ]);
            
        } catch (Exception $e) {
            // Don't let audit logging break the main operation
            error_log("Audit logging failed: " . $e->getMessage());
        }
    }
    
    /**
     * Log errors
     */
    protected function logError($message, Exception $exception, array $context = [])
    {
        $logMessage = sprintf(
            "[%s] %s: %s in %s:%d\nContext: %s\n",
            date('Y-m-d H:i:s'),
            $message,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            json_encode($context)
        );
        
        $logFile = __DIR__ . '/../../storage/logs/model.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit()
    {
        return $this->db->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback()
    {
        return $this->db->rollback();
    }
    
    /**
     * Execute callback within transaction
     */
    public function transaction(callable $callback)
    {
        return $this->db->transaction($callback);
    }
}