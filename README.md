
# 💰 Carteira Digital Financeira (CDF)

Uma aplicação web moderna e intuitiva desenvolvida em **PHP**, **JavaScript** e **MySQL** para gerenciar e acompanhar ganhos e gastos de forma simples e eficiente.

> **Descrição**: Controle suas finanças pessoais com uma interface amigável, autenticação segura e visualizações em tempo real.

## 📊 Visão Geral

A **Carteira Digital Financeira** permite que você:
- 📱 Registre depósitos e saques com valores e descrições
- 📈 Visualize a evolução financeira através de gráficos interativos
- 🎯 Defina limites de alerta (amarelo e vermelho) para controle de gastos
- 👤 Crie conta pessoal com autenticação (modo desenvolvimento: sem verificação de email)
- 📧 Sistema de email pronto para produção (configure em `config.php`)
- 📱 Acesse sem criar conta (modo visitante)

## ✨ Principais Funcionalidades

| Feature | Descrição | Status |
|---------|-----------|--------|
| **Autenticação** | Login/Registro seguro (desenvolvimento: sem verificação email para fins educativos) | ✅ Funcional |
| **Acesso Visitante** | Teste sem criar conta - dados não persistem | ✅ Funcional |
| **Gráfico Interativo** | Visualização em tempo real com Chart.js | ✅ Funcional |
| **Filtros Temporais** | 15m, 1h, 4h, 1d, 1m, 3m, 1y e todos os períodos | ✅ Funcional |
| **Limites Personalizados** | Define alertas amarelo (aviso) e vermelho (crítico) | ✅ Funcional |
| **Histórico Completo** | Registro de todas as transações | ✅ Funcional |
| **Google Login** | Autenticação com Google (opcional - localhost não suporta) | ⚠️ Em desenvolvimento |

## 🛠 Tecnologias Utilizadas

| Tecnologia | Versão | Função |
|------------|--------|--------|
| **PHP** | 8.2+ | Backend, lógica de negócios e API |
| **JavaScript** | ES6+ | Frontend interativo e gráficos |
| **MySQL** | 5.7+ | Banco de dados |
| **Chart.js** | 4.4.0 | Visualização de gráficos |
| **HTML5 & CSS3** | - | Marcação e estilos |

## 📋 Requisitos do Sistema

- **PHP** 8.2 ou superior
- **MySQL** 5.7 ou superior (ou MariaDB 10.3+)
- **Servidor Web** (Apache, Nginx ou servidor PHP embutido)
- **Navegador** moderno com suporte a ES6

## 🚀 Como Instalar e Executar

### 1️⃣ Clonar o Repositório

```bash
git clone https://github.com/WenSilAze/cdf-main.git
cd cdf-main/htdocs
```

### 2️⃣ Configurar Credenciais do Banco

**Abra `htdocs/config.php` e configure suas credenciais:**

```php
// Para localhost com MySQL Workbench/XAMPP/WAMP:
define('DB_HOST', 'localhost');
define('DB_NAME', 'cdf_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// OU para InfinityFree/hosting remoto:
// define('DB_HOST', 'sql113.infinityfree.com');
// define('DB_NAME', 'seu_banco_aqui');
// define('DB_USER', 'seu_usuario_aqui');
// define('DB_PASS', 'sua_senha_aqui');
```

### 3️⃣ Inicializar o Banco de Dados

**Opção A: Usando Interface Web (Recomendado)**

1. Inicie o servidor: `php -S localhost:8000 -t ./htdocs`
2. Acesse: `http://localhost:8000/setup.php`
3. Verifique se tudo passou nos testes
4. Clique em **"🔧 Inicializar BD"** para criar as tabelas

**Opção B: Criar Manualmente**

Execute este SQL no MySQL Workbench:

```sql
CREATE DATABASE cdf_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cdf_db;

-- Tabela de usuários
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255),
    email_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de transações
CREATE TABLE transactions (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de limites
CREATE TABLE limits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    yellow_limit DECIMAL(10, 2),
    red_limit DECIMAL(10, 2),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4️⃣ Iniciar o Servidor

**Opção 1: Servidor PHP Embutido** (Recomendado para desenvolvimento)

```bash
cd htdocs
php -S localhost:8000
```

Acesse: `http://localhost:8000` ou `http://localhost:8000/setup.php` para verificar a configuração

**⚙️ Status do Setup:**
- Acesse `http://localhost:8000/setup.php` para ver:
  - ✓ Versão do PHP
  - ✓ Extensões instaladas
  - ✓ Conexão com BD
  - ✓ Tabelas criadas
  - ✓ Permissões de escrita

**Opção 2: Apache/Nginx**

Configure o DocumentRoot apontando para `/htdocs`

## 📖 Como Usar

### Acessar sem Conta (Visitante)

1. Clique em **"Acessar sem entrar"** na página inicial
2. Use o dashboard sem limites (dados não são salvos)
3. Teste todas as funcionalidades

### Criar Conta

1. Clique em **"Registrar"**
2. Preencha nome, email e senha (mínimo 6 caracteres)
3. **Modo Desenvolvimento**: Conta criada instantaneamente e salva no banco de dados! ✅
4. Faça login e comece a usar

### Usar o Dashboard

1. **Adicione uma transação:**
   - **Ganhei** → Registrar entrada de dinheiro (depósito)
   - **Gastei** → Registrar saída de dinheiro (saque)
   - Digite o valor e clique no botão
   - Descrição é opcional

2. **Defina seus limites:**
   - **Limite Amarelo** → Alerta (valores maiores mostram aviso)
   - **Limite Vermelho** → Crítico (valores maiores mostram risco)
   - Formato: números com ponto ou vírgula (ex: 1000.50 ou 1000,50)

3. **Visualize seus dados:**
   - Use os filtros temporais (15m, 1h, 4h, 1d, 1m, 3m, 1y, Todos)
   - O gráfico atualiza em tempo real
   - Clique no gráfico para remover um ponto
   - Clique no ícone **↺** para desfazer a remoção

4. **Gerenciar transações:**
   - Veja o histórico completo
   - Remova transações antigo clicando sobre elas no gráfico
   - Saldo se atualiza automaticamente

## 🔌 API REST

### GET `/api/get_transactions.php`

Retorna todas as transações do usuário.

**Response:**
```json
{
  "transactions": [
    {
      "id": 1,
      "type": "deposit",
      "value": 1000.00,
      "description": "Salário",
      "timestamp_ms": 1717900000000
    }
  ]
}
```

### POST `/api/save_transaction.php`

Cria ou atualiza uma transação.

**Request:**
```json
{
  "type": "deposit|withdrawal",
  "value": 1000.00,
  "description": "Descrição"
}
```

### DELETE `/api/delete_transaction.php`

Deleta uma transação por ID.

**Query:** `?id=1`

### GET/POST `/api/get_limits.php` e `/api/save_limits.php`

Gerencia limites de alerta do usuário.

## 🔐 Segurança

- ✅ Senhas com hash bcrypt
- ✅ Proteção CSRF com sessões
- ✅ Validação de email com token
- ✅ Autenticação obrigatória para acesso aos dados
- ✅ Prepared statements contra SQL injection

## ⚙️ Modo Desenvolvimento vs Produção

### Desenvolvimento (Padrão Atual)

```php
// Em auth/register.php
define('EMAIL_VERIFICATION_ENABLED', false);
```

- ✅ Contas criadas instantaneamente
- ✅ Perfeito para testes rápidos
- ✅ Sem dependência de servidor SMTP

### Produção (Quando Necessário)

**1. Configure suas credenciais de email em `config.php`:**

```php
define('MAIL_HOST', 'smtp.seu-provedor.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'seu-email@dominio.com');
define('MAIL_PASS', 'sua-senha-email');
define('MAIL_FROM_NAME', 'Carteira Digital');
```

**2. Ative verificação de email em `auth/register.php`:**

```php
define('EMAIL_VERIFICATION_ENABLED', true);
```

**3. Descomente a função `sendVerificationEmail()` em `auth/register.php`**

**Provedores SMTP Recomendados:**
- Gmail: `smtp.gmail.com:587` (gerar app password)
- Hostinger: `smtp.hostinger.com:465` (SSL)
- SendGrid / Brevo: APIs modernas com melhor entrega

## 🐛 Troubleshooting

| Problema | Solução |
|----------|---------|
| Erro 404 ao acessar | Use `php -S localhost:8000` dentro de `htdocs` |
| Erro de conexão MySQL | Verifique `config.php` com credenciais corretas. Use `setup.php` para testar |
| "Não autenticado" em visitante | Limpe cookies/cache do navegador e acesse novamente |
| "Conta criada com sucesso" mas sem email | Está em modo desenvolvimento (esperado). Para ativar emails, veja seção **"Produção"** |
| Email não chega (modo produção) | Configure `MAIL_HOST`, `MAIL_USER`, `MAIL_PASS` em `config.php` e defina `EMAIL_VERIFICATION_ENABLED = true` |
| Gráfico não aparece | Verifique console do navegador (F12) por erros de JavaScript |
| Tabelas não existem | Acesse `http://localhost:8000/setup.php` e clique em "🔧 Inicializar BD" |

## 📚 Documentação Adicional

- **[DATABASE_SETUP.md](DATABASE_SETUP.md)** - Guia detalhado de configuração do banco de dados
- **[config.php](htdocs/config.php)** - Arquivo de configuração centralizado (edite aqui!)

## 📝 Notas de Desenvolvimento

- ✅ Configuração centralizada em `config.php` (atualizar aqui uma única vez!)
- ✅ Setup automático de banco de dados via `setup.php`
- ✅ Modo visitante não persiste dados (teste sem criar conta)
- ✅ Timestamps de transações em milissegundos para precisão
- ✅ Gráfico interativo com Chart.js (zoom, pan, filtros temporais)
- ✅ Interface totalmente responsiva (mobile, tablet, desktop)
- ✅ Descrições nos botões "Ganhei" e "Gastei" para melhor UX
- ✅ Prepared statements para proteção contra SQL injection
- ✅ Hash bcrypt para senhas com custo 12

### Arquitetura

```
Frontend (HTML/CSS/JS)
        ↓
PHP API REST (/api/)
        ↓
Config Centralizado
        ↓
MySQL Banco de Dados
```

Todos os endpoints da API exigem autenticação via sessão PHP.

## � Changelog

### v1.0.0 - Modo Desenvolvimento
- ✅ Registro/Login sem verificação de email
- ✅ Dashboard com transações em tempo real
- ✅ Gráfico interativo com múltiplos filtros
- ✅ Sistema de limites de alerta
- ✅ Modo visitante funcional
- ✅ API REST completa
- ✅ Responsivo para todos os dispositivos

## �📄 Licença

Este projeto é de código aberto para fins educacionais.

## 👨‍💻 Desenvolvido por

**Laboratório IV - Semestre 4**  
Faculdade/Instituição Senac

**Tecnologia:** PHP 8.2 + JavaScript ES6 + MySQL 5.7+  
**Deploy Recomendado:** Apache/Nginx com PHP 8.2+

### Status do Projeto ✅

**Essencial (Pronto para Desenvolvimento)**
- [x] Dashboard funcional com gráficos interativos
- [x] Autenticação com registro e login
- [x] API REST para transações
- [x] Limites de alerta personalizáveis
- [x] Configuração centralizada
- [x] Setup automático de banco de dados
- [x] Interface responsiva
- [x] Modo visitante
- [x] Repositório GitHub

**Produção**
- [x] Sistema de verificação de email (desativado em desenvolvimento)
- [ ] Deploy em produção (InfinityFree/Heroku)
- [ ] Google Login configurado para domínio
- [ ] Testes automatizados
- [ ] CI/CD Pipeline

---

**Última atualização**: Junho 2026  
**Versão**: 1.0.0  
**Licença**: Educacional
