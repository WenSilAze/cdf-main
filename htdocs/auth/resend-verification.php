<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// Carrega o PHPMailer
require_once '../vendor/phpmailer/PHPMailer.php';
require_once '../vendor/phpmailer/SMTP.php';
require_once '../vendor/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Método inválido']);
  exit();
}

$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['success' => false, 'message' => 'Email inválido']);
  exit();
}

try {
  // Conexão com o banco de dados
  $pdo = connectDB();
  
  // Busca usuário pelo email
  $stmt = $pdo->prepare("SELECT id, name, email, email_verified, verification_token FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Email não encontrado']);
    exit();
  }
  
  if ($user['email_verified']) {
    echo json_encode(['success' => false, 'message' => 'Este email já foi verificado. Faça login.']);
    exit();
  }
  
  // Gera novo token
  $new_token = bin2hex(random_bytes(32));
  $verification_link = "https://cdf.gt.tc/auth/verify.php?token=$new_token";
  
  // Atualiza token no banco
  $stmt = $pdo->prepare("
    UPDATE users 
    SET verification_token = ?, created_at = NOW() 
    WHERE email = ?
  ");
  $stmt->execute([$new_token, $email]);
  
  // Envia novo e-mail
  $mail = new PHPMailer(true);
  
  try {
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com'; // Seu e-mail host ex: gmail, hostinger
    $mail->SMTPAuth = true;
    $mail->Username = 'seu e-mail aqui'; // Seu e-mail
    $mail->Password = 'sua senha aqui'; // Sua senha
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465; // Seu e-mail porta aqui
    
    $mail->setFrom('contato@ruandmapc.com.br', 'CDF - Carteira Digital Financeira');
    $mail->addAddress($email, $user['name']);
    $mail->isHTML(true);
    $mail->Subject = 'Novo link de confirmação - Carteira Digital Financeira';
    
    $mail->Body = '
      <!DOCTYPE html>
      <html lang="pt-BR">
      <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
          body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
          .header { background: linear-gradient(135deg, #0ea5e9 0%, #065f46 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
          .content { background: #f8fafc; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e2e8f0; }
          .button { display: inline-block; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: white !important; text-decoration: none; padding: 15px 30px; border-radius: 8px; font-weight: 600; margin: 20px 0; text-align: center; }
          .button:hover { opacity: 0.9; }
          .footer { text-align: center; margin-top: 30px; color: #64748b; font-size: 0.9rem; }
          .highlight { background: #e0f2fe; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #0ea5e9; }
        </style>
      </head>
      <body>
        <div class="header">
          <h1>📧 Novo link de confirmação</h1>
          <p>Carteira Digital Financeira</p>
        </div>
        <div class="content">
          <p>Olá, <strong>' . htmlspecialchars($user['name']) . '</strong>!</p>
          <p>Você solicitou um novo link de confirmação para sua conta na <strong>Carteira Digital Financeira (CDF)</strong>.</p>
          
          <div style="text-align: center;">
            <a href="' . $verification_link . '" class="button">✅ Confirmar meu e-mail</a>
          </div>
          
          <div class="highlight">
            <p><strong>Link não funciona?</strong> Copie e cole este URL no seu navegador:</p>
            <p style="word-break: break-all; font-size: 0.85rem; margin: 10px 0; color: #0284c7;">' . htmlspecialchars($verification_link) . '</p>
          </div>
          
          <p>Este link é válido por <strong>24 horas</strong>. Após a confirmação, você poderá acessar todas as funcionalidades do CDF.</p>
          
          <p style="margin-top: 30px;">
            <strong>Atenciosamente,</strong><br>
            Equipe CDF<br>
            <a href="https://cdf.gt.tc" style="color: #0ea5e9;">cdf.gt.tc</a>
          </p>
        </div>
        <div class="footer">
          <p>© ' . date('Y') . ' Carteira Digital Financeira. Todos os direitos reservados.</p>
          <p style="font-size: 0.8rem; margin-top: 10px;">Este é um e-mail automático. Por favor, não responda.</p>
        </div>
      </body>
      </html>
    ';
    
    $mail->AltBody = "Olá {$user['name']}!\n\nVocê solicitou um novo link de confirmação para sua conta na Carteira Digital Financeira (CDF).\n\nPara ativar sua conta, clique neste link:\n$verification_link\n\nEste link é válido por 24 horas.\n\nAtenciosamente,\nEquipe CDF";
    
    $mail->send();
    echo json_encode(['success' => true]);
    
  } catch (PHPMailerException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao enviar e-mail: ' . $e->getMessage()]);
  }
  
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Erro no banco de dados']);
}
?>