<?php
require_once 'auth.php';
require_once 'db.php';

$mensagem = '';
$erro = '';
$prestador = null;
$nfa_gerada = null;

// Lógica para Excluir NFA
if (isset($_GET['action']) && $_GET['action'] === 'excluir' && !empty($_GET['id_nfa'])) {
    $id_nfa = (int)$_GET['id_nfa'];
    $stmt_del = $pdo->prepare("DELETE FROM notas_fiscais_avulsas WHERE id = :id");
    $stmt_del->execute([':id' => $id_nfa]);
    
    header("Location: emitir_nfa.php?msg=deleted");
    exit;
}
// Consulta para listar as NFAs emitidas com os nomes dos prestadores (nome_razao)
$query_nfas = "SELECT nfa.*, p.nome_razao AS prestador_nome 
               FROM notas_fiscais_avulsas nfa 
               JOIN contribuintes p ON nfa.contribuinte_prestador_id = p.id 
               ORDER BY nfa.id DESC";
$stmt_nfas = $pdo->prepare($query_nfas);
$stmt_nfas->execute();
$lista_nfas = $stmt_nfas->fetchAll(PDO::FETCH_ASSOC);

// Busca o contribuinte prestador se informado o ID via GET
$prestador_id = isset($_GET['prestador_id']) ? (int)$_GET['prestador_id'] : 0;
if ($prestador_id > 0) {
    $stmtP = $pdo->prepare("SELECT * FROM contribuintes WHERE id = :id");
    $stmtP->execute([':id' => $prestador_id]);
    $prestador = $stmtP->fetch();
}

// Processa o cadastro da Nota Fiscal Avulsa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prestador_id_post = (int)$_POST['prestador_id'];
    $tomador_id = !empty($_POST['tomador_id']) ? (int)$_POST['tomador_id'] : null;
    $descricao_servico = trim($_POST['descricao_servico']);
    $valor_servico = (float)$_POST['valor_servico'];
    $aliquota_iss = (float)$_POST['aliquota_iss'];
    $valor_iss = ($valor_servico * $aliquota_iss) / 100;
    
    // Gera número único da NFA (Ex: NFA-2026-X8F9)
    $numero_nfa = 'NFA-' . date('Y') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 5));

    if ($prestador_id_post > 0 && !empty($descricao_servico) && $valor_servico > 0) {
        try {
            $pdo->beginTransaction();

           $stmtNFA = $pdo->prepare("
                INSERT INTO notas_fiscais_avulsas 
                (contribuinte_prestador_id, contribuinte_tomador_id, numero_nfa, descricao_servico, valor_servico, aliquota_iss, valor_iss) 
                VALUES (:p_id, :t_id, :num, :desc, :v_serv, :aliq, :v_iss)
            ");
            
            $stmtNFA->execute([
                ':p_id'   => $prestador_id_post,
                ':t_id'   => $tomador_id,
                ':num'    => $numero_nfa,
                ':desc'   => $descricao_servico,
                ':v_serv' => $valor_servico,
                ':aliq'   => $aliquota_iss,
                ':v_iss'  => $valor_iss
            ]);

            $nfa_id = $pdo->lastInsertId();
            $pdo->commit();

            // Busca dados completos para exibição do comprovante
            $stmtRes = $pdo->prepare("
                SELECT n.*, 
                       p.nome_razao AS prestador_nome, p.cpf_cnpj AS prestador_doc, p.endereco AS prestador_end,
                       t.nome_razao AS tomador_nome, t.cpf_cnpj AS tomador_doc
                FROM notas_fiscais_avulsas n
                INNER JOIN contribuintes p ON n.contribuinte_prestador_id = p.id
                LEFT JOIN contribuintes t ON n.contribuinte_tomador_id = t.id
                WHERE n.id = :id
            ");
            $stmtRes->execute([':id' => $nfa_id]);
            $nfa_gerada = $stmtRes->fetch();

            $mensagem = "Nota Fiscal Avulsa emitida com sucesso!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $erro = "Erro ao emitir NFA: " . $e->getMessage();
        }
    } else {
        $erro = "Preencha todos os campos obrigatórios e garanta que o valor do serviço seja maior que zero.";
    }
}

// Lista contribuintes para os selects
$todosContribuintes = $pdo->query("SELECT id, nome_razao, cpf_cnpj FROM contribuintes ORDER BY nome_razao ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emissão de Nota Fiscal Avulsa (NFA)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { background-color: #fff !important; color: #000 !important; }
            .card { border: 1px solid #000 !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-light">

<div class="position-fixed top-0 end-0 p-3 no-print" style="z-index: 1050;">
    <button id="theme-toggle" class="theme-toggle-btn btn btn-sm btn-outline-secondary rounded-circle" title="Alternar Tema">🌙</button>
</div>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h3><i class="bi bi-file-earmark-text text-primary"></i> Emissão de Nota Fiscal Avulsa</h3>
        <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar ao Painel</a>
    </div>

    <?php if ($mensagem): ?>
        <div class="alert alert-success py-2 no-print"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>
    <?php if ($erro): ?>
        <div class="alert alert-danger py-2 no-print"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <?php if ($nfa_gerada): ?>
        <!-- Visualização do Espelho da NFA Emitida -->
        <div class="card shadow-sm border-secondary mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">NOTA FISCAL AVULSA DE SERVIÇOS - Nº <?= $nfa_gerada['numero_nfa'] ?></h5>
                <span class="badge bg-success">STATUS: <?= $nfa_gerada['status'] ?></span>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>PRESTADOR DO SERVIÇO:</strong><br>
                        <?= htmlspecialchars($nfa_gerada['prestador_nome']) ?><br>
                        CPF/CNPJ: <?= htmlspecialchars($nfa_gerada['prestador_doc']) ?>
                    </div>
                    <div class="col-6">
                        <strong>TOMADOR DO SERVIÇO:</strong><br>
                        <?= $nfa_gerada['tomador_nome'] ? htmlspecialchars($nfa_gerada['tomador_nome']) : '<em>Não informado / Consumidor Final</em>' ?><br>
                        <?= $nfa_gerada['tomador_doc'] ? 'CPF/CNPJ: ' . htmlspecialchars($nfa_gerada['tomador_doc']) : '' ?>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <strong>DESCRIÇÃO DOS SERVIÇOS PRESTADOS:</strong>
                    <p class="border p-2 bg-light rounded text-dark mt-1"><?= nl2br(htmlspecialchars($nfa_gerada['descricao_servico'])) ?></p>
                </div>
                <div class="row text-center bg-light text-dark p-2 rounded mx-0">
                    <div class="col-md-4">
                        <small class="d-block text-muted">VALOR DOS SERVIÇOS</small>
                        <strong>R$ <?= number_format($nfa_gerada['valor_servico'], 2, ',', '.') ?></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="d-block text-muted">ALÍQUOTA ISS</small>
                        <strong><?= number_format($nfa_gerada['aliquota_iss'], 2, ',', '.') ?>%</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="d-block text-muted">IMPOSTO DEVIDO (ISSQN)</small>
                        <strong class="text-primary">R$ <?= number_format($nfa_gerada['valor_iss'], 2, ',', '.') ?></strong>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end no-print">
                <button onclick="window.print();" class="btn btn-primary"><i class="bi bi-printer"></i> Imprimir Nota</button>
                <a href="emitir_nfa.php" class="btn btn-outline-secondary">Nova Emissão</a>
            </div>
        </div>
    <?php else: ?>
        <!-- Formulário de Emissão -->
        <div class="card shadow-sm border-0 no-print">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Dados da Nota Fiscal</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Prestador do Serviço *</label>
                            <select name="prestador_id" class="form-select" required>
                                <option value="">Selecione o Prestador...</option>
                                <?php foreach ($todosContribuintes as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= ($prestador && $prestador['id'] == $c['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['nome_razao']) ?> (<?= htmlspecialchars($c['cpf_cnpj']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tomador do Serviço (Opcional)</label>
                            <select name="tomador_id" class="form-select">
                                <option value="">Não cadastrado / Consumidor Final</option>
                                <?php foreach ($todosContribuintes as $c): ?>
                                    <option value="<?= $c['id'] ?>">
                                        <?= htmlspecialchars($c['nome_razao']) ?> (<?= htmlspecialchars($c['cpf_cnpj']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição Detalhada do Serviço *</label>
                            <textarea name="descricao_servico" class="form-control" rows="3" placeholder="Informe a discriminação dos serviços prestados..." required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Valor Total do Serviço (R$) *</label>
                            <input type="number" step="0.01" min="0.01" id="valor_servico" name="valor_servico" class="form-control" placeholder="0,00" required oninput="calcularISS()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Alíquota ISS (%) *</label>
                            <input type="number" step="0.01" id="aliquota_iss" name="aliquota_iss" class="form-control" value="2.00" required oninput="calcularISS()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Valor Imposto ISS (R$)</label>
                            <input type="text" id="valor_iss" class="form-control bg-light" value="R$ 0,00" readonly>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-circle"></i> Emitir Nota Avulsa</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Seção: Notas Fiscais Avulsas Emitidas -->
<div class="card bg-dark text-white border-secondary mt-4">
    <div class="card-header border-secondary d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Notas Fiscais Avulsas Emitidas</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nº NFA</th>
                        <th>Prestador</th>
                        <th>Valor Serviço</th>
                        <th>Valor ISS</th>
                        <th>Data Emissão</th>
                        <th>Status</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($lista_nfas)): ?>
                        <?php foreach ($lista_nfas as $nfa): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($nfa['numero_nfa']) ?></strong></td>
                                <td><?= htmlspecialchars($nfa['prestador_nome']) ?></td>
                                <td>R$ <?= number_format($nfa['valor_servico'], 2, ',', '.') ?></td>
                                <td>R$ <?= number_format($nfa['valor_iss'], 2, ',', '.') ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($nfa['data_emissao'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $nfa['status'] === 'EMITIDA' ? 'success' : 'danger' ?>">
                                        <?= $nfa['status'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <!-- Botão Imprimir -->
                                    <a href="imprimir_nfa.php?id=<?= $nfa['id'] ?>" target="_blank" class="btn btn-sm btn-outline-info me-1" title="Imprimir Nota">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    <a href="editar_nfa.php?id=<?= $nfa['id'] ?>" class="btn btn-sm btn-outline-warning me-1" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="emitir_nfa.php?action=excluir&id_nfa=<?= $nfa['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Tem certeza que deseja excluir esta NFA?');" 
                                       title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Nenhuma Nota Fiscal Avulsa emitida até o momento.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
    <?php endif; ?>
</div>

<script>
function calcularISS() {
    const valorServico = parseFloat(document.getElementById('valor_servico').value) || 0;
    const aliquota = parseFloat(document.getElementById('aliquota_iss').value) || 0;
    const totalISS = (valorServico * aliquota) / 100;
    document.getElementById('valor_iss').value = 'R$ ' + totalISS.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
<script src="assets/js/theme-toggle.js"></script>
</body>
</html>