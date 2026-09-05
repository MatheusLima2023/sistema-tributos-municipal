<?php
require_once 'auth.php';
require_once 'db.php';

$mensagem = '';
$erro = '';

// Exclusão
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

// Cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome    = trim($_POST['nome']);
    $usuario = trim($_POST['usuario']);
    $senha   = trim($_POST['senha']);
    $cargo   = trim($_POST['cargo']);

    if (!empty($nome) && !empty($usuario) && !empty($senha)) {
        // Verifica se já existe o usuário
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
    <title>Gerenciamento de Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Gerenciamento de Usuários</h3>
            <a href="index.php" class="btn btn-secondary">Voltar ao Painel</a>
        </div>

        <?php if ($mensagem): ?><div class="alert alert-success py-2"><?= $mensagem ?></div><?php endif; ?>
        <?php if ($erro): ?><div class="alert alert-danger py-2"><?= $erro ?></div><?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm">
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
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nome</th>
                                    <th>Usuário</th>
                                    <th>Cargo</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $u): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($u['nome']) ?></strong></td>
                                        <td><?= htmlspecialchars($u['usuario']) ?></td>
                                        <td><?= htmlspecialchars($u['cargo']) ?></td>
                                        <td>
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
</body>
</html>