<?php
session_start();
require_once 'db.php';

// Se já estiver logado, vai direto para o painel
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_input = trim($_POST['usuario']);
    $senha_input   = trim($_POST['senha']);

    if (!empty($usuario_input) && !empty($senha_input)) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1");
        $stmt->execute([':usuario' => $usuario_input]);
        $user = $stmt->fetch();

        // Para a senha inicial 'admin123' ou verificação com password_verify
        if ($user && ($senha_input === 'admin123' || password_verify($senha_input, $user['senha']))) {
            $_SESSION['usuario_id']   = $user['id'];
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['usuario_cargo'] = $user['cargo'];

            header('Location: index.php');
            exit;
        } else {
            $erro = 'Usuário ou senha incorretos!';
        }
    } else {
        $erro = 'Preencha todos os campos!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistema Tributário Municipal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="card shadow login-card border-0">
        <div class="card-body p-4 text-center">
            <img src="img.jpeg" alt="Logo Municipal" style="max-height: 80px;" class="mb-3">
            <h5 class="fw-bold mb-1">Prefeitura de Centro do Guilherme</h5>
            <p class="text-muted small mb-4">Setor Tributário & Arrecadação</p>

            <?php if ($erro): ?>
                <div class="alert alert-danger py-2 small" role="alert">
                    <?= $erro ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3 text-start">
                    <label class="form-label text-muted small fw-bold">Usuário</label>
                    <input type="text" name="usuario" class="form-control" placeholder="Digite seu usuário" required autofocus>
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label text-muted small fw-bold">Senha</label>
                    <input type="password" name="senha" class="form-control" placeholder="Digite sua senha" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold mt-2 py-2">Entrar no Sistema</button>
            </form>
        </div>
        <div class="card-footer bg-light text-center py-2">
            <small class="text-muted">Acesso restrito a servidores autorizados</small>
        </div>
    </div>
</body>
</html>