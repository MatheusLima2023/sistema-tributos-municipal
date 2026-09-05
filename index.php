<?php
require_once 'db.php';

// Busca contribuintes
$contribuintes = $pdo->query("SELECT * FROM contribuintes ORDER BY id DESC")->fetchAll();

// Busca controle de DAMs
$dams = $pdo->query("SELECT d.*, c.nome_razao FROM documentos_dam d JOIN contribuintes c ON d.contribuinte_id = c.id ORDER BY d.id DESC")->fetchAll();

// Busca controle de Certidões
$certidoes = $pdo->query("SELECT cert.*, c.nome_razao FROM certidoes cert JOIN contribuintes c ON cert.contribuinte_id = c.id ORDER BY cert.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel Tributário Municipal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">🏛️ Setor Tributário - Centro do Guilherme</a>
        </div>
    </nav>

    <div class="container mb-5">
        <!-- CONTRIBUINTES -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Contribuintes Cadastrados</h3>
            <a href="cadastrar_contribuinte.php" class="btn btn-success">+ Novo Contribuinte</a>
        </div>
        <div class="card shadow-sm mb-5">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nome / Razão Social</th>
                            <th>CPF / CNPJ</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contribuintes as $c): ?>
                            <tr>
                                <td><?= $c['id'] ?></td>
                                <td><strong><?= htmlspecialchars($c['nome_razao']) ?></strong></td>
                                <td><?= htmlspecialchars($c['cpf_cnpj']) ?></td>
                                <td>
                                    <a href="gerar_dam.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">Gerar DAM</a>
                                    <a href="emitir_certidao.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-success">Emitir Certidão</a>
                                    <a href="cadastrar_contribuinte.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <a href="excluir_contribuinte.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja realmente excluir?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CONTROLE DE DAMS -->
        <h3 class="mb-3">Controle de DAMs Emitidos</h3>
        <div class="card shadow-sm mb-5">
            <div class="card-body p-0">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-secondary">
                        <tr>
                            <th>Nº DAM</th>
                            <th>Contribuinte</th>
                            <th>Vencimento</th>
                            <th>Valor Total</th>
                            <th>Status Pagamento</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dams as $d): ?>
                            <tr>
                                <td><strong><?= $d['numero_dam'] ?></strong></td>
                                <td><?= htmlspecialchars($d['nome_razao']) ?></td>
                                <td><?= date('d/m/Y', strtotime($d['data_vencimento'])) ?></td>
                                <td>R$ <?= number_format($d['valor_total'], 2, ',', '.') ?></td>
                                <td>
                                    <span class="badge bg-<?= $d['status'] == 'PAGO' ? 'success' : ($d['status'] == 'PENDENTE' ? 'warning text-dark' : 'danger') ?>">
                                        <?= $d['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="gerar_dam.php?id=<?= $d['contribuinte_id'] ?>&dam_id=<?= $d['id'] ?>" class="btn btn-sm btn-info text-white">Visualizar / Imprimir</a>
                                    <a href="gerar_dam.php?id=<?= $d['contribuinte_id'] ?>&edit_dam_id=<?= $d['id'] ?>" class="btn btn-sm btn-warning">Editar DAM</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>