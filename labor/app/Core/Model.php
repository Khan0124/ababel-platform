<?php

namespace App\Core;

use App\Core\Database;
use PDO;

abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    protected $timestamps = true;
    protected $attributes = [];
    protected $original = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public static function find($id)
    {
        $instance = new static();
        $stmt = $instance->db->prepare("SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = ? LIMIT 1");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        if ($data) {
            $instance->fill($data);
            $instance->original = $data;
            return $instance;
        }
        
        return null;
    }

    public static function findBy($column, $value)
    {
        $instance = new static();
        $stmt = $instance->db->prepare("SELECT * FROM {$instance->table} WHERE {$column} = ? LIMIT 1");
        $stmt->execute([$value]);
        $data = $stmt->fetch();
        
        if ($data) {
            $instance->fill($data);
            $instance->original = $data;
            return $instance;
        }
        
        return null;
    }

    public static function all($conditions = [], $orderBy = null, $limit = null)
    {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $column => $value) {
                $where[] = "{$column} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $instance->db->prepare($sql);
        $stmt->execute($params);
        
        $results = [];
        while ($row = $stmt->fetch()) {
            $model = new static();
            $model->fill($row);
            $model->original = $row;
            $results[] = $model;
        }
        
        return $results;
    }

    public static function count($conditions = [])
    {
        $instance = new static();
        $sql = "SELECT COUNT(*) as count FROM {$instance->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $column => $value) {
                $where[] = "{$column} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $instance->db->prepare($sql);
        $stmt->execute($params);
        
        return (int) $stmt->fetch()['count'];
    }

    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable) || empty($this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    public function save()
    {
        if ($this->exists()) {
            return $this->update();
        }
        return $this->insert();
    }

    private function insert()
    {
        $data = $this->getDataForSave();
        
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($values), '?');

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($values);

        if ($result) {
            $this->attributes[$this->primaryKey] = $this->db->lastInsertId();
            $this->original = $this->attributes;
        }

        return $result;
    }

    private function update()
    {
        $data = $this->getDataForSave();
        
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $set = [];
        $values = [];
        foreach ($data as $column => $value) {
            $set[] = "{$column} = ?";
            $values[] = $value;
        }
        
        $values[] = $this->attributes[$this->primaryKey];

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = ?",
            $this->table,
            implode(', ', $set),
            $this->primaryKey
        );

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($values);

        if ($result) {
            $this->original = $this->attributes;
        }

        return $result;
    }

    public function delete()
    {
        if (!$this->exists()) {
            return false;
        }

        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$this->attributes[$this->primaryKey]]);
    }

    public function exists()
    {
        return isset($this->attributes[$this->primaryKey]) && !empty($this->original);
    }

    private function getDataForSave()
    {
        $data = [];
        foreach ($this->attributes as $key => $value) {
            if (($this->fillable && in_array($key, $this->fillable)) || empty($this->fillable)) {
                if ($key !== $this->primaryKey) {
                    $data[$key] = $value;
                }
            }
        }
        return $data;
    }

    public function toArray()
    {
        $array = $this->attributes;
        foreach ($this->hidden as $hidden) {
            unset($array[$hidden]);
        }
        return $array;
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    public static function query()
    {
        return new QueryBuilder(new static());
    }
}