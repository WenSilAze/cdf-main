# 🗄️ Guia de Configuração do Banco de Dados

## 📍 Status Atual

❌ **Banco de dados não está configurado!**

- Credenciais em **11 arquivos** como placeholders
- Sem arquivo centralizado de configuração
- Sem validação automática

## ✅ Solução Implementada

Criei dois arquivos para centralizar e validar a configuração:

### 1. `config.php` - Configuração Centralizada
Local: `htdocs/config.php`

Este arquivo contém:
- ✅ Credenciais do banco em um único lugar
- ✅ Funções de conexão e segurança
- ✅ Inicialização automática de tabelas
- ✅ Configuração de email
- ✅ Configurações gerais da app

### 2. `setup.php` - Interface de Teste e Inicialização
Local: `htdocs/setup.php`

Acesse em: `http://localhost:8000/setup.php`

Verifica:
- ✓ Versão do PHP
- ✓ Extensão PDO MySQL
- ✓ Conexão com banco de dados
- ✓ Existência das tabelas
- ✓ Permissões

## 🚀 Passo a Passo de Configuração

### Passo 1: Configurar Credenciais

Abra `htdocs/config.php` e preencha:

```php
// ============================================
// 🔧 CONFIGURAÇÕES DO BANCO DE DADOS
// ============================================

// Para InfinityFree:
define('DB_HOST', 'sql113.infinityfree.com');
define('DB_NAME', 'IF0_37816263_cdf');              // ← Seu banco aqui
define('DB_USER', 'IF0_37816263_cdf_user');        // ← Seu usuário aqui
define('DB_PASS', 'sua_senha_real_aqui');          // ← Sua senha aqui

// OU para localhost:
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'cdf_db');
// define('DB_USER', 'root');
// define('DB_PASS', '');
```

### Passo 2: Testar Conexão

1. Abra seu navegador
2. Vá para: `http://localhost:8000/setup.php`
3. Verifique se passa em todas as verificações

### Passo 3: Inicializar Banco de Dados

Se as tabelas não existem, clique em **"🔧 Inicializar BD"** na página de setup.

Isto criará 3 tabelas automaticamente:
- ✅ `users` - Dados dos usuários
- ✅ `transactions` - Histórico de transações
- ✅ `limits` - Limites de alerta

## 🔐 Próximo Passo: Atualizar Arquivos Principais

Após configurar o `config.php`, os seguintes arquivos precisam ser atualizados para usar a configuração centralizada:

### Arquivos a Atualizar (11 no total)

1. `auth/login.php`
2. `auth/register.php`
3. `auth/verify.php`
4. `auth/resend-verification.php`
5. `auth/google-login.php`
6. `api/get_transactions.php`
7. `api/save_transaction.php`
8. `api/delete_transaction.php`
9. `api/get_limits.php`
10. `api/save_limits.php`

### Como Atualizar

Cada arquivo deve incluir no início:

```php
<?php
// Incluir configuração centralizada
require_once __DIR__ . '/../config.php';

// Agora use:
$pdo = connectDB();
// ... resto do código
```

**Exemplo antes:**
```php
define('DB_HOST', 'sql113.infinityfree.com');
define('DB_NAME', 'nome do banco de dados');
define('DB_USER', 'seu nome de usuario do banco de dados');
define('DB_PASS', 'senha do banco de dados');

$pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
    DB_USER,
    DB_PASS
);
```

**Exemplo depois:**
```php
require_once __DIR__ . '/../config.php';

$pdo = connectDB();
```

## 📋 Credenciais InfinityFree

Se você está usando **InfinityFree**:

1. Vá para [InfinityFree](https://www.infinityfree.com)
2. Faça login no seu painel
3. Procure por **MySQL Database** ou **phpmyadmin**
4. Você encontrará:
   - **Host**: `sql113.infinityfree.com` (ou similar)
   - **Database Name**: `IF0_xxxxx_nomedoBD`
   - **Username**: `IF0_xxxxx_usuario`
   - **Password**: Sua senha

## 🧪 Testar Depois da Configuração

Após tudo configurado:

1. Abra `http://localhost:8000`
2. Clique em "Acessar sem entrar"
3. Tente adicionar uma transação
4. Se funcionar, o banco está OK! ✅

## 📧 Configuração de Email (Opcional)

Se quiser que o app envie emails de verificação, configure em `config.php`:

```php
define('MAIL_HOST', 'smtp.seu-provider.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'seu-email@exemplo.com');
define('MAIL_PASS', 'sua-senha-email');
define('MAIL_FROM_NAME', 'Carteira Digital');
```

## ❓ FAQ

**P: Posso usar SQLite em vez de MySQL?**
R: Não no momento. O projeto foi desenvolvido para MySQL. Para SQLite, seria necessário refatorar todos os arquivos.

**P: Onde fico as credenciais se usar git?**
R: Adicione `config.php` ao `.gitignore`. Crie um `config.example.php` com placeholders.

**P: E se esquecer a senha do BD?**
R: Recupere no painel de controle do seu hosting (InfinityFree, etc).

**P: Posso rodar localmente em XAMPP/WAMP?**
R: Sim! Descomente as linhas do localhost em `config.php`.

## ✅ Checklist Final

- [ ] Criei conta no hosting (InfinityFree ou similar)
- [ ] Obtive credenciais do banco MySQL
- [ ] Editei `config.php` com as credenciais reais
- [ ] Abri `http://localhost:8000/setup.php`
- [ ] Cliquei em "🔧 Inicializar BD"
- [ ] Recebi confirmação de sucesso
- [ ] Testei a app criando um acesso visitante
- [ ] ✅ Banco de dados está OK!

---

**Dúvidas?** Verifique os logs em `error_log` ou console do navegador (F12).
