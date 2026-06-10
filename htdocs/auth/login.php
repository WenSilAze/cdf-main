<?php
require_once __DIR__ . '/../config.php';

// Redireciona se já estiver logado
if (isset($_SESSION['user_id'])) {
  header('Location: ../dashboard/index.php');
  exit();
}

$error = '';
$success = '';

// Mensagem de erro do Google Login
if (isset($_SESSION['google_login_error'])) {
  $error = $_SESSION['google_login_error'];
  unset($_SESSION['google_login_error']);
}

// Processamento do formulário tradicional
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['google_login'])) {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $remember = isset($_POST['remember']);
  
  // Validação
  if (empty($email) || empty($password)) {
    $error = 'Por favor, preencha todos os campos.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Email inválido.';
  } else {
    try {
      // Conexão com o banco de dados
      $pdo = connectDB();
      
      // Busca usuário pelo email
      $stmt = $pdo->prepare("SELECT id, name, email, password, email_verified FROM users WHERE email = ?");
      $stmt->execute([$email]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);
      
      if (!$user) {
        $error = 'Email ou senha incorretos.';
      } elseif (!empty($user['password']) && !password_verify($password, $user['password'])) {
        $error = 'Email ou senha incorretos.';
      } elseif (!$user['email_verified']) {
        $error = 'Por favor, confirme seu e-mail antes de fazer login. Verifique sua caixa de entrada ou spam.';
      } else {
        // Login bem-sucedido - cria sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['auth_provider'] = 'email';
        
        // Sessão expira quando o navegador fecha (padrão)
        if ($remember) {
          ini_set('session.gc_maxlifetime', 2592000);
          session_set_cookie_params(2592000);
        }
        
        header('Location: ../dashboard/index.php');
        exit();
      }
    } catch (PDOException $e) {
      $error = 'Erro ao conectar ao banco de dados. Tente novamente mais tarde.';
      error_log("Erro DB: " . $e->getMessage());
    }
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="description" content="Faça login na Carteira Digital Financeira – controle suas finanças com segurança." />
  <title>Login – CDF</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
  <div class="login-container">
    <div class="login-header">
      <h1>Entrar</h1>
      <p>Acesse sua conta e continue controlando suas finanças</p>
    </div>
    
    <?php if ($error): ?>
      <div class="error-message">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>
    
    <!-- Contêiner ajustado para centralização e altura fixa -->
    <div id="googleSignInBtn" class="google-button-container"></div>
    
    <div class="divider">
      <span>ou</span>
    </div>
    
    <!-- Formulário de login tradicional -->
    <form method="POST" action="" id="loginForm">
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
          placeholder="Digite sua senha"
          required
          autocomplete="current-password"
        />
      </div>
      
      <div class="remember-forgot">
        <div class="remember-group">
          <input type="checkbox" id="remember" name="remember" <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
          <label for="remember" style="display:inline; font-weight:400; color:#475569; cursor:pointer;">Lembrar de mim</label>
        </div>
        <div class="forgot-password">
          <a href="#">Esqueceu a senha?</a>
        </div>
      </div>
      
      <button type="submit" class="btn btn-primary" id="submitBtn">
        Entrar
      </button>
    </form>
    
    <div class="auth-links">
      <p>Não tem uma conta?</p>
      <a href="register.php">Criar conta gratuita</a>
    </div>
  </div>
  
  <!-- Google Identity Services -->
  <script src="https://accounts.google.com/gsi/client" async defer></script>
  
  <!-- JavaScript Externo -->
  <script src="../assets/js/login.js"></script>
</body>
</html>