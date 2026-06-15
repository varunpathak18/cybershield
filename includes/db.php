<?php
require_once dirname(__DIR__) . '/config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed. Check config.php']));
        }
    }
    return $pdo;
}

function query(string $sql, array $params = []): array {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function queryOne(string $sql, array $params = []): ?array {
    $result = query($sql, $params);
    return $result[0] ?? null;
}

function execute(string $sql, array $params = []): int {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int) db()->lastInsertId();
}
