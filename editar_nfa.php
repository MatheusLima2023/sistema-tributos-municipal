<?php
require_once 'auth.php';
require_once 'db.php';

$mensagem = '';
$erro = '';

// Obtém o ID da NFA via GET
$id_nfa = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_nfa <= 0) {
    header("Location: emitir_nfa.php");
    exit;
}

// Busca a NFA existente
$stmt = $pdo->prepare("SELECT * FROM notas_fiscais_avulsas WHERE id = :id");
$stmt->execute([':id' => $id_nfa]);
$nfa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nfa) {
    header("Location: emitir_nfa.php?msg=notfound");
    exit;
}

// Busca a lista de contribuintes para os selects
$stmt_contrib = $pdo->query("SELECT id, nome_razao, cpf_cnpj FROM contribuintes ORDER BY nome_razao ASC");
$contribuintes = $stmt_contrib->fetchAll(PDO::FETCH_ASSOC);

// Processa a atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prestador_id = (int)$_POST['prestador_id'];
    $tomador_id = !empty($_POST['tomador_id']) ? (int)$_POST['tomador_id'] : null;
    $descricao_servico = trim($_POST['descricao_servico']);
    $valor_servico = (float)$_POST['valor_servico'];
    $aliquota_iss = (float)$_POST['aliquota_iss'];
    $valor_iss = ($valor_servico * $aliquota_iss) / 100;
    $status = $_POST['status'];

    if ($prestador_id > 0 && !empty($descricao_servico) && $valor_servico > 0) {
        try {
            $stmt_upd = $pdo->prepare("
                UPDATE notas_fiscais_avulsas 
                SET contribuinte_prestador_id = :p_id,
                    contribuinte_tomador_id = :t_id,
                    descricao_servico = :desc,
                    valor_servico = :v_serv,
                    aliquota_iss = :aliq,
                    valor_iss = :v_iss,
                    status = :status
                WHERE id = :id
            ");
            
            $stmt_upd->execute([
                ':p_id'   => $prestador_id,
                ':t_id'   => $tomador_id,
                ':desc'   => $descricao_servico,
                ':v_serv' => $valor_servico,
                ':aliq'   => $aliquota_iss,
                ':v_iss'  => $valor_iss,
                ':status' => $status,
                ':id'     => $id_nfa
            ]);

            header("Location: emitir_nfa.php?msg=updated");
            exit;
        } catch (Exception $e) {
            $erro = "Erro ao atualizar NFA: " . $e->getMessage();
        }
    } else {
        $erro = "Preencha todos os campos obrigatórios!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Nota Fiscal Avulsa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Editar NFA: <?= htmlspecialchars($nfa['numero_nfa']) ?></h2>
        <a href="emitir_nfa.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Voltar</a>
    </div>

    <?php if (!empty($erro)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <div class="card bg-dark text-white border-secondary">
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Prestador do Serviço *</label>
                        <select name="prestador_id" class="form-select bg-dark text-white border-secondary" required>
                            <?php foreach ($contribuintes as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $c['id'] == $nfa['contribuinte_prestador_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nome_razao']) ?> (<?= htmlspecialchars($c['cpf_cnpj']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tomador do Serviço (Opcional)</label>
                        <select name="tomador_id" class="form-select bg-dark text-white border-secondary">
                            <option value="">Não cadastrado / Consumidor Final</option>
                            <?php foreach ($contribuintes as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $c['id'] == $nfa['contribuinte_tomador_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nome_razao']) ?> (<?= htmlspecialchars($c['cpf_cnpj']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Descrição do Serviço *</label>
                        <textarea name="descricao_servico" class="form-control bg-dark text-white border-secondary" rows="3" required><?= htmlspecialchars($nfa['descricao_servico']) ?></textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Valor Total do Serviço (R$) *</label>
                        <input type="number" step="0.01" name="valor_servico" class="form-control bg-dark text-white border-secondary" value="<?= $nfa['valor_servico'] ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Alíquota ISS (%) *</label>
                        <input type="number" step="0.01" name="aliquota_iss" class="form-control bg-dark text-white border-secondary" value="<?= $nfa['aliquota_iss'] ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-select bg-dark text-white border-secondary" required>
                            <option value="EMITIDA" <?= $nfa['status'] === 'EMITIDA' ? 'selected' : '' ?>>EMITIDA</option>
                            <option value="CANCELADA" <?= $nfa['status'] === 'CANCELADA' ? 'selected' : '' ?>>CANCELADA</option>
                        </select>
                    </div>

                    <div class="col-12 text-end mt-4">
                        <button type="submit" class="btn btn-warning me-2"><i class="bi bi-check-lg me-1"></i> Salvar Alterações</button>
                        <a href="emitir_nfa.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>