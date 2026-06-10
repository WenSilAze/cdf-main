<?php
session_start();

// Limpa todas as variáveis de sessão
$_SESSION = array();

// Se quiser destruir completamente a sessão, também limpe o cookie de sessão
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destrói a sessão
session_destroy();

// Redireciona para a página inicial
header('Location: ../index.html');
exit();
?>