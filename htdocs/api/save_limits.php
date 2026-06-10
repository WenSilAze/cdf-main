<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$field = $input['field'] ?? null;
$value = trim($input['value'] ?? '');

if (!in_array($field, ['yellow_limit', 'red_limit'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Campo inválido']);
    exit;
}

if (strlen($value) > 50) {
    http_response_code(400);
    echo json_encode(['error' => 'Valor muito longo']);
    exit;
}

// Aceita: números, vírgulas, pontos, % e espaços
if ($value !== '' && !preg_match('/^[\d.,%\s]+$/', $value)) {
    http_response_code(400);
    echo json_encode(['error' => 'Formato inválido']);
    exit;
}

try {
    $pdo = connectDB();

    $stmt = $pdo->prepare("UPDATE limits SET $field = ? WHERE user_id = ?");
    $stmt->execute([$value, $_SESSION['user_id']]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log("Erro ao salvar limite: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno']);
}