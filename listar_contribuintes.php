<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$busca = trim($_GET['busca'] ?? '');

$sql = "SELECT * FROM contribuintes WHERE 1=1";
$params = [];

if (!empty($busca)) {
    $sql .= " AND (nome_razao LIKE :busca OR cpf_cnpj LIKE :busca OR inscricao_municipal LIKE :busca)";
    $params[':busca'] = "%{$busca}%";
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$contribuintes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Contribuintes - Arrecadação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <style>
        .custom-table-header th {
            background-color: #0dcaf0 !important;
            color: #000000 !important;
            font-weight: 700 !important;
            border: none !important;
        }
        .search-input-custom {
            background-color: #212529 !important;
            color: #ffffff !important;
            border: 1px solid #495057 !important;
        }
        .search-input-custom::placeholder {
            color: #a6b0ba !important;
        }
    </style>
</head>
<body class="bg-light">

<!-- Barra Superior Limpa -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-building"></i> CENTRO DO GUILHERME - Arrecadação
        </a>
        <div class="d-flex align-items-center gap-2">
            <a href="index.php" class="btn btn-outline-light btn-sm fw-bold">
                <i class="bi bi-arrow-left"></i> Voltar ao Painel
            </a>
            <a href="cadastrar_contribuinte.php" class="btn btn-warning btn-sm fw-bold text-dark">
                <i class="bi bi-person-plus-fill me-1"></i> Novo Contribuinte
            </a>
            <button id="theme-toggle" class="theme-toggle-btn btn btn-sm btn-outline-light rounded-circle" title="Alternar Tema">🌙</button>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 mb-5">
    
    <!-- Cabeçalho da Página com Ícone Azul e Busca -->
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold mb-1">
                <i class="bi bi-people-fill text-primary me-2"></i>Gerenciamento de Contribuintes
            </h3>
            <p class="text-muted mb-0">Total de <?= count($contribuintes); ?> contribuinte(s) cadastrado(s)</p>
        </div>
        <div class="col-md-6">
            <form method="GET" action="listar_contribuintes.php" class="d-flex gap-2">
                <input type="text" name="busca" class="form-control search-input-custom" placeholder="Buscar por Nome, CPF/CNPJ ou Inscrição..." value="<?= htmlspecialchars($busca); ?>">
                <button type="submit" class="btn btn-primary fw-bold px-4">
                    <i class="bi bi-search me-1"></i> Buscar
                </button>
            </form>
        </div>
    </div>

    <!-- Tabela de Contribuintes com Barra Azul Claro -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="custom-table-header">
                            <th class="py-3 ps-3">Razão Social / Nome</th>
                            <th class="py-3">CPF / CNPJ</th>
                            <th class="py-3">Inscrição Municipal</th>
                            <th class="py-3">Endereço / Bairro</th>
                            <th class="py-3">Contato</th>
                            <th class="py-3 text-center">Ações Rápidas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($contribuintes) > 0): ?>
                            <?php foreach ($contribuintes as $c): ?>
                                <tr>
                                    <td class="ps-3">
                                        <strong><?= htmlspecialchars($c['nome_razao']); ?></strong><br>
                                        <small class="text-muted">ID: #<?= str_pad($c['id'], 4, '0', STR_PAD_LEFT); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary font-monospace"><?= htmlspecialchars($c['cpf_cnpj']); ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($c['inscricao_municipal'] ?? '-'); ?></td>
                                    <td><?= htmlspecialchars($c['endereco'] ?? '-'); ?></td>
                                    <td>
                                        <?php if (!empty($c['telefone'])): ?>
                                            <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($c['telefone']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="emissao_nfa.php?prestador_id=<?= $c['id']; ?>" class="btn btn-outline-primary" title="Emitir NFA">
                                                <i class="bi bi-file-earmark-text"></i> NFA
                                            </a>
                                            <a href="emissao_dam.php?contribuinte_id=<?= $c['id']; ?>" class="btn btn-outline-success" title="Emitir DAM">
                                                <i class="bi bi-card-checklist"></i> DAM
                                            </a>
                                            <a href="certidoes.php?contribuinte_id=<?= $c['id']; ?>" class="btn btn-outline-info" title="Emitir Certidão">
                                                <i class="bi bi-patch-check"></i> Certidão
                                            </a>
                                            <a href="editar_contribuinte.php?id=<?= $c['id']; ?>" class="btn btn-outline-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="deletar_contribuinte.php?id=<?= $c['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Tem certeza que deseja excluir?');" title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-person-x fs-2 d-block mb-2"></i>
                                    Nenhum contribuinte encontrado.
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