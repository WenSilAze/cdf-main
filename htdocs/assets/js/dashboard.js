// Formatação de moeda no padrão brasileiro
function formatCurrency(value) {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value);
}

// Obtém hora atual com milissegundos no formato hh:mm:ss.xxx
function getCurrentTimeWithMs() {
  const now = new Date();
  const h = String(now.getHours()).padStart(2, '0');
  const m = String(now.getMinutes()).padStart(2, '0');
  const s = String(now.getSeconds()).padStart(2, '0');
  const ms = String(now.getMilliseconds()).padStart(3, '0');
  return `${h}:${m}:${s}.${ms}`;
}

// Calcula o saldo máximo histórico
function calculateMaxHistoricalBalance() {
  let runningBalance = 0;
  let maxBalance = 0;
  for (const t of transactions) {
    runningBalance += (t.type === 'earn' || t.type === 'deposit' ? t.value : -t.value);
    if (runningBalance > maxBalance) {
      maxBalance = runningBalance;
    }
  }
  return maxBalance;
}

// Parse de limites: 456,50 | 50%913 | 50%
function parseLimit(input, currentBalance) {
  if (!input) return 0;
  input = input.trim();

  // "50%913"
  if (input.includes('%') && !input.endsWith('%')) {
    const parts = input.split('%');
    if (parts.length === 2) {
      const percentStr = parts[0].trim();
      const baseValueStr = parts[1].trim().replace(/\./g, '').replace(',', '.');
      const percent = parseFloat(percentStr);
      const baseValue = parseFloat(baseValueStr);
      if (!isNaN(percent) && !isNaN(baseValue)) {
        return (percent / 100) * baseValue;
      }
    }
  }

  // "50%"
  if (input.endsWith('%')) {
    const percent = parseFloat(input.replace('%', '').trim());
    if (!isNaN(percent)) {
      const maxBalance = calculateMaxHistoricalBalance();
      return (percent / 100) * maxBalance;
    }
  }

  // "456,50"
  const absoluteValue = parseFloat(input.replace(/\./g, '').replace(',', '.'));
  if (!isNaN(absoluteValue)) {
    return absoluteValue;
  }

  return 0;
}

// Variáveis globais
let transactions = [];
let selectedPointIndex = -1;
let chart = null;
let removedTransaction = null;
let removedIndex = -1;
const urlParams = new URLSearchParams(window.location.search);
const isGuest = urlParams.get('guest') === '1';

// Carrega transações
async function loadTransactions() {
  let loaded = [];
  if (isGuest) {
    const stored = localStorage.getItem('cdf_transactions');
    loaded = stored ? JSON.parse(stored) : [];
  } else {
    try {
      const res = await fetch('../api/get_transactions.php', { credentials: 'include' });
      if (res.ok) {
        const data = await res.json();
        if (Array.isArray(data)) {
loaded = data.map(t => ({
    ...t,
    value: parseFloat(t.value),
    timestampMs: parseInt(t.timestamp_ms)
}));
        }
      }
    } catch (err) {
      console.warn('Erro ao carregar transações:', err);
    }
  }
  transactions = Array.isArray(loaded) ? loaded : [];

const saldoTeste = transactions.reduce(
    (acc, t) =>
        acc + (
            t.type === 'earn' || t.type === 'deposit'
                ? t.value
                : -t.value
        ),
    0
);

  renderChartAfterLoad();
}

// Carrega limites (localStorage para guest, backend para logado)
async function loadLimits() {
  const yellowInput = document.getElementById('yellowLimit');
  const redInput = document.getElementById('redLimit');

  if (isGuest) {
    const yellowSaved = localStorage.getItem('cdf_yellow_limit') || '';
    const redSaved = localStorage.getItem('cdf_red_limit') || '';
    if (yellowInput) yellowInput.value = yellowSaved;
    if (redInput) redInput.value = redSaved;
  } else {
    try {
      const res = await fetch('../api/get_limits.php', { credentials: 'include' });
      if (res.ok) {
        const limits = await res.json();
        if (yellowInput) yellowInput.value = limits.yellowLimit || '';
        if (redInput) redInput.value = limits.redLimit || '';
      }
    } catch (err) {
      console.warn('Falha ao carregar limites do banco:', err);
    }
  }

  // Força atualização da cor
  setTimeout(() => {
    if (chart) renderChartAfterLoad();
  }, 10);
}

// Salva limite (localStorage ou backend)
async function saveLimit(field, value) {
  if (isGuest) {
    if (field === 'yellow_limit') localStorage.setItem('cdf_yellow_limit', value);
    if (field === 'red_limit') localStorage.setItem('cdf_red_limit', value);
  } else {
    try {
      await fetch('../api/save_limits.php', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ field, value })
      });
    } catch (err) {
      console.error('Erro ao salvar limite:', err);
    }
  }
}

// Inicializa o gráfico quando o canvas estiver pronto
function initChartWhenReady() {
  const canvas = document.getElementById('financeChart');
  if (canvas && canvas.offsetWidth > 0 && canvas.offsetHeight > 0) {
    const ctx = canvas.getContext('2d');
    if (!ctx) {
      console.error('[ERRO] getContext("2d") falhou.');
      return;
    }

    try {
      chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: [],
          datasets: [{
            label: 'Saldo (R$)',
            data: [],
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.15)',
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointHoverRadius: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              enabled: true,
              callbacks: {
                title: function(context) {
                  const index = context[0].dataIndex;
                  const t = transactions[index];
                  if (!t) return [''];
                  const date = new Date(t.timestampMs);
                  const day = String(date.getDate()).padStart(2, '0');
                  const month = String(date.getMonth() + 1).padStart(2, '0');
                  const year = date.getFullYear();
                  const formattedDate = `${day}/${month}/${year}`;
                  const dateTimeLine = `${formattedDate} - ${t.time}`;
                  const descriptionLine = t.description ? `"${t.description}"` : '"Sem descrição"';
                  return [dateTimeLine, descriptionLine];
                },
                label: function(context) {
                  const index = context.dataIndex;
                  const t = transactions[index];
                  if (!t) return 'Transação indisponível';
                  let balanceAtPoint = 0;
                  for (let i = 0; i <= index; i++) {
                    const tx = transactions[i];
                    balanceAtPoint += (
                      tx.type === 'earn' || tx.type === 'deposit')
                      ? tx.value
                      : -tx.value;
                  }
                 const movementLabel =
                 (t.type === 'earn' || t.type === 'deposit')
                 ? 'Ganhei'
                 : 'Gastei';
                  return [
                    `${movementLabel}: ${formatCurrency(t.value)}`,
                    `Saldo: ${formatCurrency(balanceAtPoint)}`
                  ];
                }
              }
            },
            zoom: {
              zoom: { wheel: { enabled: false }, pinch: { enabled: false }, mode: 'x' },
              pan: { enabled: true, mode: 'x' }
            },
            annotation: { annotations: {} }
          },
          scales: {
            y: { beginAtZero: false, grid: { drawBorder: false } },
            x: {
              grid: { display: false },
              ticks: { maxRotation: 0, minRotation: 0, autoSkip: true, maxTicksLimit: 6 }
            }
          },
          animation: { duration: 300 },
          interaction: { intersect: false, mode: 'nearest' },
          onClick: (event, elements) => {
            if (elements.length > 0) {
              openContextMenuAbovePoint(null, { index: elements[0].index });
            } else {
              closeAllOverlays();
            }
          }
        }
      });

      renderChartAfterLoad();
    } catch (err) {
      console.error('[ERRO] Falha ao criar gráfico:', err);
    }
    return;
  }

  setTimeout(initChartWhenReady, 200);
}

// Renderiza gráfico e atualiza cor do saldo
function renderChartAfterLoad() {
  if (!chart) {
    setTimeout(renderChartAfterLoad, 200);
    return;
  }

  const totalBalance = transactions.reduce((acc, t) => {

    return acc + (
        t.type === 'earn' || t.type === 'deposit'
            ? t.value
            : -t.value
    );
}, 0);

  document.getElementById('balanceValue').textContent = formatCurrency(totalBalance);
  updateBalanceColor(totalBalance);

  if (transactions.length === 0) {
    document.getElementById('emptyMessage').style.display = 'block';
    // ✅ CORREÇÃO: Nenhum ponto é desenhado — só eixos vazios
    chart.data.labels = [];
    chart.data.datasets[0].data = [];
    chart.update();
    return;
  }

  document.getElementById('emptyMessage').style.display = 'none';
  transactions.sort((a, b) => a.timestampMs - b.timestampMs);

  const labels = [];
  const data = [];
  let runningBalance = 0;

  for (const t of transactions) {
    runningBalance += (t.type === 'earn' || t.type === 'deposit'? t.value : -t.value);
    labels.push(formatTimeShort(t.time));
    data.push(parseFloat(runningBalance.toFixed(2)));
}
  chart.data.labels = labels;
  chart.data.datasets[0].data = data;
  chart.update();
}

function formatTimeShort(timeStr) {
  return timeStr.split(':')[0] + ':' + timeStr.split(':')[1];
}

function updateBalanceColor(balance) {
  let color = '#065f46';
  const yellowVal = parseLimit(document.getElementById('yellowLimit')?.value || '', balance);
  const redVal = parseLimit(document.getElementById('redLimit')?.value || '', balance);

  if (balance < redVal) {
    color = '#dc2626';
  } else if (balance < yellowVal) {
    color = '#f59e0b';
  }

  document.getElementById('balanceValue').style.color = color;
  if (chart) {
    const dataset = chart.data.datasets[0];
    dataset.borderColor = color;
    dataset.backgroundColor = color + '20';
    dataset.pointBackgroundColor = color;
    dataset.pointBorderColor = 'white';
    chart.update();
  }
}

// === CONTROLE DE OVERLAYS ===
function closeAllOverlays() {
  const menu = document.getElementById('contextMenu');
  const undoIcon = document.getElementById('undoIcon');
  menu.classList.remove('show');
  document.removeEventListener('click', closeContextMenuOnClickOutside);
  if (undoIcon.classList.contains('show')) {
    undoIcon.classList.remove('show');
    setTimeout(() => {
      undoIcon.style.display = '';
    }, 300);
  }
  removedTransaction = null;
  removedIndex = -1;
}

function closeContextMenu() {
  const menu = document.getElementById('contextMenu');
  menu.classList.remove('show');
  document.removeEventListener('click', closeContextMenuOnClickOutside);
}

function closeContextMenuOnClickOutside(e) {
  const menu = document.getElementById('contextMenu');
  const undoIcon = document.getElementById('undoIcon');
  const wrapper = document.querySelector('.actions-wrapper');
  if (!menu.contains(e.target) && !undoIcon.contains(e.target) && !wrapper.contains(e.target)) {
    closeAllOverlays();
  }
}

function openContextMenuAbovePoint(event, element) {
  const undoIcon = document.getElementById('undoIcon');
  const menu = document.getElementById('contextMenu');

  if (undoIcon.classList.contains('show')) {
    undoIcon.classList.remove('show');
    setTimeout(() => {
      undoIcon.style.display = '';
      showNewContextMenu(element.index);
    }, 300);
  } else {
    closeContextMenu();
    setTimeout(() => showNewContextMenu(element.index), 100);
  }
}

function showNewContextMenu(index) {
  selectedPointIndex = index;
  const menu = document.getElementById('contextMenu');
  void menu.offsetWidth;
  menu.classList.add('show');
  document.addEventListener('click', closeContextMenuOnClickOutside);
}

// ✅ capturar ID ANTES do splice
function removeSelectedPoint() {
  if (selectedPointIndex >= 0 && selectedPointIndex < transactions.length) {
    const transactionToRemove = { ...transactions[selectedPointIndex] };
    removedTransaction = transactionToRemove;
    removedIndex = selectedPointIndex;

    transactions.splice(selectedPointIndex, 1);

    if (isGuest) {
      localStorage.setItem('cdf_transactions', JSON.stringify(transactions));
    } else {
      const id = transactionToRemove.id;
      if (id) {
        fetch('../api/delete_transaction.php', {
          method: 'POST',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id })
        }).catch(console.error);
      }
    }

    renderChartAfterLoad();
    closeContextMenu();

    const undoIcon = document.getElementById('undoIcon');
    undoIcon.style.display = 'block';
    void undoIcon.offsetWidth;
    undoIcon.classList.add('show');
  }
}

function undoRemove() {
  if (removedTransaction && removedIndex >= 0) {
    transactions.splice(removedIndex, 0, removedTransaction);

    if (isGuest) {
      localStorage.setItem('cdf_transactions', JSON.stringify(transactions));
    } else {
      const tx = { ...removedTransaction };
      delete tx.id;
      fetch('../api/save_transaction.php', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(tx)
      }).catch(console.error);
    }

    renderChartAfterLoad();
  }

  const undoIcon = document.getElementById('undoIcon');
  undoIcon.classList.remove('show');
  setTimeout(() => {
    undoIcon.style.display = '';
  }, 300);
  removedTransaction = null;
  removedIndex = -1;
}

// Função para adicionar transação — MODAL COMPACTO
function addTransaction(type) {
  closeAllOverlays();
  const modal = document.createElement('div');
  modal.style.cssText = `
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 3000;
    padding: 12px;
  `;

  const form = document.createElement('div');
  form.style.cssText = `
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    width: 100%;
    max-width: 360px;
    font-family: 'Inter', sans-serif;
    padding: 20px;
  `;

  const title = document.createElement('h3');
  title.textContent = type === 'earn' ? 'Ganhei' : 'Gastei';
  title.style.cssText = `
    margin: 0 0 16px;
    color: ${type === 'earn' ? '#10b981' : '#ef4444'};
    font-weight: 600;
    font-size: 1.1rem;
    text-align: center;
  `;

  const valueLabel = document.createElement('label');
  valueLabel.textContent = 'Valor (R$)';
  valueLabel.style.cssText = `
    display: block;
    margin-bottom: 6px;
    font-size: 0.875rem;
    color: #475569;
  `;

  const valueInput = document.createElement('input');
  valueInput.type = 'text';
  valueInput.placeholder = 'Ex: 1.000,00';
  valueInput.style.cssText = `
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 1rem;
    margin-bottom: 16px;
    box-sizing: border-box;
  `;

  const descLabel = document.createElement('label');
  descLabel.textContent = 'Descrição (opcional)';
  descLabel.style.cssText = `
    display: block;
    margin-bottom: 6px;
    font-size: 0.875rem;
    color: #475569;
  `;

  const descContainer = document.createElement('div');
  descContainer.style.position = 'relative';

  const descInput = document.createElement('input');
  descInput.type = 'text';
  descInput.placeholder = 'Ex: Salário, mercado...';
  descInput.maxLength = 20;
  descInput.style.cssText = `
    width: 100%;
    padding: 12px 14px 12px 40px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 1rem;
    box-sizing: border-box;
  `;

  const counter = document.createElement('span');
  counter.textContent = '0/20';
  counter.style.cssText = `
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.75rem;
    color: #64748b;
  `;

  descContainer.appendChild(descInput);
  descContainer.appendChild(counter);

  const buttonContainer = document.createElement('div');
  buttonContainer.style.display = 'flex';
  buttonContainer.style.gap = '12px';
  buttonContainer.style.marginTop = '20px';

  const confirmBtn = document.createElement('button');
  confirmBtn.textContent = 'Confirmar';
  confirmBtn.style.cssText = `
    flex: 1;
    padding: 12px;
    background: ${type === 'earn' ? '#10b981' : '#ef4444'};
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
  `;
  confirmBtn.onmouseenter = () => confirmBtn.style.opacity = '0.95';
  confirmBtn.onmouseleave = () => confirmBtn.style.opacity = '1';

  const cancelBtn = document.createElement('button');
  cancelBtn.textContent = 'Cancelar';
  cancelBtn.style.cssText = `
    flex: 1;
    padding: 12px;
    background: #f1f5f9;
    color: #334155;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
  `;

  descInput.addEventListener('input', () => {
    const len = descInput.value.length;
    counter.textContent = `${len}/20`;
    if (len > 20) {
      descInput.value = descInput.value.substring(0, 20);
      counter.textContent = '20/20';
    }
  });

  confirmBtn.onclick = async () => {
    const valueStr = valueInput.value.trim();
    if (!valueStr) {
      alert('Por favor, insira um valor.');
      return;
    }
    let cleanValue = valueStr.replace(/\./g, '').replace(',', '.');
    const value = parseFloat(cleanValue);
    if (isNaN(value) || value <= 0) {
      alert('Por favor, insira um valor válido.');
      return;
    }

    const now = new Date();
    const timeWithMs = getCurrentTimeWithMs();
    const description = descInput.value.trim().substring(0, 20) || null;
    const newTransaction = {
      time: timeWithMs,
      type: type,
      value: value,
      description: description,
      timestampMs: now.getTime()
    };

    if (!isGuest) {
      try {
        const res = await fetch('../api/save_transaction.php', {
          method: 'POST',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(newTransaction)
        });
        const result = await res.json();
        if (result.error) {
          alert('Erro: ' + result.error);
          return;
        }
        transactions.push({ ...newTransaction, id: result.id });
        renderChartAfterLoad();
        document.body.removeChild(modal);
        alert(`${type === 'earn' ? 'Ganho' : 'Gasto'} de ${formatCurrency(value)} registrado!`);
      } catch (err) {
        console.error(err);
        alert('Falha na conexão com o servidor.');
      }
    } else {
      transactions.push(newTransaction);
      localStorage.setItem('cdf_transactions', JSON.stringify(transactions));
      renderChartAfterLoad();
      document.body.removeChild(modal);
      alert(`${type === 'earn' ? 'Ganho' : 'Gasto'} de ${formatCurrency(value)} registrado!`);
    }
  };

  cancelBtn.onclick = () => {
    document.body.removeChild(modal);
  };

  form.appendChild(title);
  form.appendChild(valueLabel);
  form.appendChild(valueInput);
  form.appendChild(descLabel);
  form.appendChild(descContainer);
  buttonContainer.appendChild(confirmBtn);
  buttonContainer.appendChild(cancelBtn);
  form.appendChild(buttonContainer);
  modal.appendChild(form);
  document.body.appendChild(modal);
  valueInput.focus();
}

// Inicialização robusta com MutationObserver implícito via retry
document.addEventListener('DOMContentLoaded', () => {
  loadTransactions().then(() => {
    // Tenta inicializar o gráfico várias vezes até o canvas estar pronto
    let attempts = 0;
    const tryInit = () => {
      const canvas = document.getElementById('financeChart');
      if (canvas && canvas.offsetWidth > 0) {
        initChartWhenReady();
      } else if (attempts < 20) {
        attempts++;
        setTimeout(tryInit, 200);
      }
    };
    tryInit();
    loadLimits();
  });

  // Configura salvamento dos limites
  function setupInput(input, field) {
    if (!input) return;
    input.addEventListener('blur', () => saveLimit(field, input.value.trim()));
    input.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') input.blur();
    });
    input.addEventListener('input', () => renderChartAfterLoad());
  }

  setupInput(document.getElementById('yellowLimit'), 'yellow_limit');
  setupInput(document.getElementById('redLimit'), 'red_limit');
});