document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('registerForm');
  const password = document.getElementById('password');
  const confirmPassword = document.getElementById('confirm_password');
  const submitBtn = document.getElementById('submitBtn');
  
  // Validação em tempo real (verifica se elementos existem)
  if (password && confirmPassword) {
    password.addEventListener('input', validatePasswords);
    confirmPassword.addEventListener('input', validatePasswords);
  }
  
  function validatePasswords() {
    const passValue = password.value;
    const confirmValue = confirmPassword.value;
    
    if (confirmValue && passValue !== confirmValue) {
      confirmPassword.classList.add('error');
    } else {
      confirmPassword.classList.remove('error');
    }
  }
  
  // Desabilitar botão durante envio (verifica se forma existe)
  if (form && submitBtn) {
    form.addEventListener('submit', function() {
      submitBtn.disabled = true;
      submitBtn.innerHTML = `
        <span style="display:inline-block;width:16px;height:16px;border:2px solid white;border-top-color:transparent;border-radius:50%;animation:spin 1s linear infinite;margin-right:8px"></span>
        Cadastrando...
      `;
    });
  }
  
  // Animação de loading
  const style = document.createElement('style');
  style.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
  document.head.appendChild(style);
});