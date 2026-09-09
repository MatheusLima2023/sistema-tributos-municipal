<?php
require_once 'auth.php';
require_once 'db.php';

if (isset($_GET['excluir'])) {
    $stmt = $pdo->prepare("DELETE FROM tributos WHERE id = :id");
    $stmt->execute([':id' => $_GET['excluir']]);
    header("Location: tributos.php");
    exit;
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Tributos e Taxas</title>
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
        <h3>Tributos e Taxas Municipais</h3>
        <a href="index.php" class="btn btn-secondary">Voltar ao Painel</a>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
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
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nome do Tributo</th>
                                <th>Alíquota Padrão</th>
                                <th class="text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tributos as $t): ?>
                                <tr>
                                    <td><?= $t['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($t['nome']) ?></strong></td>
                                    <td><?= number_format($t['aliquota_padrao'], 2, ',', '.') ?>%</td>
                                    <td class="text-center">
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

<script src="assets/js/theme-toggle.js"></script>
</body>
</html>