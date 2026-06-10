<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado']);
    exit;
}

try {
    $pdo = connectDB();

    // 🔑 REMOVIDA A VÍRGULA ERRANTE APÓS `timestamp_ms`
    $stmt = $pdo->prepare("
        SELECT
            id,
            type,
            value,
            description,
            timestamp_ms,
            FROM_UNIXTIME(timestamp_ms / 1000, '%H:%i:%s.%f') AS time
        FROM transactions
        WHERE user_id = ?
        ORDER BY timestamp_ms ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $transactions = $stmt->fetchAll();

    // Formata milissegundos para hh:mm:ss.xxx
    foreach ($transactions as &$t) {
        if (isset($t['time']) && strpos($t['time'], '.') !== false) {
            [$timePart, $microseconds] = explode('.', $t['time'], 2);
            $ms = substr($microseconds, 0, 3);
            $t['time'] = $timePart . '.' . str_pad($ms, 3, '0', STR_PAD_RIGHT);
        }
    }

    echo json_encode($transactions);
} catch (PDOException $e) {
    error_log("Erro ao buscar transações: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao carregar dados']);
}