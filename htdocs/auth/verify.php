<?php
require_once __DIR__ . '/../config.php';

// Redireciona se já estiver logado
if (isset($_SESSION['user_id'])) {
  header('Location: ../dashboard/index.php');
  exit();
}

$message = '';
$message_type = ''; // 'success', 'error', 'info'
$show_resend = false;
$email = '';

// Verifica se o token foi fornecido
if (isset($_GET['token'])) {
  $token = $_GET['token'];
  
  try {
    // Conexão com o banco de dados
    $pdo = connectDB();
    
    // Busca usuário pelo token de verificação
    $stmt = $pdo->prepare("
      SELECT id, name, email, email_verified, created_at 
      FROM users 
      WHERE verification_token = ? 
      AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
      if ($user['email_verified']) {
        // Email já verificado
        $message = 'Seu e-mail já foi confirmado anteriormente. Você pode fazer login.';
        $message_type = 'info';
        $email = $user['email'];
      } else {
        // Verifica se o token pertence a este usuário
        if (hash_equals($user['verification_token'], $token)) {
          // Atualiza o status de verificação
          $stmt = $pdo->prepare("
            UPDATE users 
            SET email_verified = TRUE, verification_token = NULL 
            WHERE id = ?
          ");
          $stmt->execute([$user['id']]);
          
          if ($stmt->rowCount() > 0) {
            $message = '✅ Seu e-mail foi confirmado com sucesso! Sua conta está pronta para uso.';
            $message_type = 'success';
            $email = $user['email'];
          } else {
            $message = 'Erro ao confirmar e-mail. Tente novamente.';
            $message_type = 'error';
          }
        } else {
          $message = 'Token de verificação inválido.';
          $message_type = 'error';
        }
      }
    } else {
      $message = 'Token de verificação inválido ou expirado. O link é válido por 24 horas.';
      $message_type = 'error';
      $show_resend = true;
    }
  } catch (PDOException $e) {
    $message = 'Erro ao processar a verificação. Tente novamente mais tarde.';
    $message_type = 'error';
    error_log("Erro DB: " . $e->getMessage());
  }
} else {
  $message = 'Nenhum token de verificação fornecido.';
  $message_type = 'error';
  $show_resend = true;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="description" content="Verificação de e-mail - Carteira Digital Financeira" />
  <title>Verificar E-mail – CDF</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/verify.css">
</head>
<body>
  <div class="verify-container">
    <div class="verify-header">
      <div class="verify-icon <?php echo $message_type === 'error' ? 'error' : ($message_type === 'info' ? 'pending' : ''); ?>">
        <?php if ($message_type === 'success'): ?>
          <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
        <?php elseif ($message_type === 'error'): ?>
          <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        <?php else: ?>
          <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
        <?php endif; ?>
      </div>
      <h1>
        <?php 
        if ($message_type === 'success') echo 'E-mail Confirmado!';
        elseif ($message_type === 'error') echo 'Erro na Verificação';
        else echo 'Verificando E-mail...';
        ?>
      </h1>
      <p>
        <?php 
        if ($message_type === 'success') echo 'Sua conta foi ativada com sucesso!';
        elseif ($message_type === 'error') echo 'Algo deu errado com a verificação.';
        else echo 'Processando sua confirmação de e-mail...';
        ?>
      </p>
    </div>
    
    <?php if ($message): ?>
      <div class="<?php echo $message_type; ?>-message">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>
    
    <?php if ($message_type === 'success'): ?>
      <a href="login.php?email=<?php echo urlencode($email); ?>" class="btn btn-primary">
        Fazer Login
      </a>
    <?php endif; ?>
    
    <?php if ($show_resend || ($message_type === 'error' && isset($_GET['token']))): ?>
      <div style="margin-top: 20px;">
        <p style="color: #64748b; margin-bottom: 15px; font-size: 0.9rem;">
          Não recebeu o e-mail de confirmação?
        </p>
        
        <form method="POST" action="resend-verification.php" id="resendForm">
          <input 
            type="email" 
            name="email" 
            placeholder="Digite seu e-mail" 
            required
            style="width:100%;padding:12px;border:2px solid #e2e8f0;border-radius:12px;margin-bottom:12px;font-family:'Inter',sans-serif;font-size:1rem;"
          >
          <button type="submit" class="btn btn-secondary" id="resendBtn">
            Reenviar e-mail de confirmação
          </button>
        </form>
        
        <div id="resendTimer" class="timer" style="display:none;">
          Aguarde 1:00 para reenviar
        </div>
      </div>
    <?php endif; ?>
    
    <?php if ($message_type !== 'success'): ?>
      <div class="auth-links">
        <p>Já tem uma conta verificada?</p>
        <a href="login.php">Fazer login</a>
      </div>
    <?php endif; ?>
  </div>
  
  <script src="../assets/js/verify.js"></script>
</body>
</html>