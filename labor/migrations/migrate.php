<?php
/**
 * Database Migration Runner
 * Run this script to apply all pending migrations
 */

require_once __DIR__ . '/../includes/config_secure.php';

echo "=== Labor SaaS Database Migration Tool ===\n\n";

// Create migrations tracking table
$tracking_table = "
CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `migration` VARCHAR(255) NOT NULL UNIQUE,
    `batch` INT NOT NULL,
    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if (!$conn->query($tracking_table)) {
    die("Error creating migrations table: " . $conn->error . "\n");
}

// Get already executed migrations
$executed = [];
$result = $conn->query("SELECT migration FROM migrations");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $executed[] = $row['migration'];
    }
}

// Get all migration files
$migration_files = glob(__DIR__ . '/*.sql');
sort($migration_files);

// Get next batch number
$batch_result = $conn->query("SELECT MAX(batch) as max_batch FROM migrations");
$batch = 1;
if ($batch_result && $row = $batch_result->fetch_assoc()) {
    $batch = ($row['max_batch'] ?? 0) + 1;
}

$migrations_run = 0;

foreach ($migration_files as $file) {
    $filename = basename($file);
    
    // Skip if already executed
    if (in_array($filename, $executed)) {
        echo "✓ Already executed: $filename\n";
        continue;
    }
    
    echo "→ Running migration: $filename... ";
    
    // Read migration file
    $sql = file_get_contents($file);
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^(--|\/\*|DELIMITER)/i', $statement)) {
                if (!$conn->query($statement)) {
                    throw new Exception($conn->error);
                }
            }
        }
        
        // Record migration
        $stmt = $conn->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->bind_param("si", $filename, $batch);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        $conn->commit();
        echo "✓ SUCCESS\n";
        $migrations_run++;
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "✗ FAILED: " . $e->getMessage() . "\n";
        
        // Ask user if they want to continue
        echo "Continue with remaining migrations? (y/n): ";
        $input = trim(fgets(STDIN));
        if (strtolower($input) !== 'y') {
            break;
        }
    }
}

echo "\n";
echo "=== Migration Summary ===\n";
echo "Total migrations: " . count($migration_files) . "\n";
echo "Already executed: " . count($executed) . "\n";
echo "Newly executed: " . $migrations_run . "\n";

if ($migrations_run === 0) {
    echo "\nNo new migrations to run. Database is up to date!\n";
} else {
    echo "\nMigrations completed successfully!\n";
}

// Close connection
$conn->close();
?>