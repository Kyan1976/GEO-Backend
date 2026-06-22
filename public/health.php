<?php
// Standalone health check - bypasses Laravel entirely
header('Content-Type: application/json');

// Check DB connection
$db_ok = false;
try {
    $pdo = new PDO(
        'pgsql:host=postgres;port=5432;dbname=geoflow',
        'postgres',
        getenv('DB_PASSWORD') ?: 'geoflow-password',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT, PDO::ATTR_TIMEOUT => 3]
    );
    $stmt = $pdo->query('SELECT 1');
    $db_ok = ($stmt !== false);
    $pdo = null;
} catch (Exception $e) {
    // try with different password
    try {
        $pdo = new PDO(
            'pgsql:host=postgres;port=5432;dbname=geoflow',
            'geo_user',
            'geoflow-password',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT, PDO::ATTR_TIMEOUT => 3]
        );
        $stmt = $pdo->query('SELECT 1');
        $db_ok = ($stmt !== false);
        $pdo = null;
    } catch (Exception $e2) {
        $db_ok = false;
    }
}

echo json_encode([
    'success' => true,
    'data' => [
        'status' => 'ok',
        'service' => 'geoflow',
        'database' => $db_ok ? 'connected' : 'disconnected',
        'php_version' => PHP_VERSION,
    ],
    'meta' => [
        'request_id' => uniqid(),
        'timestamp' => date('c'),
    ]
], JSON_UNESCAPED_UNICODE);
