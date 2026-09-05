<?php
require_once 'auth.php';
require_once 'db.php';

// Filtro Contribuintes
$busca_contribuinte = trim($_GET['busca_contribuinte'] ?? '');
$sql_c = "SELECT * FROM contribuintes";
$params_c = [];
if (!empty($busca_contribuinte)) {
    $sql_c .= " WHERE nome_razao LIKE :busca OR cpf_cnpj LIKE :busca OR inscricao_municipal LIKE :busca";
    $params_c[':busca'] = "%{$busca_contribuinte}%";
}
$sql_c .= " ORDER BY id DESC";
$stmtC = $pdo->prepare($sql_c);
$stmtC->execute($params_c);
$contribuintes = $stmtC->fetchAll();

// Filtro DAMs
$busca_dam = trim($_GET['busca_dam'] ?? '');
$sql_d = "SELECT d.*, c.nome_razao FROM documentos_dam d JOIN contribuintes c ON d.contribuinte_id = c.id";
$params_d = [];
if (!empty($busca_dam)) {
    $sql_d .= " WHERE d.numero_dam LIKE :busca OR c.nome_razao LIKE :busca OR d.receita_tributo LIKE :busca";
    $params_d[':busca'] = "%{$busca_dam}%";
}
$sql_d .= " ORDER BY d.id DESC";
$stmtD = $pdo->prepare($sql_d);
$stmtD->execute($params_d);
$dams = $stmtD->fetchAll();
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
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand fw-bold" href="index.php">🏛️ Setor Tributário - Centro do Guilherme</a>
            <div class="text-white d-flex align-items-center gap-3">
                <small>👤 <strong><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Servidor') ?></strong></small>
                <a href="usuarios.php" class="btn btn-sm btn-light text-dark fw-bold">👥 Usuários</a>
                <a href="logout.php" class="btn btn-sm btn-outline-light">Sair 🚪</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <!-- CONTRIBUINTES -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Contribuintes Cadastrados</h3>
            <div>
                <a href="tributos.php" class="btn btn-outline-primary me-2">⚙️ Tributos</a>
                <a href="cadastrar_contribuinte.php" class="btn btn-success">+ Novo Contribuinte</a>
            </div>
        </div>

        <!-- FILTRO DE BUSCA CONTRIBUINTE -->
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="busca_contribuinte" class="form-control" placeholder="Buscar contribuinte por Nome, CPF/CNPJ ou Inscrição..." value="<?= htmlspecialchars($busca_contribuinte) ?>">
                <button type="submit" class="btn btn-primary">🔍 Pesquisar</button>
                <?php if ($busca_contribuinte): ?>
                    <a href="index.php" class="btn btn-outline-secondary">Limpar</a>
                <?php endif; ?>
            </div>
        </form>

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
                        <?php if (empty($contribuintes)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Nenhum contribuinte encontrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CONTROLE DE DAMS -->
        <h3 class="mb-3">Controle de DAMs Emitidos</h3>

        <!-- FILTRO DE BUSCA DAM -->
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="busca_dam" class="form-control" placeholder="Buscar por Nº do DAM, Contribuinte ou Tributo..." value="<?= htmlspecialchars($busca_dam) ?>">
                <button type="submit" class="btn btn-primary">🔍 Pesquisar</button>
                <?php if ($busca_dam): ?>
                    <a href="index.php" class="btn btn-outline-secondary">Limpar</a>
                <?php endif; ?>
            </div>
        </form>

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
                        <?php if (empty($dams)): ?>
                            <tr><td colspan="6" class="text-center py-3 text-muted">Nenhum documento DAM encontrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>