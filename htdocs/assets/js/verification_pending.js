document.addEventListener('DOMContentLoaded', function() {
  // Contagem regressiva de 24 horas
  let hours = 23, minutes = 59, seconds = 59;
  const countdownElement = document.getElementById('countdown');
  
  if (!countdownElement) return;
  
  const countdown = setInterval(() => {
    if (seconds > 0) {
      seconds--;
    } else {
      seconds = 59;
      if (minutes > 0) {
        minutes--;
      } else {
        minutes = 59;
        if (hours > 0) {
          hours--;
        } else {
          clearInterval(countdown);
          if (countdownElement) {
            countdownElement.textContent = 'Tempo expirado. Solicite um novo e-mail.';
            countdownElement.style.color = '#dc2626';
            countdownElement.style.fontWeight = '600';
          }
          return;
        }
      }
    }
    
    if (countdownElement) {
      countdownElement.textContent = 
        `Tempo restante: ${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }
  }, 1000);
});