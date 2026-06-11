<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['type'], $input['value'], $input['timestampMs'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados ausentes']);
    exit;
}

$type = $input['type'];
$value = floatval($input['value']);
$description = isset($input['description']) ? trim(substr($input['description'], 0, 20)) : null;
$timestampMs = intval($input['timestampMs']);

if (!in_array($type, ['earn', 'spend'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo inválido']);
    exit;
}
if ($value <= 0 || $value > 999999999.99) {
    http_response_code(400);
    echo json_encode(['error' => 'Valor inválido']);
    exit;
}

try {
    $pdo = connectDB();

    $stmt = $pdo->prepare("
        INSERT INTO transactions (user_id, type, value, description, timestamp_ms)
        VALUES (?, ?, ?, ?, ?)
    ");

error_log("INPUT TYPE: " . $input['type']);
error_log("TYPE PARA BANCO: " . $type);

$stmt->execute([
    $_SESSION['user_id'],
    $type,
    $value,
    $description,
    $timestampMs
]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);

} catch (PDOException $e) {
    error_log("Erro ao salvar transação: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}