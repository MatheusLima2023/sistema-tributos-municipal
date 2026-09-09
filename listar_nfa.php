<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$busca = trim($_GET['busca'] ?? '');

$sql = "SELECT * FROM notas_fiscais WHERE 1=1";
$params = [];

if (!empty($busca)) {
    $sql .= " AND (numero_nota LIKE :busca OR prestador_nome LIKE :busca OR tomador_nome LIKE :busca OR prestador_cpf_cnpj LIKE :busca)";
    $params[':busca'] = "%{$busca}%";
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$notas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notas Fiscais Avulsas Emitidas - Arrecadação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/dark-mode.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-building"></i> CENTRO DO GUILHERME - Arrecadação
        </a>
        <div class="d-flex align-items-center">
            <a href="index.php" class="btn btn-outline-light btn-sm me-2"><i class="bi bi-arrow-left"></i> Painel</a>
            <button id="theme-toggle" class="theme-toggle-btn btn btn-sm btn-outline-light rounded-circle">🌙</button>
        </div>
    </div>
</nav>

<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0"><i class="bi bi-receipt me-2"></i>Notas Fiscais Avulsas Emitidas</h3>
        <a href="emissao_nfa.php" class="btn btn-success fw-bold"><i class="bi bi-plus-lg"></i> Nova NFA</a>
    </div>

    <!-- Barra de Pesquisa -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="listar_nfa.php" class="row g-2">
                <div class="col-md-10">
                    <input type="text" name="busca" class="form-control" placeholder="Buscar por Nº da Nota, Prestador, Tomador ou CPF/CNPJ..." value="<?= htmlspecialchars($busca); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="bi bi-search"></i> Pesquisar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Notas -->
    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Nº Nota</th>
                            <th>Data Emissão</th>
                            <th>Prestador</th>
                            <th>Tomador (Cliente)</th>
                            <th>Valor Total</th>
                            <th>ISS</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($notas) > 0): ?>
                            <?php foreach ($notas as $n): ?>
                                <tr>
                                    <td><span class="badge bg-primary">#<?= htmlspecialchars($n['numero_nota']); ?></span></td>
                                    <td><?= date('d/m/Y H:i', strtotime($n['data_emissao'])); ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($n['prestador_nome']); ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($n['prestador_cpf_cnpj']); ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($n['tomador_nome']); ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($n['tomador_cpf_cnpj']); ?></small>
                                    </td>
                                    <td class="fw-bold">R$ <?= number_format($n['valor_total'], 2, ',', '.'); ?></td>
                                    <td class="text-success fw-bold">R$ <?= number_format($n['valor_iss'], 2, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <a href="visualizar_nfa.php?id=<?= $n['id']; ?>" class="btn btn-sm btn-info text-white" title="Visualizar / Imprimir">
                                            <i class="bi bi-printer-fill"></i> Imprimir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Nenhuma Nota Fiscal emitida até o momento.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-toggle.js"></script>
</body>
</html>