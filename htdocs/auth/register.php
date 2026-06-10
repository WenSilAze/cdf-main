<?php
require_once __DIR__ . '/../config.php';

// Redireciona se já estiver logado
if (isset($_SESSION['user_id'])) {
  header('Location: ../dashboard/index.php');
  exit();
}

$error = '';
$success = '';

// ============================================
// 🔧 MODO DE DESENVOLVIMENTO: Verificação de Email Desativada
// Em produção, descomente a função sendVerificationEmail abaixo
// ============================================
define('EMAIL_VERIFICATION_ENABLED', false);

// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';
  
  // Validação
  if (empty($name) || empty($email) || empty($password)) {
    $error = 'Por favor, preencha todos os campos.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Email inválido.';
  } elseif (strlen($password) < 6) {
    $error = 'A senha deve ter pelo menos 6 caracteres.';
  } elseif ($password !== $confirm_password) {
    $error = 'As senhas não coincidem.';
  } else {
    try {
      // Conexão com o banco de dados
      $pdo = connectDB();
      
      // Verifica se email já existe
      $stmt = $pdo->prepare("SELECT id, email_verified FROM users WHERE email = ?");
      $stmt->execute([$email]);
      $existingUser = $stmt->fetch();
      
      if ($existingUser) {
        $error = 'Este email já está cadastrado.';
      } else {
        // Cria novo usuário
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Em modo desenvolvimento: email verificado automaticamente
        // Em produção: mudar email_verified para FALSE e implementar verificação
        $stmt = $pdo->prepare("
          INSERT INTO users (name, email, password, email_verified, created_at)
          VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$name, $email, $hashed_password, EMAIL_VERIFICATION_ENABLED ? 0 : 1]);
        
        $success = '✅ Conta criada com sucesso! Você já pode fazer login.';
      }
    } catch (PDOException $e) {
      $error = 'Erro ao conectar ao banco de dados. Tente novamente mais tarde.';
      error_log("Erro DB: " . $e->getMessage());
    }
  }
}

// ============================================
// MODO DESENVOLVIMENTO - Email desativado
// Em produção, descomente a função sendVerificationEmail() abaixo
// e mude EMAIL_VERIFICATION_ENABLED para true
// ============================================
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="description" content="Cadastre-se na Carteira Digital Financeira – controle suas finanças com segurança." />
  <title>Registrar – CDF</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/register.css">
</head>
<body>
  <div class="register-container">
    <div class="register-header">
      <h1>Criar conta</h1>
      <p>Cadastre-se e comece a controlar suas finanças</p>
    </div>
    
    <div class="info-message">
      <?php if (EMAIL_VERIFICATION_ENABLED): ?>
        📧 Após o cadastro, você receberá um e-mail para confirmar sua conta.
      <?php else: ?>
        ✅ Modo desenvolvimento: Sua conta será criada instantaneamente! Você poderá fazer login imediatamente.
      <?php endif; ?>
    </div>
    
    <?php if ($error): ?>
      <div class="error-message">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="success-message">
        <?php echo htmlspecialchars($success); ?>
        <div style="margin-top: 20px; text-align: center;">
          <a href="login.php" style="
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
          ">Ir para Login →</a>
        </div>
      </div>
      <script>
        // Redireciona para login após 3 segundos
        setTimeout(() => {
          window.location.href = 'login.php';
        }, 3000);
      </script>
    <?php else: ?>
      <form method="POST" action="" id="registerForm">
      <div class="form-group">
        <label for="name">Nome completo</label>
        <input
          type="text"
          id="name"
          name="name"
          value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
          placeholder="Digite seu nome"
          required
          autocomplete="name"
        />
      </div>
      
      <div class="form-group">
        <label for="email">Email</label>
        <input
          type="email"
          id="email"
          name="email"
          value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
          placeholder="seu@email.com"
          required
          autocomplete="email"
        />
      </div>
      
      <div class="form-group">
        <label for="password">Senha</label>
        <input
          type="password"
          id="password"
          name="password"
          placeholder="Mínimo 6 caracteres"
          required
          autocomplete="new-password"
        />
      </div>
      
      <div class="form-group">
        <label for="confirm_password">Confirmar senha</label>
        <input
          type="password"
          id="confirm_password"
          name="confirm_password"
          placeholder="Repita sua senha"
          required
          autocomplete="new-password"
        />
      </div>
      
      <button type="submit" class="btn btn-primary" id="submitBtn">
        Cadastrar
      </button>
    </form>
    <?php endif; ?>
    
    <div class="auth-links">
      <p>Já tem uma conta?</p>
      <a href="login.php">Entrar agora</a>
    </div>
  </div>
  
  <script src="../assets/js/register.js"></script>
</body>
</html>