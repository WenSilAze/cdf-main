document.addEventListener('DOMContentLoaded', function() {
  const resendForm = document.getElementById('resendForm');
  const resendBtn = document.getElementById('resendBtn');
  const timerElement = document.getElementById('resendTimer');
  
  let countdown = 60;
  let countdownInterval = null;
  
  // Função para atualizar o timer
  function updateTimer() {
    if (countdown > 0) {
      countdown--;
      const minutes = Math.floor(countdown / 60);
      const seconds = countdown % 60;
      timerElement.textContent = `Aguarde ${minutes}:${seconds.toString().padStart(2, '0')} para reenviar`;
    } else {
      clearInterval(countdownInterval);
      timerElement.style.display = 'none';
      resendBtn.disabled = false;
      resendBtn.innerHTML = 'Reenviar e-mail de confirmação';
    }
  }
  
  // Inicializa o timer se existir
  if (timerElement && resendBtn) {
    countdownInterval = setInterval(updateTimer, 1000);
    
    resendForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      resendBtn.disabled = true;
      resendBtn.innerHTML = `
        <span class="loading-spinner"></span>
        Enviando...
      `;
      
      // Envia formulário
      fetch('resend-verification.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(new FormData(resendForm))
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Reinicia o timer
          countdown = 60;
          timerElement.style.display = 'block';
          resendBtn.disabled = true;
          resendBtn.innerHTML = 'E-mail reenviado!';
          
          // Mostra mensagem de sucesso
          alert('E-mail de confirmação reenviado com sucesso! Verifique sua caixa de entrada e spam.');
        } else {
          alert('Erro: ' + (data.message || 'Não foi possível reenviar o e-mail.'));
          resendBtn.disabled = false;
          resendBtn.textContent = 'Reenviar e-mail de confirmação';
        }
      })
      .catch(error => {
        console.error('Erro:', error);
        alert('Erro de conexão. Tente novamente.');
        resendBtn.disabled = false;
        resendBtn.textContent = 'Reenviar e-mail de confirmação';
      });
    });
  }
  
  // Animação de loading
  const style = document.createElement('style');
  style.textContent = '.loading-spinner{display:inline-block;width:20px;height:20px;border:3px solid rgba(255,255,255,0.3);border-top-color:white;border-radius:50%;animation:spin 1s linear infinite;margin-right:8px;}@keyframes spin{to{transform:rotate(360deg)}}';
  document.head.appendChild(style);
});