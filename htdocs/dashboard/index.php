<?php
session_start();
$isGuest = isset($_GET['guest']) && $_GET['guest'] == 1;
$userId = $isGuest ? null : ($_SESSION['user_id'] ?? null);
$userName = $_SESSION['user_name'] ?? 'Visitante';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="description" content="Dashboard da Carteira Digital Financeira – controle seus ganhos e gastos em tempo real." />
<title>Dashboard – CDF</title>
<!-- Chart.js + Plugins -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.0/dist/chartjs-plugin-zoom.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@3.0.0/dist/chartjs-plugin-annotation.min.js"></script>
<!-- CSS Externo -->
<link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<!-- Header com informações do usuário e botão de logout -->
<div class="header-container">
  <div class="user-info">
    <div class="user-name">Olá, <?php echo htmlspecialchars($userName); ?>!</div>
    <?php if (!$isGuest && $userId): ?>
      <div class="user-email"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></div>
    <?php endif; ?>
  </div>
  <button class="logout-btn" onclick="window.location.href='../auth/logout.php'" title="Sair">
    <span class="logout-icon">
      <svg viewBox="0 0 24 24" width="20" height="20" xmlns="http://www.w3.org/2000/svg">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
        <polyline points="16 17 21 12 16 7"/>
        <line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
    </span>
  </button>
</div>

<div class="balance-box">
  <div class="balance-label">Saldo atual</div>
  <div class="balance-value" id="balanceValue">R$ 0,00</div>
</div>

<div class="controls">
  <button class="filter-btn" onclick="applyTimeFilter('15m')">15m</button>
  <button class="filter-btn" onclick="applyTimeFilter('1h')">1h</button>
  <button class="filter-btn" onclick="applyTimeFilter('4h')">4h</button>
  <button class="filter-btn" onclick="applyTimeFilter('1d')">1d</button>
  <button class="filter-btn" onclick="applyTimeFilter('1m')">1m</button>
  <button class="filter-btn" onclick="applyTimeFilter('3m')">3m</button>
  <button class="filter-btn" onclick="applyTimeFilter('1y')">1y</button>
  <button class="filter-btn" onclick="applyTimeFilter('all')">Todos</button>
</div>

<!-- Campos de limite SEM valores fixos -->
<div class="limit-input">
  <label class="limit-label yellow">Limite Amarelo:</label>
  <input type="text" id="yellowLimit" placeholder="Ex: 50%913">
  <label class="limit-label red">Limite Vermelho:</label>
  <input type="text" id="redLimit" placeholder="Ex: 60%456,50">
</div>

<div class="chart-container">
  <canvas id="financeChart"></canvas>
</div>

<div class="empty-message" id="emptyMessage">Nenhuma movimentação ainda. Adicione uma!</div>

<div class="actions-wrapper">
  <div class="actions">
    <div class="action-group">
      <button class="btn earn" onclick="addTransaction('earn')">Ganhei</button>
      <span class="btn-description">Registrar entrada de dinheiro</span>
    </div>
    <div class="action-group">
      <button class="btn spend" onclick="addTransaction('spend')">Gastei</button>
      <span class="btn-description">Registrar saída de dinheiro</span>
    </div>
  </div>

  <!-- Menu de contexto -->
  <div class="context-menu" id="contextMenu">
    <button onclick="removeSelectedPoint()">Remover este ponto</button>
    <button onclick="closeContextMenu()">Cancelar</button>
  </div>

  <!-- Ícone de desfazer -->
  <div class="undo-icon" id="undoIcon" onclick="undoRemove()">
    <span class="undo-icon-text">↺</span>
  </div>
</div>

<!-- JavaScript -->
<script src="../assets/js/dashboard.js"></script>
</body>
</html>