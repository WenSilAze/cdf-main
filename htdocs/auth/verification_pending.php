<?php
session_start();

// Verifica se veio do registro
if (!isset($_SESSION['pending_verification_email'])) {
  header('Location: register.php');
  exit();
}

$email = $_SESSION['pending_verification_email'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="description" content="Verificação de e-mail pendente - Carteira Digital Financeira" />
  <title>Verifique seu E-mail – CDF</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/verification_pending.css">
</head>
<body>
  <div class="pending-container">
    <div class="email-icon">📧</div>
    
    <div class="pending-header">
      <h1>Verifique seu e-mail</h1>
    </div>
    
    <div class="email-display">
      <?php echo htmlspecialchars($email); ?>
    </div>
    
    <div class="pending-content">
      <p>Enviamos um link de confirmação para o seu e-mail. Clique no link para ativar sua conta e começar a usar o CDF.</p>
      
      <div class="highlight">
        <p><strong>📧 Não recebeu o e-mail?</strong></p>
        <ul>
          <li>Verifique sua pasta de <strong>spam</strong> ou <strong>lixo eletrônico</strong></li>
          <li>Confira se o e-mail está correto</li>
          <li>Aguarde alguns minutos, pode demorar um pouco</li>
        </ul>
      </div>
      
      <p>O link de confirmação é válido por <strong>24 horas</strong>.</p>
      
      <div class="timer" id="countdown">Tempo restante: 24:00:00</div>
    </div>
    
    <a href="login.php" class="btn">Já verifiquei, fazer login</a>
    <a href="../index.html" class="btn btn-secondary">Voltar ao início</a>
  </div>
  
  <script src="../assets/js/verification_pending.js"></script>
</body>
</html>