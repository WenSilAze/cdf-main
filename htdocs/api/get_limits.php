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

    $stmt = $pdo->prepare("SELECT yellow_limit, red_limit FROM limits WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'yellowLimit' => $row['yellow_limit'] ?? '',
        'redLimit' => $row['red_limit'] ?? ''
    ]);

} catch (PDOException $e) {
    error_log("Erro ao carregar limites: " . $e->getMessage());
    echo json_encode(['yellowLimit' => '', 'redLimit' => '']);
}