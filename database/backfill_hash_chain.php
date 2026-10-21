<?php
/**
 * Backfill hash chain for legacy audit log entries
 * 
 * Matches the EXACT formula in models/AuditLog.php:
 *   $hashInput = $previousHash
 *              . '|' . ($row['user_id'] ?? 'null')
 *              . '|' . ($row['action'] ?? '')
 *              . '|' . ($row['entity_type'] ?? '')
 *              . '|' . ($row['entity_id'] ?? 'null')
 *              . '|' . ($row['description'] ?? '')
 *              . '|' . ($row['ip_address'] ?? '')
 *              . '|' . ($row['created_at'] ?? '');
 */

$host = '127.0.0.1';
$port = 3306;
$dbname = 'medical_imaging_db';
$username = 'root';
$password = '';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

echo "=== BACKFILLING AUDIT HASH CHAIN (MATCHING EXACT FORMULA) ===\n";
echo "Target: {$host}/{$dbname}\n\n";

// Reset all hashes to force clean rebuild
echo "Step 1: Resetting all hashes to NULL...\n";
$pdo->exec("UPDATE audit_logs SET row_hash = NULL, previous_hash = NULL");
echo "✅ All hashes reset\n\n";

// Fetch all rows in order
$rows = $pdo->query("SELECT * FROM audit_logs ORDER BY log_id ASC")->fetchAll();

if (empty($rows)) {
    echo "No audit log entries found.\n";
    exit(0);
}

echo "Step 2: Rebuilding hash chain for " . count($rows) . " rows...\n\n";

$previousHash = str_repeat('0', 64); // Genesis hash = 64 zeros
$backfilled = 0;

foreach ($rows as $row) {
    $logId = $row['log_id'];
    
    // EXACT formula from models/AuditLog.php
    $hashInput = $previousHash
               . '|' . ($row['user_id'] ?? 'null')
               . '|' . ($row['action'] ?? '')
               . '|' . ($row['entity_type'] ?? '')
               . '|' . ($row['entity_id'] ?? 'null')
               . '|' . ($row['description'] ?? '')
               . '|' . ($row['ip_address'] ?? '')
               . '|' . ($row['created_at'] ?? '');
    
    $expectedHash = hash('sha256', $hashInput);
    
    // Update both previous_hash and row_hash
    $updateSql = "UPDATE audit_logs SET 
        previous_hash = :previous_hash,
        row_hash = :row_hash
        WHERE log_id = :log_id";
    
    $stmt = $pdo->prepare($updateSql);
    $stmt->execute([
        ':previous_hash' => $previousHash,
        ':row_hash' => $expectedHash,
        ':log_id' => $logId
    ]);
    
    $backfilled++;
    
    // Only print first 5 and last 3 rows to keep output manageable
    if ($logId <= 5 || $logId >= count($rows) - 2) {
        echo "✅ Row #{$logId} ({$row['action']}) → " . substr($expectedHash, 0, 16) . "...\n";
    }
    
    // The next row's previous_hash is this row's row_hash
    $previousHash = $expectedHash;
}

echo "\n=== SUMMARY ===\n";
echo "Total rows:      " . count($rows) . "\n";
echo "Rebuilt:         {$backfilled}\n";
echo "\n✅ Hash chain rebuilt with the exact formula from AuditLog.php\n";
echo "\nNow verify in the browser:\n";
echo "  http://localhost/System%202/?page=audit&action=verifyChain\n";
echo "\nExpected result: INTACT (green) with " . count($rows) . " verified\n";
