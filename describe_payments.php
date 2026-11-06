<?php
require 'config.php';
$db = (new Database())->getConnection();
try {
    $stmt = $db->query("DESCRIBE payments");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($rows, JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
