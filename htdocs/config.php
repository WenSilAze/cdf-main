<?php
/**
 * Arquivo de Configuração Centralizado - Carteira Digital Financeira
 * 
 * Este arquivo contém todas as configurações da aplicação
 * IMPORTANTE: Preencha as credenciais reais do seu banco de dados
 */

// ============================================
// 🔧 CONFIGURAÇÕES DO BANCO DE DADOS
// ============================================

// Se estiver usando localhost com MySQL Workbench / XAMPP / WAMP
define('DB_HOST', 'localhost');
define('DB_NAME', 'cdf_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Se estiver usando InfinityFree ou outro host remoto
// define('DB_HOST', 'sql113.infinityfree.com');
// define('DB_NAME', 'IF0_37816263_cdf');
// define('DB_USER', 'IF0_37816263_cdf_user');
// define('DB_PASS', 'sua_senha_aqui');

// ============================================
// CONFIGURAÇÕES GERAIS
// ============================================

define('APP_NAME', 'Carteira Digital Financeira');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost:8000');

// Configuração de timezone
date_default_timezone_set('America/Sao_Paulo');

// ============================================
// 📧 CONFIGURAÇÕES DE EMAIL (PHPMailer)
// ============================================

define('MAIL_HOST', 'smtp.seu-email.com');           // Seu SMTP
define('MAIL_PORT', 587);                           // Porta SMTP
define('MAIL_USER', 'seu-email@exemplo.com');       // Email remetente
define('MAIL_PASS', 'sua-senha-email');             // Senha email
define('MAIL_FROM_NAME', 'Carteira Digital');       // Nome exibido

// ============================================
// 🔐 CONFIGURAÇÕES DE SEGURANÇA
// ============================================

define('SESSION_TIMEOUT', 3600);                    // Sessão expira em 1 hora
define('PASSWORD_MIN_LENGTH', 6);                   // Mínimo de caracteres na senha
define('MAX_LOGIN_ATTEMPTS', 5);                    // Máximo de tentativas de login
define('LOCKOUT_TIME', 900);                        // Bloqueio por 15 minutos

// ============================================
// 🗄️ FUNÇÃO DE CONEXÃO COM BANCO DE DADOS
// ============================================

/**
 * Função para conectar ao banco de dados
 * @return PDO Conexão com o banco
 * @throws PDOException Se houver erro na conexão
 */
function connectDB() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 10,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        // Log do erro (não mostrar detalhes ao usuário)
        error_log("Erro de conexão BD: " . $e->getMessage());
        
        // Se não está em produção, mostrar erro
        if ($_ENV['APP_ENV'] !== 'production') {
            die("❌ Erro ao conectar no banco de dados:<br>" . htmlspecialchars($e->getMessage()));
        } else {
            die("❌ Erro ao conectar no banco de dados. Tente novamente mais tarde.");
        }
    }
}

// ============================================
// 📋 INICIALIZAÇÃO AUTOMÁTICA DO BANCO
// ============================================

/**
 * Criar tabelas se não existirem
 * Execute esta função uma única vez durante a instalação
 */
function initializeDatabase() {
    try {
        $pdo = connectDB();
        
        // Tabela de usuários
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255),
                email_verified BOOLEAN DEFAULT FALSE,
                verification_token VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Tabela de transações
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS transactions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                type ENUM('deposit', 'withdrawal') NOT NULL,
                value DECIMAL(10, 2) NOT NULL,
                description TEXT,
                timestamp_ms BIGINT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX (user_id),
                INDEX (timestamp_ms)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Tabela de limites
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS limits (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL UNIQUE,
                yellow_limit DECIMAL(10, 2),
                red_limit DECIMAL(10, 2),
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        return true;
    } catch (PDOException $e) {
        error_log("Erro ao inicializar BD: " . $e->getMessage());
        return false;
    }
}

// ============================================
// 🛡️ FUNÇÕES DE SEGURANÇA
// ============================================

/**
 * Hash seguro de senha com bcrypt
 * @param string $password Senha em texto plano
 * @return string Senha com hash
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verificar senha
 * @param string $password Senha em texto plano
 * @param string $hash Hash da senha
 * @return bool Verdadeiro se coincide
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Sanitizar entrada do usuário
 * @param string $input Entrada a sanitizar
 * @return string Entrada sanitizada
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// ============================================
// 📝 INICIALIZAÇÃO
// ============================================

// Iniciar sessão se não estiver já
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detectar ambiente
$_ENV['APP_ENV'] = getenv('APP_ENV') ?: 'development';
