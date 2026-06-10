<?php
require_once __DIR__ . '/../config.php';

// Headers para CORS e JSON
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = connectDB();

    // Lê o token do corpo da requisição
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    if (!isset($data['credential']) || empty($data['credential'])) {
        throw new Exception('Token não fornecido');
    }

    $id_token = $data['credential'];

    // Valida o token com a API do Google
    $response = file_get_contents("https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($id_token));
    $payload = json_decode($response, true);

    if (!$payload || isset($payload['error'])) {
        throw new Exception('Token inválido ou expirado');
    }

    // Extrai dados do usuário
    $email = $payload['email'] ?? '';
    $name = $payload['name'] ?? '';
    $google_id = $payload['sub'] ?? '';
    $picture = $payload['picture'] ?? null;

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email inválido');
    }

    // Verifica se usuário já existe
    $stmt = $pdo->prepare("SELECT id, name, email, google_id FROM users WHERE email = ? OR google_id = ?");
    $stmt->execute([$email, $google_id]);
    $user = $stmt->fetch();

    if ($user) {
        // Usuário existe
        if (empty($user['google_id'])) {
            // Atualiza para login via Google
            $stmt = $pdo->prepare("
                UPDATE users
                SET google_id = ?, name = ?, email_verified = TRUE, verified_at = NOW(), auth_provider = 'google'
                WHERE id = ?
            ");
            $stmt->execute([$google_id, $name, $user['id']]);
        }
        $user_id = $user['id'];
    } else {
        // Cria novo usuário
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, google_id, picture_url, email_verified, verified_at, created_at, auth_provider)
            VALUES (?, ?, ?, ?, TRUE, NOW(), NOW(), 'google')
        ");
        $stmt->execute([$name, $email, $google_id, $picture]);
        $user_id = $pdo->lastInsertId();
    }

    // Define sessão com duração longa
    ini_set('session.gc_maxlifetime', 2592000);
    session_set_cookie_params(2592000);
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['auth_provider'] = 'google';

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("Erro Google Login: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Falha na autenticação. Tente novamente.']);
}