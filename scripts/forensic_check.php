<?php
/**
 * Forensic Integrity Check Script
 * DCF-01-0247/2025 - Wairimu David Kariuki
 *
 * Detects tampering in stored medical images by recomputing hashes
 * and comparing them against the hashes stored at upload time.
 *
 * Schema: medical_images
 *   - image_id (int)
 *   - file_path (varchar)
 *   - file_hash (char 64)
 *   - record_status (enum: active, archived)
 *
 * Usage: php scripts/forensic_check.php
 */

$config = require __DIR__ . '/../config/database.php';

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

echo "=== FORENSIC INTEGRITY CHECK ===\n";
echo "Database: {$config['database']}\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

$issues = 0;
$checked = 0;
$missing = 0;
$tampered = 0;

// Query all active images
$stmt = $pdo->query("
    SELECT image_id, file_path, file_hash, original_file_name
    FROM medical_images
    WHERE record_status != 'archived'
");
$images = $stmt->fetchAll();

if (empty($images)) {
    echo "No images found in the database.\n";
    exit(0);
}

foreach ($images as $row) {
    $checked++;
    $imageId = $row['image_id'];
    $fileName = $row['original_file_name'] ?? $row['file_path'];
    $storedHash = $row['file_hash'];

    // Resolve the full file path
    $filePath = $row['file_path'];
    if ($filePath[0] !== '/') {
        $filePath = __DIR__ . '/../' . $filePath;
    }

    // Check if file exists
    if (!file_exists($filePath)) {
        echo "[MISSING]  IMG-$imageId ($fileName) - File not found at $filePath\n";
        $missing++;
        $issues++;
        continue;
    }

    // Recompute the SHA-256 hash
    $currentHash = hash('sha256', file_get_contents($filePath));

    if ($currentHash !== $storedHash) {
        echo "[TAMPERED] IMG-$imageId ($fileName)\n";
        echo "  Stored:   $storedHash\n";
        echo "  Current:  $currentHash\n";
        echo "  Path:     $filePath\n";
        $tampered++;
        $issues++;
    } else {
        echo "[OK]       IMG-$imageId ($fileName)\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total images checked:   $checked\n";
echo "Missing files:          $missing\n";
echo "Tampered files:         $tampered\n";
echo "Issues detected:        $issues\n";

if ($issues > 0) {
    echo "\n⚠️  WARNING: Tampering or missing files detected.\n";
    echo "\nInvestigate using:\n";
    echo "  SELECT * FROM audit_logs WHERE entity_id IN (SELECT image_id FROM medical_images);\n";
    echo "  SELECT * FROM data_integrity_logs WHERE record_id IN (SELECT image_id FROM medical_images);\n";
    exit(1);
} else {
    echo "\n✅ All images verified. No integrity issues detected.\n";
    exit(0);
}
