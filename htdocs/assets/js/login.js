document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('loginForm');
  const submitBtn = document.getElementById('submitBtn');
  const googleBtn = document.getElementById('googleSignInBtn');
  
  // Desabilitar botão durante envio do formulário tradicional
  form.addEventListener('submit', function() {
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
      <span style="display:inline-block;width:16px;height:16px;border:2px solid white;border-top-color:transparent;border-radius:50%;animation:spin 1s linear infinite;margin-right:8px"></span>
      Entrando...
    `;
  });
  
  // Inicializa o Google Sign-In
  function initGoogleSignIn() {
    try {
      google.accounts.id.initialize({
        client_id: "1084282047796-4bsm7nnt6ppi9nevjk8r6ke8qna9p61r.apps.googleusercontent.com",
        callback: handleCredentialResponse,
        auto_select: false,
        cancel_on_tap_outside: true
      });
      
      google.accounts.id.renderButton(googleBtn, {
        theme: 'outline',
        size: 'large',
        width: '100%',
        text: 'signin_with',
        logo_alignment: 'center'
      });
    } catch (error) {
      console.error('Erro ao inicializar Google Sign-In:', error);
      
      // Botão de fallback se der erro
      googleBtn.innerHTML = `
        <div style="width:100%;height:100%;padding:14px;border:2px solid #e2e8f0;border-radius:12px;text-align:center;font-family:'Inter',sans-serif;font-weight:600;color:#1e293b;background:white;display:flex;align-items:center;justify-content:center;gap:12px;">
          <span style="display:inline-block;width:20px;height:20px;background:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><path fill="%23EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="%234285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="%23FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="%2334A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/><path fill="none" d="M0 0h48v48H0z"/></svg>') center/contain no-repeat;"></span>
          Google (erro)
        </div>
      `;
      googleBtn.style.opacity = '0.5';
    }
  }
  
  // Handler para resposta do Google
  function handleCredentialResponse(response) {
    if (!response || !response.credential) {
      console.error('Resposta do Google inválida');
      return;
    }
    
    googleBtn.innerHTML = `
      <div style="width:100%;height:100%;text-align:center;font-family:'Inter',sans-serif;font-weight:600;color:#1e293b;display:flex;align-items:center;justify-content:center;gap:12px;">
        <span style="display:inline-block;width:16px;height:16px;border:2px solid #1e293b;border-top-color:transparent;border-radius:50%;animation:spin 1s linear infinite;margin-right:8px"></span>
        Entrando com Google...
      </div>
    `;
    
    fetch('google-login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ credential: response.credential })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        window.location.href = '../dashboard/index.php';
      } else {
        alert('Erro ao fazer login com Google: ' + (data.message || 'Erro desconhecido'));
        location.reload();
      }
    })
    .catch(error => {
      console.error('Erro:', error);
      alert('Erro de conexão. Tente novamente.');
      location.reload();
    });
  }
  
  // Verifica se a API do Google carregou
  function checkGoogleAPI() {
    if (typeof google !== 'undefined' && google.accounts && google.accounts.id) {
      initGoogleSignIn();
    } else {
      setTimeout(checkGoogleAPI, 300);
    }
  }
  
  checkGoogleAPI();
  
  // Animação de loading
  const style = document.createElement('style');
  style.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
  document.head.appendChild(style);
});