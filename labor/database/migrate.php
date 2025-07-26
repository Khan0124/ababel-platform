<?php

/**
 * Database Migration Runner
 * Run this script to create/update database schema
 */

require_once __DIR__ . '/../bootstrap/app.php';

try {
    $db = app('db');
    $conn = $db->getConnection();
    
    echo "🚀 Starting database migration...\n";
    
    // Read and execute migration file
    $migrationFile = __DIR__ . '/migrations/001_create_tables.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );
    
    $conn->beginTransaction();
    
    $executed = 0;
    foreach ($statements as $statement) {
        if (trim($statement)) {
            echo "Executing: " . substr(trim($statement), 0, 50) . "...\n";
            $conn->exec($statement);
            $executed++;
        }
    }
    
    $conn->commit();
    
    echo "✅ Migration completed successfully!\n";
    echo "📊 Executed $executed SQL statements\n";
    
    // Verify tables were created
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "📋 Created " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        echo "   - $table\n";
    }
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "🔍 Error in file: " . $e->getFile() . " line " . $e->getLine() . "\n";
    
    // Log the error
    error_log("Migration Error: " . $e->getMessage());
    
    exit(1);
}