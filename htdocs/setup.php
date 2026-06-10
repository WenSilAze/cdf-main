<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Configuração - CDF</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .check-item {
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 8px;
            background: #f5f5f5;
            border-left: 4px solid #ddd;
        }
        .check-item.success {
            background: #e8f5e9;
            border-left-color: #4caf50;
        }
        .check-item.error {
            background: #ffebee;
            border-left-color: #f44336;
        }
        .check-item.warning {
            background: #fff3e0;
            border-left-color: #ff9800;
        }
        .icon {
            font-size: 20px;
            margin-right: 12px;
            width: 24px;
        }
        .text {
            flex: 1;
        }
        .label {
            font-weight: 600;
            color: #333;
            display: block;
        }
        .detail {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }
        .actions {
            margin-top: 30px;
            display: flex;
            gap: 10px;
        }
        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-secondary {
            background: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
        }
        .btn-secondary:hover {
            background: #e8e8e8;
        }
        .alert {
            padding: 15px;
            margin-top: 20px;
            border-radius: 8px;
            font-size: 13px;
        }
        .alert-warning {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            color: #e65100;
        }
        .alert-error {
            background: #ffebee;
            border-left: 4px solid #f44336;
            color: #c62828;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Teste de Configuração</h1>
        <p class="subtitle">Carteira Digital Financeira</p>

        <?php
        // Incluir configuração
        require_once 'config.php';

        $allOk = true;
        $checks = [];

        // ============================
        // Teste 1: Versão do PHP
        // ============================
        $phpVersion = phpversion();
        $phpOk = version_compare($phpVersion, '7.4', '>=');
        $checks[] = [
            'name' => 'Versão do PHP',
            'status' => $phpOk ? 'success' : 'error',
            'detail' => "Versão: $phpVersion (Mínimo: 7.4)",
            'icon' => $phpOk ? '✓' : '✗'
        ];
        $allOk = $allOk && $phpOk;

        // ============================
        // Teste 2: Extensão PDO
        // ============================
        $pdoOk = extension_loaded('PDO');
        $checks[] = [
            'name' => 'Extensão PDO',
            'status' => $pdoOk ? 'success' : 'error',
            'detail' => $pdoOk ? 'Instalada' : 'Não encontrada',
            'icon' => $pdoOk ? '✓' : '✗'
        ];
        $allOk = $allOk && $pdoOk;

        // ============================
        // Teste 3: Extensão PDO MySQL
        // ============================
        $mysqlOk = extension_loaded('pdo_mysql');
        $checks[] = [
            'name' => 'Extensão PDO MySQL',
            'status' => $mysqlOk ? 'success' : 'error',
            'detail' => $mysqlOk ? 'Instalada' : 'Não encontrada',
            'icon' => $mysqlOk ? '✓' : '✗'
        ];
        $allOk = $allOk && $mysqlOk;

        // ============================
        // Teste 4: Conexão com BD
        // ============================
        $bdOk = false;
        $bdDetail = '';
        $bdIcon = '✗';

        try {
            $pdo = connectDB();
            $bdOk = true;
            $bdDetail = "Conectado a: " . DB_NAME . " @ " . DB_HOST;
            $bdIcon = '✓';
        } catch (Exception $e) {
            $bdDetail = "Erro: " . substr($e->getMessage(), 0, 60) . "...";
            $bdIcon = '✗';
        }

        $checks[] = [
            'name' => 'Conexão com Banco de Dados',
            'status' => $bdOk ? 'success' : 'error',
            'detail' => $bdDetail,
            'icon' => $bdIcon
        ];
        $allOk = $allOk && $bdOk;

        // ============================
        // Teste 5: Tabelas do BD
        // ============================
        $tablesOk = false;
        $tablesDetail = '';
        $tablesIcon = '✗';

        if ($bdOk) {
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
                $tablesOk = $stmt->rowCount() > 0;
                $tablesDetail = $tablesOk 
                    ? "Tabelas encontradas (users, transactions, limits)" 
                    : "Tabelas não encontradas - Execute a inicialização";
                $tablesIcon = $tablesOk ? '✓' : '⚠';
            } catch (Exception $e) {
                $tablesDetail = "Erro ao verificar: " . $e->getMessage();
                $tablesIcon = '✗';
            }
        } else {
            $tablesDetail = "Não foi possível verificar (BD não conectado)";
            $tablesIcon = '—';
        }

        $checks[] = [
            'name' => 'Tabelas do Banco de Dados',
            'status' => $tablesOk ? 'success' : ($bdOk ? 'warning' : 'error'),
            'detail' => $tablesDetail,
            'icon' => $tablesIcon
        ];
        $allOk = $allOk && $tablesOk;

        // ============================
        // Teste 6: Permissão de escrita
        // ============================
        $writeOk = is_writable(__DIR__);
        $checks[] = [
            'name' => 'Permissão de Escrita',
            'status' => $writeOk ? 'success' : 'warning',
            'detail' => $writeOk ? 'OK' : 'Sem permissão (opcional)',
            'icon' => $writeOk ? '✓' : '⚠'
        ];

        // Renderizar checks
        foreach ($checks as $check):
        ?>
        <div class="check-item <?php echo $check['status']; ?>">
            <div class="icon"><?php echo $check['icon']; ?></div>
            <div class="text">
                <span class="label"><?php echo $check['name']; ?></span>
                <span class="detail"><?php echo $check['detail']; ?></span>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Alertas -->
        <?php if (!$bdOk): ?>
        <div class="alert alert-error">
            <strong>⚠️ Banco de dados não configurado!</strong><br>
            Edite o arquivo <code>config.php</code> com suas credenciais MySQL reais.
        </div>
        <?php endif; ?>

        <?php if (!$tablesOk && $bdOk): ?>
        <div class="alert alert-warning">
            <strong>⚠️ Tabelas não encontradas!</strong><br>
            Execute a inicialização do banco de dados.
        </div>
        <?php endif; ?>

        <!-- Ações -->
        <div class="actions">
            <?php if ($bdOk && !$tablesOk): ?>
            <form method="POST" style="flex: 1;">
                <button type="submit" name="action" value="init_db" class="btn btn-primary">
                    🔧 Inicializar BD
                </button>
            </form>
            <?php endif; ?>
            
            <?php if ($allOk): ?>
            <a href="index.html" class="btn btn-primary" style="text-decoration: none;">
                ✅ Ir para Home
            </a>
            <?php else: ?>
            <button class="btn btn-secondary" onclick="location.reload()">
                🔄 Recarregar
            </button>
            <?php endif; ?>
        </div>
    </div>

    <?php
    // ============================
    // Processar Inicialização do BD
    // ============================
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'init_db' && $bdOk) {
            if (initializeDatabase()) {
                echo '<script>alert("✅ Banco de dados inicializado com sucesso!"); location.reload();</script>';
            } else {
                echo '<script>alert("❌ Erro ao inicializar banco de dados. Verifique os logs.");</script>';
            }
        }
    }
    ?>
</body>
</html>
