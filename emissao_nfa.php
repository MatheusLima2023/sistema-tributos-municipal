<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$mensagem = '';
$tipoMensagem = '';

// Processar emissão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prestadorId = !empty($_POST['prestador_id']) ? intval($_POST['prestador_id']) : null;
    $prestadorNome = trim($_POST['prestador_nome'] ?? '');
    $prestadorCpfCnpj = trim($_POST['prestador_cpf_cnpj'] ?? '');

    $tomadorNome = trim($_POST['tomador_nome'] ?? '');
    $tomadorCpfCnpj = trim($_POST['tomador_cpf_cnpj'] ?? '');

    $descricaoServico = trim($_POST['descricao_servico'] ?? '');
    $valorTotal = floatval(str_replace(',', '.', $_POST['valor_total'] ?? 0));
    $aliquota = floatval($_POST['aliquota'] ?? 2.00);
    $valorIss = ($valorTotal * $aliquota) / 100;
    $dataEmissao = date('Y-m-d H:i:s');
    $numeroNota = date('Ym') . rand(1000, 9999);

    try {
        $sql = "INSERT INTO notas_fiscais (
                    numero_nota, prestador_id, prestador_nome, prestador_cpf_cnpj, 
                    tomador_nome, tomador_cpf_cnpj, descricao_servico, 
                    valor_total, aliquota_iss, valor_iss, data_emissao
                ) VALUES (
                    :numero_nota, :prestador_id, :prestador_nome, :prestador_cpf_cnpj, 
                    :tomador_nome, :tomador_cpf_cnpj, :descricao_servico, 
                    :valor_total, :aliquota_iss, :valor_iss, :data_emissao
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':numero_nota' => $numeroNota,
            ':prestador_id' => $prestadorId,
            ':prestador_nome' => $prestadorNome,
            ':prestador_cpf_cnpj' => $prestadorCpfCnpj,
            ':tomador_nome' => $tomadorNome,
            ':tomador_cpf_cnpj' => $tomadorCpfCnpj,
            ':descricao_servico' => $descricaoServico,
            ':valor_total' => $valorTotal,
            ':aliquota_iss' => $aliquota,
            ':valor_iss' => $valorIss,
            ':data_emissao' => $dataEmissao
        ]);

        $idNota = $pdo->lastInsertId();
        header("Location: visualizar_nfa.php?id=" . $idNota);
        exit;

    } catch (PDOException $e) {
        $mensagem = "Erro ao emitir Nota Fiscal: " . $e->getMessage();
        $tipoMensagem = "danger";
    }
}

// Carregar lista de contribuintes
$contribuintes = $pdo->query("SELECT id, nome_razao, cpf_cnpj FROM contribuintes ORDER BY nome_razao ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emitir NFA - Prefeitura Municipal</title>
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
            <a href="listar_nfa.php" class="btn btn-outline-light btn-sm me-2"><i class="bi bi-receipt"></i> Ver Notas Emitidas</a>
            <button id="theme-toggle" class="theme-toggle-btn btn btn-sm btn-outline-light rounded-circle">🌙</button>
        </div>
    </div>
</nav>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <?php if (!empty($mensagem)): ?>
                <div class="alert alert-<?= $tipoMensagem; ?> alert-dismissible fade show">
                    <?= htmlspecialchars($mensagem); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0 fw-bold"><i class="bi bi-file-earmark-plus me-2"></i>Emitir Nota Fiscal Avulsa (NFA)</h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="emissao_nfa.php">

                        <!-- Dados Prestador -->
                        <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-person-badge me-1"></i> Prestador do Serviço</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-12 mb-2">
                                <label class="form-label fw-bold">Selecionar Contribuinte Cadastrado</label>
                                <select id="select_contribuinte" class="form-select">
                                    <option value="">-- Selecione para preencher automaticamente --</option>
                                    <?php foreach ($contribuintes as $c): ?>
                                        <option value="<?= $c['id']; ?>" data-nome="<?= htmlspecialchars($c['nome_razao']); ?>" data-doc="<?= htmlspecialchars($c['cpf_cnpj']); ?>">
                                            <?= htmlspecialchars($c['nome_razao']); ?> (CPF/CNPJ: <?= htmlspecialchars($c['cpf_cnpj']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <input type="hidden" name="prestador_id" id="prestador_id">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nome / Razão Social</label>
                                <input type="text" name="prestador_nome" id="prestador_nome" class="form-control" required placeholder="Razão social">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">CPF / CNPJ</label>
                                <input type="text" name="prestador_cpf_cnpj" id="prestador_cpf_cnpj" class="form-control" required placeholder="000.000.000-00">
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Dados Tomador -->
                        <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-person me-1"></i> Tomador do Serviço (Cliente)</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nome / Razão Social</label>
                                <input type="text" name="tomador_nome" class="form-control" required placeholder="Nome do tomador">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">CPF / CNPJ</label>
                                <input type="text" name="tomador_cpf_cnpj" class="form-control" required placeholder="000.000.000-00">
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Serviços -->
                        <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-cash-stack me-1"></i> Descrição e Valores</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Descrição do Serviço Prestado</label>
                                <textarea name="descricao_servico" class="form-control" rows="3" required placeholder="Descreva o serviço..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Valor Total (R$)</label>
                                <input type="text" name="valor_total" class="form-control" required placeholder="0.00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Alíquota ISS (%)</label>
                                <input type="number" step="0.01" name="aliquota" class="form-control" value="2.00" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3">
                            <a href="listar_nfa.php" class="btn btn-secondary btn-lg">Cancelar</a>
                            <button type="submit" class="btn btn-success btn-lg fw-bold"><i class="bi bi-check-circle"></i> Emitir NFA</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-toggle.js"></script>
<script>
document.getElementById('select_contribuinte').addEventListener('change', function() {
    let option = this.options[this.selectedIndex];
    if (this.value !== '') {
        document.getElementById('prestador_id').value = this.value;
        document.getElementById('prestador_nome').value = option.getAttribute('data-nome');
        document.getElementById('prestador_cpf_cnpj').value = option.getAttribute('data-doc');
    } else {
        document.getElementById('prestador_id').value = '';
        document.getElementById('prestador_nome').value = '';
        document.getElementById('prestador_cpf_cnpj').value = '';
    }
});
</script>
</body>
</html>