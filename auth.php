<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se não houver usuário na sessão, redireciona para o login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
?>