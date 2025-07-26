<?php

namespace App\Services;

class CacheService
{
    private $cacheDir;
    private $defaultTtl;
    
    public function __construct()
    {
        $this->cacheDir = __DIR__ . '/../../storage/cache/';
        $this->defaultTtl = 3600; // 1 hour
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    public function get($key, $default = null)
    {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return $default;
        }
        
        $data = unserialize(file_get_contents($filename));
        
        if ($data['expires'] && $data['expires'] < time()) {
            $this->forget($key);
            return $default;
        }
        
        return $data['value'];
    }
    
    public function put($key, $value, $ttl = null)
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $expires = $ttl > 0 ? time() + $ttl : null;
        
        $data = [
            'value' => $value,
            'expires' => $expires,
            'created' => time()
        ];
        
        $filename = $this->getFilename($key);
        return file_put_contents($filename, serialize($data), LOCK_EX) !== false;
    }
    
    public function forget($key)
    {
        $filename = $this->getFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }
    
    public function flush()
    {
        $files = glob($this->cacheDir . '*.cache');
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    public function remember($key, $ttl, $callback)
    {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = call_user_func($callback);
        $this->put($key, $value, $ttl);
        
        return $value;
    }
    
    public function increment($key, $value = 1)
    {
        $current = $this->get($key, 0);
        $new = $current + $value;
        $this->put($key, $new);
        
        return $new;
    }
    
    public function decrement($key, $value = 1)
    {
        return $this->increment($key, -$value);
    }
    
    public function has($key)
    {
        return $this->get($key) !== null;
    }
    
    public function getMultiple($keys, $default = null)
    {
        $result = [];
        
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        
        return $result;
    }
    
    public function putMultiple($values, $ttl = null)
    {
        $success = true;
        
        foreach ($values as $key => $value) {
            if (!$this->put($key, $value, $ttl)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    public function cleanExpired()
    {
        $files = glob($this->cacheDir . '*.cache');
        $cleaned = 0;
        
        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            
            if ($data['expires'] && $data['expires'] < time()) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    public function getStats()
    {
        $files = glob($this->cacheDir . '*.cache');
        $totalSize = 0;
        $expired = 0;
        $valid = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            $data = unserialize(file_get_contents($file));
            
            if ($data['expires'] && $data['expires'] < time()) {
                $expired++;
            } else {
                $valid++;
            }
        }
        
        return [
            'total_items' => count($files),
            'valid_items' => $valid,
            'expired_items' => $expired,
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize)
        ];
    }
    
    private function getFilename($key)
    {
        $hash = md5($key);
        return $this->cacheDir . $hash . '.cache';
    }
    
    private function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }
}