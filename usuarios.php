<?php
require_once 'auth.php';
require_once 'db.php';

$mensagem = '';
$erro = '';

if (isset($_GET['excluir'])) {
    $id_excluir = (int)$_GET['excluir'];
    if ($id_excluir !== $_SESSION['usuario_id']) {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $id_excluir]);
        header("Location: usuarios.php");
        exit;
    } else {
        $erro = "Você não pode excluir seu próprio usuário logado!";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome    = trim($_POST['nome']);
    $usuario = trim($_POST['usuario']);
    $senha   = trim($_POST['senha']);
    $cargo   = trim($_POST['cargo']);

    if (!empty($nome) && !empty($usuario) && !empty($senha)) {
        $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = :usuario");
        $stmtCheck->execute([':usuario' => $usuario]);
        
        if ($stmtCheck->fetch()) {
            $erro = "Nome de usuário já cadastrado!";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, usuario, senha, cargo) VALUES (:nome, :usuario, :senha, :cargo)");
            $stmt->execute([
                ':nome'    => $nome,
                ':usuario' => $usuario,
                ':senha'   => $senha_hash,
                ':cargo'   => $cargo
            ]);
            $mensagem = "Usuário cadastrado com sucesso!";
        }
    } else {
        $erro = "Preencha todos os campos obrigatórios!";
    }
}

$usuarios = $pdo->query("SELECT id, nome, usuario, cargo, criado_em FROM usuarios ORDER BY nome ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/dark-mode.css">
</head>
<body class="bg-light">

<div class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
    <button id="theme-toggle" class="theme-toggle-btn btn btn-sm btn-outline-secondary rounded-circle" title="Alternar Tema">🌙</button>
</div>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Gerenciamento de Usuários</h3>
        <a href="index.php" class="btn btn-secondary">Voltar ao Painel</a>
    </div>

    <?php if ($mensagem): ?><div class="alert alert-success py-2"><?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
    <?php if ($erro): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Cadastrar Novo Usuário</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nome Completo *</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Usuário para Login *</label>
                            <input type="text" name="usuario" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Senha *</label>
                            <input type="password" name="senha" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cargo / Função</label>
                            <input type="text" name="cargo" class="form-control" value="Atendente">
                        </div>
                        <button type="submit" class="btn btn-success w-100">Cadastrar Usuário</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Nome</th>
                                <th>Usuário</th>
                                <th>Cargo</th>
                                <th class="text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($u['nome']) ?></strong></td>
                                    <td><?= htmlspecialchars($u['usuario']) ?></td>
                                    <td><?= htmlspecialchars($u['cargo']) ?></td>
                                    <td class="text-center">
                                        <?php if ($u['id'] !== $_SESSION['usuario_id']): ?>
                                            <a href="usuarios.php?excluir=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Excluir este usuário?')">Excluir</a>
                                        <?php else: ?>
                                            <span class="badge bg-info text-dark">Atual</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/theme-toggle.js"></script>
</body>
</html>