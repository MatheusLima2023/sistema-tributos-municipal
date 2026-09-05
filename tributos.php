<?php
require_once 'db.php';

// Exclusão
if (isset($_GET['excluir'])) {
    $stmt = $pdo->prepare("DELETE FROM tributos WHERE id = :id");
    $stmt->execute([':id' => $_GET['excluir']]);
    header("Location: tributos.php");
    exit;
}

// Cadastro / Edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $aliquota = (float)$_POST['aliquota_padrao'];

    if (!empty($nome)) {
        $stmt = $pdo->prepare("INSERT INTO tributos (nome, aliquota_padrao) VALUES (:nome, :aliquota)");
        $stmt->execute([':nome' => $nome, ':aliquota' => $aliquota]);
    }
    header("Location: tributos.php");
    exit;
}

$tributos = $pdo->query("SELECT * FROM tributos ORDER BY nome ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Tributos e Taxas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Tributos e Taxas Municipais</h3>
            <a href="index.php" class="btn btn-secondary">Voltar ao Painel</a>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Novo Tributo</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nome do Tributo / Taxa *</label>
                                <input type="text" name="nome" class="form-control" placeholder="Ex: ITBI" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alíquota Padrão (%)</label>
                                <input type="number" step="0.01" name="aliquota_padrao" class="form-control" value="0.00">
                            </div>
                            <button type="submit" class="btn btn-success w-100">Cadastrar Tributo</button>
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
                                    <th>ID</th>
                                    <th>Nome do Tributo</th>
                                    <th>Alíquota Padrão</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tributos as $t): ?>
                                    <tr>
                                        <td><?= $t['id'] ?></td>
                                        <td><strong><?= htmlspecialchars($t['nome']) ?></strong></td>
                                        <td><?= number_format($t['aliquota_padrao'], 2, ',', '.') ?>%</td>
                                        <td>
                                            <a href="tributos.php?excluir=<?= $t['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja excluir este tributo?')">Excluir</a>
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